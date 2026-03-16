# System Prompt — Petrotechnical Platform UC2

## Project Identity

You are an AI agent working on the **Petrotechnical Platform UC2**, an internal cloud infrastructure management platform developed for **Pertamina UC2** (an upstream oil and gas subsidiary).

The platform provides:
- **VDI (Virtual Desktop Infrastructure)** — browser-based remote desktop sessions to petrotechnical engineering workstations
- **License Management** — lifecycle tracking of expensive engineering software licenses (Petrel, Eclipse, Kingdom, etc.)
- **VM Monitoring** — real-time and historical CPU/memory/GPU/network telemetry for virtual machines
- **Storage Monitoring** — NAS/SAN/object-storage capacity and usage trend tracking
- **Ticketing System** — internal IT helpdesk with assignment, comments, and resolution tracking
- **Analytics & Reports** — platform-wide operational KPIs for administrators
- **Settings** — user profile management and password change

---

## Technology Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.5) |
| Database | PostgreSQL 18 |
| Auth | Laravel Breeze (session-based) |
| Authorization | Spatie `laravel-permission` (roles: `user`, `admin`, `super_admin`) |
| Activity Log | Spatie `laravel-activitylog` |
| Frontend | Vanilla HTML/CSS/JS + Tabler UI framework (Bootstrap 5 base) |
| Charts | ApexCharts 3.44 (loaded from CDN) |
| HTTP Server | PHP artisan serve (dev) / Nginx + PHP-FPM (prod) |

---

## Architecture Style

**Modular Monolith** — a single Laravel application with clear module boundaries enforced through the directory structure:

```
app/
  Http/Controllers/
    Admin/          ← Admin-only controllers
    Auth/           ← Laravel Breeze auth controllers
  Models/           ← Eloquent models (thin, relationship + cast focused)
  Services/
    Analytics/      ← Report aggregation logic
    License/        ← License state machine
    Storage/        ← Storage metric processing
    Ticketing/      ← Ticket workflow logic
    VDI/            ← Session connect/terminate logic
    VmMonitoring/   ← VM metric queries
  Policies/         ← Gate authorization policies per model
  Providers/        ← Service providers (AppServiceProvider registers policies)
```

---

## Development Philosophy

1. **Thin controllers, fat services** — controllers only handle HTTP (validate, call service, return view/redirect). All business logic lives in `Services/`.
2. **Policy-based authorization** — every protected resource has a `Policy` class registered in `AppServiceProvider`. Never hardcode role checks in controllers except for middleware-level guards.
3. **Explicit is better than magic** — avoid `authorizeResource()` (incompatible with Laravel 12 controller-less approach). Use explicit `$this->authorize('action', $model)` calls.
4. **No JavaScript frameworks** — all interactivity is vanilla JS + ApexCharts. No Vue, React, or Livewire unless explicitly introduced.
5. **Charts use `@push('scripts')`** — All chart JavaScript is pushed to the layout `@stack('scripts')` block, never inline in the main section.
6. **OS-aware UI** — VDI RDP simulation detects `os_type` (Windows vs Linux) and renders an appropriate desktop environment.

---

## Roles & Access Control

| Role | Access |
|---|---|
| `user` | Dashboard, VDI Access, Ticketing, Settings |
| `admin` | All user access + License Management, VM Monitoring, Storage Monitor, Analytics |
| `super_admin` | All admin access + Settings (system config) |

Role middleware: `role:admin|super_admin` applied to the `admin.*` route group.

---

## Constraints & Best Practices

- **PostgreSQL types**: `duration_minutes` is `integer` — always cast floats before DB write. Use `(int) abs($value)`.
- **PHP heredocs in Blade `<script>` blocks**: Never use `{{ }}` with PHP operators (`>=`, `&&`, `<=`) inside `<script>` tags — they become HTML-encoded. Use `@php $var = ...; @endphp` + `{!! $var !!}` or `@json()` directive instead.
- **Eloquent method naming**: Don't name model methods the same as a relationship (e.g., `latestMetric()` would be resolved as a relationship). Use descriptive names like `latestMetricData()`.
- **Chart data from services**: Always call `->values()->toArray()` on Collections before passing to Blade `@json()` to ensure flat PHP arrays.
- **File naming**: Blade views use `snake_case` directory names and `.blade.php` extension. Controllers are `PascalCase`, services are `PascalCaseService`.
- **CSRF on AJAX**: When using `fetch()` for form posts, always include `X-Requested-With: XMLHttpRequest` header and pass the CSRF token from the hidden form.

---

## Key File Locations

| Purpose | Path |
|---|---|
| Main layout | `resources/views/layouts/app.blade.php` |
| Route definitions | `routes/web.php` |
| Middleware / App bootstrap | `bootstrap/app.php` |
| Environment config | `.env` |
| Database seeders | `database/seeders/` |
| AI documentation | `ai/` |
