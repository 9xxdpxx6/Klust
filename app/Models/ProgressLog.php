<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class ProgressLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'loggable_type',
        'loggable_id',
        'points_earned',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter logs for skills only
     */
    public function scopeForSkills(Builder $query): Builder
    {
        return $query->where('loggable_type', Skill::class);
    }

    /**
     * Scope to filter logs for a specific skill
     */
    public function scopeForSkill(Builder $query, Skill $skill): Builder
    {
        return $query->where('loggable_type', Skill::class)
            ->where('loggable_id', $skill->id);
    }
}
