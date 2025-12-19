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
            FacultySeeder::class,        // 1. Факультеты
            RoleSeeder::class,           // 2. Роли (Spatie)
            PermissionSeeder::class,     // 3. Права и их назначение ролям (Spatie)
            UserSeeder::class,           // 4. Пользователи (150 студентов, 8 учителей, 15 партнёров, 1 админ)
            ProfileSeeder::class,        // 5. Профили (student, partner, teacher)
            SkillSeeder::class,          // 6. Навыки (10)
            BadgeSeeder::class,          // 7. Достижения (15)
            PartnerSeeder::class,        // 8. Партнёры (15)
            SimulatorSeeder::class,      // 9. Симуляторы (5, 1 активный)
            SimulatorSessionSeeder::class, // 10. Сессии симуляторов (200)
            CaseSeeder::class,           // 11. Кейсы (30)
            CaseSkillSeeder::class,      // 12. Навыки кейсов (pivot)
            ApplicationStatusSeeder::class, // 13. Статусы заявок (pending, accepted, rejected)
            CaseApplicationSeeder::class, // 14. Заявки на кейсы (120)
            UserSkillSeeder::class,      // 15. Навыки студентов (pivot)
            UserBadgeSeeder::class,      // 16. Достижения студентов (pivot)
            CaseTeamMemberSeeder::class, // 17. Участники команд (pivot)
            NotificationSeeder::class,   // 18. Уведомления (400)
            ProgressLogSeeder::class,    // 19. Логи прогресса (500)
        ]);
    }
}
