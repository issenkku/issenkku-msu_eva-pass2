<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Setting\Positions;

class AssignmentData extends Model
{
    use HasFactory;

    protected $table = 'assignment_datas';

    protected $fillable = [
        'evaluator_position_id',
        'evaluatee_position_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'evaluator_position_id' => 'integer',
        'evaluatee_position_id' => 'integer',
        'start_time' => 'date',
        'end_time' => 'date',
    ];

    // Relationships
    public function assignments()
    {
        return $this->hasMany(Assignments::class);
    }

    public function evaluatorPosition()
    {
        return $this->belongsTo(Positions::class, 'evaluator_position_id');
    }

    public function evaluateePosition()
    {
        return $this->belongsTo(Positions::class, 'evaluatee_position_id');
    }

    public function positions()
    {
        return $this->hasMany(Positions::class, 'id', 'evaluator_position_id');
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
