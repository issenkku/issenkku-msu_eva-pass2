<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use App\Models\Setting\Positions;
use App\Models\Setting\Departments;

class User extends Authenticatable implements CanResetPassword
{
    /** @use HasFactory<\Database\Factories\UserFactory> */

    use HasFactory, Notifiable, HasRoles, HasApiTokens, CanResetPasswordTrait;


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
        'status',
        'position_id',
        'department_id',
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
        ];
    }

    public function getAuthIdentifierName()
    {
        return 'employee_id';
    }

    public function position(){
        return $this->belongsTo(Positions::class);
    }

    public function department(){
        return $this->belongsTo(Departments::class);
    }
    
    public function assignment(){
        return $this->hasMany(Assignments::class, 'evaluatee', 'id');
    }

    public function evaluatorAssignments()
    {
        return $this->hasMany(Assignments::class, 'evaluator', 'id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

}
