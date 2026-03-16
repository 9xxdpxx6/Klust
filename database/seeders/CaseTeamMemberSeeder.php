<?php

namespace Database\Seeders;

use App\Models\CaseApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CaseTeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $applications = CaseApplication::with(['case', 'status', 'teamMembers'])->get();

        foreach ($applications as $application) {
            $case = $application->case;

            if (! $case) {
                continue;
            }

            $requiredSize = max(1, (int) $case->required_team_size);
            $currentMemberIds = $application->teamMembers->pluck('user_id')->push($application->leader_id);
            $availableStudents = $students->whereNotIn('id', $currentMemberIds->all())->shuffle()->values();
            $targetTeamSize = $this->pickTargetTeamSize($requiredSize, $application->status?->name);
            $membersToAdd = max(0, $targetTeamSize - 1);

            if ($membersToAdd === 0 || $availableStudents->isEmpty()) {
                continue;
            }

            $selectedMembers = $availableStudents->take(min($membersToAdd, $availableStudents->count()));
            $joinBoundary = $this->resolveJoinBoundary($application);

            foreach ($selectedMembers as $index => $member) {
                $joinedAt = $this->makeJoinedAt($application, $joinBoundary, $index);

                DB::table('case_team_members')->insert([
                    'application_id' => $application->id,
                    'user_id' => $member->id,
                    'created_at' => $joinedAt,
                    'updated_at' => $joinedAt,
                ]);
            }
        }
    }

    private function pickTargetTeamSize(int $requiredSize, ?string $statusName): int
    {
        if ($requiredSize === 1) {
            return 1;
        }

        if ($statusName === 'accepted') {
            return fake()->randomElement([$requiredSize - 1, $requiredSize, $requiredSize]);
        }

        if ($statusName === 'rejected') {
            return fake()->numberBetween(1, $requiredSize);
        }

        return fake()->numberBetween(1, max(1, $requiredSize - 1));
    }

    private function resolveJoinBoundary(CaseApplication $application): Carbon
    {
        if ($application->reviewed_at) {
            return Carbon::parse($application->reviewed_at);
        }

        if ($application->case?->deadline) {
            return Carbon::parse($application->case->deadline)->subDay()->endOfDay()->min(now());
        }

        return now();
    }

    private function makeJoinedAt(CaseApplication $application, Carbon $joinBoundary, int $index): Carbon
    {
        $submittedAt = Carbon::parse($application->submitted_at ?? $application->created_at);
        $earliestJoin = $submittedAt->copy()->addHours($index === 0 ? 2 : 8);
        $latestJoin = $submittedAt->copy()->addDays(min(5, $index + 1));

        if ($latestJoin->greaterThan($joinBoundary)) {
            $latestJoin = $joinBoundary->copy();
        }

        if ($earliestJoin->greaterThan($latestJoin)) {
            return $latestJoin;
        }

        return Carbon::instance(fake()->dateTimeBetween($earliestJoin, $latestJoin));
    }
}
