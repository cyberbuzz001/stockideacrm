<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientConsent extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'channel',
        'purpose',
        'status',
        'consented_at',
        'revoked_at',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
