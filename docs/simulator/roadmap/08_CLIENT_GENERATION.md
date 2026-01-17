# Модуль 08: Генерация клиентов

## Цель модуля

Реализовать генерацию клиентов для симулятора:
- Шаблоны клиентов (студент, семья, предприниматель, пенсионер)
- Случайная генерация параметров
- Полная реализация ClientGeneratorService

---

## Что нужно сделать

### 1. Полная реализация ClientGeneratorService

**Файл**: `app/Services/BankSimulator/ClientGeneratorService.php`

**Методы**:
- `generateClient(string $type = 'random'): array` - Генерация клиента
- `getAvailableTypes(): array` - Список доступных типов
- `getTemplate(string $type): array` - Получить шаблон типа

---

## Зависимости

**Требует**: Модуль 01 (Backend Foundation)

---

## Следующий модуль

После завершения переходи к: **[09_SCORING_INTEGRATION.md](09_SCORING_INTEGRATION.md)**
