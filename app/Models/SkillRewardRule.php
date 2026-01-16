<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillRewardRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_id',
        'difficulty_id',
        'points',
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function difficulty(): BelongsTo
    {
        return $this->belongsTo(Difficulty::class);
    }
}
