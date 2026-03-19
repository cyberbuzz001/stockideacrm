<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisoryCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'segment',
        'call_text',
        'outcome',
        'call_date',
        'call_time',
        'user_id',
    ];

    protected $casts = [
        'call_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
