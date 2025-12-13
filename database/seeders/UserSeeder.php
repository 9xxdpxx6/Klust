<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $studentRole = Role::findByName('student');
        $teacherRole = Role::findByName('teacher');
        $partnerRole = Role::findByName('partner');
        $adminRole = Role::findByName('admin');

        // 150 студентов с разнообразными датами регистрации (от 6 месяцев назад до сейчас)
        $students = [];
        for ($i = 0; $i < 150; $i++) {
            $createdAt = fake()->dateTimeBetween('-6 months', 'now');
            $student = User::factory()->student()->create([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'email_verified_at' => fake()->boolean(85) ? fake()->dateTimeBetween($createdAt, 'now') : null,
            ]);
            $student->assignRole($studentRole);
            $students[] = $student;
        }

        // 8 преподавателей с разными датами регистрации
        $teachers = [];
        for ($i = 0; $i < 8; $i++) {
            $createdAt = fake()->dateTimeBetween('-8 months', '-1 month');
            $teacher = User::factory()->teacher()->create([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'email_verified_at' => fake()->dateTimeBetween($createdAt, 'now'),
            ]);
            $teacher->assignRole($teacherRole);
            $teachers[] = $teacher;
        }

        // 15 партнёров с разными датами регистрации (раньше, чем студенты)
        $partners = [];
        for ($i = 0; $i < 15; $i++) {
            $createdAt = fake()->dateTimeBetween('-10 months', '-2 months');
            $partner = User::factory()->partner()->create([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'email_verified_at' => fake()->dateTimeBetween($createdAt, 'now'),
            ]);
            $partner->assignRole($partnerRole);
            $partners[] = $partner;
        }

        // 1 админ (создан в начале проекта)
        $admin = User::factory()->create([
            'name' => 'Администратор',
            'email' => 'asd@asd.asd',
            'password' => bcrypt('asd'),
            'created_at' => Carbon::now()->subMonths(12),
            'updated_at' => Carbon::now()->subMonths(12),
            'email_verified_at' => Carbon::now()->subMonths(12),
        ]);
        $admin->assignRole($adminRole);

        // 1 суперпользователь (имеет все права и роли)
        $superUser = User::factory()->create([
            'name' => 'Суперпользователь',
            'email' => 'qwe@qwe.qwe',
            'password' => bcrypt('qwe'),
            'created_at' => Carbon::now()->subMonths(11),
            'updated_at' => Carbon::now()->subMonths(11),
            'email_verified_at' => Carbon::now()->subMonths(11),
        ]);
        // Назначаем все доступные роли
        $allRoles = Role::all();
        foreach ($allRoles as $role) {
            $superUser->assignRole($role);
        }

        // 1 студент (тестовый аккаунт)
        $student = User::factory()->create([
            'name' => 'Студент',
            'email' => 'zxc@zxc.zxc',
            'password' => bcrypt('zxc'),
            'created_at' => Carbon::now()->subMonths(3),
            'updated_at' => Carbon::now()->subMonths(3),
            'email_verified_at' => Carbon::now()->subMonths(3),
        ]);
        $student->assignRole($studentRole);

        // 1 партнёр (тестовый аккаунт)
        $partner = User::factory()->create([
            'name' => 'Партнёр',
            'email' => 'wer@wer.wer',
            'password' => bcrypt('wer'),
            'created_at' => Carbon::now()->subMonths(4),
            'updated_at' => Carbon::now()->subMonths(4),
            'email_verified_at' => Carbon::now()->subMonths(4),
        ]);
        $partner->assignRole($partnerRole);

        // 1 преподаватель (тестовый аккаунт)
        $teacher = User::factory()->create([
            'name' => 'Преподаватель',
            'email' => 'sdf@sdf.sdf',
            'password' => bcrypt('sdf'),
            'created_at' => Carbon::now()->subMonths(5),
            'updated_at' => Carbon::now()->subMonths(5),
            'email_verified_at' => Carbon::now()->subMonths(5),
        ]);
        $teacher->assignRole($teacherRole);
    }
}
