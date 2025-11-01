<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,           // 1. Роли (Spatie)
            UserSeeder::class,           // 2. Пользователи (100 студентов, 5 учителей, 10 партнёров, 1 админ)
            ProfileSeeder::class,        // 3. Профили (student, partner, teacher)
            SkillSeeder::class,          // 4. Навыки (10)
            BadgeSeeder::class,          // 5. Бейджи (15)
            PartnerSeeder::class,        // 6. Партнёры (10)
            SimulatorSeeder::class,      // 7. Симуляторы (5, 1 активный)
            SimulatorSessionSeeder::class, // 8. Сессии симуляторов (80)
            CaseSeeder::class,           // 9. Кейсы (8)
            CaseApplicationSeeder::class, // 10. Заявки на кейсы (30)
            UserSkillSeeder::class,      // 11. Навыки студентов (pivot)
            UserBadgeSeeder::class,      // 12. Бейджи студентов (pivot)
            CaseTeamMemberSeeder::class, // 13. Участники команд (pivot)
            NotificationSeeder::class,   // 14. Уведомления (~200)
            ProgressLogSeeder::class,    // 15. Логи прогресса (~300)
        ]);
    }
}
