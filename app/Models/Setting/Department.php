<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'department_name',
        'faculty',
    ];
    public $timestamps = false;

    public function user(){
        return $this->hasMany(User::class);
    }
}
