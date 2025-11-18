# План миграции с ролей на Policy и права

## 📋 Обзор

Этот документ описывает план миграции системы авторизации с использования ролей на гибридный подход:
- **Роли** - для разграничения порталов (оставить как есть)
- **Права (Permissions)** - для гранулярного контроля действий
- **Policy** - для проверки владения ресурсом и сложной бизнес-логики

## 🎯 Принципы разделения

### Роли (оставить)
Используются для разграничения интерфейсов/порталов:
- `role:admin|teacher` - доступ к админ-панели
- `role:student` - доступ к студенческому порталу
- `role:partner` - доступ к партнерскому порталу

### Права (переделать)
Используются для проверки доступа к конкретным действиям:
- `permission:users.delete` - право на удаление пользователей
- `permission:cases.create` - право на создание кейсов
- `permission:cases.approve` - право на одобрение заявок

### Policy (переделать)
Используются для проверки владения ресурсом и сложной логики:
- Партнер может редактировать только свои кейсы
- Студент может отозвать только свою заявку
- Пользователь может удалить только свои уведомления

---

## ✅ Категория 1: Оставить с ролями (разграничение порталов)

Эти места **НЕ ТРОГАТЬ** - роли здесь используются правильно для разграничения интерфейсов.

### Маршруты (routes/web.php)

```php
// ✅ ОСТАВИТЬ - разграничение порталов
Route::prefix('admin')->middleware(['auth', 'role:admin|teacher'])
Route::prefix('student')->middleware('role:student')
Route::prefix('partner')->middleware('role:partner')
```

**Файл:** `routes/web.php`
- Строки: 94, 128, 162
- **Действие:** Оставить без изменений

### Редиректы по ролям

**Файл:** `routes/web.php`
- Строки: 69-75 (редирект на dashboard)
- **Действие:** Оставить без изменений

**Файл:** `app/Http/Controllers/Auth/LoginController.php`
- Строки: 38-44 (редирект после логина)
- **Действие:** Оставить без изменений

### Middleware в конструкторах контроллеров (базовая защита портала)

Эти middleware защищают весь контроллер от доступа неавторизованных ролей:

**Файлы:**
- `app/Http/Controllers/Client/Student/*` - все контроллеры студента
- `app/Http/Controllers/Client/Partner/*` - все контроллеры партнера
- `app/Http/Controllers/Admin/CaseController.php` (строка 25)

**Действие:** Оставить как есть, но добавить дополнительные проверки через Policy/права внутри методов

---

## 🔄 Категория 2: Переделать на права (гранулярный контроль)

Эти места нужно переделать с проверки ролей на проверку прав.

### 2.1. Admin контроллеры - заменить middleware на права

#### CaseController

**Файл:** `app/Http/Controllers/Admin/CaseController.php`

**Текущее состояние:**
```php
$this->middleware(['auth', 'role:admin|teacher']);
```

**Действие:** 
1. Убрать middleware из конструктора
2. Добавить проверки прав в методы:
   - `index()` → `$this->authorize('viewAny', CaseModel::class)` или `permission:cases.view`
   - `create()` → `$this->authorize('create', CaseModel::class)` или `permission:cases.create`
   - `store()` → `$this->authorize('create', CaseModel::class)` или `permission:cases.create`
   - `show()` → `$this->authorize('view', $case)` или `permission:cases.view`
   - `edit()` → `$this->authorize('update', $case)` или `permission:cases.update`
   - `update()` → `$this->authorize('update', $case)` или `permission:cases.update`
   - `destroy()` → `$this->authorize('delete', $case)` или `permission:cases.delete`

**Приоритет:** Высокий

#### UsersController

**Файл:** `app/Http/Controllers/Admin/UsersController.php`

**Текущее состояние:** Нет middleware, но нет проверок прав

**Действие:**
1. Добавить проверки прав в методы:
   - `index()` → `$this->authorize('viewAny', User::class)` или `permission:users.view`
   - `show()` → `$this->authorize('view', $user)` или `permission:users.view`
   - `create()` → `$this->authorize('create', User::class)` или `permission:users.create`
   - `store()` → `$this->authorize('create', User::class)` или `permission:users.create`
   - `edit()` → `$this->authorize('update', $user)` или `permission:users.update`
   - `update()` → `$this->authorize('update', $user)` или `permission:users.update`
   - `destroy()` → **Policy** (проверка "нельзя удалить самого себя" + `permission:users.delete`)

**Приоритет:** Высокий

#### SkillController

**Файл:** `app/Http/Controllers/Admin/SkillController.php`

**Текущее состояние:** Есть TODO комментарии, нужно раскомментировать

**Действие:**
1. Создать `app/Policies/SkillPolicy.php`
2. Раскомментировать проверки в методах:
   - `index()` → `$this->authorize('viewAny', Skill::class)`
   - `store()` → `$this->authorize('create', Skill::class)`
   - `update()` → `$this->authorize('update', $skill)`
   - `destroy()` → `$this->authorize('delete', $skill)`

**Приоритет:** Средний

#### SimulatorController

**Файл:** `app/Http/Controllers/Admin/SimulatorController.php`

**Текущее состояние:** Есть TODO комментарии, нужно раскомментировать

**Действие:**
1. Создать `app/Policies/SimulatorPolicy.php`
2. Раскомментировать проверки в методах:
   - `index()` → `$this->authorize('viewAny', Simulator::class)`
   - `store()` → `$this->authorize('create', Simulator::class)`
   - `update()` → `$this->authorize('update', $simulator)`
   - `destroy()` → `$this->authorize('delete', $simulator)`

**Приоритет:** Средний

#### BadgeController

**Файл:** `app/Http/Controllers/Admin/BadgeController.php`

**Текущее состояние:** ✅ Уже использует Policy (`BadgePolicy`)

**Действие:** 
- Проверить, что `BadgePolicy` проверяет права, а не только роли
- Если проверяет только роли - переделать на права

**Приоритет:** Низкий (уже реализовано, но нужно проверить)

### 2.2. Маршруты - заменить role middleware на permission

**Файл:** `routes/web.php`

**Текущее состояние:**
```php
Route::prefix('admin')->middleware(['auth', 'role:admin|teacher'])
```

**Действие:** 
- Оставить `role:admin|teacher` для базовой защиты портала
- Внутри контроллеров использовать права для гранулярного контроля

**Приоритет:** Низкий (можно оставить как есть, если добавить проверки в контроллерах)

---

## 🛡️ Категория 3: Переделать на Policy (проверка владения ресурсом)

Эти места требуют проверки владения ресурсом и должны использовать Policy.

### 3.1. Partner Cases - проверка владения кейсом

**Файл:** `app/Http/Controllers/Client/Partner/CasesController.php`

**Текущее состояние:** Использует `$this->caseService->ensureCaseBelongsToPartner($case, $partner)`

**Проблема:** Логика проверки владения находится в сервисе, а должна быть в Policy

**Действие:**
1. Создать `app/Policies/CasePolicy.php` с методами:
   ```php
   public function view(User $user, CaseModel $case): bool
   public function update(User $user, CaseModel $case): bool
   public function delete(User $user, CaseModel $case): bool
   public function archive(User $user, CaseModel $case): bool
   public function viewApplications(User $user, CaseModel $case): bool
   public function approveApplication(User $user, CaseModel $case): bool
   public function rejectApplication(User $user, CaseModel $case): bool
   ```

2. В Policy проверять:
   - Админ/учитель → всегда `true` (если есть право)
   - Партнер → только если `$case->partner_id === $user->partnerProfile->partner_id`

3. Заменить все вызовы `ensureCaseBelongsToPartner` на:
   ```php
   $this->authorize('update', $case);
   ```

**Методы для изменения:**
- `show()` (строка 155) → `$this->authorize('view', $case)`
- `edit()` (строка 205) → `$this->authorize('update', $case)`
- `update()` (строка 245) → `$this->authorize('update', $case)`
- `archive()` (строка 271) → `$this->authorize('archive', $case)`
- `applications()` (строка 296) → `$this->authorize('viewApplications', $case)`
- `approve()` (строка 352) → `$this->authorize('approveApplication', $case)`
- `reject()` (строка 388) → `$this->authorize('rejectApplication', $case)`
- `exportApplications()` (строка 424) → `$this->authorize('viewApplications', $case)`

**Приоритет:** Высокий

### 3.2. Student Cases - проверка владения заявкой

**Файл:** `app/Http/Controllers/Client/Student/CasesController.php`

**Текущее состояние:** Проверки вручную в методах

**Действие:**
1. Создать `app/Policies/CaseApplicationPolicy.php` с методами:
   ```php
   public function view(User $user, CaseApplication $application): bool
   public function update(User $user, CaseApplication $application): bool
   public function delete(User $user, CaseApplication $application): bool
   public function addTeamMember(User $user, CaseApplication $application): bool
   public function viewTeam(User $user, CaseApplication $application): bool
   ```

2. В Policy проверять:
   - Лидер заявки → `$application->leader_id === $user->id`
   - Член команды → `$application->teamMembers()->where('user_id', $user->id)->exists()`
   - Для `addTeamMember` → только лидер и статус `pending`

3. Заменить проверки в методах:
   - `addTeamMember()` (строка 169) → `$this->authorize('addTeamMember', $application)`
   - `withdraw()` (строка 195) → `$this->authorize('delete', $application)`
   - `team()` (строка 215) → `$this->authorize('viewTeam', $application)`

**Приоритет:** Высокий

### 3.3. Notifications - проверка владения уведомлением

**Файл:** `app/Http/Controllers/NotificationController.php`

**Текущее состояние:** Проверка `$notification->user_id !== $user->id`

**Действие:**
1. Создать `app/Policies/AppNotificationPolicy.php` с методами:
   ```php
   public function view(User $user, AppNotification $notification): bool
   public function update(User $user, AppNotification $notification): bool
   public function delete(User $user, AppNotification $notification): bool
   ```

2. В Policy проверять: `$notification->user_id === $user->id`

3. Заменить проверки в методах:
   - `markAsRead()` (строка 66) → `$this->authorize('update', $notification)`
   - `destroy()` (строка 103) → `$this->authorize('delete', $notification)`

**Приоритет:** Средний

### 3.4. Users - проверка "нельзя удалить самого себя"

**Файл:** `app/Http/Controllers/Admin/UsersController.php`

**Текущее состояние:** Проверка `$user->id === auth()->id()` в методе `destroy()`

**Действие:**
1. Создать `app/Policies/UserPolicy.php` с методом:
   ```php
   public function delete(User $user, User $targetUser): bool
   {
       // Нельзя удалить самого себя
       if ($user->id === $targetUser->id) {
           return false;
       }
       
       // Проверка права на удаление
       return $user->hasPermissionTo('users.delete');
   }
   ```

2. Заменить проверку в `destroy()` (строка 256):
   ```php
   $this->authorize('delete', $user);
   ```

**Приоритет:** Высокий

### 3.5. Simulator Sessions - проверка владения сессией

**Файл:** `app/Http/Controllers/Client/Student/SimulatorsController.php`

**Текущее состояние:** Проверка `$session->user_id !== $user->id`

**Действие:**
1. Создать `app/Policies/SimulatorSessionPolicy.php` с методами:
   ```php
   public function view(User $user, SimulatorSession $session): bool
   public function update(User $user, SimulatorSession $session): bool
   ```

2. В Policy проверять: `$session->user_id === $user->id`

3. Заменить проверки в методах:
   - `session()` (строка 82) → `$this->authorize('view', $session)`
   - `complete()` (строка 102) → `$this->authorize('update', $session)`

**Приоритет:** Средний

### 3.6. Partner Teams - проверка владения кейсом

**Файл:** `app/Http/Controllers/Client/Partner/TeamController.php`

**Текущее состояние:** Использует `ensureCaseBelongsToPartner`

**Действие:**
1. Использовать `CasePolicy` (созданный в п. 3.1)
2. Заменить `ensureCaseBelongsToPartner` на `$this->authorize('view', $application->case)`

**Методы для изменения:**
- `show()` (строка 95) → `$this->authorize('view', $application->case)`

**Приоритет:** Средний

---

## 📝 Примеры реализации

### Пример 1: CasePolicy

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CaseModel;
use App\Models\User;

class CasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('cases.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут видеть все (если есть право)
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        // Партнер может видеть только свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id;
        }

        // Студент может видеть только активные кейсы
        if ($user->hasRole('student')) {
            return $case->status === 'active';
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('cases.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут редактировать все (если есть право)
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.update');
        }

        // Партнер может редактировать только свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id
                && $user->hasPermissionTo('cases.update');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CaseModel $case): bool
    {
        // Только админ может удалять (если есть право)
        if ($user->hasRole('admin')) {
            return $user->hasPermissionTo('cases.delete');
        }

        return false;
    }

    /**
     * Determine whether the user can archive the model.
     */
    public function archive(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут архивировать все
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.update');
        }

        // Партнер может архивировать только свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id
                && $user->hasPermissionTo('cases.update');
        }

        return false;
    }

    /**
     * Determine whether the user can view applications for the case.
     */
    public function viewApplications(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут видеть все заявки
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        // Партнер может видеть заявки только на свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id;
        }

        return false;
    }

    /**
     * Determine whether the user can approve applications.
     */
    public function approveApplication(User $user, CaseModel $case): bool
    {
        // Админ и учитель могут одобрять заявки на все кейсы
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.approve');
        }

        // Партнер может одобрять заявки только на свои кейсы
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $case->partner_id
                && $user->hasPermissionTo('cases.approve');
        }

        return false;
    }

    /**
     * Determine whether the user can reject applications.
     */
    public function rejectApplication(User $user, CaseModel $case): bool
    {
        // Аналогично approveApplication
        return $this->approveApplication($user, $case);
    }
}
```

### Пример 2: CaseApplicationPolicy

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CaseApplication;
use App\Models\User;

class CaseApplicationPolicy
{
    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, CaseApplication $application): bool
    {
        // Лидер заявки
        if ($application->leader_id === $user->id) {
            return true;
        }

        // Член команды
        if ($application->teamMembers()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Партнер, которому принадлежит кейс
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $application->case->partner_id;
        }

        // Админ и учитель
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        return false;
    }

    /**
     * Determine whether the user can update the application.
     */
    public function update(User $user, CaseApplication $application): bool
    {
        // Только лидер может обновлять заявку
        return $application->leader_id === $user->id
            && $application->status->name === 'pending';
    }

    /**
     * Determine whether the user can delete the application.
     */
    public function delete(User $user, CaseApplication $application): bool
    {
        // Только лидер может отозвать заявку
        return $application->leader_id === $user->id
            && $application->status->name === 'pending';
    }

    /**
     * Determine whether the user can add team members.
     */
    public function addTeamMember(User $user, CaseApplication $application): bool
    {
        // Только лидер может добавлять участников
        return $application->leader_id === $user->id
            && $application->status->name === 'pending';
    }

    /**
     * Determine whether the user can view the team.
     */
    public function viewTeam(User $user, CaseApplication $application): bool
    {
        // Лидер или член команды
        if ($application->leader_id === $user->id) {
            return true;
        }

        if ($application->teamMembers()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Партнер, которому принадлежит кейс
        if ($user->hasRole('partner')) {
            return $user->partnerProfile?->partner_id === $application->case->partner_id;
        }

        // Админ и учитель
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        return false;
    }
}
```

### Пример 3: Использование в контроллере

**До:**
```php
public function update(UpdateRequest $request, CaseModel $case): RedirectResponse
{
    $user = auth()->user();
    $partner = $user->partner;

    // Проверить права
    $this->caseService->ensureCaseBelongsToPartner($case, $partner);

    // Обновить кейс
    $this->caseService->updateCase($case, $request->validated());

    return redirect()
        ->route('partner.cases.show', $case)
        ->with('success', 'Кейс успешно обновлен');
}
```

**После:**
```php
public function update(UpdateRequest $request, CaseModel $case): RedirectResponse
{
    // Policy автоматически проверит владение ресурсом
    $this->authorize('update', $case);

    // Обновить кейс
    $this->caseService->updateCase($case, $request->validated());

    return redirect()
        ->route('partner.cases.show', $case)
        ->with('success', 'Кейс успешно обновлен');
}
```

---

## 🎯 Приоритеты выполнения

### Высокий приоритет (сделать первым)

1. **CasePolicy** - используется в Partner/CasesController (много мест)
2. **UserPolicy** - проверка "нельзя удалить самого себя"
3. **CaseApplicationPolicy** - используется в Student/CasesController
4. **Admin контроллеры** - добавить проверки прав в CaseController и UsersController

### Средний приоритет

1. **SkillPolicy** - раскомментировать проверки в SkillController
2. **SimulatorPolicy** - раскомментировать проверки в SimulatorController
3. **AppNotificationPolicy** - проверка владения уведомлением
4. **SimulatorSessionPolicy** - проверка владения сессией
5. **Partner Teams** - использовать CasePolicy

### Низкий приоритет

1. **BadgePolicy** - проверить, что использует права, а не только роли
2. **Маршруты** - можно оставить role middleware, если добавить проверки в контроллерах

---

## 📋 Чеклист миграции

### Шаг 1: Создать Policies

- [ ] `app/Policies/CasePolicy.php`
- [ ] `app/Policies/CaseApplicationPolicy.php`
- [ ] `app/Policies/UserPolicy.php`
- [ ] `app/Policies/SkillPolicy.php`
- [ ] `app/Policies/SimulatorPolicy.php`
- [ ] `app/Policies/AppNotificationPolicy.php`
- [ ] `app/Policies/SimulatorSessionPolicy.php`

### Шаг 2: Зарегистрировать Policies

- [ ] Добавить в `app/Providers/AuthServiceProvider.php`:
  ```php
  protected $policies = [
      CaseModel::class => CasePolicy::class,
      CaseApplication::class => CaseApplicationPolicy::class,
      User::class => UserPolicy::class,
      Skill::class => SkillPolicy::class,
      Simulator::class => SimulatorPolicy::class,
      AppNotification::class => AppNotificationPolicy::class,
      SimulatorSession::class => SimulatorSessionPolicy::class,
  ];
  ```

### Шаг 3: Обновить контроллеры

- [ ] `app/Http/Controllers/Client/Partner/CasesController.php` - заменить `ensureCaseBelongsToPartner` на `authorize()`
- [ ] `app/Http/Controllers/Client/Student/CasesController.php` - заменить проверки на `authorize()`
- [ ] `app/Http/Controllers/Admin/CaseController.php` - добавить проверки прав
- [ ] `app/Http/Controllers/Admin/UsersController.php` - добавить проверки прав и Policy
- [ ] `app/Http/Controllers/Admin/SkillController.php` - раскомментировать проверки
- [ ] `app/Http/Controllers/Admin/SimulatorController.php` - раскомментировать проверки
- [ ] `app/Http/Controllers/NotificationController.php` - заменить проверки на `authorize()`
- [ ] `app/Http/Controllers/Client/Student/SimulatorsController.php` - заменить проверки на `authorize()`
- [ ] `app/Http/Controllers/Client/Partner/TeamController.php` - использовать CasePolicy

### Шаг 4: Обновить BadgePolicy

- [ ] Проверить `app/Policies/BadgePolicy.php` - использует ли права или только роли
- [ ] Если только роли - переделать на права

### Шаг 5: Удалить устаревший код

- [ ] Удалить метод `ensureCaseBelongsToPartner` из `CaseService`
- [ ] Удалить ручные проверки владения ресурсом из контроллеров

### Шаг 6: Тестирование

- [ ] Написать тесты для всех Policies
- [ ] Проверить, что админ может делать все действия
- [ ] Проверить, что учитель имеет ограниченные права
- [ ] Проверить, что партнер может редактировать только свои кейсы
- [ ] Проверить, что студент может управлять только своими заявками
- [ ] Проверить, что пользователь может управлять только своими уведомлениями

---

## 🔍 Дополнительные замечания

1. **Права уже определены** в `database/seeders/PermissionSeeder.php` - их нужно использовать
2. **BadgePolicy уже существует** - проверить и обновить при необходимости
3. **TODO комментарии** в SkillController и SimulatorController указывают на необходимость Policy
4. **Метод ensureCaseBelongsToPartner** в CaseService должен быть заменен на Policy

---

## 📚 Полезные ссылки

- [Laravel Authorization Documentation](https://laravel.com/docs/10.x/authorization)
- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Laravel Policies Best Practices](https://laravel.com/docs/10.x/authorization#policy-methods)

---

**Дата создания:** 2024
**Статус:** План миграции
**Приоритет:** Высокий

