<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppMessageLog extends Model
{
    protected $fillable = [
        'direction',
        'from_number',
        'to_number',
        'message_id',
        'template_name',
        'content',
        'media_type',
        'status',
        'lead_id',
        'client_id',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function client()
    {
        return $this->belongsTo(Lead::class, 'client_id');
    }
}
