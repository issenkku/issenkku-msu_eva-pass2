<?php

namespace App\Models\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Positions extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'position_id');
    }
}
