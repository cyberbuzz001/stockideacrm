<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'login_at',
        'logout_at',
        'total_minutes',
        'ip_address',
        'last_activity_at',
        'effective_hours',
        'idle_duration',
        'total_dials',
        'productivity_score',
        'status',
        'attendance_type',
        'admin_remarks',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'effective_hours' => 'decimal:2',
        'productivity_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
