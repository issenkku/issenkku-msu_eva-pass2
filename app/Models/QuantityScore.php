<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantityScore extends Model
{
    protected $table = 'quantity_scores';

    protected $fillable = [
        'quantity_sub_criteria_id',
        'evaluation_list_id',
        'report_id',
        'score_C',
        'score_D',
    ];

    public function quantitySubCriteria(): BelongsTo
    {
        return $this->belongsTo(QuantitySubCriteria::class);
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
