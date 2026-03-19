<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type', // e.g., 'Login', 'Logout', 'User Created'
        'description',   // Details
        'ip_address',
        'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
