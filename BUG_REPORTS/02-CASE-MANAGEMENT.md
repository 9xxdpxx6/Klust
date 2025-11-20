# Баги: Управление кейсами

## BUG-004: Дублирование сообщений об успешном создании/обновлении кейса

**Приоритет**: Medium  
**Модуль**: Управление кейсами / Уведомления

### Описание
При создании или редактировании кейса сообщение об успешном выполнении операции отображается дважды (дублируется).

### Шаги для воспроизведения
1. Авторизоваться как партнер
2. Перейти в раздел создания кейса (`/partner/cases/create`)
3. Заполнить форму и создать кейс
4. Наблюдать за сообщением об успехе

Или:

1. Открыть любой существующий кейс для редактирования
2. Внести изменения и сохранить
3. Наблюдать за сообщением об успехе

### Ожидаемое поведение
- Сообщение об успешном создании/обновлении должно отображаться один раз

### Фактическое поведение
- Сообщение отображается дважды (дублируется)

### Файлы для проверки
- `resources/js/Layouts/ClientLayoutWithSidebar.vue` (строка 33 - компонент `<FlashMessage />`)
- `resources/js/Pages/Client/Partner/Cases/Create.vue`
- `resources/js/Pages/Client/Partner/Cases/Edit.vue`
- `resources/js/Pages/Admin/Cases/Create.vue` (строки 13-19)
- `resources/js/Pages/Admin/Cases/Edit.vue` (строки 13-19)
- `resources/js/Components/Shared/FlashMessage.vue`

### Предполагаемая причина
1. **Основная причина**: Компонент `FlashMessage` включен в layout (`ClientLayoutWithSidebar.vue`), но некоторые страницы также имеют встроенные блоки для отображения flash-сообщений.

В `ClientLayoutWithSidebar.vue`:
```33:33:resources/js/Layouts/ClientLayoutWithSidebar.vue
<FlashMessage />
```

В `Admin/Cases/Create.vue` и `Edit.vue` есть дополнительные блоки:
```13:19:resources/js/Pages/Admin/Cases/Create.vue
<div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
    {{ $page.props.flash.success }}
</div>

<div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
    {{ $page.props.flash.error }}
</div>
```

2. `FlashMessage` компонент отображает сообщения из `$page.props.flash.success`, а страницы также отображают их напрямую через `$page.props.flash.success`.

### Решение
1. **Удалить дублирующие блоки** из страниц, так как `FlashMessage` уже включен в layout
2. Убедиться, что все страницы используют единый компонент `FlashMessage` из layout
3. Удалить встроенные блоки flash-сообщений из:
   - `resources/js/Pages/Admin/Cases/Create.vue`
   - `resources/js/Pages/Admin/Cases/Edit.vue`
   - Любых других страниц с дублирующими сообщениями

---

## BUG-005: Проблемы с редиректом и отображением после создания кейса (партнер)

**Приоритет**: Critical  
**Модуль**: Управление кейсами / Партнерский портал

### Описание
После создания кейса партнером:
1. Система не выполняет автоматический редирект (или редирект работает некорректно)
2. При ручном переходе на `/partner/dashboard` боковая панель не работает
3. Ничего не отображается (белая страница)
4. После обновления страницы в URL появляется ссылка с нужным кейсом, но страница все равно белая и ничего не показывает

### Шаги для воспроизведения
1. Авторизоваться как партнер
2. Перейти в раздел создания кейса (`/partner/cases/create`)
3. Заполнить форму создания кейса
4. Нажать "Опубликовать" или "Сохранить как черновик"
5. Наблюдать за поведением после отправки формы

### Ожидаемое поведение
- После создания кейса должен произойти редирект на страницу просмотра созданного кейса (`/partner/cases/{id}`)
- Страница должна корректно отображаться
- Боковая панель должна работать
- Все данные кейса должны быть видны

### Фактическое поведение
- Редирект не происходит автоматически
- При ручном переходе на `/partner/dashboard` боковая панель не работает
- Отображается белая страница
- После обновления страницы в URL есть правильная ссылка, но страница все равно белая

### Файлы для проверки
- `app/Http/Controllers/Client/Partner/CasesController.php` (метод `store`, строки 121-150)
- `resources/js/Pages/Client/Partner/Cases/Create.vue` (метод `submitForm`, строки 226-247)
- `resources/js/Pages/Client/Partner/Cases/Show.vue`
- `resources/js/Layouts/PartnerLayout.vue`
- `resources/js/Layouts/ClientLayoutWithSidebar.vue`
- Консоль браузера (JavaScript ошибки)
- Network tab (ответы сервера)

### Предполагаемая причина

1. **Проблема с Inertia.js редиректом**:
   В `Create.vue` используется `preserveState: true` и `preserveScroll: true`, что может конфликтовать с серверным редиректом:
   ```236:246:resources/js/Pages/Client/Partner/Cases/Create.vue
   router.post(route('partner.cases.store'), formData, {
       preserveState: true,
       preserveScroll: true,
       onSuccess: () => {
           processing.value = false;
       },
       onError: (err) => {
           processing.value = false;
           errors.value = err.errors || err;
       }
   });
   ```

2. **Контроллер делает редирект**, но Inertia может не обработать его корректно из-за `preserveState`:
   ```141:143:app/Http/Controllers/Client/Partner/CasesController.php
   return redirect()
       ->route('partner.cases.show', $case)
       ->with('success', 'Кейс успешно создан');
   ```

3. **Возможные ошибки JavaScript** на странице `Show.vue`:
   - Ошибки в обработке данных кейса
   - Проблемы с компонентами на странице
   - Ошибки в props

4. **Проблемы с layout**:
   - Ошибка в `PartnerLayout.vue` или `ClientLayoutWithSidebar.vue`
   - Проблема с инициализацией sidebar

### Решение

1. **Убрать `preserveState` и `preserveScroll`** из запроса создания кейса, чтобы позволить Inertia корректно обработать редирект:
   ```javascript
   router.post(route('partner.cases.store'), formData, {
       onSuccess: (page) => {
           processing.value = false;
           // Inertia автоматически обработает редирект
       },
       onError: (err) => {
           processing.value = false;
           errors.value = err.errors || err;
       }
   });
   ```

2. **Проверить консоль браузера** на наличие JavaScript ошибок при открытии страницы `Show.vue`

3. **Проверить структуру данных**, передаваемых в `Show.vue`:
   - Убедиться, что все необходимые данные загружаются в контроллере
   - Проверить типы данных в props компонента

4. **Проверить layout**:
   - Убедиться, что `PartnerLayout.vue` корректно использует `ClientLayoutWithSidebar`
   - Проверить инициализацию sidebar

5. **Добавить обработку ошибок** в компоненте `Show.vue` для отображения ошибок, если они есть

### Дополнительные проверки

1. Проверить Network tab - какой статус возвращает сервер
2. Проверить Response - что именно возвращает сервер
3. Проверить, правильно ли работает route `partner.cases.show`
4. Проверить, авторизован ли пользователь после редиректа

