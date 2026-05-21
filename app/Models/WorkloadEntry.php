<?php

// ไฟล์คลาสของระบบ: app/Models/WorkloadEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkloadEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_values',
        'calculated_score',
        'report_id',
        'workload_form_id',
        'subject_id',
    ];

    protected $casts = [
        'field_values' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(WorkloadForm::class, 'workload_form_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
