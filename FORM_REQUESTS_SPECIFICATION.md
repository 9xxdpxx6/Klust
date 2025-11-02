# 📋 Спецификация Form Request классов для проекта Klust

## 📝 Общие правила валидации

- Все Form Request классы должны использовать `declare(strict_types=1);`
- Все сообщения об ошибках должны быть на русском языке
- Использовать соответствующие правила валидации Laravel
- Проверять существование связанных записей через `exists:` правило
- Использовать `nullable()` для необязательных полей
- Использовать `sometimes` для условной валидации

**Дефолтные параметры файлов:**
- Аватары: `image`, `mimes:jpeg,png,jpg,gif`, `max:2048` (2MB)
- Логотипы: `image`, `mimes:jpeg,png,jpg,gif,svg`, `max:5120` (5MB)
- Иконки бейджей: `image`, `mimes:jpeg,png,jpg,gif,svg`, `max:2048` (2MB)
- Превью изображения: `image`, `mimes:jpeg,png,jpg,gif`, `max:5120` (5MB)

---

## 🔐 Auth (Аутентификация)

### ✅ Уже созданы:
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/Http/Requests/Auth/RegisterStudentRequest.php`
- `app/Http/Requests/Auth/RegisterPartnerRequest.php`

### ❓ Опционально:
- `app/Http/Requests/Auth/ResetPasswordRequest.php` - для восстановления пароля (если будет реализовано)

---

## 👥 Admin (Админка) - Управление пользователями

### `app/Http/Requests/Admin/User/StoreRequest.php`
**Использование:** Создание пользователя администратором

**Поля:**
```php
[
    'kubgtu_id' => ['nullable', 'string', 'max:255', 'unique:users,kubgtu_id'],
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
    'password' => ['required', 'string', 'min:8', 'confirmed'],
    'role' => ['required', 'string', 'in:student,teacher,partner,admin'],
    'course' => ['required_if:role,student', 'nullable', 'integer', 'min:1', 'max:6'],
    'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    
    // Для StudentProfile
    'faculty' => ['required_if:role,student', 'nullable', 'string', 'max:255'],
    'group' => ['required_if:role,student', 'nullable', 'string', 'max:50'],
    'specialization' => ['nullable', 'string', 'max:255'],
    'bio' => ['nullable', 'string', 'max:1000'],
    
    // Для PartnerProfile
    'company_name' => ['required_if:role,partner', 'nullable', 'string', 'max:255'],
    'inn' => ['nullable', 'string', 'max:20'],
    'address' => ['nullable', 'string', 'max:500'],
    'website' => ['nullable', 'url', 'max:255'],
    'description' => ['nullable', 'string', 'max:2000'],
    'contact_person' => ['required_if:role,partner', 'nullable', 'string', 'max:255'],
    'contact_phone' => ['nullable', 'string', 'max:20'],
]
```

**Примечания:**
- Валидация полей профиля зависит от роли
- `kubgtu_id` обязателен только для студентов, но уникален для всех
- Email должен быть уникальным

---

### `app/Http/Requests/Admin/User/UpdateRequest.php`
**Использование:** Обновление пользователя администратором

**Поля:**
```php
[
    'kubgtu_id' => ['nullable', 'string', 'max:255', 'unique:users,kubgtu_id,' . $this->route('user')->id],
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->route('user')->id],
    'password' => ['nullable', 'string', 'min:8', 'confirmed'],
    'role' => ['sometimes', 'required', 'string', 'in:student,teacher,partner,admin'],
    'course' => ['nullable', 'integer', 'min:1', 'max:6'],
    'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    
    // Для StudentProfile
    'faculty' => ['nullable', 'string', 'max:255'],
    'group' => ['nullable', 'string', 'max:50'],
    'specialization' => ['nullable', 'string', 'max:255'],
    'bio' => ['nullable', 'string', 'max:1000'],
    
    // Для PartnerProfile
    'company_name' => ['nullable', 'string', 'max:255'],
    'inn' => ['nullable', 'string', 'max:20'],
    'address' => ['nullable', 'string', 'max:500'],
    'website' => ['nullable', 'url', 'max:255'],
    'description' => ['nullable', 'string', 'max:2000'],
    'contact_person' => ['nullable', 'string', 'max:255'],
    'contact_phone' => ['nullable', 'string', 'max:20'],
]
```

**Примечания:**
- Пароль опционален при обновлении
- Unique правила должны исключать текущего пользователя
- Можно использовать `sometimes` для условной валидации

---

## 📦 Cases (Кейсы)

### `app/Http/Requests/Admin/Case/StoreRequest.php`
**Использование:** Создание кейса администратором (от имени партнера)

**Поля:**
```php
[
    'partner_id' => ['required', 'exists:partners,id'],
    'title' => ['required', 'string', 'max:255'],
    'description' => ['required', 'string', 'min:50', 'max:10000'],
    'simulator_id' => ['nullable', 'exists:simulators,id'],
    'deadline' => ['required', 'date', 'after:today'],
    'reward' => ['required', 'string', 'max:1000'],
    'required_team_size' => ['required', 'integer', 'min:1', 'max:10'], // От 1 до 10 человек
    'status' => ['nullable', 'string', 'in:draft,active,completed,archived'], // По умолчанию 'draft'
    'required_skills' => ['nullable', 'array'], // Массив ID навыков
    'required_skills.*' => ['exists:skills,id', 'distinct'], // Уникальные ID навыков
]
```

**Примечания:**
- Дедлайн должен быть в будущем
- Размер команды: 1-10 человек (капитан + до 9 участников)
- Статус по умолчанию 'draft', можно сразу установить 'active'
- Навыки хранятся в pivot таблице `case_skills`

---

### `app/Http/Requests/Admin/Case/UpdateRequest.php`
**Использование:** Обновление кейса администратором

**Поля:**
```php
[
    'partner_id' => ['sometimes', 'required', 'exists:partners,id'],
    'title' => ['required', 'string', 'max:255'],
    'description' => ['required', 'string', 'min:50', 'max:10000'],
    'simulator_id' => ['nullable', 'exists:simulators,id'],
    'deadline' => ['required', 'date'], // Можно изменить на любую дату
    'reward' => ['required', 'string', 'max:1000'],
    'required_team_size' => ['required', 'integer', 'min:1', 'max:10'],
    'status' => ['nullable', 'string', 'in:draft,active,completed,archived'],
    'required_skills' => ['nullable', 'array'],
    'required_skills.*' => ['exists:skills,id', 'distinct'],
]
```

**Примечания:**
- При обновлении можно изменить дедлайн на любую дату (включая прошедшую)
- Статус можно изменять (например, архивировать кейс)

---

### `app/Http/Requests/Partner/Case/StoreRequest.php`
**Использование:** Создание кейса партнером (от своего имени)

**Поля:**
```php
[
    // partner_id автоматически берется из auth()->user()
    'title' => ['required', 'string', 'max:255'],
    'description' => ['required', 'string', 'min:50', 'max:10000'],
    'simulator_id' => ['nullable', 'exists:simulators,id'], // Любой доступный симулятор
    'deadline' => ['required', 'date', 'after:today'],
    'reward' => ['required', 'string', 'max:1000'],
    'required_team_size' => ['required', 'integer', 'min:1', 'max:10'],
    'status' => ['nullable', 'string', 'in:draft,active'], // Партнер не может создавать completed/archived
    'required_skills' => ['nullable', 'array'],
    'required_skills.*' => ['exists:skills,id', 'distinct'],
]
```

**Примечания:**
- `partner_id` не валидируется, так как берется из текущего пользователя
- Партнер может выбрать любой симулятор (проверка прав в контроллере, если нужна)
- Партнер может создать только draft или active статус

---

### `app/Http/Requests/Partner/Case/UpdateRequest.php`
**Использование:** Обновление кейса партнером

**Поля:**
```php
[
    'title' => ['required', 'string', 'max:255'],
    'description' => ['required', 'string', 'min:50', 'max:10000'],
    'simulator_id' => ['nullable', 'exists:simulators,id'],
    'deadline' => ['required', 'date'],
    'reward' => ['required', 'string', 'max:1000'],
    'required_team_size' => ['required', 'integer', 'min:1', 'max:10'],
    'status' => ['nullable', 'string', 'in:draft,active'], // Партнер не может архивировать/завершать (только админ)
    'required_skills' => ['nullable', 'array'],
    'required_skills.*' => ['exists:skills,id', 'distinct'],
]
```

**Примечания:**
- Партнер может изменить дедлайн на любую дату
- Партнер не может устанавливать статусы 'completed' или 'archived' (только админ)

---

## 🎓 Student (Студент)

### `app/Http/Requests/Student/Case/ApplyRequest.php`
**Использование:** Подача заявки студентом на кейс

**Поля:**
```php
[
    // case_id берется из роута
    'motivation' => ['required', 'string', 'min:20', 'max:2000'], // Мотивационное письмо
    'team_members' => ['nullable', 'array', 'max:9'], // До 9 участников (лидер уже есть, итого до 10)
    'team_members.*' => ['exists:users,id', 'distinct'], // Уникальные ID пользователей
]
```

**Примечания:**
- Лидер (заявитель) автоматически включается в команду
- Можно указать сразу до 9 участников (итого с лидером до 10 человек)
- После подачи заявки можно добавлять участников до одобрения заявки (если регистрация еще не завершена)
- **Дополнительная валидация в контроллере:**
  - Проверка, что студент еще не подал заявку на этот кейс
  - Проверка, что все team_members являются студентами (роль 'student')
  - Проверка, что кейс имеет статус 'active' и дедлайн не прошел
  - Проверка, что общий размер команды (лидер + участники) не превышает required_team_size кейса
  - Проверка, что участники не находятся уже в другой команде на этом кейсе

---

### `app/Http/Requests/Student/Case/AddTeamMemberRequest.php`
**Использование:** Добавление участника в команду (до одобрения заявки)

**Поля:**
```php
[
    // application_id берется из роута
    'user_id' => ['required', 'exists:users,id'],
]
```

**Примечания:**
- Можно добавлять участников только если заявка имеет статус 'pending'
- **Дополнительная валидация в контроллере:**
  - Проверка, что заявка принадлежит текущему студенту
  - Проверка, что user_id - студент
  - Проверка, что общий размер команды не превышает required_team_size кейса
  - Проверка, что студент не находится уже в другой команде на этом кейсе

---

### `app/Http/Requests/Student/Profile/UpdateRequest.php`
**Использование:** Обновление профиля студента

**Поля:**
```php
[
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
    'password' => ['nullable', 'string', 'min:8', 'confirmed'],
    'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    'course' => ['nullable', 'integer', 'min:1', 'max:6'],
    
    // StudentProfile
    'faculty' => ['nullable', 'string', 'max:255'],
    'group' => ['nullable', 'string', 'max:50'],
    'specialization' => ['nullable', 'string', 'max:255'],
    'bio' => ['nullable', 'string', 'max:1000'],
]
```

**Примечания:**
- `kubgtu_id` не должен редактироваться студентом (readonly, только админ может изменить)
- Email должен быть уникальным, исключая текущего пользователя

---

## 🏢 Partner (Партнер)

### `app/Http/Requests/Partner/Profile/UpdateRequest.php`
**Использование:** Обновление профиля партнера

**Поля:**
```php
[
    'name' => ['required', 'string', 'max:255'], // contact_person
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
    'password' => ['nullable', 'string', 'min:8', 'confirmed'],
    
    // PartnerProfile
    'company_name' => ['required', 'string', 'max:255'],
    'inn' => ['nullable', 'string', 'max:20'],
    'address' => ['nullable', 'string', 'max:500'],
    'website' => ['nullable', 'url', 'max:255'],
    'description' => ['nullable', 'string', 'max:2000'],
    'contact_person' => ['required', 'string', 'max:255'],
    'contact_phone' => ['required', 'string', 'max:20'],
    'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'], // 5MB для логотипа
]
```

**Примечания:**
- Логотип компании может быть больше аватара (5MB vs 2MB)

---

### `app/Http/Requests/Partner/Application/ApproveRequest.php`
**Использование:** Одобрение заявки партнером

**Поля:**
```php
[
    // application_id и case_id берутся из роута
    'comment' => ['nullable', 'string', 'max:1000'], // Комментарий к одобрению
]
```

**Примечания:**
- Валидация в контроллере: проверка, что кейс принадлежит партнеру
- Проверка, что заявка существует и имеет статус 'pending'

---

### `app/Http/Requests/Partner/Application/RejectRequest.php`
**Использование:** Отклонение заявки партнером

**Поля:**
```php
[
    // application_id и case_id берутся из роута
    'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'], // Причина отклонения обязательна
]
```

**Примечания:**
- Причина отклонения обязательна для обратной связи со студентом
- Валидация в контроллере аналогична ApproveRequest

---

## 🎯 Skills (Навыки)

### `app/Http/Requests/Admin/Skill/StoreRequest.php`
**Использование:** Создание навыка администратором

**Поля:**
```php
[
    'name' => ['required', 'string', 'max:255', 'unique:skills,name'],
    'category' => ['required', 'string', 'in:hard,soft,language,other'], // Категории навыков
    'max_level' => ['required', 'integer', 'min:1', 'max:1000'], // Максимальный уровень навыка
]
```

**Примечания:**
- Название навыка должно быть уникальным
- Категории: hard (технические), soft (мягкие навыки), language (языки), other (другие)

---

### `app/Http/Requests/Admin/Skill/UpdateRequest.php`
**Использование:** Обновление навыка администратором

**Поля:**
```php
[
    'name' => ['required', 'string', 'max:255', 'unique:skills,name,' . $this->route('skill')->id],
    'category' => ['required', 'string', 'in:hard,soft,language,other'],
    'max_level' => ['required', 'integer', 'min:1', 'max:1000'],
]
```

---

## 🏅 Badges (Бейджи)

### `app/Http/Requests/Admin/Badge/StoreRequest.php`
**Использование:** Создание бейджа администратором

**Поля:**
```php
[
    'name' => ['required', 'string', 'max:255', 'unique:badges,name'],
    'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Иконка бейджа
    'description' => ['required', 'string', 'min:10', 'max:1000'], // Описание и условия получения
    'required_points' => ['required', 'integer', 'min:0', 'max:10000'], // Требуемое количество очков
]
```

**Примечания:**
- Иконка опциональна, но желательна для визуализации
- Описание должно объяснять условия получения бейджа

---

### `app/Http/Requests/Admin/Badge/UpdateRequest.php`
**Использование:** Обновление бейджа администратором

**Поля:**
```php
[
    'name' => ['required', 'string', 'max:255', 'unique:badges,name,' . $this->route('badge')->id],
    'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
    'description' => ['required', 'string', 'min:10', 'max:1000'],
    'required_points' => ['required', 'integer', 'min:0', 'max:10000'],
]
```

---

## 🎮 Simulators (Симуляторы)

### `app/Http/Requests/Admin/Simulator/StoreRequest.php`
**Использование:** Создание симулятора администратором

**Поля:**
```php
[
    'partner_id' => ['required', 'exists:partners,id'],
    'title' => ['required', 'string', 'max:255'],
    'slug' => ['required', 'string', 'max:255', 'unique:simulators,slug', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'], // URL-friendly slug
    'description' => ['required', 'string', 'min:50', 'max:5000'],
    'preview_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // Превью изображение
    'is_active' => ['nullable', 'boolean'],
]
```

**Примечания:**
- Slug должен быть уникальным и URL-friendly

---

### `app/Http/Requests/Admin/Simulator/UpdateRequest.php`
**Использование:** Обновление симулятора администратором

**Поля:**
```php
[
    'partner_id' => ['sometimes', 'required', 'exists:partners,id'],
    'title' => ['required', 'string', 'max:255'],
    'slug' => ['required', 'string', 'max:255', 'unique:simulators,slug,' . $this->route('simulator')->id, 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
    'description' => ['required', 'string', 'min:50', 'max:5000'],
    'preview_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
    'is_active' => ['nullable', 'boolean'],
]
```

---

### `app/Http/Requests/Student/Simulator/StartRequest.php`
**Использование:** Запуск симулятора студентом

**Поля:**
```php
[
    // simulator_id берется из роута
    // Дополнительных полей нет, сессия создается автоматически
]
```

**Примечания:**
- Валидация в контроллере: проверка, что симулятор активен
- Проверка, что у студента нет активной сессии для этого симулятора (если нужно)

---

### `app/Http/Requests/Student/Simulator/CompleteRequest.php`
**Использование:** Завершение сессии симулятора студентом

**Поля:**
```php
[
    // session_id берется из роута
    'score' => ['required', 'integer', 'min:0', 'max:100'], // Балл от 0 до 100
    'time_spent' => ['required', 'integer', 'min:1'], // В секундах
    'answers' => ['nullable', 'array'], // Ответы на вопросы симулятора (если нужно)
]
```

**Примечания:**
- Максимальный балл: 100
- Начисление очков происходит автоматически в контроллере на основе score

---

## 📊 Analytics (Аналитика - опционально)

### `app/Http/Requests/Partner/Analytics/IndexRequest.php`
**Использование:** Фильтры для аналитики партнера

**Поля:**
```php
[
    'date_from' => ['nullable', 'date'],
    'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
    'case_id' => ['nullable', 'exists:cases,id'],
]
```

**Примечания:**
- Все поля опциональны
- Если указан case_id, то фильтрация только по этому кейсу
- Проверка в контроллере: case_id должен принадлежать партнеру

---

## 📝 Примечания к реализации:

1. **Неймспейсы:** Использовать структуру `Admin/User/StoreRequest`, `Partner/Case/StoreRequest` и т.д.
2. **Проверка прав доступа:** Должна быть в контроллере, а не в Form Request (например, партнер может редактировать только свои кейсы)
3. **Уникальность полей:** Должна учитывать текущую запись при обновлении (unique:table,column,{id})
4. **Файлы:** Использовать правила `image`, `mimes`, `max` (в килобайтах)
5. **Даты:** Использовать `date`, `after`, `before`, `after_or_equal` правила
6. **Массивы:** Использовать `array`, `distinct`, валидацию элементов через `.*`
7. **Статусы кейсов:** enum с значениями: `draft`, `active`, `completed`, `archived`
8. **Размер команды:** От 1 до 10 человек (лидер + до 9 участников)
9. **Pivot таблицы:** Навыки кейсов хранятся в `case_skills` (case_id, skill_id)

---

## 🔄 Миграции, которые нужно выполнить:

1. ✅ `create_case_skills_table` - pivot таблица для связи кейсов и навыков
2. ✅ `add_status_to_cases_table` - замена is_active на enum status

---

**Создано:** 2025  
**Обновлено:** 2025-11-02  
**Версия:** 2.0
