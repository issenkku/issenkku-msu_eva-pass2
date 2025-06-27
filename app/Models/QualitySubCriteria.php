<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualitySubCriteria extends Model
{
    protected $table = 'quality_sub_criterias';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'sequence',
        'num_score',
        'quality_main_criteria_id',
        'criteria_version_id',
        'evaluation_list_id',
    ];

    public function mainCriteria(): BelongsTo
    {
        return $this->belongsTo(QualityMainCriteria::class, 'quality_main_criteria_id');
    }

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function qualityScores()
    {
        return $this->hasMany(QualityScore::class);
    }

    public function evaluationList(): BelongsTo
    {
        return $this->belongsTo(EvaluationList::class, 'evaluation_list_id');
    }
}
