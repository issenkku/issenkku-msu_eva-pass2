<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityMainCriteria extends Model
{
    protected $table = 'quality_main_criterias';

    protected $fillable = [
        'name',
        'ratio',
        'tooltips',
        'sequence',
        'criteria_version_id',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function qualitySubCriterias(): HasMany
    {
        return $this->hasMany(QualitySubCriteria::class);
    }
}
