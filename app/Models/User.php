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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPassword
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use CanResetPasswordTrait, HasApiTokens, HasFactory, HasRoles, LogsActivity, Notifiable;

    public const DASHBOARD_ROLE_MAP = [
        'admin' => 'dashboard',
        'ผู้บริหาร' => 'manager.dashboard',
        'กรรมการ' => 'director.dashboard',
        'ผู้ประเมิน' => 'evaluator.index',
        'ผู้รับการประเมิน' => 'evaluatee.dashboard',
    ];

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
        'education_history',
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
            'education_history' => 'array',
        ];
    }

    public function getEducationHistoryEntriesAttribute(): array
    {
        $entries = collect($this->education_history ?? [])
            ->filter(fn ($entry) => is_array($entry))
            ->map(fn (array $entry) => [
                'graduation_year' => filled($entry['graduation_year'] ?? null) ? (string) $entry['graduation_year'] : null,
                'degree' => filled($entry['degree'] ?? null) ? trim((string) $entry['degree']) : null,
                'university' => filled($entry['university'] ?? null) ? trim((string) $entry['university']) : null,
            ])
            ->filter(fn (array $entry) => filled($entry['graduation_year']) || filled($entry['degree']) || filled($entry['university']))
            ->values()
            ->all();

        if (!empty($entries)) {
            return $entries;
        }

        if (filled($this->bio)) {
            return [[
                'graduation_year' => null,
                'degree' => $this->bio,
                'university' => null,
            ]];
        }

        return [];
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(collect([
            $this->prefix,
            $this->name,
        ])->filter(fn ($value) => filled($value))->implode(' '));
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
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('จัดการผู้ใช้') // custom log_name in DB
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'updated' => 'แก้ไขข้อมูลผู้ใช้',
                    'created' => 'สร้างผู้ใช้ใหม่',
                    'deleted' => 'ลบข้อมูลผู้ใช้',
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
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return asset('storage/'.$this->profile_photo_path);
        }

        return asset('images/default-avatar.svg');
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
        // Get all assignment data where user is evaluator
        $assignmentDataIds = $this->evaluatorAssignmentData()->pluck('id');
        
        // Return assignments query with necessary relationships
        return Assignments::whereIn('assignment_data_id', $assignmentDataIds)
            ->with([
                'evaluateeUser.department',
                'assignmentData.evaluatorUser',
                'report.reportData',
            ]);
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

    // AssignmentData rows where this user is the evaluator
    public function evaluatorAssignmentData()
    {
        return $this->hasMany(AssignmentData::class, 'evaluator_id', 'id');
    }

    public function availableDashboardRoles(): array
    {
        return collect(self::DASHBOARD_ROLE_MAP)
            ->filter(fn ($routeName, $roleName) => $this->hasRole($roleName))
            ->map(fn ($routeName, $roleName) => [
                'role' => $roleName,
                'route' => $routeName,
                'url' => route($routeName),
            ])
            ->values()
            ->all();
    }

    public function defaultDashboardRoute(): ?string
    {
        foreach (self::DASHBOARD_ROLE_MAP as $roleName => $routeName) {
            if ($this->hasRole($roleName)) {
                return $routeName;
            }
        }

        return null;
    }

    public function hasMultipleDashboardRoles(): bool
    {
        return count($this->availableDashboardRoles()) > 1;
    }
}
