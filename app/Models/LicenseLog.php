<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'license_id', 'event_type', 'event_detail', 'user_count', 'recorded_at', 'ip_address',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
