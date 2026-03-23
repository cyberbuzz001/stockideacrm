<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'fileName',
        'sentiment',
        'sentiment_score',
        'keywords',
        'summary',
        'transcript',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
