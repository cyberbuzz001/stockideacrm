<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataAccessLog extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'field',
        'action',
        'context',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
