<?php

namespace App\Models\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Positions extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('จัดการตำแหน่งงาน') // custom log_name in DB
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'updated' => 'แก้ไขข้อมูลตำแหน่งงาน',
                    'created' => 'สร้างตำแหน่งงานใหม่',
                    'deleted' => 'ลบข้อมูลตำแหน่งงาน',
                    default => $eventName,
                };
            });
    }

    public function user()
    {
        return $this->hasMany(User::class, 'position_id');
    }
}
