# Entity Relationship Document — Petrotechnical Platform UC2

## Database: PostgreSQL 18 (database: `petrotech`)

---

## ERD Overview

```
users ──────────────────────────────────────────────────────────┐
  │                                                             │
  ├─< vdi_sessions >── vms ──< vm_metrics                      │
  │                                                             │
  ├─< tickets >── ticket_comments                               │
  │                                                             │
  ├── licenses (created_by) >── license_servers                 │
  │         └── license_logs                                    │
  │                                                             │
  └── reports (generated_by)                                    │
                                                                │
storage_devices ──< storage_metrics                             │
                                                                │
[Spatie permissions tables]                                     │
roles, permissions, model_has_roles, model_has_permissions, ────┘
role_has_permissions
```

---

## Tables

### `users`
Core authentication table (Laravel default + custom fields).

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | Display name |
| email | varchar UNIQUE | Login identifier |
| email_verified_at | timestamp | nullable |
| password | varchar | Bcrypt hashed |
| department | varchar | nullable — e.g., "Upstream Engineering" |
| phone | varchar | nullable |
| avatar_path | varchar | nullable — stored avatar filename |
| remember_token | varchar | nullable |
| created_at / updated_at | timestamp | |

Accessor: `avatar_url` → generates `/storage/{avatar_path}` or initials-based SVG URL.

---

### `vms`
Virtual machines available for VDI access.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| vm_name | varchar | e.g., "VM-PETRA-001" |
| application_name | varchar | e.g., "Petrel 2023.2" |
| os_type | varchar | e.g., "Windows Server 2022", "Red Hat Enterprise Linux 8" |
| protocol | varchar(20) | default "RDP" — also support SSH |
| ip_address | varchar(50) | |
| host_server | varchar | nullable |
| region | varchar(100) | |
| data_center | varchar(100) | nullable |
| cpu_cores | int | |
| ram_gb | int | |
| has_gpu | boolean | default false |
| gpu_model | varchar | nullable |
| gpu_vram_gb | int | nullable |
| status | varchar(50) | "running", "stopped", "maintenance" |
| assigned_user_id | bigint FK → users | nullable — dedicated user |
| notes | text | nullable |
| created_at / updated_at | timestamp | |

Accessor: `status_badge` → Bootstrap color class based on status.  
Method: `latestMetricData()` → returns latest `VmMetric` row (not a relation).

---

### `vdi_sessions`
Tracks RDP/SSH session lifecycle.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| vm_id | bigint FK → vms | CASCADE DELETE |
| user_id | bigint FK → users | CASCADE DELETE |
| protocol | varchar(20) | default "RDP" |
| status | varchar(50) | "active", "terminated" |
| session_token | varchar UNIQUE | nullable |
| connected_at | timestamp | Session start |
| disconnected_at | timestamp | nullable — set on terminate |
| duration_minutes | int | nullable — `(int) abs(diff)` |
| created_at / updated_at | timestamp | |

**Indexes:** `(vm_id, status)`, `(user_id, connected_at)`

Method: `terminate()` — sets status, records disconnected_at, calculates duration as `(int) abs()`.

---

### `license_servers`
Physical/virtual servers running license daemon software (e.g., FlexLM).

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| server_name | varchar | |
| hostname | varchar | |
| ip_address | varchar(50) | |
| port | int | default 27000 (FlexLM) |
| os_type | varchar(100) | nullable |
| location | varchar | nullable |
| status | varchar(50) | default "active" |
| created_at / updated_at | timestamp | |

---

### `licenses`
Software license records.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| license_name | varchar | Friendly name |
| application_name | varchar INDEX | e.g., "Petrel", "Eclipse" |
| license_key | text | nullable |
| status | varchar(20) INDEX | "enable", "disable" |
| expiry_date | date INDEX | License expiry |
| log_file_path | varchar(500) | nullable |
| license_server_id | bigint FK → license_servers | nullable, NULL ON DELETE |
| notes | text | nullable |
| created_by | bigint FK → users | |
| created_at / updated_at | timestamp | |

---

### `license_logs`
Event log for license activities (check-out, check-in, expired, etc).

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| license_id | bigint FK → licenses | CASCADE DELETE |
| event_type | varchar(100) | e.g., "checkout", "expiry_warning" |
| event_detail | text | nullable |
| user_count | int | Concurrent users at time of event |
| recorded_at | timestamp | |

**Indexes:** `(license_id, recorded_at)`  
Note: No `timestamps` — uses only `recorded_at`.

---

### `vm_metrics`
Time-series performance telemetry for VMs.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| vm_id | bigint FK → vms | CASCADE DELETE |
| cpu_utilisation | decimal(5,2) | nullable — percentage |
| memory_utilisation | decimal(5,2) | nullable — percentage |
| disk_io_read_mb | decimal(10,2) | nullable |
| disk_io_write_mb | decimal(10,2) | nullable |
| network_in_mb | decimal(10,2) | nullable |
| network_out_mb | decimal(10,2) | nullable |
| gpu_utilisation | decimal(5,2) | nullable — only if has_gpu |
| recorded_at | timestamp | |

Note: No `timestamps` — append-only time series.  
**Indexes:** `(vm_id, recorded_at)`  
Scope: `scopeLastHours($query, int $hours = 24)`

---

### `storage_devices`
NAS/SAN/Object storage devices.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| storage_name | varchar | e.g., "SAN-JKT-01" |
| storage_type | varchar(50) | "NAS", "SAN", "Object Storage" |
| total_space_gb | decimal(12,2) | Total capacity in GB |
| mount_location | varchar(500) | nullable — e.g., "/dev/san-jkt-01" |
| region | varchar(100) | nullable |
| data_center | varchar(100) | nullable |
| ip_address | varchar(50) | nullable |
| status | varchar(50) | default "active" |
| created_at / updated_at | timestamp | |

---

### `storage_metrics`
Time-series usage snapshots for storage devices.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| storage_device_id | bigint FK → storage_devices | CASCADE DELETE |
| used_space_gb | decimal(12,2) | |
| free_space_gb | decimal(12,2) | |
| usage_percentage | decimal(5,2) | |
| recorded_at | timestamp | |

Note: No `timestamps`.  
**Indexes:** `(storage_device_id, recorded_at)`  
Scope: `scopeLastDays($query, int $days = 30)`

---

### `tickets`
IT helpdesk tickets.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| ticket_number | varchar(20) UNIQUE | e.g., "TKT-20260316-0001" |
| title | varchar(500) | |
| description | text | |
| category | varchar(100) | e.g., "VDI", "License", "Network" |
| priority | varchar(20) INDEX | "low", "medium", "high", "critical" |
| status | varchar(30) INDEX | "open", "in_progress", "resolved", "closed" |
| assigned_to | bigint FK → users | nullable |
| created_by | bigint FK → users | |
| attachment_path | varchar(500) | nullable |
| resolution_notes | text | nullable |
| resolved_at | timestamp | nullable |
| closed_at | timestamp | nullable |
| created_at / updated_at | timestamp | |

**Indexes:** `(status, created_at)`, `(created_by, status)`, `(assigned_to, status)`

---

### `ticket_comments`
Discussion thread on a ticket.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| ticket_id | bigint FK → tickets | CASCADE DELETE |
| user_id | bigint FK → users | CASCADE DELETE |
| body | text | |
| attachment_path | varchar(500) | nullable |
| is_internal_note | boolean | default false — hidden from ticket creator |
| created_at / updated_at | timestamp | |

**Indexes:** `(ticket_id, created_at)`

---

### `reports`
Generated analytics reports snapshot.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| report_type | varchar(100) INDEX | e.g., "vm_utilisation", "license_usage" |
| title | varchar | |
| parameters | jsonb | nullable — filter params used |
| result_data | jsonb | nullable — cached report output |
| generated_by | bigint FK → users | |
| generated_at | timestamp | |
| created_at / updated_at | timestamp | |

---

### Spatie Permission Tables
`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`  
See spatie/laravel-permission documentation.  
Roles in use: `user`, `admin`, `super_admin`

---

## Indexing Strategy

- **Time-series reads** use composite indexes on `(entity_id, recorded_at)` for fast range queries
- **Status filtering** uses single-column indexes on `status` for quick dashboard counts
- **License expiry** uses index on `expiry_date` for "expiring soon" queries
- **Ticket lookups** use multi-column indexes covering the three major query patterns (by status, by creator, by assignee)
- **Unique constraints** on `ticket_number`, `session_token`, `users.email`
