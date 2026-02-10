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
    ];

    public function fields()
    {
        return $this->hasMany(WorkloadFormField::class);
    }
}
