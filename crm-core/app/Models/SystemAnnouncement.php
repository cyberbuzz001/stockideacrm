<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAnnouncement extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'title',
        'body',
        'type',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
