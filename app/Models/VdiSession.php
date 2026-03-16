<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VdiSession extends Model
{
    protected $fillable = [
        'vm_id', 'user_id', 'protocol', 'status',
        'session_token', 'connected_at', 'disconnected_at', 'duration_minutes',
    ];

    protected $casts = [
        'connected_at'    => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    public function vm(): BelongsTo
    {
        return $this->belongsTo(Vm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function terminate(): void
    {
        $now = now();
        $durationMinutes = $this->connected_at
            ? (int) abs($now->diffInMinutes($this->connected_at))
            : 0;

        $this->update([
            'status'           => 'terminated',
            'disconnected_at'  => $now,
            'duration_minutes' => $durationMinutes,
        ]);
    }
}
