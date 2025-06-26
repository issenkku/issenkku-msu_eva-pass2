<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationList extends Model
{
    protected $table = 'evaluation_lists';

    protected $fillable = [
        'name',
        'sum_score',
        'sequence',
        'annotation',
        'criteria_version_id',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
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
