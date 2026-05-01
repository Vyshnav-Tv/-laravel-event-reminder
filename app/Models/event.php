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

    public function reminder(){
        return $this->hasMany(reminder::class);
    }

    public function user()
    {
        return $this->belongTo(User::class);
    }

}
