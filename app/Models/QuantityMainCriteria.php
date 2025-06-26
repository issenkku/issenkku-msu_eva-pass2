<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantityMainCriteria extends Model
{
    protected $table = 'quantity_main_criterias';

    protected $fillable = [
        'name',
        'tooltips',
        'criteria_version_id',
    ];

    public function criteriaVersion(): BelongsTo
    {
        return $this->belongsTo(CriteriaVersion::class);
    }

    public function quantitySubCriterias(): HasMany
    {
        return $this->hasMany(QuantitySubCriteria::class);
    }
}