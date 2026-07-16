import os
import re
import json
import time
import requests
from datetime import datetime

# Setup directories
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
LOG_FILE = os.path.join(BASE_DIR, 'log', 'debug', 'debug.log')

# Load Config
config_path = os.path.join(BASE_DIR, 'config.json')
with open(config_path, 'r') as f:
    config = json.load(f)

API_URL = config.get('api_url', 'http://127.0.0.1:8000/api/v1/licenses/sync')
SYNC_INTERVAL = config.get('sync_interval_seconds', 10)

# Mappings
EVENT_MAP = {
    'OUT': 'checkout',
    'IN': 'checkin',
    'DENIED': 'denied',
    'LOST': 'lost',
    'EXPIRED': 'expired'
}

# Regex to parse FlexLM log line
# Example: 14:30:12 (schlumb) OUT: "DATA_ANALYZER" ahmad.ramadhan@GEO-WS01
# Or: 14:36:00 (schlumb) DENIED: "DATA_ANALYZER" joko.wibowo@RESV-PC01  (Licensed number of...)
log_pattern = re.compile(
    r'^(\d{2}:\d{2}:\d{2})\s+\((\w+)\)\s+(OUT|IN|DENIED|LOST|EXPIRED):\s+"([^"]+)"\s+([^@\s]+)@(\S+)'
)

# State Management
# active_users[vendor_name][feature_name] = set(usernames)
active_users = {}
# events_queue[vendor_name] = [event_dict, ...]
events_queue = {}

# Initialize State
for v in config['vendors']:
    vendor_name = v['vendor_name']
    active_users[vendor_name] = {f['name']: set() for f in v['features']}
    events_queue[vendor_name] = []

def get_ip_address(username, ip_prefixes):
    if not ip_prefixes:
        return "127.0.0.1"
    prefix = ip_prefixes[hash(username) % len(ip_prefixes)]
    octet = (sum(ord(c) for c in username) % 150) + 50
    return f"{prefix}{octet}"

def parse_line(line):
    match = log_pattern.match(line.strip())
    if not match:
        return None
    
    time_str, vendor_name, event_raw, feature_name, username, hostname = match.groups()
    
    # We only care if vendor is configured
    vendor_cfg = next((v for v in config['vendors'] if v['vendor_name'] == vendor_name), None)
    if not vendor_cfg:
        return None
        
    event_type = EVENT_MAP.get(event_raw, 'checkout')
    
    # Update Active Users State
    if feature_name in active_users.get(vendor_name, {}):
        if event_type == 'checkout':
            active_users[vendor_name][feature_name].add(username)
        elif event_type in ['checkin', 'lost', 'expired']:
            active_users[vendor_name][feature_name].discard(username)

    # Format recorded_at with today's date
    today_str = datetime.now().strftime("%Y-%m-%d")
    recorded_at = f"{today_str} {time_str}"
    
    ip_address = get_ip_address(username, vendor_cfg.get('ip_prefixes', []))
    
    event_data = {
        "event_type": event_type,
        "feature_name": feature_name,
        "username": username,
        "hostname": hostname,
        "ip_address": ip_address,
        "recorded_at": recorded_at,
        "raw_log": line.strip()
    }
    
    events_queue[vendor_name].append(event_data)
    return event_data

def send_sync(vendor_cfg):
    vendor_name = vendor_cfg['vendor_name']
    features_usage = []
    
    for f in vendor_cfg['features']:
        feat_name = f['name']
        used = len(active_users[vendor_name].get(feat_name, set()))
        features_usage.append({
            "feature_name": feat_name,
            "version": f.get('version', '1.0'),
            "total_seats": f['total_seats'],
            "used_seats": used
        })
        
    events = events_queue[vendor_name]
    events_queue[vendor_name] = [] # Clear queue after fetch
    
    payload = {
        "server_hostname": vendor_cfg['server_hostname'],
        "vendor_name": vendor_name,
        "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "features_usage": features_usage,
        "events": events
    }
    
    try:
        response = requests.post(API_URL, json=payload, headers={"Content-Type": "application/json"})
        if response.status_code == 200:
            print(f"[{datetime.now().strftime('%H:%M:%S')}] Synced {vendor_name} successfully: {response.json()}")
        else:
            print(f"[{datetime.now().strftime('%H:%M:%S')}] Failed to sync {vendor_name}: {response.status_code} - {response.text}")
            # Put events back in queue if failed
            events_queue[vendor_name].extend(events)
    except Exception as e:
        print(f"[{datetime.now().strftime('%H:%M:%S')}] Error syncing {vendor_name}: {e}")
        events_queue[vendor_name].extend(events)

def main():
    print(f"FlexLM Python Sync Agent Started.")
    print(f"Tailing log file: {LOG_FILE}")
    print(f"Target API: {API_URL}")
    print(f"Sync Interval: {SYNC_INTERVAL} seconds")
    
    # Wait for log file to be created
    while not os.path.exists(LOG_FILE):
        print("Waiting for log file to be created by generator...")
        time.sleep(2)
        
    # Read existing lines to build initial state
    print("Parsing existing logs to rebuild state...")
    with open(LOG_FILE, 'r') as f:
        lines = f.readlines()
        for line in lines:
            parse_line(line)
            
    # Clear the initial events queue so we don't spam the API with historic logs
    for vendor_name in events_queue:
        events_queue[vendor_name] = []
        
    print("State rebuilt. Starting live tailing and syncing...")
    
    # Tail Log File
    f = open(LOG_FILE, 'r')
    f.seek(0, os.SEEK_END)
    
    last_sync_time = time.time()
    
    while True:
        line = f.readline()
        if line:
            parse_line(line)
            
        # Check if it's time to sync
        current_time = time.time()
        if current_time - last_sync_time >= SYNC_INTERVAL:
            for vendor_cfg in config['vendors']:
                send_sync(vendor_cfg)
            last_sync_time = current_time
            
        if not line:
            time.sleep(0.2)

if __name__ == "__main__":
    main()
