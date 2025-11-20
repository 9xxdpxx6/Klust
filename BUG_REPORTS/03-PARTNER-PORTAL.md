# Баги: Партнерский портал

## BUG-006: Не работают разделы "Аналитика" и "Профиль" в партнерском портале

**Приоритет**: High  
**Модуль**: Партнерский портал

### Описание
Разделы "Аналитика" (`/partner/analytics`) и "Профиль" (`/partner/profile`) не работают в партнерском портале.

### Шаги для воспроизведения
1. Авторизоваться как партнер
2. Перейти в раздел "Аналитика" из меню или напрямую по адресу `/partner/analytics`
3. Или перейти в раздел "Профиль" из меню или напрямую по адресу `/partner/profile`

### Ожидаемое поведение
- Страница "Аналитика" должна отображаться с данными аналитики партнера
- Страница "Профиль" должна отображаться с информацией о профиле компании

### Фактическое поведение
- Страницы не работают (точное поведение требует проверки: белый экран, ошибка 404, ошибка 500, или другие проблемы)

### Файлы для проверки

#### Для аналитики:
- `app/Http/Controllers/Client/Partner/AnalyticsController.php`
- `resources/js/Pages/Client/Partner/Analytics/Index.vue`
- `app/Services/AnalyticsService.php`
- `routes/web.php` (строка 154)

#### Для профиля:
- `app/Http/Controllers/Client/Partner/ProfileController.php`
- `resources/js/Pages/Client/Partner/Profile/Index.vue`
- `resources/js/Pages/Client/Partner/Profile/Edit.vue` (если используется)
- `routes/web.php` (строки 132-134)

### Предполагаемая причина

#### Для аналитики:
1. **Отсутствует или некорректно работает сервис `AnalyticsService`**:
   - Метод `getPartnerAnalytics()` может не существовать или работать некорректно
   - Ошибки при получении данных аналитики

2. **Проблемы в контроллере**:
   ```29:46:app/Http/Controllers/Client/Partner/AnalyticsController.php
   public function index(IndexRequest $request): Response
   {
       try {
           $user = auth()->user();
           $partner = $user->partner;

           // Получить аналитику через AnalyticsService::getPartnerAnalytics($partner, $request->validated())
           $analytics = $this->analyticsService->getPartnerAnalytics($partner, $request->validated());

           return Inertia::render('Client/Partner/Analytics/Index', [
               'analytics' => $analytics,
           ]);
       } catch (\Exception $e) {
           return Inertia::render('Client/Partner/Analytics/Index', [
               'analytics' => [],
               'error' => 'Ошибка при загрузке аналитики: '.$e->getMessage(),
           });
       }
   }
   ```
   - Может быть проблема с зависимостью `$this->analyticsService`
   - Ошибка при вызове метода `getPartnerAnalytics()`

3. **Проблемы во Vue компоненте**:
   - Ошибки JavaScript в `Index.vue`
   - Неправильная обработка props
   - Отсутствие необходимых данных

4. **Проблемы с маршрутизацией**:
   - Маршрут может быть не зарегистрирован правильно
   - Проблемы с middleware

#### Для профиля:
1. **Проблемы в контроллере**:
   ```27:43:app/Http/Controllers/Client/Partner/ProfileController.php
   public function show(): Response
   {
       try {
           $user = auth()->user()->load(['partnerProfile', 'partner']);

           return Inertia::render('Client/Partner/Profile/Index', [
               'user' => $user,
           ]);
       } catch (\Exception $e) {
           $user = auth()->user();

           return Inertia::render('Client/Partner/Profile/Index', [
               'user' => $user,
               'error' => 'Ошибка при загрузке профиля: '.$e->getMessage(),
           });
       }
   }
   ```
   - Ошибка при загрузке связей `partnerProfile` или `partner`
   - Проблемы с моделями или отношениями

2. **Проблемы во Vue компоненте**:
   - Ошибки JavaScript в `Index.vue`
   - Неправильная обработка данных пользователя

3. **Проблемы с отношениями моделей**:
   - Возможно, `User` не имеет отношения `partnerProfile` или `partner`
   - Или отношения не настроены правильно

### Решение

#### Для аналитики:
1. **Проверить существование сервиса**:
   - Убедиться, что `app/Services/AnalyticsService.php` существует
   - Проверить, что метод `getPartnerAnalytics()` реализован

2. **Проверить dependency injection** в контроллере:
   - Убедиться, что `AnalyticsService` правильно внедряется через конструктор

3. **Проверить логи ошибок**:
   - Проверить `storage/logs/laravel.log` на наличие ошибок
   - Проверить консоль браузера на JavaScript ошибки

4. **Проверить Vue компонент**:
   - Убедиться, что `Index.vue` существует и корректно обрабатывает props
   - Проверить наличие всех необходимых компонентов и зависимостей

5. **Проверить маршрут**:
   ```154:154:routes/web.php
   Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
   ```
   - Убедиться, что маршрут зарегистрирован правильно

#### Для профиля:
1. **Проверить отношения моделей**:
   - В модели `User` должно быть отношение `partnerProfile()`
   - В модели `User` должно быть отношение `partner()`
   - Проверить, что эти отношения правильно определены

2. **Проверить Vue компонент**:
   - Убедиться, что `Index.vue` существует и корректно отображает данные
   - Проверить наличие ошибок в консоли браузера

3. **Проверить маршруты**:
   ```132:134:routes/web.php
   Route::get('/profile', [PartnerProfileController::class, 'show'])->name('profile.show');
   Route::get('/profile/edit', [PartnerProfileController::class, 'edit'])->name('profile.edit');
   Route::put('/profile', [PartnerProfileController::class, 'update'])->name('profile.update');
   ```
   - Убедиться, что маршруты зарегистрированы правильно

### Дополнительные проверки

1. Проверить, что пользователь имеет роль `partner`
2. Проверить, что у пользователя существует запись в таблице `partner_profiles`
3. Проверить, что у пользователя существует связанная запись в таблице `partners`
4. Проверить консоль браузера на наличие ошибок
5. Проверить Network tab на статусы ответов сервера
6. Проверить логи Laravel на наличие исключений

