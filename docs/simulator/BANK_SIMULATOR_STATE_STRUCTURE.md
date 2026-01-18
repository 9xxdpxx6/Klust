# Структура данных банковского симулятора

## Обзор

Документ описывает структуру JSON данных, хранящихся в поле `state` модели `SimulatorSession`. Эта структура используется для отслеживания состояния симулятора во время прохождения студентом.

---

## Полная структура JSON

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

---

## Описание полей

### `current_stage` (string, required)

Текущий этап симулятора. Определяет, на какой стадии находится процесс консультирования.

**Возможные значения**:
- `greeting` - Приветствие и начало консультации
- `collecting_data` - Сбор данных о клиенте
- `calculating` - Выполнение расчетов (скоринг, калькуляторы)
- `presenting` - Представление результатов клиенту
- `completed` - Сессия завершена

**Пример**:
```json
"current_stage": "collecting_data"
```

---

### `client` (object, required)

Данные клиента, с которым работает студент. Генерируются в модуле 08 (Client Generation).

**Поля**:
- `id` (string, nullable) - Уникальный идентификатор клиента
- `type` (string, nullable) - Тип клиента (student, family, business, etc.)
- `name` (string, nullable) - Имя клиента
- `age` (integer, required) - Возраст клиента (18-100)
- `income` (number, required) - Доход клиента в рублях (≥ 0)
- `expenses` (number, required) - Расходы клиента в рублях (≥ 0, ≤ income)
- `credit_history` (string, required) - Кредитная история
- `has_deposit` (boolean, required) - Наличие вклада в банке

**`credit_history` возможные значения**:
- `excellent` - Отличная кредитная история
- `good` - Хорошая кредитная история
- `fair` - Средняя кредитная история
- `poor` - Плохая кредитная история
- `none` - Нет кредитной истории

**Пример**:
```json
"client": {
  "id": "client_123",
  "type": "student",
  "name": "Иванов А.П.",
  "age": 25,
  "income": 80000,
  "expenses": 60000,
  "credit_history": "good",
  "has_deposit": false
}
```

---

### `dialogue` (object, required)

История диалога между студентом и клиентом, а также текущий шаг в диалоге.

**Поля**:
- `messages` (array, nullable) - Массив сообщений диалога
- `current_step` (string, nullable) - Текущий шаг в диалоге
- `selected_options` (array, nullable) - Выбранные опции в диалоге

**Структура элемента `messages`**:
- `role` (string, required) - Роль отправителя: `client` или `user`
- `text` (string, required) - Текст сообщения
- `timestamp` (string, required) - ISO 8601 дата и время сообщения

**Пример**:
```json
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
}
```

---

### `calculations` (object, required)

Результаты расчетов, выполненных во время сессии (скоринг, кредитный калькулятор, калькулятор вкладов).

**Поля**:
- `credit_score` (number, nullable) - Балл скоринга (0-1)
- `credit_limit` (number, nullable) - Предлагаемый кредитный лимит в рублях (≥ 0)
- `interest_rate` (number, nullable) - Процентная ставка в процентах (≥ 0)
- `monthly_payment` (number, nullable) - Ежемесячный платеж в рублях (≥ 0)
- `deposit_result` (number, nullable) - Результат расчета вклада в рублях (≥ 0)

**Пример**:
```json
"calculations": {
  "credit_score": 0.7085,
  "credit_limit": 750000,
  "interest_rate": 15.0,
  "monthly_payment": 11895,
  "deposit_result": null
}
```

---

### `actions` (array, nullable)

История действий пользователя (студента) во время сессии. Используется для оценки и анализа работы студента.

**Структура элемента `actions`**:
- `type` (string, required) - Тип действия (например: `data_collected`, `calculation_performed`, `product_offered`)
- `field` (string, nullable) - Поле, с которым связано действие (для `data_collected`)
- `value` (mixed, nullable) - Значение, связанное с действием
- `calculation` (string, nullable) - Тип расчета (для `calculation_performed`)
- `timestamp` (string, required) - ISO 8601 дата и время действия

**Пример**:
```json
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
]
```

---

### `errors` (array, nullable)

Массив ошибок валидации или других ошибок, возникших во время сессии.

**Пример**:
```json
"errors": [
  "Недостаточно данных для расчета скоринга",
  "Расходы превышают доход"
]
```

---

## Примеры использования

### Начальное состояние (при старте сессии)

```json
{
  "current_stage": "greeting",
  "client": {
    "age": 25,
    "income": 0,
    "expenses": 0,
    "credit_history": "none",
    "has_deposit": false
  },
  "dialogue": {
    "messages": [],
    "current_step": null,
    "selected_options": []
  },
  "calculations": {
    "credit_score": null,
    "credit_limit": null,
    "interest_rate": null,
    "monthly_payment": null,
    "deposit_result": null
  },
  "actions": [],
  "errors": []
}
```

### Состояние после сбора данных

```json
{
  "current_stage": "calculating",
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
      },
      {
        "role": "client",
        "text": "Мой доход 80000 рублей в месяц",
        "timestamp": "2025-01-01T12:00:10Z"
      }
    ],
    "current_step": "calculating",
    "selected_options": []
  },
  "calculations": {
    "credit_score": null,
    "credit_limit": null,
    "interest_rate": null,
    "monthly_payment": null,
    "deposit_result": null
  },
  "actions": [
    {
      "type": "data_collected",
      "field": "income",
      "value": 80000,
      "timestamp": "2025-01-01T12:00:10Z"
    }
  ],
  "errors": []
}
```

### Состояние после расчетов

```json
{
  "current_stage": "presenting",
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
      }
    ],
    "current_step": "presenting_results",
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
      "type": "calculation_performed",
      "calculation": "credit_score",
      "timestamp": "2025-01-01T12:00:10Z"
    }
  ],
  "errors": []
}
```

---

## Невалидные примеры

### Невалидный current_stage

```json
{
  "current_stage": "invalid_stage",  // ❌ Неверное значение
  ...
}
```

### Невалидные данные клиента

```json
{
  "client": {
    "age": 15,  // ❌ Возраст меньше 18
    "income": -1000,  // ❌ Отрицательный доход
    "expenses": 100000,
    "income": 50000,  // ❌ Расходы превышают доход
    "credit_history": "invalid"  // ❌ Неверное значение
  },
  ...
}
```

### Невалидная структура dialogue

```json
{
  "dialogue": {
    "messages": [
      {
        "role": "invalid_role",  // ❌ Неверная роль
        "text": "Hello",
        "timestamp": "invalid_date"  // ❌ Неверный формат даты
      }
    ]
  },
  ...
}
```

---

## Связь с другими модулями

- **Модуль 01 (Backend Foundation)**: Сервисы `ScoringService`, `CreditCalculatorService`, `DepositCalculatorService` заполняют поля в `calculations`
- **Модуль 08 (Client Generation)**: Сервис `ClientGeneratorService` генерирует данные для поля `client`
- **Модуль 07 (Dialogue System)**: Управляет полем `dialogue` и обновляет `messages`, `current_step`, `selected_options`
- **Модуль 12 (State Sync)**: Отвечает за сохранение и загрузку этой структуры в/из `SimulatorSession.state`

---

## Примечания

1. Все числовые значения (income, expenses, credit_limit, etc.) хранятся в рублях
2. Процентные ставки хранятся как числа (например, 15.0 для 15%)
3. Даты и время хранятся в формате ISO 8601 (например, "2025-01-01T12:00:00Z")
4. Поля могут быть `null` до тех пор, пока данные не собраны или расчеты не выполнены
5. Массив `errors` используется для отслеживания ошибок валидации и других проблем

---

**Следующий шаг**: Использовать эту структуру при реализации валидации через Form Request (модуль 02) и синхронизации состояния (модуль 12).
