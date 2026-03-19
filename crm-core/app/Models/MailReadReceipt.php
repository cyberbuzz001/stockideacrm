<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailReadReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get the mail this receipt belongs to
     */
    public function mail()
    {
        return $this->belongsTo(InternalMail::class, 'mail_id');
    }

    /**
     * Get the user who read the mail
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
