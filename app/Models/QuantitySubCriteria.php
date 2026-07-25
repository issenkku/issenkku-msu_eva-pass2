<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantitySubCriteria extends Model
{
    use HasFactory;

    protected $table = 'quantity_sub_criterias';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sequence',
        'score_a',
        'score_b',
        'quantity_main_criteria_id',
        'criteria_version_id',
        'evaluation_list_id',
        'description',
        'require_evidence',
        'require_subject',
    ];

    protected $casts = [
        'require_evidence' => 'boolean',
        'require_subject' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas(
            'evaluationList',
            fn (Builder $evaluationQuery) => $evaluationQuery
                ->where('quantity_enabled', true),
        );
    }

    public function groups()
    {
        return $this->hasMany(QuantitySubCriteriaGroup::class, 'quantity_sub_criteria_id')
            ->orderBy('sequence');
    }

    public function mainCriteria(): BelongsTo
    {
        return $this->belongsTo(QuantityMainCriteria::class, 'quantity_main_criteria_id');
    }

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class, 'criteria_version_id');
    }

    public function quantityScores()
    {
        return $this->hasMany(QuantityScore::class);
    }

    public function evaluationList(): BelongsTo
    {
        return $this->belongsTo(EvaluationList::class, 'evaluation_list_id');
    }
}
