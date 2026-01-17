# 📊 Текущее состояние проекта

## Что уже есть в проекте

### 1. Инфраструктура симуляторов

- ✅ Модель `Simulator` (связь с партнерами, активность)
- ✅ Модель `SimulatorSession` (сессии студентов, состояние в JSON)
- ✅ Контроллеры для админа и студентов
- ✅ Сервис `SimulatorService` (CRUD операции)
- ✅ Интеграция с системой навыков (Skills) и достижений (Badges)
- ✅ Начисление очков через `ProgressLogService`

### 2. Система оценки и прогресса

- ✅ Поле `score` в `SimulatorSession`
- ✅ Поле `time_spent` для отслеживания времени
- ✅ JSON поле `state` для хранения состояния симулятора
- ✅ Автоматическое начисление очков навыкам при завершении
- ✅ Уведомления о завершении симулятора

### 3. UI компоненты

- ✅ Базовый компонент `Session.vue` (пока заглушка)
- ✅ Список симуляторов для студентов
- ✅ Таймер и прогресс-бар
- ✅ Интеграция с Inertia.js

---

## Структура данных

### Модель SimulatorSession

```php
// Поля модели
- id
- user_id (связь с User)
- simulator_id (связь с Simulator)
- state (JSON) ← Здесь храним состояние симулятора
- score (integer)
- time_spent (integer)
- is_completed (boolean)
- started_at (timestamp)
- completed_at (timestamp)
```

### Предлагаемая структура для `state`

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
      {"role": "client", "text": "Мне нужна кредитная карта", "timestamp": "..."},
      {"role": "user", "text": "Расскажите о вашем доходе", "timestamp": "..."}
    ],
    "current_step": "income_collection"
  },
  "calculations": {
    "credit_score": 0.7085,
    "credit_limit": 750000,
    "interest_rate": 15.0,
    "monthly_payment": 11895
  },
  "actions": [
    {"type": "data_collected", "field": "income", "value": 80000, "timestamp": "..."},
    {"type": "calculation_performed", "calculation": "credit_score", "timestamp": "..."}
  ],
  "errors": []
}
```

**Преимущества**:
- Гибкость (можно добавлять новые поля)
- Сохранение прогресса при перезагрузке
- История действий для анализа

---

## Генерация клиентов

### Подход 1: Шаблоны в конфиге

```php
// config/bank_simulator.php
'client_templates' => [
    'student' => [
        'age_range' => [18, 25],
        'income_range' => [20000, 50000],
        'credit_history' => 'none',
        'needs' => ['credit_card']
    ],
    'family' => [
        'age_range' => [30, 40],
        'income_range' => [60000, 150000],
        'credit_history' => 'good',
        'needs' => ['refinancing', 'auto_loan']
    ],
    // ...
]
```

### Подход 2: Фабрики (Laravel Factories)

```php
// database/factories/BankClientFactory.php
class BankClientFactory extends Factory {
    public function definition() {
        $type = $this->faker->randomElement(['student', 'family', 'entrepreneur', 'pensioner']);
        return $this->getTemplate($type);
    }
}
```

**Рекомендация**: Использовать конфиг для шаблонов + фабрики для генерации случайных значений в рамках шаблона.

---

## Система диалога

### ❌ НЕ делать: Полноценный NLP

**Почему**:
- Сложность интеграции (нужен внешний сервис)
- Задержки API (GPT может отвечать 2-5 секунд)
- Стоимость (GPT API платный)
- Непредсказуемость ответов
- Для MVP избыточно

### ✅ Делать: Branching Dialogue (дерево выбора)

**Структура**:
```php
// app/Data/BankSimulator/DialogueTree.php
class DialogueTree {
    public function getStage(string $stageId, array $context): DialogueStage {
        return match($stageId) {
            'greeting' => new DialogueStage([
                'client_message' => 'Здравствуйте! Чем могу помочь?',
                'user_options' => [
                    'credit_card' => 'Мне нужна кредитная карта',
                    'deposit' => 'Хочу открыть вклад',
                    'consultation' => 'Нужна консультация'
                ],
                'next_stage' => [
                    'credit_card' => 'credit_inquiry',
                    'deposit' => 'deposit_inquiry',
                    'consultation' => 'consultation'
                ]
            ]),
            // ...
        };
    }
}
```

**Преимущества**:
- Предсказуемость
- Легко редактировать
- Быстрая работа
- Можно добавить GPT позже как опцию

**Опционально**: Простой pattern matching для ключевых слов
```php
if (str_contains($userInput, 'кредит')) {
    return 'credit_inquiry';
}
```

---

## Интеграция с существующей системой

### Использовать существующую инфраструктуру

- `SimulatorSession.state` для хранения состояния
- `ProgressLogService` для начисления очков
- Существующие контроллеры и роуты

### Следовать архитектуре проекта

- Сервисы в `app/Services/BankSimulator/`
- Form Requests для валидации
- Vue компоненты в `resources/js/Pages/Client/Student/Simulators/`

### Делать расширяемым

- Конфиги для настройки формул
- Шаблоны клиентов легко редактировать
- Диалог структурирован (JSON/YAML)

### Интегрироваться с системой навыков

- Связывать симулятор с кейсами (если нужно)
- Начислять очки навыкам при завершении
- Добавить новые бейджи для симулятора

---

## Дополнительные миграции (опционально)

### Конфигурация симулятора

```php
Schema::create('bank_simulator_configs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('simulator_id')->constrained()->cascadeOnDelete();
    $table->json('scoring_weights')->nullable(); // Веса для скоринга
    $table->json('client_templates')->nullable(); // Шаблоны клиентов
    $table->json('dialogue_tree')->nullable(); // Структура диалога
    $table->timestamps();
});
```

**Или**: Хранить в JSON поле модели `Simulator` (проще для MVP)

---

**Следующий шаг**: Изучить [04_FORMULAS.md](04_FORMULAS.md) для формул скоринга и калькуляторов
