<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadComplianceStep extends Model
{
    protected $fillable = [
        'lead_id',
        'step_key',
        'status',
        'completed_at',
        'completed_by',
        'metadata',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
