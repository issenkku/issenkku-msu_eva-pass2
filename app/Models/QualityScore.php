<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QualityScore extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'quality_scores';
    protected $primaryKey = ['quality_sub_criteria_id', 'report_id'];
    protected $fillable = [
        'quality_sub_criteria_id',
        'report_id',
        'score',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function qualitySubCriteria(): BelongsTo
    {
        return $this->belongsTo(QualitySubCriteria::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
