<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Models\ApplicationStatus;
use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\CaseTeamMember;
use App\Models\Skill;
use App\Models\User;
use App\Services\CaseService;
use Database\Seeders\ApplicationStatusSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationsExcludeAppliedCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendations_do_not_include_cases_where_user_is_leader_or_team_member(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(ApplicationStatusSeeder::class);

        /** @var User $student */
        $student = User::factory()->create();
        $student->assignRole('student');

        $skill = Skill::factory()->create();
        $student->skills()->attach($skill->id);

        $caseVisible = CaseModel::factory()->create([
            'status' => 'active',
            'deadline' => now()->addDays(10),
        ]);
        $caseAsLeader = CaseModel::factory()->create([
            'status' => 'active',
            'deadline' => now()->addDays(10),
        ]);
        $caseAsTeamMember = CaseModel::factory()->create([
            'status' => 'active',
            'deadline' => now()->addDays(10),
        ]);

        $caseVisible->skills()->attach($skill->id);
        $caseAsLeader->skills()->attach($skill->id);
        $caseAsTeamMember->skills()->attach($skill->id);

        $pendingId = ApplicationStatus::getIdByName('pending');

        CaseApplication::factory()->create([
            'case_id' => $caseAsLeader->id,
            'leader_id' => $student->id,
            'status_id' => $pendingId,
        ]);

        $otherLeader = User::factory()->create();
        $otherLeader->assignRole('student');

        $application = CaseApplication::factory()->create([
            'case_id' => $caseAsTeamMember->id,
            'leader_id' => $otherLeader->id,
            'status_id' => $pendingId,
        ]);

        CaseTeamMember::create([
            'application_id' => $application->id,
            'user_id' => $student->id,
        ]);

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);
        $recommendations = $caseService->getRecommendedCases($student, 10);

        $ids = $recommendations->pluck('id');

        $this->assertTrue($ids->contains($caseVisible->id));
        $this->assertFalse($ids->contains($caseAsLeader->id));
        $this->assertFalse($ids->contains($caseAsTeamMember->id));
    }
}


