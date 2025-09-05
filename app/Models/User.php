<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Notifications\CustomResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPassword
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use CanResetPasswordTrait, HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'prefix',
        'name',
        'employee_id',
        'password',
        'email',
        'phone',
        'personnel_type',
        'bio',
        'portfolio',
        'profile_photo_path',
        'status',
        'position_id',
        'department_id',
        'public_profile_uuid',
        'is_public_profile_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_public_profile_enabled' => 'boolean',
        ];
    }

    /**
     * Boot method to generate UUID on creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->public_profile_uuid)) {
                $user->public_profile_uuid = (string) Str::uuid();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->useLogName('user_info') // custom log_name in DB
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'updated' => 'แก้ไขข้อมูลผู้ใช้',
                    'created' => 'สร้างผู้ใช้ใหม่',
                    default => $eventName,
                };
            });
    }

    public function getAuthIdentifierName()
    {
        return 'employee_id';
    }

    public function getRouteKeyName()
    {
        return 'employee_id';
    }

    /**
     * Get the user's profile photo URL.
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : asset('images/default-avatar.svg');
    }

    public function position()
    {
        return $this->belongsTo(Positions::class);
    }

    public function department()
    {
        return $this->belongsTo(Departments::class);
    }

    public function assignment()
    {
        return $this->hasMany(Assignments::class, 'evaluatee_id', 'id');
    }

    public function evaluatorAssignments()
    {
        return $this->hasManyThrough(
            Assignments::class,        // Final model
            AssignmentData::class,     // Intermediate model
            'evaluator_position_id',   // Foreign key on AssignmentData (points to Positions table)
            'assignment_data_id',      // Foreign key on Assignments (points to AssignmentData)
            'position_id',             // Local key on Users (points to Positions)
            'id'                       // Local key on AssignmentData
        );
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    /**
     * Generate UUID for public profile if not exists
     */
    public function generatePublicProfileUuid()
    {
        if (empty($this->public_profile_uuid)) {
            $this->public_profile_uuid = (string) Str::uuid();
            $this->save();
        }

        return $this->public_profile_uuid;
    }

    /**
     * Get public profile URL
     */
    public function getPublicProfileUrlAttribute()
    {
        if (empty($this->public_profile_uuid)) {
            $this->generatePublicProfileUuid();
        }

        return route('profile.public', $this->public_profile_uuid);
    }

    public function assignmentsForDashboard()
    {
        $query = $this->evaluatorAssignments()
            ->with([
                'evaluateeUser.department',
                'assignmentData',
                'report.reportData',
            ])
            ->whereHas('evaluateeUser', function ($q) {
                $q->where('department_id', $this->department_id);
            });

        return $query;
    }

    public function allAssignmentsForDashboard()
    {
        return Assignments::query()
            ->with([
                'evaluateeUser.department',
                'assignmentData',
                'report.reportData',
            ]);
    }
}
