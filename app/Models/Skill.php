<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'max_level',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_skills')
            ->using(UserSkill::class)
            ->withPivot('level', 'points_earned')
            ->withTimestamps();
    }

    public function cases(): BelongsToMany
    {
        return $this->belongsToMany(CaseModel::class, 'case_skills')
            ->withTimestamps();
    }
}

