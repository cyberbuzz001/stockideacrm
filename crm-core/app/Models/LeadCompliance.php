<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadCompliance extends Model
{
    protected $fillable = [
        'lead_id',
        'kyc_status',
        'rpm_status',
        'kyc_verified_at',
        'rpm_verified_at',
        'kyc_expires_at',
        'rpm_expires_at',
    ];

    protected $casts = [
        'kyc_verified_at' => 'datetime',
        'rpm_verified_at' => 'datetime',
        'kyc_expires_at' => 'datetime',
        'rpm_expires_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function steps()
    {
        return $this->hasMany(LeadComplianceStep::class);
    }
}
