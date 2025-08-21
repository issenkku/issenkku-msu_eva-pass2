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
        'evaluatee',
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
        return $this->belongsTo(User::class, 'evaluatee', 'id');
    }

    public function evaluatorUser()
    {
        // Get first user from evaluator position
        return $this->assignmentData ? 
            $this->assignmentData->evaluatorPosition()->first()?->user()->first() : 
            null;
    }

    public function evaluatorUsers()
    {
        // Get all users from evaluator position
        return $this->assignmentData ? 
            $this->assignmentData->evaluatorPosition()->first()?->user ?? collect() : 
            collect();
    }

    // Helper to get evaluator position
    public function evaluatorPosition()
    {
        return $this->assignmentData?->evaluatorPosition();
    }

    public function getEvaluatorUser()
    {
        return $this->assignmentData
            ? $this->assignmentData->evaluatorPosition()->first()?->user()->first()
            : null;
    }
}
