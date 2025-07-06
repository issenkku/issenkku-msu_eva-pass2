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
        'evaluator',
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
        return $this->belongsTo(User::class, 'evaluator', 'id');
    }
}
