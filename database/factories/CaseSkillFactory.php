<?php

namespace Database\Factories;

use App\Models\CaseModel;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseSkillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'case_id' => CaseModel::factory(),
            'skill_id' => Skill::factory(),
        ];
    }
}
