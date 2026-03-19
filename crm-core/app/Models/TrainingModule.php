<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingModule extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'file_type',
        'target_team',
        'schedule_type',
        'deadline_date',
    ];

    protected $casts = [
        'deadline_date' => 'date',
    ];

    protected $appends = [
        'file_url',
    ];

    public function getFileUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path);
    }

    public function logs()
    {
        return $this->hasMany(TrainingLog::class, 'module_id');
    }
}
