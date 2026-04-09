<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory, \App\Traits\ShardsByYear;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            $lead->status_changed_at = now();
        });

        static::updating(function ($lead) {
            if ($lead->isDirty('status')) {
                $lead->status_changed_at = now();
            }
        });
    }

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'company',
        'city',
        'status',
        'status_history',
        'status_change_reason',
        'lead_score',
        'win_probability',
        'enrichment_data',
        'sentiment_label',
        'source',
        'assigned_to',
        'assigned_by',
        'notes',
        'remarks',
        'follow_up_date',
        'trial_start_date',
        'trial_end_date',
        'service_start_date',
        'renewal_date',
        'next_follow_up',
        'is_kyc_completed',
        'is_rpm_completed',
        'pan_number',
        'aadhaar_number',
        'interest_segments',
        'is_trading',
        'expected_payment',
        'is_escalated',
        'follow_up_notes',
        'conversion_notes',
        'service_expired_at',
        'escalated_at',
        'alt_mobile',
        'address',
        'demat_id',
        'demat_status',
        'investment_cap',
        'experience_level',
        'internal_notes',
        'npc_count',
        'last_npc_at',
        'last_seen_at',
        'status_changed_at',
    ];

    protected $hidden = [
        'pan_number',
        'aadhaar_number',
    ];

    protected $appends = ['completion_percentage', 'is_stale'];

    /**
     * Determine if a lead is "stale" (no activity for > 3 days)
     */
    public function getIsStaleAttribute()
    {
        $closedStatuses = ['Paid Client', 'Lost', 'Junk', 'Not Interested'];
        if (in_array($this->status, $closedStatuses)) {
            return false;
        }

        $lastTouched = $this->last_seen_at ?? $this->updated_at;
        return $lastTouched && $lastTouched->diffInDays(now()) >= 3;
    }
    /**
     * Get Profile Completion Percentage
     */
    public function getCompletionPercentageAttribute()
    {
        $fields = [
            'name',
            'email',
            'mobile',
            'city',
            'alt_mobile',
            'address',
            'pan_number',
            'aadhaar_number',
            'demat_id',
            'investment_cap',
            'experience_level'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    /**
     * Mask Sensitive Data for non-admins
     */
    public function getMaskedPanAttribute()
    {
        if (optional(auth()->user())->role === 'Admin') {
            return $this->pan_number;
        }
        return $this->pan_number ? 'XXXXX' . substr($this->pan_number, -4) : null;
    }

    public function getMaskedAadhaarAttribute()
    {
        if (optional(auth()->user())->role === 'Admin') {
            return $this->aadhaar_number;
        }
        return $this->aadhaar_number ? 'XXXXXXXX' . substr($this->aadhaar_number, -4) : null;
    }

    public function getMaskedPanForRole(?string $role): ?string
    {
        if ($role === 'Admin') {
            return $this->pan_number;
        }
        return $this->pan_number ? 'XXXXX' . substr($this->pan_number, -4) : null;
    }

    public function getMaskedAadhaarForRole(?string $role): ?string
    {
        if ($role === 'Admin') {
            return $this->aadhaar_number;
        }
        return $this->aadhaar_number ? 'XXXXXXXX' . substr($this->aadhaar_number, -4) : null;
    }

    protected $casts = [
        'enrichment_data' => 'json',
        'lead_score' => 'integer',
        'win_probability' => 'float',
        'follow_up_date' => 'datetime',
        'trial_start_date' => 'datetime',
        'trial_end_date' => 'datetime',
        'service_start_date' => 'date',
        'renewal_date' => 'date',
        'next_follow_up' => 'datetime',
        'status_history' => 'array',
        'interest_segments' => 'json',
        'is_trading' => 'boolean',
        'is_kyc_completed' => 'boolean',
        'is_rpm_completed' => 'boolean',
        'is_escalated' => 'boolean',
        'last_npc_at' => 'datetime',
        'service_expired_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Add status change to history
     */
    public function addStatusHistory($oldStatus, $newStatus, $reason = null)
    {
        $history = $this->status_history ?? [];

        $history[] = [
            'from' => $oldStatus,
            'to' => $newStatus,
            'reason' => $reason,
            'changed_by' => auth()->id(),
            'changed_at' => now()->toDateTimeString(),
        ];

        $this->status_history = $history;
        $this->status_change_reason = $reason;
        $this->save();
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getWhatsappNumberAttribute()
    {
        return preg_replace('/[^0-9]/', '', $this->mobile);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function messages()
    {
        return $this->hasMany(LeadMessage::class);
    }

    public function consents()
    {
        return $this->hasMany(ClientConsent::class);
    }

    public function documents()
    {
        return $this->hasMany(LeadDocument::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(DataAccessLog::class);
    }

    public function compliance()
    {
        return $this->hasOne(LeadCompliance::class);
    }

    public function complianceSteps()
    {
        return $this->hasMany(LeadComplianceStep::class);
    }

    public function clientProof()
    {
        return $this->hasOne(ClientProof::class, 'client_id');
    }

    public function whatsappLogs()
    {
        return $this->hasMany(WhatsAppMessageLog::class, 'lead_id');
    }

    /**
     * Get Compliance Progress Data for UI
     */
    public function getComplianceProgress()
    {
        $steps = [
            'step_1_payment'   => 'Payment Confirmation',
            'step_2_activation' => 'Service Activation (YES)',
            'step_3_terms'      => 'Terms Acceptance (AGREE)',
            'step_4_delivery'   => 'Trade Delivery Start',
            'step_5_usage'      => 'Usage Proof (Screenshot)',
            'step_6_followup'   => 'Regular Follow-up',
            'step_7_continuity' => 'Continuity Proof',
            'step_8_completion' => 'Service Completion',
        ];

        $completedSteps = $this->complianceSteps->where('status', 'completed')->pluck('step_key')->toArray();
        $total = count($steps);
        $done = count($completedSteps);
        
        $percentage = $total > 0 ? round(($done / $total) * 100) : 0;

        return [
            'percentage' => $percentage,
            'is_safe' => $percentage >= 35, // After YES and AGREE
            'steps' => collect($steps)->map(function($label, $key) use ($completedSteps) {
                return [
                    'key' => $key,
                    'label' => $label,
                    'is_completed' => in_array($key, $completedSteps)
                ];
            })->values()
        ];
    }
}
