<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenseServer extends Model
{
    protected $fillable = [
        'server_name', 'hostname', 'ip_address', 'port',
        'os_type', 'location', 'status',
    ];

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }
}
