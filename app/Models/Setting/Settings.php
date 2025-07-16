<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'faculty',
        'university',
        'notification_days',
    ];

    public $timestamps = false; // Assuming you don't want timestamps for this model
}
