# Development Workflow — Petrotechnical Platform UC2

## Overview

This document defines how developers and AI agents should work on this repository to maintain code quality, architecture consistency, and safe iteration.

---

## 1. Recommended Development Workflow

```
Understand → Plan → Implement → Verify → Document
```

### Step 1: Understand
Before writing any code:
1. Read `ai/system_prompt.md` to understand project context
2. Read `ai/architecture.md` to understand module structure
3. Read `ai/coding_rules.md` to understand conventions
4. Read `ai/erd.md` if touching database

### Step 2: Plan
For non-trivial changes:
1. Identify which files need to change
2. Identify whether a new service/model is needed
3. Check if any existing route/view names will change (breaking changes)
4. For database changes: plan migration before model changes

### Step 3: Implement
1. **Start with migration** → then model → then service → then controller → then view
2. Keep changes small and focused
3. Follow coding rules exactly

### Step 4: Verify
```bash
# Always run after changes
php artisan route:list          # Verify routes registered correctly
php artisan config:clear        # Clear any cached config
php artisan view:clear         # Clear compiled views

# Load affected pages in browser and confirm:
# - No 500 errors
# - No JS console errors (right-click → Inspect → Console)
# - Charts render with data
# - Redirects work correctly
```

### Step 5: Document
- Update `ai/` docs if architecture or ERD changed
- Add comments to complex service logic
- Update migrations are self-documenting via column comments

---

## 2. Branching Strategy

```
main ──────────────────────────────────────► production
  │
  ├── feature/vdi-session-recording
  ├── feature/license-alert-emails
  ├── fix/storage-chart-blank
  └── chore/update-dependencies
```

### Branch Naming
| Type | Pattern | Example |
|---|---|---|
| New feature | `feature/{module}-{description}` | `feature/ticketing-sla-tracking` |
| Bug fix | `fix/{description}` | `fix/storage-chart-encoding` |
| Chore | `chore/{description}` | `chore/composer-update` |
| Hotfix | `hotfix/{description}` | `hotfix/vdi-session-null-crash` |

### Rules
- **Never commit directly to `main`** — always branch and PR
- Each PR should address a single concern
- Keep database migrations in the same PR as the code that uses them

---

## 3. How to Introduce a New Module

Follow this exact sequence to avoid breaking existing functionality:

### Phase 1: Database
```bash
# 1. Create migration
php artisan make:migration create_{table_name}_table

# 2. Edit migration following ERD conventions (see ai/erd.md)
# 3. Run migration
php artisan migrate

# 4. Verify table exists
php artisan tinker --execute="echo Schema::hasTable('{table}') ? 'OK' : 'MISSING';"
```

### Phase 2: Model
```bash
# Create model
php artisan make:model {ModelName}

# Model must have:
# - $fillable array
# - $casts array for dates and decimals
# - Relationships (BelongsTo, HasMany, etc.)
# - Query scopes (scopeLastDays, scopeForUser, etc.)
# - No business logic
```

### Phase 3: Service
```
Create: app/Services/{Module}/{ModelName}Service.php

Service must:
- Be a plain PHP class (no extends)
- Accept models/other services via constructor injection
- Return arrays, not Collections or Eloquent models
- Contain all business logic for the module
```

### Phase 4: Policy (if needed)
```bash
php artisan make:policy {Model}Policy --model={Model}

# Register in AppServiceProvider::boot():
Gate::policy({Model}::class, {Model}Policy::class);
```

### Phase 5: Controller
```bash
php artisan make:controller {optional Admin/}{Model}Controller

# Inject service via constructor
# Call $this->authorize() for each protected action
# No direct model queries in the controller — use service
```

### Phase 6: Views
```
resources/views/{module}/
├── index.blade.php    ← List view
├── show.blade.php     ← Detail view
├── create.blade.php   ← New record form (optional)
└── edit.blade.php     ← Edit form (optional)

All views must:
- @extends('layouts.app')
- @section('title', '...')
- @section('breadcrumb', '...')
- @push('scripts') for charts (never inline in section)
```

### Phase 7: Routes
```php
// In routes/web.php — add to appropriate middleware group:

// For admin-only:
Route::middleware('role:admin|super_admin')->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('{module}')->name('{module}.')->group(function () {
        Route::get('/', [{Controller}::class, 'index'])->name('index');
        Route::get('/{model}', [{Controller}::class, 'show'])->name('show');
    });
});

// For all authenticated users:
Route::prefix('{module}')->name('{module}.')->group(function () {
    Route::get('/', [{Controller}::class, 'index'])->name('index');
});
```

### Phase 8: Sidebar Navigation
In `resources/views/layouts/app.blade.php`, add nav item in the appropriate section:
```blade
@role('admin')  {{-- or remove for all users --}}
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('{module}.*') ? 'active' : '' }}"
       href="{{ route('{module}.index') }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            <!-- SVG icon -->
        </span>
        <span class="nav-link-title">Module Name</span>
    </a>
</li>
@endrole
```

### Phase 9: Seeder (optional)
```bash
php artisan make:seeder {Module}Seeder
# Add to DatabaseSeeder::run()
php artisan db:seed --class={Module}Seeder
```

---

## 4. Modifying Existing Modules Safely

### Safe Changes (low risk)
- Adding new Blade view content (new sections, cards)
- Adding new optional columns via migration (nullable columns)
- Adding new query scopes to models
- Adding new service methods
- Adding new routes without changing existing route names

### Medium Risk Changes
- Changing controller return values — verify each view still receives expected variables
- Adding required columns to migrations — provide default values
- Renaming service methods — search for all usages first: `grep -r "methodName" app/`

### High Risk Changes (requires extra careful verification)
- **Renaming route names** — breaks all `route()` helper calls and Blade links  
  → Search all views: `grep -r "route('" resources/views/`
- **Renaming model methods** — can silently fail if Eloquent treats it as a relation  
  → Always test: `php artisan tinker --execute="App\Models\{Model}::first()->{method}()"`
- **Changing column types** — PostgreSQL is strict about type coercion  
  → Always test insertion: `php artisan tinker --execute="App\Models\{Model}::create([...])"`
- **Changing JS chart data structure** — charts fail silently  
  → Always inspect browser console after changes

---

## 5. AI Agent Workflow

When an AI agent is contributing to this repository:

### Before starting
```
1. Read ai/system_prompt.md
2. Read ai/architecture.md  
3. Read any relevant module files (controller, service, view)
4. Use `grep` to find all usages of methods/variables you plan to change
```

### During implementation
```
1. Make one focused change at a time
2. Verify each step before proceeding: php artisan route:list, view the page
3. Never break existing route names
4. Run `php artisan tinker` to verify DB writes work before testing in browser
5. Check browser console for JS errors after any chart changes
```

### After implementation
```
1. Run: php artisan route:list | grep {module}
2. Visit each affected URL in browser
3. Check: no 500 errors, no JS console errors
4. If charts are involved: confirm data renders (not blank)
5. Update ai/ documentation if architecture changed
```

---

## 6. Database Change Protocol

```
NEVER edit existing migrations that have already been run.
ALWAYS create a new migration for changes.
```

```bash
# Correct: new migration for adding a column
php artisan make:migration add_department_to_users_table

# Contents:
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('department')->nullable()->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('department');
    });
}
```

### After migration changes
```bash
php artisan migrate        # Apply
php artisan migrate:status # Verify all ran
```

---

## 7. Common Commands Reference

```bash
# Development server
php artisan serve --port=8000

# Route debugging
php artisan route:list
php artisan route:list --path=vdi

# Cache management
php artisan optimize:clear        # Clear all caches
php artisan config:cache          # Cache config (production)
php artisan route:cache           # Cache routes (production)

# Database
php artisan migrate               # Run pending migrations
php artisan migrate:rollback      # Rollback last batch
php artisan db:seed               # Run all seeders
php artisan db:seed --class=Name  # Run specific seeder
php artisan migrate:fresh --seed  # Full reset + reseed

# Tinker (REPL)
php artisan tinker
php artisan tinker --execute="App\Models\Vm::first()->toArray();"

# Model/Controller generation
php artisan make:model {Name}
php artisan make:controller {Name}Controller
php artisan make:migration {description}
php artisan make:policy {Name}Policy --model={Name}
php artisan make:seeder {Name}Seeder
```

---

## 8. Known Gotchas

| Issue | Cause | Fix |
|---|---|---|
| `Unexpected token '&'` in browser console | `{{ $var >= x }}` in `<script>` encodes `>=` as `&gt;=` | Use `@php $color = ...; @endphp` + `{!! $color !!}` |
| Chart container is blank with zero height | No explicit height on chart div | Add `style="min-height:240px"` |
| Mixed ApexCharts (area+line, dual y-axis) silently fails | Complex chart config can fail without error | Use two separate single-series charts instead |
| `Call to undefined method Model::method()` | Method name conflicts with Eloquent relation resolver | Rename method (e.g., `latestMetricData()` not `latestMetric()`) |
| `SQLSTATE[22P02]: invalid input syntax for type integer` | Float value inserted into integer DB column | Cast: `(int) abs($floatValue)` |
| `View [module.name] not found` | Missing blade file | Create at `resources/views/{module}/{name}.blade.php` |
| Settings visible to all roles | `@role('super_admin')` guard on sidebar item only shows to super_admin | By design — only super_admin sees Settings in sidebar |
| `window.open()` blocked | Browser popup blocker | Must call `window.open()` in direct user gesture handler chain (e.g., inside `.then()` from button click `fetch()`) |
