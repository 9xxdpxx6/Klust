<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kubgtu_id',
        'name',
        'email',
        'password',
        'avatar',
        'course',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Profile relationships
    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function partnerProfile()
    {
        return $this->hasOne(PartnerProfile::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    // Skills & Badges
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
            ->using(UserSkill::class)
            ->withPivot('level', 'points_earned')
            ->withTimestamps();
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->using(UserBadge::class)
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    // Simulator Sessions
    public function simulatorSessions()
    {
        return $this->hasMany(SimulatorSession::class);
    }

    // Case Applications
    public function caseApplications()
    {
        return $this->hasMany(CaseApplication::class, 'leader_id');
    }

    public function caseTeamMembers()
    {
        return $this->hasMany(CaseTeamMember::class);
    }

    // Участие в командах (many-to-many через pivot)
    public function caseTeams()
    {
        return $this->belongsToMany(CaseApplication::class, 'case_team_members')
            ->using(CaseTeamMember::class)
            ->withTimestamps();
    }

    // Notifications
    public function notifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    // Progress Logs
    public function progressLogs()
    {
        return $this->hasMany(ProgressLog::class);
    }

    // Partner (if user is a partner)
    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    // Accessor для удобного доступа к факультету студента
    public function getFacultyAttribute()
    {
        return $this->studentProfile?->faculty;
    }
}
