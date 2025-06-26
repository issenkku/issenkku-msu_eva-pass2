<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityScore extends Model
{
    protected $table = 'quality_scores';

    protected $fillable = [
        'quality_sub_criteria_id',
        'evaluation_list_id',
        'report_id',
        'score',
    ];

    public function qualitySubCriteria(): BelongsTo
    {
        return $this->belongsTo(QualitySubCriteria::class);
    }

    public function evaluationList(): BelongsTo
    {
        return $this->belongsTo(EvaluationList::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
