<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationList extends Model
{
    use HasFactory;

    protected $table = 'evaluation_lists';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sum_score',
        'sequence',
        'annotation',
        'quantity_enabled',
        'categorie_id',
        'criteria_version_id',
    ];

    protected $casts = [
        'quantity_enabled' => 'boolean',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function evidenceAnswers()
    {
        return $this->hasMany(EvidenceAnswer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }

    public function quantitySubCriterias()
    {
        return $this->hasMany(QuantitySubCriteria::class, 'evaluation_list_id', 'id');
    }

    public function qualitySubCriterias()
    {
        return $this->hasMany(QualitySubCriteria::class, 'evaluation_list_id', 'id');
    }

    public function supportCriterias(): HasMany
    {
        return $this->hasMany(SupportCriteria::class)->orderBy('sequence');
    }
}
