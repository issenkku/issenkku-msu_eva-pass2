<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WorkloadEntry extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('ภาระงาน')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => 'เพิ่มข้อมูลภาระงาน',
                    'updated' => 'แก้ไขข้อมูลภาระงาน',
                    'deleted' => 'ลบข้อมูลภาระงาน',
                    default => $eventName,
                };
            });
    }

    public function form()
    {
        return $this->belongsTo(WorkloadForm::class, 'workload_form_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
