<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'faculty',
        'university'
    ];

    public $timestamps = false; // Assuming you don't want timestamps for this model
}
