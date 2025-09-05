<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualitySubCriteria extends Model
{
    use HasFactory;

    protected $table = 'quality_sub_criterias';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sequence',
        'num_score',
        'description',
        'quality_main_criteria_id',
        'criteria_version_id',
        'evaluation_list_id',
    ];

    public function qualityMainCriteria(): BelongsTo
    {
        return $this->belongsTo(QualityMainCriteria::class, 'quality_main_criteria_id');
    }

    // Alias for consistency with QuantitySubCriteria
    public function mainCriteria(): BelongsTo
    {
        return $this->qualityMainCriteria();
    }

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class, 'criteria_version_id');
    }

    public function qualityScores()
    {
        return $this->hasMany(QualityScore::class);
    }

    public function evaluationList(): BelongsTo
    {
        return $this->belongsTo(EvaluationList::class, 'evaluation_list_id');
    }

    public function evidenceAnswers()
    {
        return $this->hasMany(EvidenceAnswer::class, 'evaluation_list_id', 'evaluation_list_id');
    }
}
