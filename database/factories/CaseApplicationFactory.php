<?php

namespace Database\Factories;

use App\Models\ApplicationStatus;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CaseApplicationFactory extends Factory
{
    public function definition(): array
    {
        $case = CaseModel::query()
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->whereDate('deadline', '>=', now()->toDateString());
            })
            ->orWhereIn('status', ['completed', 'archived'])
            ->inRandomOrder()
            ->first() ?? CaseModel::factory()->create();

        $leaderId = User::query()->role('student')->inRandomOrder()->value('id') ?? User::factory()->student()->create()->id;
        $statusName = $this->pickStatusName($case);
        $submittedAt = $this->makeSubmittedAt($case);
        $reviewedAt = $statusName === 'pending' ? null : $this->makeReviewedAt($case, $submittedAt);

        return [
            'case_id' => $case->id,
            'leader_id' => $leaderId,
            'motivation' => $this->makeMotivation(),
            'status_id' => ApplicationStatus::getIdByName($statusName),
            'rejection_reason' => $statusName === 'rejected' ? 'The team profile is weaker than competing applications.' : null,
            'partner_comment' => $statusName === 'accepted' ? 'Accepted due to clear motivation and relevant skills.' : null,
            'reviewed_at' => $reviewedAt,
            'submitted_at' => $submittedAt,
            'created_at' => $submittedAt,
            'updated_at' => $reviewedAt ?? $submittedAt,
        ];
    }

    private function pickStatusName(CaseModel $case): string
    {
        if (in_array($case->status, ['completed', 'archived'], true)) {
            return fake()->randomElement(['accepted', 'accepted', 'rejected']);
        }

        return fake()->randomElement(['pending', 'pending', 'accepted', 'accepted', 'rejected']);
    }

    private function makeSubmittedAt(CaseModel $case): Carbon
    {
        $createdAt = Carbon::parse($case->created_at)->addDay();

        if (in_array($case->status, ['completed', 'archived'], true)) {
            $latest = Carbon::parse($case->deadline)->subDays(3);

            if ($createdAt->greaterThan($latest)) {
                return $latest;
            }

            return Carbon::instance(fake()->dateTimeBetween($createdAt, $latest));
        }

        $latest = Carbon::parse($case->deadline)->subDays(2)->endOfDay();
        $latest = $latest->min(now()->subHour());

        if ($createdAt->greaterThan($latest)) {
            return $latest;
        }

        return Carbon::instance(fake()->dateTimeBetween($createdAt, $latest));
    }

    private function makeReviewedAt(CaseModel $case, Carbon $submittedAt): Carbon
    {
        $latest = in_array($case->status, ['completed', 'archived'], true)
            ? Carbon::parse($case->deadline)->subDay()
            : Carbon::parse($case->deadline)->subDay()->min(now()->subMinutes(30));

        $earliest = $submittedAt->copy()->addHours(4);

        if ($earliest->greaterThan($latest)) {
            return $latest;
        }

        return Carbon::instance(fake()->dateTimeBetween($earliest, $latest));
    }

    private function makeMotivation(): string
    {
        return fake()->randomElement([
            'We want to work on a case with measurable business impact and a clear deadline.',
            'This application is based on our interest in practical teamwork and structured problem solving.',
            'The case aligns with our coursework, and we can contribute both analysis and presentation.',
            'We are applying because the topic is relevant and the scope looks realistic for our team.',
        ]);
    }
}
