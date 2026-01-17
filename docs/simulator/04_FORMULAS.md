# 🧮 Формулы и расчеты

## Формула скоринга

### Реализация в PHP

```php
// app/Services/BankSimulator/ScoringService.php
class ScoringService {
    private array $weights = [
        'income' => 0.3,
        'expenses' => 0.25,
        'age' => 0.2,
        'credit_history' => 0.25
    ];
    
    public function calculateCreditScore(array $clientData, array $creditParams): float {
        // Коэффициент дохода
        $incomeCoeff = $clientData['income'] / 50000;
        
        // Коэффициент расходов
        $expenseCoeff = $clientData['expenses'] / $clientData['income'];
        
        // Коэффициент возраста (оптимальный возраст 30)
        $ageCoeff = abs(30 - $clientData['age']) / 30;
        
        // Коэффициент кредитной истории
        $creditHistoryCoeff = match($clientData['credit_history']) {
            'excellent' => 1.2,
            'good' => 1.0,
            'fair' => 0.7,
            'poor' => 0.3,
            'none' => 0.5,
            default => 0.5
        };
        
        // Бонусы/штрафы
        $bonuses = 0;
        if ($clientData['has_deposit']) {
            $bonuses += 0.2;
        }
        
        // Расчет общего балла
        $score = 
            ($incomeCoeff * $this->weights['income']) -
            ($expenseCoeff * $this->weights['expenses']) -
            ($ageCoeff * $this->weights['age']) +
            ($creditHistoryCoeff * $this->weights['credit_history']) +
            $bonuses;
        
        return max(0, min(1, $score)); // Ограничение 0-1
    }
    
    public function interpretScore(float $score): array {
        return match(true) {
            $score >= 0.8 => [
                'decision' => 'auto_approve',
                'interest_rate' => 12.0,
                'limit_multiplier' => 1.5,
                'requires_insurance' => false
            ],
            $score >= 0.5 => [
                'decision' => 'approve_with_conditions',
                'interest_rate' => 15.0,
                'limit_multiplier' => 1.2,
                'requires_insurance' => true
            ],
            $score >= 0.3 => [
                'decision' => 'manual_review',
                'interest_rate' => null,
                'limit_multiplier' => null,
                'requires_insurance' => null
            ],
            default => [
                'decision' => 'auto_reject',
                'interest_rate' => null,
                'limit_multiplier' => null,
                'requires_insurance' => null
            ]
        };
    }
}
```

### Пример расчета

**Данные клиента**:
- Доход: 80 000 руб.
- Расходы: 60 000 руб.
- Возраст: 25 лет
- Кредитная история: Хорошая
- Есть вклад в банке

**Расчет**:
- Коэф_дохода = 80000 / 50000 = 1.6
- Коэф_расходов = 60000 / 80000 = 0.75
- Коэф_возраста = |30 - 25| / 30 = 0.17
- Коэф_КИ = 1.0
- Бонус за вклад = 0.2

**Общий балл**:
```
(1.6 * 0.3) - (0.75 * 0.25) - (0.17 * 0.2) + (1.0 * 0.25) + 0.2
= 0.48 - 0.1875 - 0.034 + 0.25 + 0.2
= 0.7085
```

**Интерпретация**: 0.7085 → `approve_with_conditions` (одобрение с условиями)

### Настройка через админку (опционально)

- Хранить веса в БД таблице `simulator_configs`
- Позволить админу редактировать веса

---

## Кредитный калькулятор (аннуитетный)

### Формула

**Ежемесячный платеж**:
```
Платёж = Сумма * (Ставка_мес * (1 + Ставка_мес)^Срок) / ((1 + Ставка_мес)^Срок - 1)
```

Где `Ставка_мес = Годовая_ставка / 12 / 100`

### Реализация

```php
// app/Services/BankSimulator/CreditCalculatorService.php
class CreditCalculatorService {
    public function calculateAnnuityPayment(
        float $amount,
        int $months,
        float $annualRate
    ): float {
        $monthlyRate = $annualRate / 12 / 100;
        
        if ($monthlyRate == 0) {
            return $amount / $months;
        }
        
        $numerator = $monthlyRate * pow(1 + $monthlyRate, $months);
        $denominator = pow(1 + $monthlyRate, $months) - 1;
        
        return $amount * ($numerator / $denominator);
    }
    
    public function calculateTotalPayment(float $monthlyPayment, int $months): float {
        return $monthlyPayment * $months;
    }
    
    public function calculateOverpayment(float $totalPayment, float $amount): float {
        return $totalPayment - $amount;
    }
}
```

### Пример

**Параметры**:
- Сумма: 500 000 руб.
- Срок: 5 лет (60 мес.)
- Ставка: 15% годовых

**Расчет**:
- Ставка_мес = 15 / 12 / 100 = 0.0125
- Платёж = 500000 * (0.0125 * (1+0.0125)^60) / ((1+0.0125)^60 - 1) ≈ 11 895 руб.

---

## Калькулятор вкладов (с капитализацией)

### Формула

**Итоговая сумма**:
```
Итог = Начальная_сумма * (1 + Ставка_годовая_в_десятичных / N)^(N * лет)
```

Где `N` — количество периодов капитализации в год (ежемесячно = 12, ежеквартально = 4)

### Реализация

```php
public function calculateDeposit(
    float $initialAmount,
    float $annualRate,
    int $years,
    int $capitalizationPeriods = 12 // ежемесячно
): float {
    $ratePerPeriod = $annualRate / $capitalizationPeriods / 100;
    $totalPeriods = $capitalizationPeriods * $years;
    
    return $initialAmount * pow(1 + $ratePerPeriod, $totalPeriods);
}
```

### Пример

**Параметры**:
- Сумма: 100 000 руб.
- Ставка: 8% годовых
- Срок: 3 года
- Капитализация: ежемесячная

**Расчет**:
- Итог = 100000 * (1 + 0.08/12)^(12*3) ≈ 127 024 руб.

---

## Система оценки действий пользователя

### Критерии оценки

1. **Корректность решения** (40%)
   - Правильный выбор продукта для клиента
   - Корректные расчеты
   - Правильная интерпретация скоринга

2. **Качество обслуживания** (30%)
   - Скорость работы
   - Полнота сбора данных
   - Объяснение условий клиенту

3. **Соблюдение регламентов** (30%)
   - Проверка KYC данных
   - Запрос необходимых документов
   - Соблюдение процедур

### Реализация

```php
class EvaluationService {
    public function evaluateSession(SimulatorSession $session): int {
        $state = $session->state;
        $score = 0;
        
        // Корректность решения
        $correctness = $this->evaluateCorrectness($state);
        $score += $correctness * 0.4;
        
        // Качество обслуживания
        $serviceQuality = $this->evaluateServiceQuality($state);
        $score += $serviceQuality * 0.3;
        
        // Соблюдение регламентов
        $compliance = $this->evaluateCompliance($state);
        $score += $compliance * 0.3;
        
        return (int) ($score * 100); // 0-100
    }
}
```

---

**Следующий шаг**: Изучить [05_IMPLEMENTATION_GUIDE.md](05_IMPLEMENTATION_GUIDE.md) для руководства по реализации
