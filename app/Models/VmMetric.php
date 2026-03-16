<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VmMetric extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'vm_id', 'cpu_utilisation', 'memory_utilisation',
        'disk_io_read_mb', 'disk_io_write_mb',
        'network_in_mb', 'network_out_mb',
        'gpu_utilisation', 'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function vm(): BelongsTo
    {
        return $this->belongsTo(Vm::class);
    }

    public function scopeInPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }

    public function scopeLastHours($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }
}
