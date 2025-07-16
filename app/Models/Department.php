<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Department extends Model
{
    protected $fillable = [
        'department_name',
        //'faculty',
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }
}
