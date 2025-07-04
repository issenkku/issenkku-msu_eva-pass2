<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class Positions extends Model
{
    protected $fillable = [
        'name',
        'description',

    ];

    public function user(){
        return $this->hasMany(User::class);
    }
}
