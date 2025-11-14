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
        // Link evaluator via assignment_datas.evaluator_id -> users.id
        return $this->hasOneThrough(
            User::class,
            AssignmentData::class,
            'id',                // Join assignment_datas.id = assignments.assignment_data_id
            'id',                // Join users.id = assignment_datas.evaluator_id
            'assignment_data_id',
            'evaluator_id'
        );
    }

    public function evaluatorUsers()
    {
        return $this->hasManyThrough(
            User::class,
            AssignmentData::class,
            'id',                // assignment_datas.id = assignments.assignment_data_id
            'id',                // users.id = assignment_datas.evaluator_id
            'assignment_data_id',
            'evaluator_id'
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
