<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalMail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'subject',
        'body',
        'category',
        'recipient_ids',
        'attachment_path',
    ];

    protected $casts = [
        'recipient_ids' => 'array',
    ];

    protected $appends = [
        'attachment_url',
    ];

    /**
     * Get the sender of the mail
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get all read receipts for this mail
     */
    public function readReceipts()
    {
        return $this->hasMany(MailReadReceipt::class, 'mail_id');
    }

    /**
     * Check if a specific user has read this mail
     */
    public function isReadBy($userId)
    {
        return $this->readReceipts()->where('user_id', $userId)->exists();
    }

    /**
     * Get unread count for this mail
     */
    public function getUnreadCountAttribute()
    {
        if (in_array('all', $this->recipient_ids)) {
            $totalRecipients = User::count() - 1; // Exclude sender
        } else {
            $totalRecipients = count($this->recipient_ids);
        }

        return $totalRecipients - $this->readReceipts()->count();
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->attachment_path);
    }
}
