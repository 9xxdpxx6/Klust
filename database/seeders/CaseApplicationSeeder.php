<?php

namespace Database\Seeders;

use App\Models\ApplicationStatus;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CaseApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();

        if ($students->isEmpty()) {
            return;
        }

        $openCases = CaseModel::query()
            ->where('status', 'active')
            ->whereDate('deadline', '>=', now()->toDateString())
            ->get();

        $historicalCases = CaseModel::query()
            ->whereIn('status', ['completed', 'archived'])
            ->get();

        if ($openCases->isEmpty() && $historicalCases->isEmpty()) {
            return;
        }

        $statusIds = [
            'pending' => ApplicationStatus::getIdByName('pending'),
            'accepted' => ApplicationStatus::getIdByName('accepted'),
            'rejected' => ApplicationStatus::getIdByName('rejected'),
        ];

        $usedLeadersByCase = [];

        foreach ($openCases as $case) {
            $created = $this->seedCaseApplications($case, $students, $statusIds, $usedLeadersByCase, false);
            $usedLeadersByCase[$case->id] = $created;
        }

        foreach ($historicalCases as $case) {
            $created = $this->seedCaseApplications($case, $students, $statusIds, $usedLeadersByCase, true);
            $usedLeadersByCase[$case->id] = $created;
        }

        $this->seedExtraApplicationsForTestStudent($students, $openCases, $historicalCases, $statusIds, $usedLeadersByCase);
    }

    private function seedCaseApplications(
        CaseModel $case,
        Collection $students,
        array $statusIds,
        array $usedLeadersByCase,
        bool $historical
    ): array {
        $existingLeaders = collect($usedLeadersByCase[$case->id] ?? []);
        $availableStudents = $students->whereNotIn('id', $existingLeaders->all())->shuffle()->values();

        if ($availableStudents->isEmpty()) {
            return $existingLeaders->all();
        }

        $applicationsCount = min(
            $availableStudents->count(),
            fake()->numberBetween(2, $historical ? 4 : 5)
        );

        for ($i = 0; $i < $applicationsCount; $i++) {
            $leader = $availableStudents[$i];
            $timeline = $this->makeTimelineForCase($case, $historical);
            $statusName = $this->pickStatusName($historical);

            CaseApplication::create([
                'case_id' => $case->id,
                'leader_id' => $leader->id,
                'motivation' => $this->makeMotivation($case),
                'status_id' => $statusIds[$statusName],
                'rejection_reason' => $statusName === 'rejected' ? $this->makeRejectionReason() : null,
                'partner_comment' => $statusName === 'accepted' ? $this->makePartnerComment() : null,
                'reviewed_at' => $statusName === 'pending' ? null : $timeline['reviewed_at'],
                'submitted_at' => $timeline['submitted_at'],
                'created_at' => $timeline['submitted_at'],
                'updated_at' => $statusName === 'pending' ? $timeline['submitted_at'] : $timeline['reviewed_at'],
            ]);

            $existingLeaders->push($leader->id);
        }

        return $existingLeaders->unique()->values()->all();
    }

    private function seedExtraApplicationsForTestStudent(
        Collection $students,
        Collection $openCases,
        Collection $historicalCases,
        array $statusIds,
        array $usedLeadersByCase
    ): void {
        $testStudent = User::where('email', 'zxc@zxc.zxc')->first();

        if (! $testStudent) {
            return;
        }

        $candidateCases = $openCases
            ->merge($historicalCases)
            ->filter(function (CaseModel $case) use ($usedLeadersByCase, $testStudent) {
                return ! in_array($testStudent->id, $usedLeadersByCase[$case->id] ?? [], true);
            })
            ->shuffle()
            ->take(12);

        foreach ($candidateCases as $case) {
            $historical = in_array($case->status, ['completed', 'archived'], true);
            $timeline = $this->makeTimelineForCase($case, $historical);
            $statusName = $historical ? fake()->randomElement(['accepted', 'accepted', 'rejected']) : fake()->randomElement(['pending', 'accepted', 'accepted', 'rejected']);

            CaseApplication::create([
                'case_id' => $case->id,
                'leader_id' => $testStudent->id,
                'motivation' => $this->makeMotivation($case),
                'status_id' => $statusIds[$statusName],
                'rejection_reason' => $statusName === 'rejected' ? $this->makeRejectionReason() : null,
                'partner_comment' => $statusName === 'accepted' ? $this->makePartnerComment() : null,
                'reviewed_at' => $statusName === 'pending' ? null : $timeline['reviewed_at'],
                'submitted_at' => $timeline['submitted_at'],
                'created_at' => $timeline['submitted_at'],
                'updated_at' => $statusName === 'pending' ? $timeline['submitted_at'] : $timeline['reviewed_at'],
            ]);
        }
    }

    private function makeTimelineForCase(CaseModel $case, bool $historical): array
    {
        $createdAt = Carbon::parse($case->created_at)->startOfDay();

        if ($historical) {
            $deadline = Carbon::parse($case->deadline)->endOfDay();
            $submittedAt = $this->randomCarbonBetween(
                $createdAt->copy()->addDay(),
                $deadline->copy()->subDays(3)
            );
            $reviewedAt = $this->randomCarbonBetween(
                $submittedAt->copy()->addHours(6),
                $deadline->copy()->subDay()
            );

            return [
                'submitted_at' => $submittedAt,
                'reviewed_at' => $reviewedAt,
            ];
        }

        $deadline = Carbon::parse($case->deadline)->subDay()->endOfDay();
        $submittedStart = $createdAt->copy();
        $recentBoundary = now()->copy()->subMonths(2)->startOfDay();
        if ($recentBoundary->greaterThan($submittedStart)) {
            $submittedStart = $recentBoundary;
        }

        $submittedAt = $this->randomCarbonBetween(
            $submittedStart,
            $deadline->copy()->subDays(2)
        );

        $reviewBoundary = $deadline->copy();
        $nowBoundary = now()->copy()->subHours(2);
        if ($nowBoundary->lessThan($reviewBoundary)) {
            $reviewBoundary = $nowBoundary;
        }

        $reviewedAt = $this->randomCarbonBetween(
            $submittedAt->copy()->addHours(4),
            $reviewBoundary
        );

        return [
            'submitted_at' => $submittedAt,
            'reviewed_at' => $reviewedAt,
        ];
    }

    private function pickStatusName(bool $historical): string
    {
        if ($historical) {
            return fake()->randomElement(['accepted', 'accepted', 'accepted', 'rejected']);
        }

        return fake()->randomElement(['pending', 'pending', 'accepted', 'accepted', 'rejected']);
    }

    private function makeMotivation(CaseModel $case): string
    {
        $templates = [
            'We want to work on this case because it matches our coursework and gives us a realistic business problem to solve.',
            'Our team can cover analysis, presentation, and execution, so this case is a good fit for us.',
            'This case looks practical and measurable, which makes it useful for testing both teamwork and decision making.',
            'We are applying because the topic is relevant to our specialization and the expected outcome is concrete.',
        ];

        return fake()->randomElement($templates).' '.$case->title.'.';
    }

    private function makePartnerComment(): string
    {
        return fake()->randomElement([
            'Strong motivation and balanced team composition.',
            'The application looks solid and relevant to the case.',
            'Good fit for the scope and expected workload.',
            'Accepted because the team has a clear plan and relevant skills.',
        ]);
    }

    private function makeRejectionReason(): string
    {
        return fake()->randomElement([
            'Another team matched the case requirements more closely.',
            'The application is valid, but the team composition is weaker than competing submissions.',
            'The motivation is too generic for this case.',
            'The team profile does not align well with the expected workload.',
        ]);
    }

    private function randomCarbonBetween(Carbon $start, Carbon $end): Carbon
    {
        if ($start->greaterThan($end)) {
            return $end->copy();
        }

        return Carbon::instance(fake()->dateTimeBetween($start, $end));
    }
}
