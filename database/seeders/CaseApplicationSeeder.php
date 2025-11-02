<?php

namespace Database\Seeders;

use App\Models\CaseApplication;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CaseApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $cases = CaseModel::where('status', 'active')->get();

        // 30 заявок на кейсы
        for ($i = 0; $i < 30; $i++) {
            CaseApplication::factory()->create([
                'case_id' => $cases->random()->id,
                'leader_id' => $students->random()->id,
            ]);
        }
    }
}

