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
        // This creates a proper HasOneThrough relationship
        return $this->hasOneThrough(
            User::class,
            AssignmentData::class,
            'id',                    // Foreign key on assignment_datas table
            'position_id',           // Foreign key on users table
            'assignment_data_id',    // Local key on assignments table
            'evaluator_position_id'  // Local key on assignment_datas table
        );
    }

    public function evaluatorUsers()
    {
        // Get all users from evaluator position safely
        if (! $this->assignmentData || ! $this->assignmentData->evaluator_position_id) {
            return collect();
        }

        return User::where('position_id', $this->assignmentData->evaluator_position_id)->get();
    }

    // Helper to get evaluator position
    public function evaluatorPosition()
    {
        return $this->assignmentData?->evaluatorPosition();
    }
}
