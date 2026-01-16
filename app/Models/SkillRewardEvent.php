<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillRewardEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'case_id',
        'skill_id',
        'difficulty_id',
        'points_awarded',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function caseModel(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function difficulty(): BelongsTo
    {
        return $this->belongsTo(Difficulty::class);
    }
}
