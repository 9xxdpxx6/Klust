<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'leader_id',
        'motivation',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(CaseTeamMember::class, 'application_id');
    }

    // Получить всю команду (через pivot)
    public function team()
    {
        return $this->belongsToMany(User::class, 'case_team_members')
            ->using(CaseTeamMember::class)
            ->withTimestamps();
    }
}

