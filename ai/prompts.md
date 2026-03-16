# Development Prompts — Petrotechnical Platform UC2

Ready-to-use prompts for AI agents and developers working on this repository.  
**Always read `ai/system_prompt.md` and `ai/architecture.md` before using these prompts.**

---

## 1. Generate a New Module

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL).
Read ai/system_prompt.md and ai/architecture.md for full context.

Create a new module called "{ModuleName}" with the following responsibilities:
{describe what the module does}

Follow the modular monolith pattern:
1. Create migration: database/migrations/{timestamp}_create_{table}_table.php
2. Create Eloquent model: app/Models/{Model}.php
   - Include $fillable, $casts, relationships, and relevant query scopes
3. Create service: app/Services/{Module}/{Model}Service.php
   - Business logic only, return arrays not models
4. Create controller: app/Http/Controllers/{optional Admin/}{Model}Controller.php
   - Thin controller: authorize(), call service, return view()
   - If admin-only, put in Admin/ subfolder
5. Create Blade views: resources/views/{module}/index.blade.php and show.blade.php
   - Extend layouts/app.blade.php
   - Use @push('scripts') for any ApexCharts initialization
6. Register routes in routes/web.php inside the appropriate middleware group
7. Add sidebar nav item in resources/views/layouts/app.blade.php

Apply all rules from ai/coding_rules.md.
```

---

## 2. Create a New Controller

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL).
Read ai/coding_rules.md before proceeding.

Create a controller for {resource name} at app/Http/Controllers/{path}/{Name}Controller.php.

Requirements:
- Constructor-inject the service: public function __construct(private {Name}Service $service) {}
- Methods needed: {index / show / create / store / edit / update / destroy}
- For each method that involves a policy-protected model, call $this->authorize('action', $model)
- Return view() for GET methods, redirect()->route()->with('success'/'error') for POST methods
- For AJAX-capable actions, check request()->ajax() and return JSON response
- Do NOT use authorizeResource() — it is incompatible with Laravel 12

Route to register: Route::{method}('{path}', [{Controller}::class, '{method}'])->name('{name}');
```

---

## 3. Add a Database Migration

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL 18).
Read ai/erd.md for the existing schema context.

Create a migration to {describe the change: add column / create table / add index}.

Table: {table_name}
Change: {description}

Rules to follow:
- Use foreignId()->constrained('table')->cascadeOnDelete() for FK references
- Use nullOnDelete() for optional/soft references
- Time-series tables: do NOT use timestamps(), use timestamp('recorded_at') only
- Always add index on status columns: ->index()
- Composite indexes for time-series: $table->index(['entity_id', 'recorded_at'])
- Decimal precision: decimal(5,2) for %, decimal(10,2) for MB, decimal(12,2) for GB
- Integer type for duration_minutes (never float/decimal)

After creating the migration, run: php artisan migrate
```

---

## 4. Create a Service Class

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL).
Read ai/coding_rules.md section "Service Layer Rules".

Create a service at app/Services/{Module}/{Name}Service.php.

Service purpose: {describe the business logic this service should handle}

Requirements:
- Namespace: App\Services\{Module}
- Inject any models or other services via constructor if needed
- All query results returned as arrays (not Collections or Eloquent models)  
- When returning data for Blade charts, always call ->values()->toArray() on plucked Collections
- No HTTP/session/request logic — services are pure business logic
- Methods to implement:
  {list method names and their purpose}
```

---

## 5. Implement a New Dashboard Widget

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL).
The dashboard is at resources/views/dashboard.blade.php.
The controller is at app/Http/Controllers/DashboardController.php.

Add a new KPI widget showing: {describe the metric}

Steps:
1. In DashboardController::index(), query the required data and add it to the compact() array
2. In dashboard.blade.php, add a new card in the KPI row section
3. If it requires a chart, add the chart div in @section('content') and initialize in @push('scripts')
4. Chart should use ApexCharts (already loaded in layout from CDN)
5. For percentage data in script tags, use @php + {!! !!} pattern to avoid HTML entity encoding

Style the card consistently with existing metric cards (card border-0 shadow-sm).
```

---

## 6. Add a New Chart to an Existing View

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL).
Read ai/coding_rules.md section "Blade / Frontend Rules".

Add an ApexCharts chart to {view path}.

Chart type: {area / bar / donut / radialBar / line}
Data source: {describe where the data comes from — controller variable name}
Chart container: <div id="chart-{name}" style="min-height:{height}px"></div>

Follow this pattern exactly:
1. Add the container div in @section('content')
2. Use @push('scripts') at the bottom of the view (NOT inline in section)
3. Reference controller data with @json($variable) directive
4. For PHP conditionals in JS (colors based on thresholds), use @php block + {!! $var !!}
5. ApexCharts is already available globally — just call new ApexCharts(...).render()

Example for a simple area chart:
@push('scripts')
<script>
const labels = @json($trendData['labels']);
const values = @json($trendData['values']);
new ApexCharts(document.querySelector('#chart-{name}'), {
    series: [{ name: '{Label}', data: values }],
    chart: { type: 'area', height: 240, toolbar: { show: false } },
    colors: ['{hex_color}'],
    xaxis: { categories: labels },
}).render();
</script>
@endpush
```

---

## 7. Refactor an Existing Module

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL).
Read ai/system_prompt.md, ai/architecture.md, and ai/coding_rules.md.

Refactor the {module name} module. Current issues: {describe problems}.

Rules for refactoring:
1. Keep the same routes and route names — do not break existing links
2. Move any business logic from controllers to services
3. Remain backward-compatible with Blade views (same variable names)
4. Do not change migration files for existing tables — use new addColumn migrations
5. Preserve all existing policies
6. After refactoring, verify routes with: php artisan route:list --path={prefix}
7. Test via browser: navigate to relevant pages and confirm no 500 errors
```

---

## 8. Debug a Page Error

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL 18).
Read ai/system_prompt.md for known constraints and gotchas.

Error encountered at {URL}:
{paste error message and stack trace}

Common issues to check first:
- "Call to undefined method Model::method()" → method name conflicts with Eloquent relation resolution. Rename using a prefix like latestMetricData() instead of latestMetric()
- "SQLSTATE[22P02]: Invalid text representation" → trying to insert a float into an integer column. Always cast: (int) abs($value)
- "Uncaught SyntaxError: Unexpected token '&'" → {{ $var >= threshold }} in <script> tag encodes >= as &gt;=. Move to @php block + {!! !!}
- Chart not rendering (blank) → chart container has no height. Add style="min-height:240px" to the div
- Chart rendering blank (mixed type) → ApexCharts mixed charts (area+line with dual y-axis) can silently fail. Use two separate charts instead
- "View [module.name] not found" → blade file missing at resources/views/{module}/{name}.blade.php

After identifying the issue, apply the fix and verify by loading the page in the browser.
```

---

## 9. Add VDI OS Theme

```
You are working on the Petrotechnical Platform UC2 (Laravel 12, PostgreSQL).
The RDP simulation page is at resources/views/vdi/rdp.blade.php.
OS detection is done via: $isWindows = stripos($vm->os_type, 'windows') !== false

Add support for a new OS theme: {OS name, e.g., "Ubuntu", "macOS", "CentOS"}

Steps:
1. Add an @php detection variable: $isUbuntu = stripos($vm->os_type, 'ubuntu') !== false
2. Update the theme cascade (check most specific first)
3. Add themed CSS inside the OS-specific @if block
4. Add desktop icons appropriate for that OS
5. Add themed taskbar/panel at the bottom
6. Add appropriate window types (terminal for Linux variants, Finder for macOS, etc.)
7. Add loading steps appropriate for the OS (SSH tunnel for Linux, AFP for macOS, etc.)

Keep the connection bar (#rdp-bar) identical across all OS themes — it's OS-agnostic.
```
