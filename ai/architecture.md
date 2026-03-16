# Architecture — Petrotechnical Platform UC2

## High-Level Architecture

```
Browser ──HTTP──► Nginx (prod) / artisan serve (dev)
                      │
                      ▼
               Laravel 12 Application
                      │
          ┌───────────┼───────────────┐
          │           │               │
       Routes      Middleware      Bootstrap
       web.php    (auth, role)    app.php
          │
          ▼
     Controllers
    ┌───────────────────────────────────────┐
    │  DashboardController                  │
    │  VdiController                        │
    │  TicketController                     │
    │  SettingsController                   │
    │  Admin\LicenseController              │
    │  Admin\VmMonitorController            │
    │  Admin\StorageController              │
    │  Admin\AnalyticsController            │
    └──────────────┬────────────────────────┘
                   │ calls
                   ▼
              Services Layer
    ┌───────────────────────────────────────┐
    │  VDI\VdiSessionService                │
    │  License\LicenseService               │
    │  Ticketing\TicketService              │
    │  Storage\StorageMetricService         │
    │  VmMonitoring\VmMetricService         │
    │  Analytics\ReportService              │
    └──────────────┬────────────────────────┘
                   │ Eloquent ORM
                   ▼
           Models (Eloquent)
    ┌───────────────────────────────────────┐
    │  User, Vm, VmMetric                   │
    │  VdiSession                           │
    │  License, LicenseServer, LicenseLog   │
    │  StorageDevice, StorageMetric         │
    │  Ticket, TicketComment                │
    │  Report                              │
    └──────────────┬────────────────────────┘
                   │
                   ▼
            PostgreSQL 18
```

---

## Application Structure

```
petrotech/
├── ai/                         ← AI context documentation (this folder)
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          ← Protected by role:admin|super_admin middleware
│   │   │   │   ├── AnalyticsController.php
│   │   │   │   ├── LicenseController.php
│   │   │   │   ├── StorageController.php
│   │   │   │   └── VmMonitorController.php
│   │   │   ├── Auth/           ← Laravel Breeze generated
│   │   │   ├── Controller.php  ← Base (uses AuthorizesRequests trait)
│   │   │   ├── DashboardController.php
│   │   │   ├── SettingsController.php
│   │   │   ├── TicketController.php
│   │   │   └── VdiController.php
│   │   └── Requests/
│   │       └── Auth/LoginRequest.php
│   ├── Models/
│   │   ├── User.php            ← HasRoles (Spatie), avatar_url accessor
│   │   ├── Vm.php              ← status_badge accessor, latestMetricData()
│   │   ├── VmMetric.php        ← scopeLastHours()
│   │   ├── VdiSession.php      ← terminate() method
│   │   ├── License.php         ← status helpers
│   │   ├── LicenseServer.php
│   │   ├── LicenseLog.php
│   │   ├── StorageDevice.php
│   │   ├── StorageMetric.php   ← scopeLastDays()
│   │   ├── Ticket.php          ← scopeForUser(), generateNumber()
│   │   ├── TicketComment.php
│   │   └── Report.php
│   ├── Policies/
│   │   ├── LicensePolicy.php
│   │   ├── TicketPolicy.php
│   │   └── VmPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php  ← Registers policies
│   ├── Services/
│   │   ├── Analytics/ReportService.php
│   │   ├── License/LicenseService.php
│   │   ├── Storage/StorageMetricService.php
│   │   ├── Ticketing/TicketService.php
│   │   ├── VDI/VdiSessionService.php
│   │   └── VmMonitoring/VmMetricService.php
│   └── View/Components/
│       ├── AppLayout.php
│       └── GuestLayout.php
├── bootstrap/app.php            ← Middleware alias registration (Spatie roles)
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/views/
│   ├── layouts/app.blade.php   ← Main layout (sidebar, topbar, @stack('scripts'))
│   ├── dashboard.blade.php
│   ├── vdi/                    ← index, show, rdp
│   ├── tickets/                ← index, create, show
│   ├── licenses/               ← index, show, create, edit
│   ├── vm-monitoring/          ← index, show
│   ├── storage/                ← index, show
│   ├── analytics/              ← index
│   └── settings/               ← index
└── routes/
    ├── web.php                 ← All application routes
    └── auth.php                ← Breeze auth routes
```

---

## Module Responsibilities

### VDI Module
Files: `VdiController`, `VdiSessionService`, `Vm`, `VdiSession` models  
- Displays available VMs with real-time status
- Creates VDI sessions (connect), tracks duration, terminates sessions
- `rdp.blade.php` renders OS-aware fullscreen desktop simulation (Windows 11 or Linux/GNOME)
- Buttons use `fetch()` POST + `window.open('_blank')` to open RDP in new tab

### License Management Module
Files: `LicenseController`, `LicenseService`, `License`, `LicenseServer`, `LicenseLog` models  
- CRUD for software licenses, toggle active/inactive
- Tracks license servers, expiry dates, seat counts
- Activity log via Spatie activitylog
- Policies: only `admin`/`super_admin` can modify

### VM Monitoring Module
Files: `VmMonitorController`, `VmMetricService`, `Vm`, `VmMetric` models  
- Lists all VMs with last-known metrics
- Detail page shows 24h trend charts (CPU, Memory, Disk I/O, Network, GPU)
- `latestMetricData()` method on Vm retrieves latest row (not a relation to avoid Eloquent confusion)

### Storage Monitoring Module
Files: `StorageController`, `StorageMetricService`, `StorageDevice`, `StorageMetric` models  
- Lists NAS/SAN/object-storage devices with capacity overview
- Detail page shows 30-day trend charts for used GB and usage %
- `StorageMetric::scopeLastDays($query, int $days = 30)` scopes historical data

### Ticketing Module
Files: `TicketController`, `TicketService`, `Ticket`, `TicketComment` models  
- Users create tickets; admins can assign and update status
- Tickets flow: `open → in_progress → resolved → closed`
- Internal notes on comments (`is_internal_note` flag)

### Analytics Module
Files: `AnalyticsController`, `ReportService`  
- Aggregates platform-wide stats: running VMs, expiring licenses, open tickets, active sessions, storage summary
- Supports time period filter: week / month / year
- Renders donut, bar, and radial charts via ApexCharts

### Settings Module
Files: `SettingsController`  
- All authenticated users can update profile (name, email, department, phone) and change password
- Accessible from sidebar under SYSTEM section (super_admin sees it via `@role('super_admin')` guard)

---

## Request Flow

```
1. Browser → GET /admin/vm-monitoring/2
2. Middleware: auth (redirect if unauthenticated)
3. Middleware: role:admin|super_admin (403 if insufficient role)
4. Router dispatches to VmMonitorController::show(Vm $vm)
5. Controller calls VmMetricService::trendData($vm, $hours=24)
6. Service queries VmMetric::where('vm_id', $vm->id)->lastHours(24)->orderBy('recorded_at')
7. Service returns ['labels'=>[...], 'cpu'=>[...], 'memory'=>[...], ...]
8. Controller returns view('vm-monitoring.show', compact('vm', 'latest', 'trendData'))
9. Blade extends layouts/app.blade.php, yields 'content'
10. @push('scripts') block initializes ApexCharts with @json($trendData) data
11. Layout @stack('scripts') renders the pushed script block at page bottom
```

---

## How Modules Interact

- **Services are injected via constructor** into controllers: `public function __construct(private VdiSessionService $service) {}`
- **Models never call services** — data flow is one-way: Controller → Service → Model
- **Policies are registered in AppServiceProvider** and called via `$this->authorize('action', $model)` in controllers
- **No module calls another module's service** — if cross-module data is needed, controllers query models directly (e.g., DashboardController pulls from multiple models)
