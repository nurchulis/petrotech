<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageMetric extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'storage_device_id', 'used_space_gb', 'free_space_gb',
        'usage_percentage', 'recorded_at',
    ];

    protected $casts = [
        'recorded_at'      => 'datetime',
        'used_space_gb'    => 'decimal:2',
        'free_space_gb'    => 'decimal:2',
        'usage_percentage' => 'decimal:2',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(StorageDevice::class, 'storage_device_id');
    }

    public function scopeLastDays($query, int $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }
}
