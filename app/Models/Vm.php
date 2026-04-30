<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;
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
        // RDP / Guacamole fields
        'is_dummy', 'rdp_host', 'rdp_port', 'rdp_username', 'rdp_password',
    ];

    protected $casts = [
        'has_gpu'  => 'boolean',
        'is_dummy' => 'boolean',
        'rdp_port' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->useLogName('vm');
    }

    // -------------------------------------------------------------------------
    // Encryption — rdp_password is ALWAYS stored encrypted, NEVER plain text
    // -------------------------------------------------------------------------

    protected function rdpPassword(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    // -------------------------------------------------------------------------
    // Validation rules helper — use in Form Requests / Controllers
    // Usage: Vm::rdpValidationRules()
    // -------------------------------------------------------------------------

    public static function rdpValidationRules(): array
    {
        return [
            'is_dummy'     => ['required', 'boolean'],
            'rdp_host'     => ['required_if:is_dummy,false', 'nullable', 'string', 'max:255'],
            'rdp_port'     => ['nullable', 'integer', 'min:1', 'max:65535'],
            'rdp_username' => ['required_if:is_dummy,false', 'nullable', 'string', 'max:255'],
            'rdp_password' => ['required_if:is_dummy,false', 'nullable', 'string'],
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

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

    /**
     * Users with direct access to this VM.
     */
    public function accessUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_vm_access')
                    ->withPivot('expires_at')
                    ->withTimestamps();
    }

    /**
     * Groups with access to this VM.
     */
    public function accessGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_vm_access')
                    ->withTimestamps();
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

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

    public function scopeRealRdp($query)
    {
        return $query->where('is_dummy', false);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

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
