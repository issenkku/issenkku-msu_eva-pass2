<?php

namespace App\Models\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Departments extends Model
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
