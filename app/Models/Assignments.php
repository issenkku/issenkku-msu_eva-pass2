<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignments extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_data_id',
        'report_id',
        'evaluatee',
        'evaluator',
    ];

    public $timestamps = false;



    // Relationships
    public function assignmentData()
    {
        return $this->belongsTo(AssignmentData::class);
    }

    public function evaluateeUser()
    {
        return $this->belongsTo(User::class, 'evaluatee', 'id');
    }

    public function evaluatorUser()
    {
        return $this->belongsTo(User::class, 'evaluator', 'id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
