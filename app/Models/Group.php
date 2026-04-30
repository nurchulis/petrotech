<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Group extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->useLogName('group');
    }

    // ── Relationships ────────────────────────────────────────────────────────

    /**
     * Users belonging to this group.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * VMs accessible to this group.
     */
    public function vms(): BelongsToMany
    {
        return $this->belongsToMany(Vm::class, 'group_vm_access')
                    ->withTimestamps();
    }
}
