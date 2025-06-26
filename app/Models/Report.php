<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'report_data_id',
        'report_code',
        'status',
    ];

    public function reportData(): BelongsTo
    {
        return $this->belongsTo(ReportData::class);
    }

    public function quantityScores()
    {
        return $this->hasMany(QuantityScore::class);
    }

    public function qualityScores()
    {
        return $this->hasMany(QualityScore::class);
    }

    public function evidenceAnswers()
    {
        return $this->hasMany(EvidenceAnswer::class);
    }
}
