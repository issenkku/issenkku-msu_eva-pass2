<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'formula_logic',
        'quantity_sub_criteria_id',
        'quantity_sub_criteria_item_id',
    ];

    public function fields()
    {
        return $this->hasMany(WorkloadFormField::class);
    }

    public function items()
    {
        return $this->hasMany(WorkloadFormItem::class)->orderBy('sequence');
    }

    public function subCriteriaItem()
    {
        return $this->belongsTo(QuantitySubCriteriaItem::class, 'quantity_sub_criteria_item_id');
    }
}
