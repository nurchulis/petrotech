<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['name', 'description', 'license_server_id', 'status'];

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function server()
    {
        return $this->belongsTo(LicenseServer::class, 'license_server_id');
    }
}
