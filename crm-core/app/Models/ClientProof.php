<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientProof extends Model
{
    protected $fillable = [
        'client_id',
        'payment_id',
        'has_confirmed_service',
        'has_agreed_terms',
        'confirmed_service_at',
        'agreed_terms_at',
        'usage_proof_paths',
    ];

    protected $casts = [
        'has_confirmed_service' => 'boolean',
        'has_agreed_terms' => 'boolean',
        'confirmed_service_at' => 'datetime',
        'agreed_terms_at' => 'datetime',
        'usage_proof_paths' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Lead::class, 'client_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
