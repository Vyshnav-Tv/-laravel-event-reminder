<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'event_date',
    ];

}
