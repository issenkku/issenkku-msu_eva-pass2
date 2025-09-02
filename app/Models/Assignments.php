<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignments extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_data_id',
        'report_id',
        'evaluatee_id',
    ];

    public $timestamps = false;

    // Relationships
    public function assignmentData()
    {
        return $this->belongsTo(AssignmentData::class);
    }

    public function report()
    {
        return $this->belongsTo(Reports::class);
    }

    public function evaluateeUser()
    {
        return $this->belongsTo(User::class, 'evaluatee_id', 'id');
    }

    public function evaluatorUser()
    {
        return $this->hasOneThrough(
            User::class,              // Final model
            AssignmentData::class,    // Intermediate
            'id',                     // Local key on AssignmentData
            'position_id',            // Foreign key on Users
            'assignment_data_id',     // Local key on Assignments
            'evaluator_position_id'   // Foreign key on AssignmentData
        );
    }

    public function evaluatorUsers()
    {
        return $this->hasManyThrough(
            User::class,            // Final model
            AssignmentData::class,  // Intermediate
            'id',                   // Local key on AssignmentData
            'position_id',          // Foreign key on Users
            'assignment_data_id',   // Local key on Assignments
            'evaluator_position_id' // Foreign key on AssignmentData
        );
    }

    // Helper to get evaluator position
    public function evaluatorPosition()
    {
        return $this->assignmentData?->evaluatorPosition();
    }

    public function getEvaluatorUsers()
    {
        return $this->assignmentData
            ? $this->evaluatorUsers()->get()
            : collect();
    }

}
