# Monitoring Guide — Petrotechnical Platform UC2

## Overview

The platform collects operational metrics via its own time-series tables and should be supplemented with infrastructure-level monitoring. This document outlines the recommended monitoring stack.

---

## Current Built-in Monitoring (Application Layer)

The application already provides:

| Feature | How |
|---|---|
| VM Utilisation (CPU/Memory/GPU/Disk/Network) | `vm_metrics` table, queried via `VmMetricService` |
| Storage Usage Trends (30-day) | `storage_metrics` table, queried via `StorageMetricService` |
| License Expiry Monitoring | `licenses.expiry_date` index, surfaced in Analytics dashboard |
| Active VDI Sessions | `vdi_sessions` with `status = 'active'` |
| Ticket Queue Health | `tickets` by status/priority in Analytics |

---

## Recommended Infrastructure Monitoring Stack

```
                  ┌──────────────────────┐
   VM agents ───► │  Prometheus (scrape) │
   App metrics    │  + Alertmanager      │
   PostgreSQL      └──────────┬───────────┘
   exporter                   │
                              ▼
                    ┌─────────────────┐
                    │  Grafana        │
                    │  Dashboards     │
                    └─────────────────┘
                              │
                    ┌─────────────────┐
                    │  Alert channels │
                    │  (Email / Slack)│
                    └─────────────────┘
```

---

## 1. Prometheus Setup

### Install Prometheus

```bash
# Download
wget https://github.com/prometheus/prometheus/releases/download/v2.50.1/prometheus-2.50.1.linux-amd64.tar.gz
tar xf prometheus-2.50.1.linux-amd64.tar.gz
sudo mv prometheus-2.50.1.linux-amd64 /opt/prometheus

# prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  - job_name: 'petrotech-app'
    static_configs:
      - targets: ['localhost:9101']  # Laravel metrics exporter

  - job_name: 'postgres'
    static_configs:
      - targets: ['localhost:9187']  # postgres_exporter

  - job_name: 'node'
    static_configs:
      - targets: ['localhost:9100']  # node_exporter on each VM host

  - job_name: 'vm-hosts'
    file_sd_configs:
      - files: ['/opt/prometheus/targets/vm_hosts.json']
        refresh_interval: 60s
```

### PostgreSQL Exporter

```bash
# Install postgres_exporter
wget https://github.com/prometheus-community/postgres_exporter/releases/latest/download/postgres_exporter_linux_amd64.tar.gz
tar xf postgres_exporter_linux_amd64.tar.gz

# Set connection
export DATA_SOURCE_NAME="postgresql://petrotech_user:password@localhost:5432/petrotech?sslmode=disable"
./postgres_exporter
```

### Node Exporter (per VM host)

```bash
wget https://github.com/prometheus/node_exporter/releases/latest/download/node_exporter-1.7.0.linux-amd64.tar.gz
tar xf node_exporter-1.7.0.linux-amd64.tar.gz
./node_exporter   # exposes :9100/metrics
```

---

## 2. Laravel Application Metrics

Add a `/metrics` endpoint using `spatie/prometheus-laravel` or a custom controller:

```php
// routes/web.php — add outside auth middleware for Prometheus scraping
Route::get('/metrics', function () {
    // Active sessions
    $activeSessions = \App\Models\VdiSession::where('status', 'active')->count();
    // Open tickets
    $openTickets = \App\Models\Ticket::where('status', 'open')->count();
    // Expiring licenses (30 days)
    $expiringLicenses = \App\Models\License::where('expiry_date', '<=', now()->addDays(30))->count();
    // Average CPU (last hour)
    $avgCpu = \App\Models\VmMetric::where('recorded_at', '>=', now()->subHour())->avg('cpu_utilisation') ?? 0;

    $output = "# HELP petrotech_active_vdi_sessions Active VDI sessions\n";
    $output .= "# TYPE petrotech_active_vdi_sessions gauge\n";
    $output .= "petrotech_active_vdi_sessions {$activeSessions}\n\n";
    $output .= "petrotech_open_tickets {$openTickets}\n";
    $output .= "petrotech_expiring_licenses {$expiringLicenses}\n";
    $output .= "petrotech_avg_cpu_pct " . round($avgCpu, 2) . "\n";

    return response($output, 200, ['Content-Type' => 'text/plain']);
})->middleware('throttle:60,1');
```

---

## 3. Grafana Dashboards

### Recommended Dashboards

#### Dashboard 1: VM Utilisation Overview
- **Panel: CPU Utilisation per VM** — PromQL: `avg by(vm_name) (vm_cpu_utilisation_pct)`
- **Panel: Memory Utilisation** — gauge panels per VM
- **Panel: GPU Utilisation** — filter by `has_gpu = true`
- **Panel: Network I/O (in/out MB/s)** — time-series from node_exporter
- **Threshold alerts**: CPU > 85% for 5 min → warning; > 95% → critical

#### Dashboard 2: Storage Monitor
- **Panel: Total Platform Storage Used (TB)** — sum of `storage_metric.used_space_gb / 1024`
- **Panel: Per-Device Usage %** — bar chart per storage device
- **Panel: 30-day Usage Trend** — time-series per device
- **Alert**: Any device > 85% usage → send Slack alert

#### Dashboard 3: VDI Sessions
- **Panel: Active Sessions** — `petrotech_active_vdi_sessions` gauge
- **Panel: Sessions per VM** — time-series
- **Panel: Average Session Duration** — histogram of `vdi_sessions.duration_minutes`
- **Alert**: A single VM with > 10 concurrent sessions → investigate

#### Dashboard 4: Application Health
- **Panel: HTTP Response Time** — Nginx access log processing
- **Panel: Error Rate (5xx)** — from Nginx + Laravel log
- **Panel: Open Tickets by Priority** — from PostgreSQL exporter custom query
- **Panel: Expiring Licenses** — `petrotech_expiring_licenses` alert when > 0

---

## 4. VM Metrics Ingestion

The application currently **simulates** metrics via seeded data. In production, metrics should be ingested from real hypervisor telemetry:

### Option A: Agent-based Push (Recommended for production)
```
ESXi / KVM / Hyper-V  →  Telegraf agent  →  API endpoint  →  vm_metrics table
```

```php
// Add a POST endpoint in routes/api.php
Route::post('/metrics/vm', function (Request $request) {
    $validated = $request->validate([
        'vm_id'              => 'required|exists:vms,id',
        'cpu_utilisation'    => 'required|numeric',
        'memory_utilisation' => 'required|numeric',
    ]);
    \App\Models\VmMetric::create([...$validated, 'recorded_at' => now()]);
    return response()->json(['ok' => true]);
})->middleware('api_token');
```

### Option B: Pull via Scheduled Command
```php
// app/Console/Commands/CollectVmMetrics.php
// Schedule in bootstrap/app.php:
Schedule::command('metrics:collect-vms')->everyMinute();
```

---

## 5. Alert Rules (Prometheus Alertmanager)

```yaml
# alerts.yml
groups:
  - name: petrotech
    rules:
      - alert: HighCpuUsage
        expr: vm_cpu_utilisation_pct > 90
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High CPU on {{ $labels.vm_name }}"

      - alert: StorageCapacityCritical
        expr: storage_usage_percentage > 90
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Storage {{ $labels.device_name }} is {{ $value }}% full"

      - alert: LicenseExpiringSoon
        expr: petrotech_expiring_licenses > 0
        for: 1h
        labels:
          severity: warning
        annotations:
          summary: "{{ $value }} license(s) expiring within 30 days"

      - alert: AppDown
        expr: up{job="petrotech-app"} == 0
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "Petrotechnical Platform application is down"
```

---

## 6. Log Monitoring

```bash
# Key log files to monitor
/var/www/petrotech/storage/logs/laravel-YYYY-MM-DD.log  # Application errors
/var/log/nginx/access.log                               # HTTP requests
/var/log/nginx/error.log                               # Nginx errors
/var/log/php/petrotech-fpm.log                         # PHP-FPM issues
/var/log/postgresql/postgresql-*.log                   # Database errors

# Ship to centralized logging (ELK / Loki)
# Recommended: use Promtail + Loki for log aggregation
```

---

## 7. Health Check Endpoint

```php
// routes/web.php — simple health check (no auth required)
Route::get('/health', function () {
    $db = true;
    try { DB::connection()->getPdo(); } catch (\Exception $e) { $db = false; }

    return response()->json([
        'status'    => $db ? 'ok' : 'degraded',
        'database'  => $db ? 'connected' : 'error',
        'timestamp' => now()->toISOString(),
        'version'   => config('app.version', '1.0'),
    ], $db ? 200 : 503);
});
```

Use this endpoint in:
- **Nginx upstream health checks**
- **Load balancer health checks** 
- **Kubernetes liveness probes**
- **Uptime monitoring tools** (UptimeRobot, Checkly, etc.)
