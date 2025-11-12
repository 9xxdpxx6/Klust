# Filter System Architecture

## Overview

Система фильтрации в Klust использует двухуровневую архитектуру для обеспечения гибкости и переиспользования кода.

## Architecture Layers

### 1. FilterHelper (Low-level utilities)

**Расположение:** `app/Helpers/FilterHelper.php`

**Назначение:** Низкоуровневые утилиты для валидации и нормализации типов данных фильтров.

**Когда использовать:**
- В сервисах для простой валидации параметров
- Для быстрой типизации и санитизации входных данных
- В любом месте, где нужна базовая проверка фильтров

**Основные методы:**
```php
FilterHelper::hasValue($value)           // Проверка наличия значения
FilterHelper::getStringFilter($value)    // Получить строковый фильтр
FilterHelper::getArrayFilter($value)     // Получить массив фильтров
FilterHelper::getIntegerFilter($value)   // Получить целочисленный фильтр
FilterHelper::getBooleanFilter($value)   // Получить булевый фильтр
FilterHelper::getDateFilter($value)      // Получить фильтр даты
FilterHelper::sanitizeSearch($search)    // Санитизация поискового запроса
FilterHelper::getPaginationParams($arr)  // Получить параметры пагинации
FilterHelper::getSortParams($arr)        // Получить параметры сортировки
```

**Пример использования в сервисе:**
```php
public function getFilteredData(array $filters): LengthAwarePaginator
{
    $query = Model::query();

    // Простая фильтрация
    $search = FilterHelper::getStringFilter($filters['search'] ?? null);
    if ($search) {
        $sanitized = FilterHelper::sanitizeSearch($search);
        $query->where('name', 'like', "%{$sanitized}%");
    }

    $partnerId = FilterHelper::getIntegerFilter($filters['partner_id'] ?? null);
    if ($partnerId) {
        $query->where('partner_id', $partnerId);
    }

    $pagination = FilterHelper::getPaginationParams($filters, 20);
    return $query->paginate($pagination['per_page']);
}
```

### 2. BaseFilter + Subclasses (High-level strategy)

**Расположение:** `app/Filters/`

**Назначение:** Высокоуровневая стратегия фильтрации для сложных запросов с множеством параметров.

**Когда использовать:**
- В контроллерах для комплексной фильтрации
- Когда нужна сложная логика фильтрации (smart search, multiple conditions)
- Для переиспользования логики фильтрации между разными контроллерами

**Структура:**
```
app/Filters/
├── Contracts/
│   └── FilterInterface.php      # Интерфейс фильтра
├── BaseFilter.php               # Базовый класс с общими методами
├── CaseFilter.php               # Фильтр для кейсов
├── CaseApplicationFilter.php    # Фильтр для заявок
└── TeamFilter.php               # Фильтр для команд
```

**BaseFilter использует FilterHelper** для базовых операций:
```php
abstract class BaseFilter
{
    // Использует FilterHelper::hasValue()
    protected function hasFilter(string $key): bool

    // Использует FilterHelper::getStringFilter() + sanitizeSearch()
    protected function applySearch(Builder $query, string $field)

    // Использует FilterHelper::getSortParams()
    protected function applySorting(Builder $query, ...)

    // Использует FilterHelper::getDateFilter()
    protected function applyDateRange(Builder $query, string $field)

    // Использует FilterHelper::getPaginationParams()
    public function getPaginationParams(): array

    // Сложная логика (не делегируется в FilterHelper)
    protected function applySmartSearch(...)
    protected function applySmartSearchInRelation(...)
}
```

**Пример создания нового фильтра:**
```php
final class UserFilter extends BaseFilter
{
    private const ALLOWED_SORT_FIELDS = ['name', 'email', 'created_at'];

    public function apply(Builder $query): Builder
    {
        $this->applySearchFilter($query);
        $this->applyRoleFilter($query);
        $this->applyStatusFilter($query);
        $this->applySortingFilter($query);

        return $query;
    }

    private function applySearchFilter(Builder $query): void
    {
        if (!$this->hasFilter('search')) {
            return;
        }

        $searchTerm = (string) $this->getFilter('search');
        $this->applySmartSearch($query, ['name', 'email'], $searchTerm);
    }

    private function applyRoleFilter(Builder $query): void
    {
        if (!$this->hasFilter('role')) {
            return;
        }

        $query->role($this->getFilter('role'));
    }

    protected function getDefaultPerPage(): int
    {
        return 20;
    }
}
```

**Пример использования в контроллере:**
```php
public function index(Request $request): Response
{
    $filters = $request->only([
        'search',
        'role',
        'status',
        'sort_by',
        'sort_order',
        'per_page',
    ]);

    $userFilter = new UserFilter($filters);

    $query = User::query()->with('roles');
    $pagination = $userFilter->getPaginationParams();

    $users = $userFilter
        ->apply($query)
        ->paginate($pagination['per_page'])
        ->withQueryString();

    return Inertia::render('Admin/Users/Index', [
        'users' => $users,
        'filters' => $filters,
    ]);
}
```

## Decision Tree: When to Use What?

```
┌─ Нужна фильтрация?
│
├─ Простая фильтрация (1-3 параметра)?
│  └─ Используй FilterHelper в сервисе
│
└─ Сложная фильтрация (4+ параметров, smart search, сортировка)?
   │
   ├─ Уже есть Filter класс?
   │  └─ Используй существующий Filter класс
   │
   └─ Нет Filter класса?
      └─ Создай новый класс, наследуй BaseFilter
```

## Benefits of This Architecture

### Separation of Concerns
- **FilterHelper**: Низкоуровневая валидация и типизация
- **BaseFilter**: Высокоуровневая бизнес-логика фильтрации

### Code Reusability
- FilterHelper переиспользуется везде (сервисы, фильтры)
- BaseFilter переиспользуется через наследование

### Maintainability
- Единая точка изменения для валидации типов (FilterHelper)
- Легко добавлять новые фильтры (наследование от BaseFilter)

### Security
- Централизованная санитизация (FilterHelper::sanitizeSearch)
- Защита от SQL injection на уровне утилит

### Type Safety
- Явная типизация через FilterHelper методы
- Меньше ошибок типов в runtime

## Migration Guide

### Старый код (до рефакторинга):
```php
// В сервисе
if (isset($filters['status']) && !empty($filters['status'])) {
    $query->where('status', $filters['status']);
}
```

### Новый код (после рефакторинга):
```php
// В сервисе - простая фильтрация
$status = FilterHelper::getStringFilter($filters['status'] ?? null);
if ($status) {
    $query->where('status', $status);
}

// В контроллере - сложная фильтрация
$filter = new CaseFilter($filters);
$cases = $filter->apply($query)->paginate();
```

## Best Practices

1. **Всегда используй FilterHelper::sanitizeSearch()** для поисковых запросов
2. **Создавай Filter класс** если фильтрация используется в нескольких местах
3. **Используй FilterHelper в сервисах** для ad-hoc фильтрации
4. **Валидируй фильтры в контроллерах** через Form Request классы
5. **Документируй allowed fields** в Filter классах (например, ALLOWED_SORT_FIELDS)

## Testing

### Тестирование FilterHelper:
```php
public function test_sanitize_search_escapes_special_characters()
{
    $search = FilterHelper::sanitizeSearch('test%_value');
    $this->assertEquals('test\\%\\_value', $search);
}
```

### Тестирование Filter классов:
```php
public function test_case_filter_applies_search()
{
    $filter = new CaseFilter(['search' => 'test']);
    $query = CaseModel::query();
    $filter->apply($query);

    // Assert query contains WHERE clause with LIKE
}
```

## Future Improvements

- [ ] Добавить валидацию в контроллеры через Form Requests
- [ ] Создать Filter классы для всех основных моделей
- [ ] Добавить фильтр по датам во все Filter классы
- [ ] Оптимизировать smart search для больших таблиц
- [ ] Добавить кеширование результатов фильтрации
