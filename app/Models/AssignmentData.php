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

    // ดึง user ที่เกี่ยวข้องกับ assignment data (เช่น evaluator หรือ evaluatee)
    public function evaluators()
    {
        return $this->hasManyThrough(
            User::class,
            Assignments::class,
            'assignment_data_id', // Foreign key on assignments table
            'id', // Foreign key on users table
            'id', // Local key on assignment_datas table
            'evaluator' // Local key on assignments table
        );
    }

    public function evaluatees()
    {
        return $this->hasManyThrough(
            User::class,
            Assignments::class,
            'assignment_data_id',
            'id',
            'id',
            'evaluatee'
        );
    }
}
