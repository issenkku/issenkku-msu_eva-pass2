<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class JobLevel extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('จัดการระดับตำแหน่งงาน')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'updated' => 'แก้ไขข้อมูลระดับตำแหน่งงาน',
                    'created' => 'สร้างระดับตำแหน่งงานใหม่',
                    'deleted' => 'ลบข้อมูลระดับตำแหน่งงาน',
                    default => $eventName,
                };
            });
    }
}
