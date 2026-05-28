<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuantitySubCriteriaGroup extends Model
{
    use HasFactory;

    protected $table = 'quantity_sub_criteria_groups';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sequence',
        'quantity_sub_criteria_id',
        'criteria_version_id',
        'evaluation_list_id',
    ];

    public function subCriteria(): BelongsTo
    {
        return $this->belongsTo(QuantitySubCriteria::class, 'quantity_sub_criteria_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuantitySubCriteriaItem::class, 'quantity_sub_criteria_group_id')
            ->orderBy('sequence');
    }
}
