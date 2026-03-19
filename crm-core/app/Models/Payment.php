<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'amount',
        'commission_amount',
        'payment_date',
        'payment_mode',
        'transaction_id',
        'subscription_plan',
        'status',
        'entered_by',
        'verified_by',
        'commission_ba',
        'commission_sba',
        'remarks'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_ba' => 'decimal:2',
        'commission_sba' => 'decimal:2',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
