<?php

namespace App\Models\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Positions extends Model
{
    protected $fillable = [
        'name',
        'description',

    ];

    public function user()
    {
        return $this->hasMany(User::class, 'position_id');
    }
}
