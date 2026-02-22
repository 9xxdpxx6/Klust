# Аудит: расхождения документации и кода

> Дата аудита: 2026-02-19
> Последнее обновление: 2026-02-19

---

## 📋 Формальности (обновить документацию)

Код корректен или лучше документации. Нужно просто привести доки в соответствие с реальностью.

| # | Что | Действие |
|---|-----|----------|
| 1 | Путь сервисов: доки пишут `app/Services/BankSimulator/`, реально `app/Services/Simulators/BankSimulator/` | Обновить пути в `02_TASK.md`, `README.md`, `05_IMPLEMENTATION_GUIDE.md` |
| 2 | Доки: `BankSimulatorPrototype.vue`, код: `BankSimulatorSession.vue` | Обновить имя файла в `02_TASK.md`, `README.md` |
| 3 | Доки: фиксированные ставки (12%, 15%), код: динамическая формула `24 - 14 * score` | Обновить `04_FORMULAS.md` — описать динамическую формулу |
| 4 | Доки: фиксированные `limit_multiplier`, код: динамические формулы | Обновить `04_FORMULAS.md` — описать формулы мультипликаторов |
| 5 | Доки: `manual_review` → `null`, код: → рассчитанные значения | Обновить `04_FORMULAS.md` — `manual_review` теперь получает ставку и мультипликатор |
| 6 | Имена Vue-компонентов в доках устарели (`MonitorScreen` → `LaptopScreen`, `DeskItems` → `OfficeFurniture`, и т.д.) | Обновить `05_IMPLEMENTATION_GUIDE.md` и `06_VISUALIZATION.md` — привести список компонентов в соответствие |
| 7 | Доки: «монитор на столе», код: ноутбук (`Laptop.vue`) | Обновить описание визуализации в `06_VISUALIZATION.md` |
| 8 | Roadmap: `config('bank_simulator.scoring.weights')` | Обновить `roadmap/01_BACKEND_FOUNDATION.md` → `config('simulators.bank_simulator.scoring.weights')` |
| 9 | Roadmap: `config/bank_simulator_dialogue.json` (JSON) | Обновить `roadmap/07_DIALOGUE_SYSTEM.md` → `config/simulators/dialogues/*.php` (PHP) |

**Трудозатраты**: ~30 мин, правка текста в .md файлах.

---

## ✅ Решённые проблемы

### 1. ~~Нет системы итоговой оценки (`EvaluationService`)~~ — РЕШЕНО

**Было**: только `add_score_points` — простое суммирование баллов без категоризации.

**Сделано**:
- ✅ Создан `app/Services/Simulators/BankSimulator/EvaluationService.php`
  - Весовые категории: `correctness` (40%), `service_quality` (30%), `compliance` (30%)
  - Метод `evaluate()` — разбивка по категориям, нормализация 0-100, оценки A/B/C/D/F, обратная связь
  - Метод `getCategoryInfo()` — информация о категориях для фронтенда
- ✅ В `ActionProcessor` добавлено сохранение `category` в `score_history`
- ✅ Во всех диалоговых конфигах (`credit_card`, `mortgage`, `consumer_loan`, `deposit`) добавлено поле `category` к каждому `add_score_points` action
- ✅ Добавлен `max_score` в каждый конфиг диалога
- ✅ В `bank_simulator.php` добавлена секция `evaluation_weights`

---

### 2. ~~Мало шаблонов клиентов~~ — РЕШЕНО

**Было**: только `student` и `entrepreneur`.

**Сделано**:
- ✅ Добавлен шаблон `family` (30–45 лет, доход 100–200к, КИ good/excellent, вклад 50%)
- ✅ Добавлен шаблон `pensioner` (55–70 лет, доход 25–50к, КИ excellent/good, вклад 70%)
- `ClientGeneratorService` подхватывает новые шаблоны автоматически из конфига

---

### 3. ~~Диалог «вклад» не реализован~~ — РЕШЕНО

**Было**: только кредитные сценарии (credit_card, mortgage, consumer_loan).

**Сделано**:
- ✅ Создан `config/simulators/dialogues/deposit.php` — полный сценарий «клиент хочет открыть вклад»
  - 12 стадий: приветствие → цель → пополнение/снятие → капитализация → доход → расходы → расчёт → результат → вопросы → оформление → завершение
  - Использует `calculate_deposit` action
  - Все `add_score_points` с `category`
- ✅ `DialogueService::AVAILABLE_DIALOGUE_TYPES` обновлен — включает `'deposit'`

---

### 4. ~~Балльная система 100/1000~~ — РЕШЕНО

**Было**: фронтенд `DialogueInterface.vue` угадывал `maxScore` через цепочку if/else (100 → 200 → 500 → 1000...). `ProgressLogService` предполагал шкалу 0-100 для raw score. Несогласованность между кредитным скором (float 0-1) и диалоговыми баллами (int).

**Сделано**:
- ✅ Каждый диалоговый конфиг содержит `max_score` (сейчас 125 для всех)
- ✅ Backend (`SimulatorsController`) передаёт `max_score` и `dialogue_type` в session state при каждом `processDialogueActions` и `getDialogueStage`
- ✅ `DialogueService::getMaxScore()` — новый метод для получения max_score по типу диалога
- ✅ `DialogueInterface.vue` читает `max_score` из `sessionState` и отображает `score/max_score`
- ✅ Добавлен `normalizedScore` (0-100) для корректных порогов оценок (Отлично/Хорошо/Удовлетворительно)
- ✅ `ProgressLogService::logSimulatorCompletion()` нормализует raw score через `max_score` из `session.state` перед расчётом очков

**Итоговая архитектура балльной системы**:
- **Кредитный скор** (`ScoringService`): float 0-1, отображается как процент в `ScoringResults.vue` — оценка кредитоспособности клиента
- **Диалоговые баллы** (`ActionProcessor`): int, накапливаются из `add_score_points` actions — оценка качества работы студента
- **Нормализованный балл**: `raw_score / max_score * 100` — используется для порогов оценок и начисления очков прогресса

---

## ⚪ Не проблема (опционально / на потом)

| Что | Почему не проблема |
|-----|-------------------|
| Нет админки для настройки весов скоринга | В доках помечено как «опционально». Конфиг-файл работает. Админка — отдельная фича на будущее |
| Конкретные навыки («Финансовое консультирование» и т.д.) не проверены в БД | Инфраструктура Skills есть, добавить конкретные навыки — задача сидера, не архитектурная проблема |
| Нет `ActionButtons.vue` из доков | Кнопки действий встроены в `DialogueInterface.vue` — отдельный компонент не нужен |
| Сценарий «консультация» (клиент не знает, что ему нужно) | Опциональный сценарий, не критичен. Можно добавить позже |
