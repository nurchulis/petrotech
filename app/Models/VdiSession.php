<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VdiSession extends Model
{
    // Valid status values
    const STATUS_CONNECTING = 'connecting';
    const STATUS_ACTIVE     = 'active';
    const STATUS_CLOSED     = 'closed';
    const STATUS_FAILED     = 'failed';
    const STATUS_TERMINATED = 'terminated'; // legacy

    protected $fillable = [
        'vm_id',
        'user_id',
        'guacamole_connection_id',
        'status',
        'started_at',
        'ended_at',
        // Legacy fields — kept for backward compatibility
        'protocol',
        'session_token',
        'connected_at',
        'disconnected_at',
        'duration_minutes',
    ];

    protected $casts = [
        'started_at'      => 'datetime',
        'ended_at'        => 'datetime',
        'connected_at'    => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function vm(): BelongsTo
    {
        return $this->belongsTo(Vm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeGuacamole($query)
    {
        return $query->whereNotNull('guacamole_connection_id');
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    /**
     * Terminate a Guacamole or legacy session.
     * Sets status to closed/terminated, records end time.
     */
    public function terminate(): void
    {
        $now = now();

        $update = [
            'status'   => $this->guacamole_connection_id
                ? self::STATUS_CLOSED
                : self::STATUS_TERMINATED,
            'ended_at' => $now,
            // Legacy fields
            'disconnected_at'  => $now,
            'duration_minutes' => $this->started_at
                ? (int) abs($now->diffInMinutes($this->started_at))
                : ($this->connected_at
                    ? (int) abs($now->diffInMinutes($this->connected_at))
                    : 0),
        ];

        $this->update($update);
    }
}
