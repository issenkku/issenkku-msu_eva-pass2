<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadFormItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'score',
        'sequence',
        'workload_form_id',
    ];

    public function form()
    {
        return $this->belongsTo(WorkloadForm::class, 'workload_form_id');
    }
}
