<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class License extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'license_name', 'application_name', 'vendor_id', 'version', 'total_seats', 'used_seats',
        'license_key', 'status', 'expiry_date', 'log_file_path', 'license_server_id', 'notes', 'created_by',
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->useLogName('license');
    }

    // Relationships
    public function server(): BelongsTo
    {
        return $this->belongsTo(LicenseServer::class, 'license_server_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LicenseLog::class);
    }

    public function allowedUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'license_user_access')
                    ->withPivot(['granted_by', 'expires_at'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'enable');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString());
    }

    // Helpers
    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) now()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryBadgeAttribute(): string
    {
        $days = $this->days_until_expiry;
        if ($days < 0) return 'danger';
        if ($days <= 30) return 'warning';
        return 'success';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date ? $this->expiry_date->isPast() : false;
    }
}
