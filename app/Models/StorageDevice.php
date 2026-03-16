<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StorageDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_name', 'storage_type', 'total_space_gb',
        'mount_location', 'region', 'data_center', 'ip_address', 'status',
    ];

    protected $casts = [
        'total_space_gb' => 'decimal:2',
    ];

    public function metrics(): HasMany
    {
        return $this->hasMany(StorageMetric::class);
    }

    public function latestMetric(): ?StorageMetric
    {
        return $this->metrics()->latest('recorded_at')->first();
    }

    public function getUsagePercentAttribute(): float
    {
        $latest = $this->latestMetric();
        return $latest ? (float) $latest->usage_percentage : 0;
    }

    public function getUsageBadgeAttribute(): string
    {
        $pct = $this->usage_percent;
        if ($pct >= 90) return 'danger';
        if ($pct >= 75) return 'warning';
        return 'success';
    }
}
