<?php

namespace App\Models\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Departments extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'department_name',
        // 'faculty',
    ];

    public $timestamps = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('จัดการหน่วยงาน') // custom log_name in DB
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'updated' => 'แก้ไขข้อมูลหน่วยงาน',
                    'created' => 'สร้างหน่วยงานใหม่',
                    'deleted' => 'ลบข้อมูลหน่วยงาน',
                    default => $eventName,
                };
            });
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }
}
