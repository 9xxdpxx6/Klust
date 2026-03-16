<?php

namespace Database\Factories;

use App\Models\CaseModel;
use App\Models\Difficulty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CaseModelFactory extends Factory
{
    protected $model = CaseModel::class;

    public function definition(): array
    {
        $titles = [
            'Optimize branch operations',
            'Improve customer journey',
            'Design a logistics routing model',
            'Build a reporting workflow',
            'Reduce service response time',
            'Create a growth strategy',
            'Improve internal analytics',
            'Launch a process automation initiative',
        ];

        $descriptions = [
            'The team is expected to analyze the current process, identify bottlenecks, and present a realistic implementation plan.',
            'This case focuses on measurable business outcomes and requires both analysis and a practical rollout proposal.',
            'Students should combine research, prioritization, and presentation skills to deliver a structured solution.',
            'The partner expects a concise but realistic recommendation that can be reviewed and tested internally.',
        ];

        $status = fake()->randomElement(['draft', 'active', 'active', 'active', 'completed', 'archived']);
        $createdAt = Carbon::instance(fake()->dateTimeBetween('-8 months', '-2 weeks'));
        $deadline = $this->makeDeadline($status, $createdAt);

        return [
            'user_id' => User::query()->role('partner')->inRandomOrder()->value('id') ?? User::factory()->partner(),
            'title' => fake()->randomElement($titles),
            'description' => fake()->randomElement($descriptions) . ' ' . fake()->paragraph(),
            'simulator_id' => null,
            'deadline' => $deadline,
            'difficulty_id' => Difficulty::query()->inRandomOrder()->value('id')
                ?? Difficulty::query()->create([
                    'code' => 'easy',
                    'name' => 'Easy',
                    'description' => 'Autocreated by factory.',
                ])->id,
            'required_team_size' => fake()->randomElement([1, 1, 2, 2, 3, 3, 4, 5]),
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $status === 'draft'
                ? $createdAt
                : Carbon::instance(fake()->dateTimeBetween($createdAt, min($deadline, now()))),
        ];
    }

    private function makeDeadline(string $status, Carbon $createdAt): Carbon
    {
        if (in_array($status, ['completed', 'archived'], true)) {
            return Carbon::instance(fake()->dateTimeBetween($createdAt->copy()->addWeek(), '-3 days'));
        }

        if ($status === 'draft') {
            return Carbon::instance(fake()->dateTimeBetween('+2 weeks', '+6 months'));
        }

        return Carbon::instance(fake()->dateTimeBetween('+1 week', '+4 months'));
    }
}
