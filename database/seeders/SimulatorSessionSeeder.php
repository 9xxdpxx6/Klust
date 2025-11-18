<?php

namespace Database\Seeders;

use App\Models\Simulator;
use App\Models\SimulatorSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class SimulatorSessionSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::role('student')->get();
        $simulators = Simulator::all();

        // 80 сессий (80% студентов играли)
        for ($i = 0; $i < 80; $i++) {
            SimulatorSession::factory()->create([
                'user_id' => $students->random()->id,
                'simulator_id' => $simulators->random()->id,
            ]);
        }
    }
}
