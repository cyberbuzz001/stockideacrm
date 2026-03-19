<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDocument extends Model
{
    protected $fillable = [
        'lead_id',
        'uploaded_by',
        'doc_type',
        'storage_path',
        'original_name',
        'mime_type',
        'file_size',
        'sha256',
        'expires_at',
        'watermark_text',
        'access_token',
        'access_expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'access_expires_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
