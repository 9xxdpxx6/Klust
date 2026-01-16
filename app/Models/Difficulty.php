<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Difficulty extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function cases(): HasMany
    {
        return $this->hasMany(CaseModel::class);
    }

    public function skillRewardRules(): HasMany
    {
        return $this->hasMany(SkillRewardRule::class);
    }

    public function skillRewardEvents(): HasMany
    {
        return $this->hasMany(SkillRewardEvent::class);
    }
}
