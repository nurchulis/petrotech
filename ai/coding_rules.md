# Coding Rules — Petrotechnical Platform UC2

These rules must be followed by all developers and AI agents contributing to this repository.

---

## 1. Controller Rules

### Structure
Controllers must be **thin** — they only:
1. Validate / authorize the request
2. Call the appropriate service
3. Return a view or redirect

```php
// ✅ CORRECT
class VmMonitorController extends Controller
{
    public function __construct(private VmMetricService $service) {}

    public function show(Vm $vm): View
    {
        $this->authorize('view', $vm);            // Policy check
        $latest    = $vm->latestMetricData();     // Lightweight model call
        $trendData = $this->service->trendData($vm, 24); // Service for business logic
        return view('vm-monitoring.show', compact('vm', 'latest', 'trendData'));
    }
}

// ❌ WRONG — business logic in controller
public function show(Vm $vm)
{
    $metrics = VmMetric::where('vm_id', $vm->id)
        ->where('recorded_at', '>=', now()->subHours(24))
        ->get()
        ->groupBy(fn($m) => $m->recorded_at->format('H:i'));
    // ...
}
```

### Authorization
- Use `$this->authorize('action', $model)` explicitly per method
- **Never** use `authorizeResource()` — it uses `Controller::middleware()` which doesn't exist in Laravel 12
- Role-level guards go on the **route** via middleware: `role:admin|super_admin`

```php
// ✅ CORRECT
public function edit(License $license): View
{
    $this->authorize('update', $license);
    return view('licenses.edit', compact('license'));
}

// ❌ WRONG
public function __construct()
{
    $this->authorizeResource(License::class, 'license');
}
```

### AJAX / Fetch responses
When a controller action can be called via `fetch()` (AJAX), return JSON for XHR requests:

```php
public function connect(Vm $vm): mixed
{
    $session = $this->service->connect(auth()->user(), $vm);

    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['ok' => true, 'session_id' => $session->id]);
    }

    return redirect()->route('vdi.rdp', $vm)->with('success', 'Connected.');
}
```

---

## 2. Service Layer Rules

Services live in `app/Services/{Module}/` and are injected via constructor DI.

```php
// ✅ CORRECT service structure
namespace App\Services\VmMonitoring;

use App\Models\Vm;
use App\Models\VmMetric;

class VmMetricService
{
    public function trendData(Vm $vm, int $hours = 24): array
    {
        $metrics = VmMetric::where('vm_id', $vm->id)
            ->lastHours($hours)
            ->orderBy('recorded_at')
            ->get();

        return [
            'labels' => $metrics->pluck('recorded_at')->map(fn($d) => $d->format('H:i'))->values()->toArray(),
            'cpu'    => $metrics->pluck('cpu_utilisation')->values()->toArray(),
        ];
    }
}
```

**Rules:**
- Services **do not** return Eloquent models — return arrays or DTOs
- Always call `->values()->toArray()` on plucked Collections before returning for Blade `@json()` use
- Services **never** call other services — if cross-module data is needed, the controller queries directly

---

## 3. Model Rules

Models must be **focused on data structure** only:

```php
// ✅ Model responsibilities
class StorageMetric extends Model
{
    public $timestamps = false;  // Time-series tables have no timestamps

    protected $fillable = ['storage_device_id', 'used_space_gb', 'free_space_gb', 'usage_percentage', 'recorded_at'];

    protected $casts = [
        'recorded_at'      => 'datetime',
        'used_space_gb'    => 'decimal:2',
        'usage_percentage' => 'decimal:2',
    ];

    // Relationships
    public function device(): BelongsTo
    {
        return $this->belongsTo(StorageDevice::class, 'storage_device_id');
    }

    // Query scopes (always prefix with scope)
    public function scopeLastDays($query, int $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }
}
```

**Rules:**
- **Never name a method the same as a potential Eloquent relationship** — Eloquent will try to resolve it as a relation. Use `latestMetricData()` instead of `latestMetric()`.
- Time-series tables set `public $timestamps = false`
- All decimal DB columns must have `$casts` entries with precision
- Status columns always have an index: `$table->string('status')->index()`

---

## 4. Blade / Frontend Rules

### Layout
- All app pages extend `layouts/app.blade.php`
- RDP simulation is a **standalone** page (no layout extension)
- The layout provides `@stack('scripts')` at the bottom — use `@push('scripts')` for all JS

### Chart JavaScript
```blade
{{-- ✅ CORRECT: scripts pushed, PHP logic separated --}}
@php
    $color = $usage >= 90 ? "'#d63939'" : "'#2fb344'";
@endphp

@push('scripts')
<script>
const data = @json($trendData['labels']);  {{-- @json() is safe --}}
new ApexCharts(document.querySelector('#chart'), {
    colors: [{!! $color !!},],  {{-- {!! !!} for raw PHP var --}}
}).render();
</script>
@endpush

{{-- ❌ WRONG: comparison operators in {{ }} become &gt;= in HTML --}}
@push('scripts')
<script>
colors: [{{ $usage >= 90 ? "'#d63939'" : "'#2fb344'" }}]  {{-- BREAKS --}}
</script>
@endpush
```

### Chart container sizing
Always give chart containers an explicit `min-height` or `height` style:
```html
<div id="chart-used" style="min-height:240px"></div>
```

### Active nav links
Use `request()->routeIs('pattern.*')` for consistent active state:
```blade
<a class="nav-link {{ request()->routeIs('admin.storage.*') ? 'active' : '' }}" href="...">
```

---

## 5. Naming Conventions

| Item | Convention | Example |
|---|---|---|
| Controllers | PascalCase + Controller | `LicenseController` |
| Services | PascalCase + Service | `VdiSessionService` |
| Models | PascalCase singular | `StorageDevice` |
| Blade views | snake_case directory | `vm-monitoring/show.blade.php` |
| Route names | dot.separated.lowercase | `admin.licenses.index` |
| DB tables | snake_case plural | `vdi_sessions`, `storage_metrics` |
| DB columns | snake_case | `usage_percentage`, `connected_at` |
| Scopes | camelCase prefixed `scope` | `scopeLastDays()`, `scopeForUser()` |
| Accessors | camelCase + Attribute suffix | `getAvatarUrlAttribute()` |

---

## 6. Database Migration Rules

```php
// ✅ Correct migration structure
Schema::create('example_metrics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
    $table->decimal('value', 10, 2);       // Always specify precision
    $table->timestamp('recorded_at');       // No timestamps() for time-series
    $table->index(['entity_id', 'recorded_at']); // Composite index for range queries
});
```

**Rules:**
- Foreign keys use `->constrained()` with explicit table name (don't rely on convention)
- Delete behavior: use `cascadeOnDelete()` for logs/metrics, `nullOnDelete()` for optional references
- Time-series tables: use `timestamp('recorded_at')` without `timestamps()` 
- Integer columns storing calculated values: verify type matches (e.g., `integer` not `decimal` for `duration_minutes`)
- Always add indexes on columns used in `WHERE` clauses in the application

---

## 7. Role & Authorization Rules

```php
// ✅ In AppServiceProvider (register policies here, not Gate::define in controllers)
Gate::policy(License::class, LicensePolicy::class);
Gate::policy(Vm::class, VmPolicy::class);

// ✅ In routes (role middleware)
Route::middleware('role:admin|super_admin')->prefix('admin')->group(function () {
    Route::resource('licenses', LicenseController::class);
});

// ✅ In Blade (show admin menu items)
@role('super_admin')
<li class="nav-item">Settings</li>
@endrole
```

---

## 8. PostgreSQL Specific Rules

- **Always cast float results to int** when writing to integer columns:
  ```php
  'duration_minutes' => (int) abs(now()->diffInMinutes($this->connected_at))
  ```
- **Use `jsonb`** for JSON columns (not `json`) for indexing capability
- **Decimal precision**: `decimal(5,2)` for percentages, `decimal(10,2)` for MB, `decimal(12,2)` for GB
- **Never use MySQL-specific syntax** — this app is PostgreSQL only
