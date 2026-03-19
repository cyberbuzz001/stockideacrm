<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'lead_id',
        'channel_type',
        'channel_name',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the sender of the message
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope for direct messages between two users
     */
    public function scopeDirectBetween($query, $userId1, $userId2)
    {
        return $query->where('channel_type', 'direct')
            ->where(function ($q) use ($userId1, $userId2) {
                $q->where(function ($subQ) use ($userId1, $userId2) {
                    $subQ->where('sender_id', $userId1)
                        ->where('receiver_id', $userId2);
                })->orWhere(function ($subQ) use ($userId1, $userId2) {
                    $subQ->where('sender_id', $userId2)
                        ->where('receiver_id', $userId1);
                });
            });
    }

    /**
     * Scope for group messages
     */
    public function scopeGroupChannel($query, $channelName)
    {
        return $query->where('channel_type', 'group')
            ->where('channel_name', $channelName);
    }
}
