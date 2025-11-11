# TODO: Нереализованные функции проекта Klust

Этот файл содержит список функций, запланированных в `ARCHITECTURE_PLAN.md`, но еще не реализованных в проекте.

## Статус основной архитектуры

✅ **Выполнено:**
- Все Layouts (AdminLayout, StudentLayout, PartnerLayout, GuestLayout)
- Все основные страницы (Admin, Student, Partner)
- Все основные контроллеры
- Все Form Request классы для валидации
- Все UI компоненты
- Базовая система кейсов, заявок, команд
- Система навыков и бейджей
- Симуляторы
- Аутентификация и авторизация

---

## 🔴 Критические недостающие функции

### 1. Система уведомлений (AppNotification)

**Статус:** Частично реализовано (только модель и компонент UI)

**Что есть:**
- Модель `AppNotification` (`app/Models/AppNotification.php`)
- Миграция таблицы `app_notifications`
- Компонент UI `NotificationBell.vue` (с TODO комментариями)
- Composable `useNotifications.js` (с заглушками)

**Что нужно реализовать:**

#### Backend:
1. **Контроллер уведомлений:**
   - Создать `app/Http/Controllers/NotificationController.php`
   - Методы:
     - `index()` - список уведомлений пользователя
     - `markAsRead($notification)` - отметить как прочитанное
     - `markAllAsRead()` - отметить все как прочитанные
     - `destroy($notification)` - удалить уведомление

2. **Роуты:**
   ```php
   // В routes/web.php добавить в middleware 'auth' группу:
   Route::prefix('notifications')->name('notifications.')->group(function () {
       Route::get('/', [NotificationController::class, 'index'])->name('index');
       Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
       Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
       Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
   });
   ```

3. **Сервис для создания уведомлений:**
   - Создать `app/Services/NotificationService.php`
   - Методы:
     - `createNotification($userId, $type, $title, $message, $relatedId, $relatedType)`
     - `notifyPartnerAboutNewApplication($partner, $application)`
     - `notifyStudentAboutApplicationStatus($student, $application, $status)`
     - `notifyTeamMembersAboutNewMember($application, $newMember)`

4. **Интеграция в существующие контроллеры:**
   - `Student/CasesController@apply` - уведомить партнера о новой заявке
   - `Partner/CasesController@approve` - уведомить студента об одобрении
   - `Partner/CasesController@reject` - уведомить студента об отклонении
   - `Student/CasesController@addTeamMember` - уведомить добавленного участника

#### Frontend:
1. **Обновить `NotificationBell.vue`:**
   - Заменить TODO на реальные API вызовы
   - Интегрировать с composable `useNotifications`

2. **Обновить `useNotifications.js`:**
   - Реализовать методы `fetchNotifications()`, `markAsRead()`, `markAllAsRead()`
   - Использовать Inertia для запросов или axios

3. **Создать страницу всех уведомлений (опционально):**
   - `resources/js/Pages/Notifications/Index.vue`
   - Доступна для всех ролей

---

### 2. Email уведомления

**Статус:** Не реализовано

**Что нужно реализовать:**

#### 1. Настройка Mail:
- Проверить `.env` на наличие настроек SMTP
- Настроить `config/mail.php`

#### 2. Создать Mailable классы:

```bash
php artisan make:mail WelcomeStudentMail
php artisan make:mail WelcomePartnerMail
php artisan make:mail ApplicationSubmittedMail
php artisan make:mail ApplicationApprovedMail
php artisan make:mail ApplicationRejectedMail
php artisan make:mail TeamMemberAddedMail
```

#### 3. Создать blade шаблоны:
- `resources/views/emails/welcome-student.blade.php`
- `resources/views/emails/welcome-partner.blade.php`
- `resources/views/emails/application-submitted.blade.php`
- `resources/views/emails/application-approved.blade.php`
- `resources/views/emails/application-rejected.blade.php`
- `resources/views/emails/team-member-added.blade.php`

#### 4. Интеграция:

**В RegisterController:**
```php
// После регистрации студента
Mail::to($user->email)->send(new WelcomeStudentMail($user));

// После регистрации партнера
Mail::to($user->email)->send(new WelcomePartnerMail($user));
```

**В Student/CasesController@apply:**
```php
Mail::to($case->partner->user->email)
    ->send(new ApplicationSubmittedMail($application));
```

**В Partner/CasesController@approve:**
```php
Mail::to($application->leader->email)
    ->send(new ApplicationApprovedMail($application));
```

**В Partner/CasesController@reject:**
```php
Mail::to($application->leader->email)
    ->send(new ApplicationRejectedMail($application));
```

#### 5. Использовать очереди (рекомендуется):
```php
Mail::to($user->email)->queue(new WelcomeStudentMail($user));
```

**Настроить очереди:**
- Обновить `QUEUE_CONNECTION=database` в `.env`
- Запустить `php artisan queue:table` и `php artisan migrate`
- Запустить worker: `php artisan queue:work`

---

### 3. Экспорт данных (Partner Analytics)

**Статус:** Не реализовано

**Где упоминается:**
- `ARCHITECTURE_PLAN.md:570` - "Экспорт данных (CSV/Excel)"
- `Partner/Analytics/Index.vue` - должна быть кнопка экспорта

**Что нужно реализовать:**

#### Backend:

1. **Установить пакет для экспорта:**
```bash
composer require maatwebsite/excel
```

2. **Создать Export классы:**
```bash
php artisan make:export CasesExport
php artisan make:export ApplicationsExport
php artisan make:export TeamsExport
```

3. **Реализовать Export классы:**
- `app/Exports/CasesExport.php` - экспорт кейсов партнера
- `app/Exports/ApplicationsExport.php` - экспорт заявок на кейс
- `app/Exports/TeamsExport.php` - экспорт команд партнера

4. **Добавить методы в AnalyticsController:**
```php
public function exportCases(Request $request)
{
    return Excel::download(new CasesExport(Auth::id()), 'cases.xlsx');
}

public function exportApplications(Request $request, CaseModel $case)
{
    $this->authorize('view', $case); // Проверка что это кейс партнера
    return Excel::download(new ApplicationsExport($case->id), "applications-{$case->id}.xlsx");
}
```

5. **Добавить роуты:**
```php
// В routes/web.php в группе partner:
Route::get('/analytics/export/cases', [AnalyticsController::class, 'exportCases'])
    ->name('analytics.export.cases');
Route::get('/cases/{case}/export/applications', [AnalyticsController::class, 'exportApplications'])
    ->name('cases.export.applications');
```

#### Frontend:

**В Partner/Analytics/Index.vue:**
```vue
<Button @click="exportCases">
    <i class="pi pi-download"></i> Экспорт кейсов (Excel)
</Button>

<script setup>
const exportCases = () => {
    window.location.href = route('partner.analytics.export.cases');
};
</script>
```

**В Partner/Cases/Show.vue:**
```vue
<Button @click="exportApplications">
    <i class="pi pi-download"></i> Экспорт заявок (Excel)
</Button>

<script setup>
const props = defineProps({
    case: Object
});

const exportApplications = () => {
    window.location.href = route('partner.cases.export.applications', props.case.id);
};
</script>
```

---

## 🟡 Важные дополнительные функции

### 4. История изменений статуса заявки

**Статус:** Не реализовано

**Где упоминается:** `ARCHITECTURE_PLAN.md:355` - "История изменений статуса заявки"

**Что нужно реализовать:**

#### 1. Создать миграцию:
```bash
php artisan make:migration create_case_application_status_history_table
```

```php
Schema::create('case_application_status_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('case_application_id')->constrained('case_applications')->onDelete('cascade');
    $table->string('old_status')->nullable();
    $table->string('new_status');
    $table->text('comment')->nullable();
    $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
    $table->timestamp('changed_at');
    $table->timestamps();
});
```

#### 2. Создать модель:
```bash
php artisan make:model CaseApplicationStatusHistory
```

#### 3. Интегрировать в ApplicationService:
- При изменении статуса заявки (approve/reject) создавать запись в истории
- Загружать историю при показе заявки

#### 4. Показать историю в UI:
- В `Student/Cases/Show.vue` показать timeline изменений статуса
- В `Partner/Cases/Show.vue` показать историю для каждой заявки

---

### 5. Чат для команд

**Статус:** Не реализовано

**Где упоминается:** `ARCHITECTURE_PLAN.md:381` - "Чат/обсуждения (если реализовано)"

**Примечание:** Это сложная функция, требующая WebSockets

**Что нужно реализовать (упрощенная версия без WebSockets):**

#### 1. Создать миграцию:
```bash
php artisan make:migration create_team_messages_table
```

```php
Schema::create('team_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('case_application_id')->constrained('case_applications')->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->text('message');
    $table->timestamps();
});
```

#### 2. Создать модель и контроллер:
```bash
php artisan make:model TeamMessage
php artisan make:controller Client/TeamChatController
```

#### 3. Добавить роуты:
```php
// Для студентов и партнеров
Route::middleware('auth')->group(function () {
    Route::get('/team/{application}/messages', [TeamChatController::class, 'index'])
        ->name('team.messages.index');
    Route::post('/team/{application}/messages', [TeamChatController::class, 'store'])
        ->name('team.messages.store');
});
```

#### 4. Создать компонент чата:
- `resources/js/Components/TeamChat.vue`
- Использовать polling для обновления сообщений каждые 5 секунд
- Или использовать WebSockets (см. следующий раздел)

---

### 6. Real-time уведомления (WebSockets)

**Статус:** Не реализовано

**Где упоминается:** `ARCHITECTURE_PLAN.md:1226` - "Уведомления в реальном времени (WebSockets/Pusher)"

**Что нужно реализовать:**

#### Вариант 1: Laravel Echo + Pusher (проще):

1. **Установить пакеты:**
```bash
composer require pusher/pusher-php-server
npm install --save-dev laravel-echo pusher-js
```

2. **Настроить `.env`:**
```
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

3. **Создать Events:**
```bash
php artisan make:event NewApplicationEvent
php artisan make:event ApplicationStatusChangedEvent
php artisan make:event NewTeamMessageEvent
```

4. **Настроить Laravel Echo в frontend:**
```javascript
// resources/js/bootstrap.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

5. **Слушать события в компонентах:**
```javascript
// В NotificationBell.vue
Echo.private(`user.${userId}`)
    .listen('NewApplicationEvent', (e) => {
        notifications.value.unshift(e.notification);
        unreadCount.value++;
    });
```

#### Вариант 2: Laravel WebSockets (бесплатно, сложнее):

1. **Установить пакет:**
```bash
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
php artisan migrate
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
```

2. **Запустить WebSocket сервер:**
```bash
php artisan websockets:serve
```

3. **Настроить аналогично Pusher, но с другими параметрами**

---

### 7. Темная тема

**Статус:** Не реализовано

**Где упоминается:** `ARCHITECTURE_PLAN.md:39` - "Темная/светлая тема (опционально)"

**Что нужно реализовать:**

#### 1. Настроить TailwindCSS для dark mode:

**В `tailwind.config.js`:**
```javascript
module.exports = {
    darkMode: 'class', // или 'media' для автоопределения
    // ...
}
```

#### 2. Создать composable для управления темой:

**`resources/js/Composables/useTheme.js`:**
```javascript
import { ref, watch } from 'vue';

const isDark = ref(localStorage.getItem('theme') === 'dark');

export function useTheme() {
    const toggleTheme = () => {
        isDark.value = !isDark.value;
    };

    watch(isDark, (newValue) => {
        if (newValue) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }, { immediate: true });

    return {
        isDark,
        toggleTheme
    };
}
```

#### 3. Добавить переключатель в header:

**В `Components/Navigation/UserDropdown.vue`:**
```vue
<template>
    <button @click="toggleTheme" class="flex items-center gap-2 px-4 py-2">
        <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'"></i>
        <span>{{ isDark ? 'Светлая тема' : 'Темная тема' }}</span>
    </button>
</template>

<script setup>
import { useTheme } from '@/Composables/useTheme';

const { isDark, toggleTheme } = useTheme();
</script>
```

#### 4. Обновить стили компонентов:
- Добавить `dark:` классы для всех компонентов
- Пример: `bg-white dark:bg-gray-800`, `text-gray-900 dark:text-gray-100`

---

## 🟢 Опциональные улучшения

### 8. Анимации и улучшения UX

**Что можно добавить:**

#### 1. Vue Transitions:
```vue
<Transition name="fade">
    <FlashMessage v-if="flash.success" />
</Transition>

<style>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
```

#### 2. Loading states:
- Скелетоны для загрузки данных
- Прогресс-бары при отправке форм
- Спиннеры на кнопках

#### 3. Drag & Drop:
- Для добавления участников команды
- Для загрузки файлов (если будет реализовано)
- Библиотека: `vue-draggable-plus`

---

### 9. Продвинутый поиск (Elasticsearch)

**Статус:** Базовый поиск реализован через SearchController

**Для улучшения:**

1. **Установить Scout + Elasticsearch:**
```bash
composer require laravel/scout
composer require matchish/laravel-scout-elasticsearch
```

2. **Настроить индексирование:**
```php
// В моделях User, CaseModel, Skill и т.д.
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

3. **Использовать в SearchController:**
```php
$results = [
    'users' => User::search($query)->get(),
    'cases' => CaseModel::search($query)->get(),
    'skills' => Skill::search($query)->get(),
];
```

---

### 10. Кеширование

**Что можно закешировать:**

#### Dashboard статистика:
```php
// В DashboardController
$stats = Cache::remember('admin.dashboard.stats', 3600, function () {
    return [
        'students_count' => User::role('student')->count(),
        'partners_count' => User::role('partner')->count(),
        'active_cases' => CaseModel::where('status', 'active')->count(),
        // ...
    ];
});
```

#### Списки навыков/бейджей:
```php
$skills = Cache::remember('skills.all', 86400, function () {
    return Skill::orderBy('name')->get();
});
```

**Сброс кеша при изменениях:**
```php
// После создания/обновления/удаления
Cache::forget('admin.dashboard.stats');
```

---

### 11. Тестирование

**Что нужно добавить:**

#### Feature тесты:
```bash
php artisan make:test Auth/LoginTest
php artisan make:test Student/CaseApplicationTest
php artisan make:test Partner/CaseManagementTest
php artisan make:test Admin/UserManagementTest
```

#### Unit тесты для сервисов:
```bash
php artisan make:test Services/CaseServiceTest --unit
php artisan make:test Services/ApplicationServiceTest --unit
```

#### Запуск тестов:
```bash
php artisan test
php artisan test --coverage
```

---

## Приоритеты реализации

### 🔴 Критично (сделать в первую очередь):
1. **Система уведомлений** - основная функция для взаимодействия
2. **Email уведомления** - важно для информирования пользователей
3. **История статусов заявок** - важно для прозрачности

### 🟡 Важно (сделать во вторую очередь):
4. **Экспорт данных** - полезно для партнеров
5. **Улучшенный поиск** - улучшает UX
6. **Кеширование** - улучшает производительность

### 🟢 Опционально (можно сделать позже):
7. **Чат для команд** - nice to have
8. **Real-time уведомления** - улучшает UX, но не критично
9. **Темная тема** - косметика
10. **Анимации** - косметика
11. **Тестирование** - важно, но можно делать постепенно

---

## Примечания для разработчика

- Все новые контроллеры создавать с использованием Service классов
- Все формы валидировать через Form Request классы
- Все новые страницы рендерить через `Inertia::render()`
- Использовать транзакции (`DB::transaction()`) для операций с несколькими таблицами
- Добавлять middleware проверки прав доступа
- Следовать структуре роутов: `{role}.{resource}.{action}`
- Использовать Ziggy для генерации роутов в Vue: `route('admin.users.index')`

---

**Дата создания:** 2025-11-11
**Версия:** 1.0
