# Ревью миграции на Policy и права

**Ветка:** `claude/implement-migration-policies-01W3N4DtoBJSM7EBHaXi5Pjz`  
**Дата ревью:** 2024  
**Статус:** ✅ В целом хорошо, но есть критические проблемы

---

## ✅ Что сделано правильно

### 1. Созданы все необходимые Policy

- ✅ `CasePolicy.php` - правильно реализован, использует права
- ✅ `CaseApplicationPolicy.php` - правильно реализован
- ✅ `UserPolicy.php` - правильно проверяет "нельзя удалить самого себя"
- ✅ `SkillPolicy.php` - использует права
- ✅ `SimulatorPolicy.php` - использует права
- ✅ `AppNotificationPolicy.php` - проверяет владение
- ✅ `SimulatorSessionPolicy.php` - проверяет владение
- ✅ `BadgePolicy.php` - обновлен, теперь использует права вместо только ролей

### 2. Policy зарегистрированы в AuthServiceProvider

```php
protected $policies = [
    \App\Models\Badge::class => \App\Policies\BadgePolicy::class,
    \App\Models\CaseModel::class => \App\Policies\CasePolicy::class,
    \App\Models\CaseApplication::class => \App\Policies\CaseApplicationPolicy::class,
    \App\Models\User::class => \App\Policies\UserPolicy::class,
    \App\Models\Skill::class => \App\Policies\SkillPolicy::class,
    \App\Models\Simulator::class => \App\Policies\SimulatorPolicy::class,
    \App\Models\AppNotification::class => \App\Policies\AppNotificationPolicy::class,
    \App\Models\SimulatorSession::class => \App\Policies\SimulatorSessionPolicy::class,
];
```

✅ Все Policy правильно зарегистрированы.

### 3. Контроллеры обновлены

- ✅ `Partner/CasesController` - все вызовы `ensureCaseBelongsToPartner` заменены на `authorize()`
- ✅ `Student/CasesController` - проверки заменены на `authorize()`
- ✅ `Admin/CaseController` - добавлены проверки `authorize()`
- ✅ `Admin/UsersController` - добавлены проверки `authorize()`, включая `destroy()`
- ✅ `Admin/SkillController` - раскомментированы проверки `authorize()`
- ✅ `Admin/SimulatorController` - раскомментированы проверки `authorize()`
- ✅ `NotificationController` - заменены проверки на `authorize()`
- ✅ `SimulatorsController` - заменены проверки на `authorize()`
- ✅ `TeamController` - использует `authorize()` для проверки кейса

### 4. Устаревший код удален

- ✅ Метод `ensureCaseBelongsToPartner` удален из `CaseService`

---

## ⚠️ Критические проблемы

### Проблема 1: N+1 запросы в CaseApplicationPolicy

**Файл:** `app/Policies/CaseApplicationPolicy.php`

**Проблема:** В Policy используются связи `status` и `case`, которые могут быть не загружены, что приводит к N+1 запросам или ошибкам.

**Места:**
1. `update()`, `delete()`, `addTeamMember()` - используют `$application->status->name`
2. `view()`, `viewTeam()` - используют `$application->case->partner_id`

**Пример проблемного кода:**
```php
public function update(User $user, CaseApplication $application): bool
{
    return $application->leader_id === $user->id
        && $application->status->name === 'pending'; // ❌ N+1 запрос, если status не загружен
}
```

**Решение:**

**Вариант 1:** Загружать связи в контроллерах ДО вызова `authorize()`

```php
// В контроллере
public function addTeamMember(AddTeamMemberRequest $request, CaseApplication $application): RedirectResponse
{
    // Загрузить связи ДО authorize()
    $application->load('status', 'case');
    
    $this->authorize('addTeamMember', $application);
    // ...
}
```

**Вариант 2:** Использовать проверку через `status_id` вместо связи (рекомендуется)

```php
// В CaseApplicationPolicy
public function update(User $user, CaseApplication $application): bool
{
    if ($application->leader_id !== $user->id) {
        return false;
    }
    
    // Использовать status_id вместо связи
    $pendingStatusId = \App\Models\ApplicationStatus::getIdByName('pending');
    return $application->status_id === $pendingStatusId;
}
```

**Вариант 3:** Добавить проверку на null и использовать eager loading через route model binding

```php
// В CaseApplicationPolicy
public function update(User $user, CaseApplication $application): bool
{
    if ($application->leader_id !== $user->id) {
        return false;
    }
    
    // Проверка на null
    if (!$application->relationLoaded('status')) {
        $application->load('status');
    }
    
    return $application->status?->name === 'pending';
}
```

**Рекомендация:** Использовать **Вариант 2** (через `status_id`) - это самый эффективный способ, так как не требует дополнительных запросов.

### Проблема 2: Отсутствие проверки на null в CaseApplicationPolicy

**Файл:** `app/Policies/CaseApplicationPolicy.php`

**Проблема:** Если связи `case` или `status` не загружены и равны null, будет ошибка.

**Пример:**
```php
public function view(User $user, CaseApplication $application): bool
{
    // ...
    if ($user->hasRole('partner')) {
        return $user->partnerProfile?->partner_id === $application->case->partner_id; // ❌ Может быть null
    }
    // ...
}
```

**Решение:** Добавить проверки на null:

```php
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
        // Проверка на null
        if (!$application->relationLoaded('case')) {
            $application->load('case');
        }
        
        return $user->partnerProfile?->partner_id === $application->case?->partner_id;
    }

    // Админ и учитель
    if ($user->hasAnyRole(['admin', 'teacher'])) {
        return $user->hasPermissionTo('cases.view');
    }

    return false;
}
```

### Проблема 3: Загрузка связей после authorize() в team()

**Файл:** `app/Http/Controllers/Client/Student/CasesController.php`

**Проблема:** В методе `team()` связи загружаются ПОСЛЕ вызова `authorize()`, но Policy может использовать эти связи.

**Текущий код:**
```php
public function team(CaseApplication $application): Response
{
    // Проверка статуса БЕЗ загрузки связи
    if ($application->status->name !== 'accepted') { // ❌ N+1 запрос
        abort(404);
    }

    // Проверка прав БЕЗ загрузки связи case
    $this->authorize('viewTeam', $application); // ❌ Может использовать $application->case

    // Загрузка связей ПОСЛЕ authorize()
    $application->load(['leader', 'teamMembers.user', 'case']);
    // ...
}
```

**Решение:** Загружать связи ДО использования:

```php
public function team(CaseApplication $application): Response
{
    // Загрузить связи ДО проверок
    $application->load(['status', 'case', 'leader', 'teamMembers.user']);
    
    // Теперь можно безопасно проверять
    if ($application->status->name !== 'accepted') {
        abort(404);
    }

    // Policy может безопасно использовать загруженные связи
    $this->authorize('viewTeam', $application);

    // Получить прогресс команды
    $progress = $this->teamService->getTeamProgress($application);

    return Inertia::render('Client/Student/Cases/Team', [
        'team' => $application,
        'progress' => $progress,
    ]);
}
```

---

## 🔍 Средние проблемы

### Проблема 4: Отсутствие eager loading в route model binding

**Проблема:** Laravel route model binding не загружает связи автоматически. Нужно либо:
1. Использовать `status_id` вместо `status->name` в Policy
2. Добавить eager loading в контроллерах
3. Использовать route model binding с загрузкой связей

**Рекомендация:** Использовать `status_id` в Policy (см. Проблему 1, Вариант 2).

### Проблема 5: CaseApplicationPolicy использует exists() для проверки членства

**Файл:** `app/Policies/CaseApplicationPolicy.php`

**Текущий код:**
```php
if ($application->teamMembers()->where('user_id', $user->id)->exists()) {
    return true;
}
```

**Проблема:** Это дополнительный запрос к БД каждый раз при проверке.

**Решение:** Если связи уже загружены, использовать их:

```php
public function view(User $user, CaseApplication $application): bool
{
    // Лидер заявки
    if ($application->leader_id === $user->id) {
        return true;
    }

    // Член команды - проверяем загруженную связь или делаем запрос
    if ($application->relationLoaded('teamMembers')) {
        $isTeamMember = $application->teamMembers->contains('user_id', $user->id);
    } else {
        $isTeamMember = $application->teamMembers()->where('user_id', $user->id)->exists();
    }
    
    if ($isTeamMember) {
        return true;
    }
    
    // ...
}
```

**Приоритет:** Средний - можно оптимизировать позже.

---

## 📝 Рекомендации по исправлению

### Приоритет 1 (Критично - исправить перед мержем)

1. **Исправить CaseApplicationPolicy** - использовать `status_id` вместо `status->name`
2. **Добавить проверки на null** в CaseApplicationPolicy для `case`
3. **Исправить метод team()** - загружать связи ДО authorize()

### Приоритет 2 (Желательно исправить)

4. Оптимизировать проверку членства в команде в CaseApplicationPolicy

### Приоритет 3 (Можно оставить на потом)

5. Добавить тесты для всех Policy
6. Добавить документацию по использованию Policy

---

## ✅ Чеклист для исправления

- [ ] Исправить `CaseApplicationPolicy::update()` - использовать `status_id`
- [ ] Исправить `CaseApplicationPolicy::delete()` - использовать `status_id`
- [ ] Исправить `CaseApplicationPolicy::addTeamMember()` - использовать `status_id`
- [ ] Добавить проверки на null для `case` в `CaseApplicationPolicy::view()`
- [ ] Добавить проверки на null для `case` в `CaseApplicationPolicy::viewTeam()`
- [ ] Исправить `Student/CasesController::team()` - загружать связи ДО authorize()
- [ ] Протестировать все Policy на наличие N+1 запросов
- [ ] Добавить unit-тесты для Policy

---

## 📊 Итоговая оценка

**Общая оценка:** 8/10

**Плюсы:**
- ✅ Все необходимые Policy созданы
- ✅ Правильно зарегистрированы в AuthServiceProvider
- ✅ Контроллеры обновлены согласно плану
- ✅ Устаревший код удален
- ✅ BadgePolicy обновлен на использование прав

**Минусы:**
- ⚠️ N+1 запросы в CaseApplicationPolicy (критично)
- ⚠️ Отсутствие проверок на null (критично)
- ⚠️ Неправильный порядок загрузки связей в team() (критично)

**Рекомендация:** Исправить критические проблемы перед мержем в main.

---

## 🔧 Примеры исправлений

### Исправление CaseApplicationPolicy

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApplicationStatus;
use App\Models\CaseApplication;
use App\Models\User;

class CaseApplicationPolicy
{
    /**
     * Get pending status ID (cached)
     */
    private function getPendingStatusId(): int
    {
        static $pendingStatusId = null;
        
        if ($pendingStatusId === null) {
            $pendingStatusId = ApplicationStatus::getIdByName('pending');
        }
        
        return $pendingStatusId;
    }

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
            // Загрузить case если не загружен
            if (!$application->relationLoaded('case')) {
                $application->load('case');
            }
            
            return $user->partnerProfile?->partner_id === $application->case?->partner_id;
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
            && $application->status_id === $this->getPendingStatusId(); // ✅ Используем status_id
    }

    /**
     * Determine whether the user can delete the application.
     */
    public function delete(User $user, CaseApplication $application): bool
    {
        // Только лидер может отозвать заявку
        return $application->leader_id === $user->id
            && $application->status_id === $this->getPendingStatusId(); // ✅ Используем status_id
    }

    /**
     * Determine whether the user can add team members.
     */
    public function addTeamMember(User $user, CaseApplication $application): bool
    {
        // Только лидер может добавлять участников
        return $application->leader_id === $user->id
            && $application->status_id === $this->getPendingStatusId(); // ✅ Используем status_id
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
            // Загрузить case если не загружен
            if (!$application->relationLoaded('case')) {
                $application->load('case');
            }
            
            return $user->partnerProfile?->partner_id === $application->case?->partner_id;
        }

        // Админ и учитель
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return $user->hasPermissionTo('cases.view');
        }

        return false;
    }
}
```

### Исправление Student/CasesController::team()

```php
public function team(CaseApplication $application): Response
{
    // Загрузить все необходимые связи ДО проверок
    $application->load(['status', 'case', 'leader', 'teamMembers.user']);
    
    // Теперь можно безопасно проверять статус
    if ($application->status->name !== 'accepted') {
        abort(404);
    }

    // Policy может безопасно использовать загруженные связи
    $this->authorize('viewTeam', $application);

    // Получить прогресс команды
    $progress = $this->teamService->getTeamProgress($application);

    return Inertia::render('Client/Student/Cases/Team', [
        'team' => $application,
        'progress' => $progress,
    ]);
}
```

---

**Дата ревью:** 2024  
**Ревьюер:** AI Assistant  
**Статус:** Требуются исправления перед мержем

