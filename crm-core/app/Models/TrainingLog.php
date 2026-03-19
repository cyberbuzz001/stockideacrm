<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingLog extends Model
{
    protected $fillable = [
        'user_id',
        'module_id',
        'completion_status',
        'time_spent',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(TrainingModule::class, 'module_id');
    }
}
