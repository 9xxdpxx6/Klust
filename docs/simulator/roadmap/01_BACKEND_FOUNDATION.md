# Модуль 01: Backend Foundation

## Цель модуля

Создать backend основу для банковского симулятора:
- Сервисы для формул скоринга и калькуляторов
- Конфигурация симулятора
- Базовые методы для работы с симулятором

---

## Что нужно сделать

### 1. Сервисы для расчетов

#### 1.1. ScoringService

**Файл**: `app/Services/BankSimulator/ScoringService.php`

**Методы**:
- `calculateCreditScore(array $clientData, array $creditParams): float` - Расчет скоринга
- `interpretScore(float $score): array` - Интерпретация балла
- `getWeights(): array` - Получить веса (из конфига)

**Пример использования**:
```php
$service = new ScoringService();
$score = $service->calculateCreditScore([
    'income' => 80000,
    'expenses' => 60000,
    'age' => 25,
    'credit_history' => 'good',
    'has_deposit' => false
], []);

$result = $service->interpretScore($score);
// ['decision' => 'approve_with_conditions', 'interest_rate' => 15.0, ...]
```

#### 1.2. CreditCalculatorService

**Файл**: `app/Services/BankSimulator/CreditCalculatorService.php`

**Методы**:
- `calculateAnnuityPayment(float $amount, int $months, float $annualRate): float` - Аннуитетный платеж
- `calculateTotalPayment(float $monthlyPayment, int $months): float` - Общая сумма платежа
- `calculateOverpayment(float $totalPayment, float $amount): float` - Переплата

**Пример использования**:
```php
$service = new CreditCalculatorService();
$payment = $service->calculateAnnuityPayment(500000, 60, 15.0);
// ≈ 11895 руб.
```

#### 1.3. DepositCalculatorService

**Файл**: `app/Services/BankSimulator/DepositCalculatorService.php`

**Методы**:
- `calculateDeposit(float $initialAmount, float $annualRate, int $years, int $capitalizationPeriods = 12): float` - Расчет вклада с капитализацией
- `calculateSimpleDeposit(float $initialAmount, float $annualRate, int $years): float` - Простой вклад (без капитализации)

**Пример использования**:
```php
$service = new DepositCalculatorService();
$result = $service->calculateDeposit(100000, 8.0, 3, 12);
// ≈ 127024 руб.
```

---

### 2. Конфигурация симулятора

#### 2.1. Файл конфигурации

**Файл**: `config/bank_simulator.php`

**Структура**:
```php
return [
    'scoring' => [
        'weights' => [
            'income' => 0.3,
            'expenses' => 0.25,
            'age' => 0.2,
            'credit_history' => 0.25,
        ],
        'thresholds' => [
            'auto_approve' => 0.8,
            'approve_with_conditions' => 0.5,
            'manual_review' => 0.3,
            'auto_reject' => 0.0,
        ],
    ],
    'client_templates' => [
        'student' => [...],
        'family' => [...],
        // ...
    ],
];
```

#### 2.2. Загрузка конфига в сервисы

Использовать `config('bank_simulator.scoring.weights')` в сервисах.

---

### 3. Генерация клиентов (базовая версия)

#### 3.1. ClientGeneratorService (заглушка)

**Файл**: `app/Services/BankSimulator/ClientGeneratorService.php`

**Методы**:
- `generateClient(string $type = 'random'): array` - Генерация клиента (пока возвращает статические данные)
- `getAvailableTypes(): array` - Список доступных типов

**Временная реализация** (полная реализация в модуле 08):
```php
public function generateClient(string $type = 'random'): array
{
    // Заглушка: возвращает статические данные
    return [
        'type' => 'student',
        'name' => 'Иванов А.П.',
        'age' => 25,
        'income' => 80000,
        'expenses' => 60000,
        'credit_history' => 'good',
        'has_deposit' => false,
    ];
}
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `app/Services/BankSimulator/ScoringService.php`
- [ ] `app/Services/BankSimulator/CreditCalculatorService.php`
- [ ] `app/Services/BankSimulator/DepositCalculatorService.php`
- [ ] `app/Services/BankSimulator/ClientGeneratorService.php` (заглушка)
- [ ] `config/bank_simulator.php`

### Изменить:
- Ничего

---

## Критерии готовности

- [ ] Все сервисы созданы с методами-заглушками
- [ ] Формулы скоринга реализованы полностью
- [ ] Калькуляторы работают корректно
- [ ] Конфиг создан и загружается
- [ ] Написаны unit тесты (опционально)

---

## Тестирование

### Проверить работу:

```php
// ScoringService
$scoringService = new ScoringService();
$score = $scoringService->calculateCreditScore([...]);
assert(is_float($score) && $score >= 0 && $score <= 1);

$result = $scoringService->interpretScore(0.7085);
assert(isset($result['decision']));
assert(isset($result['interest_rate']));

// CreditCalculatorService
$calcService = new CreditCalculatorService();
$payment = $calcService->calculateAnnuityPayment(500000, 60, 15.0);
assert($payment > 0 && $payment < 20000);

// DepositCalculatorService
$depositService = new DepositCalculatorService();
$result = $depositService->calculateDeposit(100000, 8.0, 3);
assert($result > 100000);
```

---

## Зависимости

**Нет зависимостей** - можно реализовывать первым.

---

## Следующий модуль

После завершения переходи к: **[02_DATA_STRUCTURE.md](02_DATA_STRUCTURE.md)**
