<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantityScore extends Model
{
    public $incrementing = false;

    protected $table = 'quantity_scores';
    protected $primaryKey = ['quantity_sub_criteria_id', 'report_id'];
    protected $fillable = [
        'quantity_sub_criteria_id',
        'report_id',
        'score_C',
        'score_D',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quantitySubCriteria(): BelongsTo
    {
        return $this->belongsTo(QuantitySubCriteria::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
