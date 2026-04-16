<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reminder extends Model
{
    protected $fillable = [
        "event_id",
        "reminder_time",
        "status"
    ];
}
