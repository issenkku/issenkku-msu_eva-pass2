<?php

namespace App\Models;

use App\Models\Setting\Positions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssignmentData extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'assignment_datas';

    protected $fillable = [
        'evaluator_id',
        'evaluator_position_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'evaluator_id' => 'integer',
        'evaluator_position_id' => 'integer',
        'start_time' => 'date',
        'end_time' => 'date',
    ];

    // Relationships
    public function assignments()
    {
        return $this->hasMany(Assignments::class);
    }

    public function evaluatorPosition()
    {
        return $this->belongsTo(Positions::class, 'evaluator_position_id');
    }

    public function evaluateePosition()
    {
        return $this->belongsTo(Positions::class, 'evaluatee_position_id');
    }

    public function positions()
    {
        return $this->hasMany(Positions::class, 'id', 'evaluator_position_id');
    }

    public function evaluatorUser()
    {
        return $this->belongsTo(User::class, 'evaluator_id', 'id');
    }

    // ดึง user ที่เกี่ยวข้องกับ assignment data (เช่น evaluator หรือ evaluatee)
    public function evaluators()
    {
        return $this->hasManyThrough(
            User::class,
            Assignments::class,
            'assignment_data_id', // Foreign key on assignments table
            'id', // Foreign key on users table
            'id', // Local key on assignment_datas table
            'evaluatee_id' // Local key on assignments table
        );
    }

    public function evaluatees()
    {
        return $this->hasManyThrough(
            User::class,
            Assignments::class,
            'assignment_data_id',
            'id',
            'id',
            'evaluatee_id'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('จัดการรอบการประเมิน') // custom log_name in DB
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'updated' => 'แก้ไขรอบการประเมิน',
                    'created' => 'สร้างรอบการประเมิน',
                    'deleted' => 'ลบรอบการประเมิน',
                    default => $eventName,
                };
            });
    }
}
