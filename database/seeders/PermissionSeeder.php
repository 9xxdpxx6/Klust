<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очистка существующих прав и ролей (опционально)
        // Permission::query()->delete();
        
        // Права для управления пользователями
        $userPermissions = [
            'users.view' => 'Просмотр пользователей',
            'users.create' => 'Создание пользователей',
            'users.update' => 'Обновление пользователей',
            'users.delete' => 'Удаление пользователей',
        ];

        // Права для управления кейсами
        $casePermissions = [
            'cases.view' => 'Просмотр кейсов',
            'cases.create' => 'Создание кейсов',
            'cases.update' => 'Обновление кейсов',
            'cases.delete' => 'Удаление кейсов',
            'cases.apply' => 'Подача заявки на кейс',
            'cases.approve' => 'Одобрение заявок на кейсы',
        ];

        // Права для управления навыками
        $skillPermissions = [
            'skills.view' => 'Просмотр навыков',
            'skills.create' => 'Создание навыков',
            'skills.update' => 'Обновление навыков',
            'skills.delete' => 'Удаление навыков',
        ];

        // Права для управления бейджами
        $badgePermissions = [
            'badges.view' => 'Просмотр бейджей',
            'badges.create' => 'Создание бейджей',
            'badges.update' => 'Обновление бейджей',
            'badges.delete' => 'Удаление бейджей',
        ];

        // Права для управления симуляторами
        $simulatorPermissions = [
            'simulators.view' => 'Просмотр симуляторов',
            'simulators.create' => 'Создание симуляторов',
            'simulators.update' => 'Обновление симуляторов',
            'simulators.delete' => 'Удаление симуляторов',
        ];

        // Права для админки
        $adminPermissions = [
            'admin.dashboard' => 'Доступ к админ панели',
            'admin.statistics' => 'Просмотр статистики',
            'admin.settings' => 'Управление настройками системы',
        ];

        // Объединяем все права
        $allPermissions = array_merge(
            $userPermissions,
            $casePermissions,
            $skillPermissions,
            $badgePermissions,
            $simulatorPermissions,
            $adminPermissions
        );

        // Создаем права
        foreach ($allPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }

        // Получаем роли
        $adminRole = Role::findByName('admin');
        $teacherRole = Role::findByName('teacher');
        $studentRole = Role::findByName('student');
        $partnerRole = Role::findByName('partner');

        // Admin - все права
        $adminRole->syncPermissions(Permission::all());

        // Teacher - права на управление кейсами, просмотр пользователей и статистики
        $teacherRole->syncPermissions([
            'users.view',
            'cases.view',
            'cases.create',
            'cases.update',
            'cases.approve',
            'skills.view',
            'badges.view',
            'simulators.view',
            'admin.dashboard',
            'admin.statistics',
        ]);

        // Student - базовые права на просмотр и взаимодействие
        $studentRole->syncPermissions([
            'cases.view',
            'cases.apply',
            'skills.view',
            'badges.view',
            'simulators.view',
        ]);

        // Partner - права на управление своими кейсами
        $partnerRole->syncPermissions([
            'cases.view',
            'cases.create',
            'cases.update',
            'cases.approve',
            'skills.view',
            'badges.view',
        ]);
    }
}

