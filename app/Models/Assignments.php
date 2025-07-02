<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignments extends Model
{
    // ชื่อตาราง 
    protected $table = 'assignments';

    // สามารถกำหนด fillable ได้หากมีการใช้ mass assignment
    protected $fillable = [
        'period',
        'start_time',
        'end_time',
        'report_id',
        'evaluatee',
    ];

    // หากใช้ soft delete:
    // use \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     * Assignment belongs to one Report
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    /**
     * Assignment มีผู้รับการประเมินคนเดียว (Evaluatee)
     */
    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluatee');
    }

    /**
     * Assignment มีหลาย Evaluator
     */
    public function evaluators(): HasMany
    {
        return $this->hasMany(Evaluator::class, 'assignment_id');
    }
}
