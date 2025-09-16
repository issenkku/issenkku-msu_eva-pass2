<?php

namespace App\Models\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departments extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_name',
        // 'faculty',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->hasMany(User::class);
    }
}
