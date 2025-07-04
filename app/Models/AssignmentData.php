<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentData extends Model
{
    use HasFactory;
    protected $table = 'assignment_datas';
    protected $fillable = [
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'date',
        'end_time' => 'date',
    ];

    // Relationships
    public function assignments()
    {
        return $this->hasMany(Assignments::class);
    }
}