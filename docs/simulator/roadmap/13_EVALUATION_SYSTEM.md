# Модуль 13: Система оценки

## Цель модуля

Реализовать систему оценки действий пользователя:
- Критерии оценки (корректность, качество обслуживания, соблюдение регламентов)
- Начисление баллов
- Сохранение результатов

---

## Что нужно сделать

### 1. EvaluationService

**Файл**: `app/Services/BankSimulator/EvaluationService.php`

**Методы**:
- `evaluateSession(SimulatorSession $session): int` - Оценка сессии
- `evaluateCorrectness(array $state): float` - Корректность решения
- `evaluateServiceQuality(array $state): float` - Качество обслуживания
- `evaluateCompliance(array $state): float` - Соблюдение регламентов

---

## Зависимости

**Требует**: Модуль 12 (State Sync)

---

## Следующий модуль

После завершения переходи к: **[14_POLISH_OPTIMIZATION.md](14_POLISH_OPTIMIZATION.md)**
