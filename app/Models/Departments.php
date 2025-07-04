<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $fillable = [
        'department_name',
        'faculty',
    ];
    public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }
}

