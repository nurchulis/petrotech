<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['name', 'name_server', 'description', 'license_server_id', 'port', 'status', 'last_updated'];

    protected $casts = [
        'last_updated' => 'datetime',
    ];

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function server()
    {
        return $this->belongsTo(LicenseServer::class, 'license_server_id');
    }
}
