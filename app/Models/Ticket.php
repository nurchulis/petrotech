<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Ticket extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ticket_number', 'title', 'description', 'category',
        'priority', 'status', 'assigned_to', 'created_by',
        'attachment_path', 'resolution_notes',
        'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->useLogName('ticket');
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('created_by', $user->id);
    }

    // Helpers
    public function getPriorityBadgeAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'danger',
            'high'     => 'warning',
            'medium'   => 'info',
            'low'      => 'secondary',
            default    => 'secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'primary',
            'in_progress' => 'warning',
            'resolved'    => 'success',
            'closed'      => 'secondary',
            default       => 'secondary',
        };
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Ticket $ticket) {
            $ticket->ticket_number = 'TKT-' . strtoupper(uniqid());
        });
    }
}
