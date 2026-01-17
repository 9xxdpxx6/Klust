# Модуль 03: Frontend Setup

## Цель модуля

Настроить frontend инфраструктуру для 3D сцены:
- Установить TresJS
- Настроить плагин
- Создать базовые компоненты структуры

---

## Что нужно сделать

### 1. Установка зависимостей

#### 1.1. Установить npm пакеты

```bash
npm install three @tresjs/core
```

#### 1.2. Проверить package.json

**Файл**: `package.json`

Должны быть добавлены:
```json
{
  "dependencies": {
    "three": "^0.160.0",
    "@tresjs/core": "^4.0.0"
  }
}
```

---

### 2. Настройка TresJS плагина

#### 2.1. Создать плагин

**Файл**: `resources/js/plugins/tresjs.js`

**Содержимое**:
```javascript
import { TresPlugin } from '@tresjs/core'

export function setupTresJS(app) {
  app.use(TresPlugin)
}
```

#### 2.2. Зарегистрировать в app.js

**Файл**: `resources/js/app.js`

**Добавить**:
```javascript
import { setupTresJS } from '@/plugins/tresjs'

// ... existing code ...

setupTresJS(app)
```

---

### 3. Базовые компоненты структуры

#### 3.1. Создать структуру папок

```
resources/js/
├── Components/
│   └── BankSimulator/
│       ├── OfficeScene.vue          (создать позже, модуль 04)
│       ├── Desk.vue                 (создать позже, модуль 04)
│       ├── Monitor.vue              (создать позже, модуль 05)
│       ├── Phone.vue                (создать позже, модуль 05)
│       └── ClientCharacter.vue      (создать позже, модуль 06)
│
└── Pages/Client/Student/Simulators/
    └── BankSimulatorSession.vue     (создать позже, модуль 04)
```

#### 3.2. Создать базовый компонент-заглушку

**Файл**: `resources/js/Components/BankSimulator/OfficeScene.vue`

**Временная версия** (полная реализация в модуле 04):
```vue
<template>
  <TresCanvas>
    <TresPerspectiveCamera :position="[0, 1.6, 5]" />
    <TresAmbientLight :intensity="0.5" />
    <TresMesh>
      <TresBoxGeometry :args="[1, 1, 1]" />
      <TresMeshStandardMaterial color="#8B4513" />
    </TresMesh>
  </TresCanvas>
</template>

<script setup>
// Заглушка - полная реализация в модуле 04
</script>

<style scoped>
/* Стили будут добавлены позже */
</style>
```

---

### 4. Главная страница симулятора

#### 4.1. Создать страницу

**Файл**: `resources/js/Pages/Client/Student/Simulators/BankSimulatorSession.vue`

**Временная версия**:
```vue
<template>
  <div class="bank-simulator-page">
    <Head :title="`Симулятор: ${simulator.title}`" />
    
    <div class="simulator-container">
      <!-- 3D сцена (заглушка) -->
      <OfficeScene :session-state="sessionState" />
      
      <!-- UI панели (будут добавлены позже) -->
      <div class="ui-overlay">
        <!-- Здесь будут UI элементы -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import OfficeScene from '@/Components/BankSimulator/OfficeScene.vue'

const props = defineProps({
  session: Object,
  simulator: Object
})

const sessionState = computed(() => props.session.state || {})
</script>

<style scoped>
.bank-simulator-page {
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

.simulator-container {
  width: 100%;
  height: 100%;
  position: relative;
}

.ui-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.ui-overlay > * {
  pointer-events: auto;
}
</style>
```

---

### 5. Обновить контроллер

#### 5.1. Добавить метод в контроллер

**Файл**: `app/Http/Controllers/Client/Student/SimulatorsController.php`

**Добавить метод**:
```php
/**
 * Продолжение сессии симулятора
 */
public function session(SimulatorSession $session): Response
{
    $this->authorize('view', $session);
    
    $simulator = $session->simulator;
    
    return Inertia::render('Client/Student/Simulators/BankSimulatorSession', [
        'session' => $session,
        'simulator' => $simulator,
    ]);
}
```

#### 5.2. Добавить роут

**Файл**: `routes/web.php`

**Добавить**:
```php
Route::get('/simulators/{session}', [SimulatorsController::class, 'session'])
    ->name('simulators.session');
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `resources/js/plugins/tresjs.js`
- [ ] `resources/js/Components/BankSimulator/OfficeScene.vue` (заглушка)
- [ ] `resources/js/Pages/Client/Student/Simulators/BankSimulatorSession.vue` (заглушка)

### Изменить:
- [ ] `resources/js/app.js` (добавить setupTresJS)
- [ ] `package.json` (добавить зависимости)
- [ ] `app/Http/Controllers/Client/Student/SimulatorsController.php` (добавить метод session)
- [ ] `routes/web.php` (добавить роут)

### Установить:
- [ ] `npm install three @tresjs/core`
- [ ] `npm run build` (проверить что собирается)

---

## Критерии готовности

- [ ] TresJS установлен и работает
- [ ] Плагин зарегистрирован в app.js
- [ ] Базовые компоненты созданы (даже если заглушки)
- [ ] Страница симулятора открывается
- [ ] 3D сцена рендерится (даже простой куб)

---

## Тестирование

### Проверить что работает:

1. Открыть страницу симулятора в браузере
2. Должна отобразиться 3D сцена (даже если только куб)
3. В консоли браузера не должно быть ошибок
4. `npm run build` должен успешно собираться

---

## Зависимости

**Нет зависимостей** - можно реализовывать параллельно с модулями 01 и 02.

---

## Следующий модуль

После завершения переходи к: **[04_3D_SCENE_BASIC.md](04_3D_SCENE_BASIC.md)**
