import json
import time
import random
import os
from datetime import datetime

# Setup directories
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
LOG_DIR = os.path.join(BASE_DIR, 'log', 'debug')
if not os.path.exists(LOG_DIR):
    os.makedirs(LOG_DIR)

LOG_FILE = os.path.join(LOG_DIR, 'debug.log')

# Load Config
config_path = os.path.join(BASE_DIR, 'config.json')
with open(config_path, 'r') as f:
    config = json.load(f)

# Internal state to track checkouts so we can simulate realistic checkins
# Format: {vendor_name: {feature_name: [user_host_tuple, ...]}}
active_sessions = {}
for v in config['vendors']:
    active_sessions[v['vendor_name']] = {}
    for f in v['features']:
        active_sessions[v['vendor_name']][f['name']] = []

event_weights = ['OUT'] * 60 + ['IN'] * 30 + ['DENIED'] * 5 + ['LOST'] * 5

def generate_log_line():
    now = datetime.now().strftime("%H:%M:%S")
    vendor = random.choice(config['vendors'])
    vendor_name = vendor['vendor_name']
    feature = random.choice(vendor['features'])
    feat_name = feature['name']
    total_seats = feature['total_seats']
    
    event = random.choice(event_weights)
    
    # If IN or LOST, we should ideally pick an active session if available
    current_sessions = active_sessions[vendor_name][feat_name]
    
    if event in ['IN', 'LOST'] and len(current_sessions) > 0:
        user_host = random.choice(current_sessions)
        current_sessions.remove(user_host)
        user, host = user_host
    else:
        # If no session to check in, force an OUT or just pick random user/host for a DENIED
        if event in ['IN', 'LOST']:
            event = 'OUT'
            
        user = random.choice(vendor['users'])
        host = random.choice(vendor['hosts'])
        
        if event == 'OUT':
            if len(current_sessions) < total_seats:
                current_sessions.append((user, host))
            else:
                event = 'DENIED'
                
    # Build log string
    if event in ['OUT', 'IN']:
        log_str = f'{now} ({vendor_name}) {event}: "{feat_name}" {user}@{host}'
    elif event == 'DENIED':
        denied_reasons = [
            f'(Licensed number of users already reached. MAX={total_seats})',
            f'(User not on INCLUDE list)'
        ]
        reason = random.choices(denied_reasons, weights=[70, 30])[0]
        log_str = f'{now} ({vendor_name}) DENIED: "{feat_name}" {user}@{host}  {reason}'
    elif event == 'LOST':
        log_str = f'{now} ({vendor_name}) LOST: "{feat_name}" {user}@{host}  (connection lost, heartbeat timeout)'
    else:
        log_str = f'{now} ({vendor_name}) {event}: "{feat_name}" {user}@{host}'
        
    return log_str

print(f"Starting FlexLM Dummy Generator...")
print(f"Writing to {LOG_FILE}")

# Initial writes to simulate daemon startup (optional but good for realism)
with open(LOG_FILE, 'a') as f:
    f.write(f"10:00:00 (lmgrd) FlexNet Licensing (v11.16.2.1 build 246538 x64_n86_mac10)\n")
    for v in config['vendors']:
        f.write(f"10:00:00 (lmgrd) Starting vendor daemon {v['vendor_name']}\n")
    f.flush()

while True:
    log_line = generate_log_line()
    with open(LOG_FILE, 'a') as f:
        f.write(log_line + "\n")
    
    print(f"Generated: {log_line}")
    # Sleep randomly between 1 and 4 seconds
    time.sleep(random.uniform(1.0, 4.0))
