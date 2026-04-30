<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\CausesActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, CausesActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'department',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Relationships
    public function vdiSessions(): HasMany
    {
        return $this->hasMany(VdiSession::class);
    }

    public function assignedVms(): HasMany
    {
        return $this->hasMany(Vm::class, 'assigned_user_id');
    }

    /**
     * Groups this user belongs to.
     */
    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * VMs directly assigned to this user (many-to-many access control).
     */
    public function directVmAccess(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Vm::class, 'user_vm_access')
                    ->withPivot('expires_at')
                    ->withTimestamps();
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function ticketComments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function accessibleLicenses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(License::class, 'license_user_access')
                    ->withPivot(['granted_by', 'expires_at'])
                    ->withTimestamps();
    }

    public function createdLicenses(): HasMany
    {
        return $this->hasMany(License::class, 'created_by');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    // Helpers
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1a3c5e&color=fff';
    }
}
