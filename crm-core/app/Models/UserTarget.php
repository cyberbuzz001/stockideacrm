<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTarget extends Model
{
    protected $fillable = ['user_id', 'amount', 'month_year'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
