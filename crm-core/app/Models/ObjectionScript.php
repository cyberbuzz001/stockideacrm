<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectionScript extends Model
{
    protected $fillable = ['tag', 'title', 'script', 'is_active'];
}
