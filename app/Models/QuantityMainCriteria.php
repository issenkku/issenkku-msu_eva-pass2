<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuantityMainCriteria extends Model
{
    use HasFactory;

    protected $table = 'quantity_main_criterias';

    public $timestamps = false;

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
        return $this->hasMany(QuantitySubCriteria::class, 'quantity_main_criteria_id');
    }

    // helper function to get the main criteria name
    public function mainCriteria()
    {
        return $this->belongsTo(QuantityMainCriteria::class, 'quantity_main_criteria_id');
    }
}
