<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CriteriaVersion extends Model
{
    protected $table = 'criteria_versions';

    protected $fillable = [
        'version_name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function quantityMainCriterias(): HasMany
    {
        return $this->hasMany(QuantityMainCriteria::class);
    }

    public function qualityMainCriterias(): HasMany
    {
        return $this->hasMany(QualityMainCriteria::class);
    }

    public function reportDatas(): HasMany
    {
        return $this->hasMany(ReportData::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function evaluationLists(): HasMany
    {
        return $this->hasMany(EvaluationList::class);
    }
}
