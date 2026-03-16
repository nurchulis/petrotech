<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Vm extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'vm_name', 'os_type', 'application_name', 'status',
        'region', 'data_center', 'ip_address', 'host_server',
        'has_gpu', 'gpu_model', 'cpu_cores', 'ram_gb',
        'assigned_user_id', 'notes',
    ];

    protected $casts = [
        'has_gpu' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->useLogName('vm');
    }

    // Relationships
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function vdiSessions(): HasMany
    {
        return $this->hasMany(VdiSession::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(VmMetric::class);
    }

    // Scopes
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeStopped($query)
    {
        return $query->where('status', 'stopped');
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    // Helpers
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'running' => 'success',
            'stopped' => 'danger',
            'paused'  => 'warning',
            default   => 'secondary',
        };
    }

    public function latestMetricData(): ?VmMetric
    {
        return $this->metrics()->latest('recorded_at')->first();
    }
}
