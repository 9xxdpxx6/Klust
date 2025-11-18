<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $studentRole = Role::findByName('student');
        $teacherRole = Role::findByName('teacher');
        $partnerRole = Role::findByName('partner');
        $adminRole = Role::findByName('admin');

        // 100 студентов
        $students = User::factory(100)->student()->create();
        foreach ($students as $student) {
            $student->assignRole($studentRole);
        }

        // 5 преподавателей
        $teachers = User::factory(5)->teacher()->create();
        foreach ($teachers as $teacher) {
            $teacher->assignRole($teacherRole);
        }

        // 10 партнёров (без пользователей сначала, потом создадим профили)
        $partners = User::factory(10)->partner()->create();
        foreach ($partners as $partner) {
            $partner->assignRole($partnerRole);
        }

        // 1 админ
        $admin = User::factory()->create([
            'name' => 'Администратор',
            'email' => 'asd@asd.asd',
            'password' => bcrypt('asd'),
        ]);
        $admin->assignRole($adminRole);

        // 1 суперпользователь (имеет все права и роли)
        $superUser = User::factory()->create([
            'name' => 'Суперпользователь',
            'email' => 'qwe@qwe.qwe',
            'password' => bcrypt('qwe'),
        ]);
        // Назначаем все доступные роли
        $allRoles = Role::all();
        foreach ($allRoles as $role) {
            $superUser->assignRole($role);
        }
    }
}
