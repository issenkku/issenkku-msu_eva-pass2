<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $fillable = [
        'department_name',
        'faculty',
    ];
    public $timestamps = false;
}
