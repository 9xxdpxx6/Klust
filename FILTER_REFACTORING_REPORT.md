# Отчет по рефакторингу фильтров

## Дата создания: 2025-01-XX

## Цель
Вынести всю логику фильтрации из контроллеров и сервисов в отдельные Filter классы для соблюдения принципа единственной ответственности (SRP) и улучшения поддерживаемости кода.

---

## ✅ Уже исправлено

### 1. `app/Http/Controllers/Admin/UsersController.php`
- **Статус**: ✅ Исправлено
- **Проблема**: Логика фильтрации была встроена в контроллер (строки 38-72)
- **Решение**: Создан `app/Filters/UserFilter.php`, логика перенесена в фильтр
- **Дата исправления**: Сегодня

---

## ❌ Требует исправления

### 1. `app/Services/CaseService.php` → `getFilteredCases()`
- **Файл**: `app/Services/CaseService.php`
- **Метод**: `getFilteredCases(array $filters)` (строки 123-156)
- **Проблема**: 
  - Фильтрация выполняется напрямую в сервисе через `FilterHelper`
  - Используются прямые `where()` запросы вместо Filter класса
  - Уже существует `app/Filters/CaseFilter.php`, но не используется
- **Что нужно сделать**:
  ```php
  // БЫЛО:
  public function getFilteredCases(array $filters): LengthAwarePaginator
  {
      $query = CaseModel::query();
      // ... прямая фильтрация через FilterHelper
  }
  
  // ДОЛЖНО БЫТЬ:
  public function getFilteredCases(array $filters): LengthAwarePaginator
  {
      $caseFilter = new CaseFilter($filters);
      $query = CaseModel::query();
      $query = $caseFilter->apply($query);
      $pagination = $caseFilter->getPaginationParams();
      return $query->paginate($pagination['per_page']);
  }
  ```
- **Приоритет**: 🔴 Высокий
- **Связанные файлы**:
  - `app/Http/Controllers/Admin/CaseController.php` (строка 41) - использует этот метод

---

### 2. `app/Http/Controllers/Client/Partner/TeamController.php` → Неправильный namespace
- **Файл**: `app/Http/Controllers/Client/Partner/TeamController.php`
- **Строка**: 7
- **Проблема**: 
  ```php
  use App\Filters\Partner\TeamFilter; // ❌ Неправильный namespace
  ```
  - Файл `TeamFilter` находится в `app/Filters/TeamFilter.php` (namespace `App\Filters`)
  - Но импортируется как `App\Filters\Partner\TeamFilter`
- **Что нужно сделать**:
  ```php
  // ИЗМЕНИТЬ:
  use App\Filters\Partner\TeamFilter;
  
  // НА:
  use App\Filters\TeamFilter;
  ```
- **Приоритет**: 🔴 Высокий (критическая ошибка - код не работает)

---

### 3. `app/Http/Controllers/Search/SearchController.php` → Прямая фильтрация в контроллере
- **Файл**: `app/Http/Controllers/Search/SearchController.php`
- **Метод**: `index(Request $request)` (строки 19-83)
- **Проблема**: 
  - Поиск выполняется напрямую в контроллере через `where()` и `orWhere()`
  - Нет использования Filter классов
  - Поиск по трем сущностям: Cases, Users, Skills
- **Что нужно сделать**:
  - Создать отдельные Filter классы для поиска или универсальный SearchFilter
  - Либо оставить как есть, если это специфичный глобальный поиск (но лучше вынести)
- **Варианты решения**:
  1. Создать `app/Filters/SearchFilter.php` с методами для каждой сущности
  2. Использовать существующие фильтры: `CaseFilter`, `UserFilter`, `SkillFilter`
  3. Оставить в контроллере, но вынести в отдельный метод `SearchService`
- **Приоритет**: 🟡 Средний (можно оставить, но лучше рефакторить)

---

## ✅ Уже правильно реализовано

### 1. `app/Http/Controllers/Client/Partner/CasesController.php`
- ✅ Использует `CaseFilter` (строка 66)
- ✅ Использует `CaseApplicationFilter` (строка 307)
- ✅ Правильная архитектура

### 2. `app/Http/Controllers/Client/Student/CasesController.php`
- ✅ Использует `CaseFilter` (строка 51)
- ✅ Правильная архитектура

### 3. `app/Http/Controllers/Client/Partner/TeamController.php`
- ✅ Использует `TeamFilter` (строка 52)
- ⚠️ Но неправильный namespace (см. проблему #2)

### 4. `app/Http/Controllers/Admin/SkillController.php`
- ✅ Использует `SkillService::getFilteredSkills()`
- ✅ `SkillService` использует `SkillFilter` (строка 87)
- ✅ Правильная архитектура

---

## 📋 Существующие Filter классы

| Filter класс | Файл | Используется в | Статус |
|-------------|------|----------------|--------|
| `UserFilter` | `app/Filters/UserFilter.php` | `UsersController` | ✅ Исправлено |
| `CaseFilter` | `app/Filters/CaseFilter.php` | `Partner/CasesController`, `Student/CasesController` | ✅ Работает |
| `CaseApplicationFilter` | `app/Filters/CaseApplicationFilter.php` | `Partner/CasesController` | ✅ Работает |
| `TeamFilter` | `app/Filters/TeamFilter.php` | `Partner/TeamController` | ⚠️ Неправильный namespace |
| `SkillFilter` | `app/Filters/SkillFilter.php` | `SkillService` | ✅ Работает |

---

## 🎯 План действий

### Приоритет 1 (Критично - исправить немедленно)
1. ✅ ~~Исправить `UsersController`~~ - **ГОТОВО**
2. ❌ Исправить namespace `TeamFilter` в `TeamController.php`
3. ❌ Рефакторить `CaseService::getFilteredCases()` для использования `CaseFilter`

### Приоритет 2 (Важно - исправить в ближайшее время)
4. ❌ Рефакторить `SearchController` (вынести логику поиска в Filter или Service)

---

## 📝 Детальные инструкции по исправлению

### Исправление #1: CaseService::getFilteredCases()

**Файл**: `app/Services/CaseService.php`

**Текущий код** (строки 123-156):
```php
public function getFilteredCases(array $filters): LengthAwarePaginator
{
    $query = CaseModel::query();

    // Apply status filter
    $status = FilterHelper::getStringFilter($filters['status'] ?? null);
    if ($status) {
        $query->where('status', $status);
    }

    // Apply partner filter
    $partnerId = FilterHelper::getIntegerFilter($filters['partner_id'] ?? null);
    if ($partnerId) {
        $query->where('partner_id', $partnerId);
    }

    // Apply search filter
    $search = FilterHelper::getStringFilter($filters['search'] ?? null);
    if ($search) {
        $sanitizedSearch = FilterHelper::sanitizeSearch($search);
        $query->where(function ($q) use ($sanitizedSearch) {
            $q->where('title', 'like', "%{$sanitizedSearch}%")
                ->orWhere('description', 'like', "%{$sanitizedSearch}%");
        });
    }

    // Eager load relationships
    $query->with(['partner.user.partnerProfile', 'skills']);

    // Get pagination parameters
    $pagination = FilterHelper::getPaginationParams($filters, 25);

    return $query->latest()->paginate($pagination['per_page']);
}
```

**Новый код**:
```php
use App\Filters\CaseFilter;

public function getFilteredCases(array $filters): LengthAwarePaginator
{
    $caseFilter = new CaseFilter($filters);
    
    $query = CaseModel::query()
        ->with(['partner.user.partnerProfile', 'skills']);
    
    $query = $caseFilter->apply($query);
    
    $pagination = $caseFilter->getPaginationParams();
    
    return $query->latest()->paginate($pagination['per_page']);
}
```

**Проверить**: Убедиться, что `CaseFilter` поддерживает все необходимые фильтры (status, partner_id, search).

---

### Исправление #2: TeamController namespace

**Файл**: `app/Http/Controllers/Client/Partner/TeamController.php`

**Изменить строку 7**:
```php
// БЫЛО:
use App\Filters\Partner\TeamFilter;

// ДОЛЖНО БЫТЬ:
use App\Filters\TeamFilter;
```

**Изменить строку 52**:
```php
// БЫЛО:
$teamFilter = new TeamFilter($filters);

// ОСТАЕТСЯ ТАК ЖЕ (только namespace изменился)
$teamFilter = new TeamFilter($filters);
```

---

### Исправление #3: SearchController (опционально)

**Файл**: `app/Http/Controllers/Search/SearchController.php`

**Вариант 1**: Создать `SearchService`
```php
// app/Services/SearchService.php
class SearchService
{
    public function searchCases(string $query): Collection { ... }
    public function searchUsers(string $query): Collection { ... }
    public function searchSkills(string $query): Collection { ... }
}
```

**Вариант 2**: Использовать существующие фильтры
```php
$caseFilter = new CaseFilter(['search' => $query]);
$userFilter = new UserFilter(['search' => $query]);
$skillFilter = new SkillFilter(['search' => $query]);
```

**Вариант 3**: Оставить как есть (если это специфичный глобальный поиск)

---

## ✅ Критерии готовности

- [x] Все Filter классы используют `BaseFilter`
- [x] Контроллеры не содержат логику фильтрации
- [ ] Сервисы используют Filter классы вместо прямой фильтрации
- [ ] Все namespace правильные
- [ ] Все импорты корректны

---

## 📚 Дополнительная информация

### Структура Filter классов
Все Filter классы должны:
1. Наследоваться от `BaseFilter`
2. Реализовывать метод `apply(Builder $query): Builder`
3. Использовать методы `hasFilter()`, `getFilter()` из `BaseFilter`
4. Использовать `getPaginationParams()` для пагинации

### Пример правильной реализации
```php
final class ExampleFilter extends BaseFilter
{
    public function apply(Builder $query): Builder
    {
        $this->applySearchFilter($query);
        $this->applyStatusFilter($query);
        $this->applySortingFilter($query);
        return $query;
    }
    
    protected function getDefaultPerPage(): int
    {
        return 25;
    }
}
```

---

## 🔍 Проверка после исправлений

После выполнения всех исправлений проверить:
1. Все тесты проходят
2. Фильтрация работает корректно во всех местах
3. Нет дублирования логики фильтрации
4. Все namespace правильные
5. Код соответствует PSR-12 стандартам

---

**Автор отчета**: AI Assistant  
**Последнее обновление**: 2025-01-XX

