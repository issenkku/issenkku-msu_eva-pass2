<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'note',
        'default_value',
        'variable_name',
        'field_type',
        'workload_form_id',
    ];

    public function form()
    {
        return $this->belongsTo(WorkloadForm::class, 'workload_form_id');
    }
}
