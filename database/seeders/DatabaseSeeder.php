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
            PermissionSeeder::class,     // 2. Права и их назначение ролям (Spatie)
            UserSeeder::class,           // 3. Пользователи (100 студентов, 5 учителей, 10 партнёров, 1 админ)
            ProfileSeeder::class,        // 4. Профили (student, partner, teacher)
            SkillSeeder::class,          // 5. Навыки (10)
            BadgeSeeder::class,          // 6. Бейджи (15)
            PartnerSeeder::class,        // 7. Партнёры (10)
            SimulatorSeeder::class,      // 8. Симуляторы (5, 1 активный)
            SimulatorSessionSeeder::class, // 9. Сессии симуляторов (80)
            CaseSeeder::class,           // 10. Кейсы (8)
            CaseSkillSeeder::class,      // 11. Навыки кейсов (pivot)
            CaseApplicationSeeder::class, // 12. Заявки на кейсы (30)
            UserSkillSeeder::class,      // 13. Навыки студентов (pivot)
            UserBadgeSeeder::class,      // 14. Бейджи студентов (pivot)
            CaseTeamMemberSeeder::class, // 15. Участники команд (pivot)
            NotificationSeeder::class,   // 16. Уведомления (~200)
            ProgressLogSeeder::class,    // 17. Логи прогресса (~300)
        ]);
    }
}
