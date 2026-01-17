# Модуль 02: Структура данных

## Цель модуля

Определить и реализовать структуру данных для `SimulatorSession.state`:
- JSON структура для хранения состояния симулятора
- Типы данных и валидация
- DTO классы для работы с данными (опционально)

---

## Что нужно сделать

### 1. Структура JSON для `state`

#### 1.1. Документация структуры

**Файл**: `docs/simulator/BANK_SIMULATOR_STATE_STRUCTURE.md`

**Структура**:
```json
{
  "current_stage": "collecting_data",
  "client": {
    "id": "client_123",
    "type": "student",
    "name": "Иванов А.П.",
    "age": 25,
    "income": 80000,
    "expenses": 60000,
    "credit_history": "good",
    "has_deposit": false
  },
  "dialogue": {
    "messages": [
      {
        "role": "client",
        "text": "Мне нужна кредитная карта",
        "timestamp": "2025-01-01T12:00:00Z"
      },
      {
        "role": "user",
        "text": "Расскажите о вашем доходе",
        "timestamp": "2025-01-01T12:00:05Z"
      }
    ],
    "current_step": "income_collection",
    "selected_options": []
  },
  "calculations": {
    "credit_score": 0.7085,
    "credit_limit": 750000,
    "interest_rate": 15.0,
    "monthly_payment": 11895,
    "deposit_result": null
  },
  "actions": [
    {
      "type": "data_collected",
      "field": "income",
      "value": 80000,
      "timestamp": "2025-01-01T12:00:05Z"
    },
    {
      "type": "calculation_performed",
      "calculation": "credit_score",
      "timestamp": "2025-01-01T12:00:10Z"
    }
  ],
  "errors": []
}
```

#### 1.2. Описание полей

- `current_stage`: Текущий этап симулятора (`greeting`, `collecting_data`, `calculating`, `presenting`, `completed`)
- `client`: Данные клиента (генерируются в модуле 08)
- `dialogue`: История диалога и текущий шаг
- `calculations`: Результаты расчетов (скоринг, калькуляторы)
- `actions`: История действий пользователя
- `errors`: Ошибки валидации

---

### 2. DTO классы (опционально)

#### 2.1. SimulatorStateDTO

**Файл**: `app/Data/BankSimulator/SimulatorStateDTO.php`

**Назначение**: Валидация и работа с state структурой.

```php
class SimulatorStateDTO
{
    public function __construct(
        public string $currentStage,
        public ClientDTO $client,
        public DialogueDTO $dialogue,
        public CalculationsDTO $calculations,
        public array $actions = [],
        public array $errors = []
    ) {}
    
    public static function fromArray(array $data): self
    {
        // Валидация и создание из массива
    }
    
    public function toArray(): array
    {
        // Преобразование в массив для сохранения
    }
}
```

**Примечание**: Можно обойтись без DTO, использовать просто массивы.

---

### 3. Валидация state

#### 3.1. Form Request для валидации

**Файл**: `app/Http/Requests/Student/Simulator/UpdateStateRequest.php`

**Правила валидации**:
```php
public function rules(): array
{
    return [
        'state.current_stage' => 'required|string|in:greeting,collecting_data,calculating,presenting,completed',
        'state.client' => 'required|array',
        'state.client.income' => 'required|numeric|min:0',
        'state.client.expenses' => 'required|numeric|min:0',
        // ...
    ];
}
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `docs/simulator/BANK_SIMULATOR_STATE_STRUCTURE.md` (документация)
- [ ] `app/Data/BankSimulator/SimulatorStateDTO.php` (опционально)
- [ ] `app/Http/Requests/Student/Simulator/UpdateStateRequest.php`

### Изменить:
- Ничего

---

## Критерии готовности

- [ ] Документация структуры создана
- [ ] Примеры JSON структуры есть
- [ ] Валидация state работает (через Form Request)
- [ ] DTO классы созданы (если используются)

---

## Тестирование

### Проверить валидацию:

```php
// Валидный state
$validState = [
    'current_stage' => 'collecting_data',
    'client' => [
        'income' => 80000,
        'expenses' => 60000,
        // ...
    ],
    // ...
];

// Невалидный state (должен вернуть ошибки)
$invalidState = [
    'current_stage' => 'invalid_stage',
    // ...
];
```

---

## Зависимости

**Нет зависимостей** - можно реализовывать параллельно с модулем 01.

---

## Следующий модуль

После завершения переходи к: **[03_FRONTEND_SETUP.md](03_FRONTEND_SETUP.md)**
