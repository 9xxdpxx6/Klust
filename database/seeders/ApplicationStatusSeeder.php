<?php

namespace Database\Seeders;

use App\Models\ApplicationStatus;
use Illuminate\Database\Seeder;

class ApplicationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'pending',
                'label' => 'На рассмотрении',
                'color' => '#FFC107', // amber/yellow
            ],
            [
                'name' => 'accepted',
                'label' => 'Принята',
                'color' => '#10B981', // green
            ],
            [
                'name' => 'rejected',
                'label' => 'Отклонена',
                'color' => '#EF4444', // red
            ],
        ];

        foreach ($statuses as $status) {
            ApplicationStatus::firstOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
