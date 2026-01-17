# Модуль 12: Синхронизация состояния

## Цель модуля

Реализовать синхронизацию состояния симулятора:
- Сохранение состояния в backend
- Загрузка состояния при старте сессии
- Обновление состояния при действиях пользователя
- Интеграция с `SimulatorSession.state`

---

## Что нужно сделать

### 1. API endpoints для работы с состоянием

#### 1.1. Добавить методы в контроллер

**Файл**: `app/Http/Controllers/Client/Student/SimulatorsController.php`

**Методы**:
```php
/**
 * Обновить состояние сессии
 */
public function updateState(UpdateStateRequest $request, SimulatorSession $session): JsonResponse
{
    $this->authorize('update', $session);
    
    $this->simulatorService->updateSessionState(
        $session, 
        $request->validated()['state']
    );
    
    return response()->json(['success' => true]);
}

/**
 * Получить текущее состояние сессии
 */
public function getState(SimulatorSession $session): JsonResponse
{
    $this->authorize('view', $session);
    
    return response()->json([
        'state' => $session->state
    ]);
}
```

#### 1.2. Добавить роуты

**Файл**: `routes/web.php`

**Добавить**:
```php
Route::post('/simulators/{session}/state', [SimulatorsController::class, 'updateState'])
    ->name('simulators.state.update');

Route::get('/simulators/{session}/state', [SimulatorsController::class, 'getState'])
    ->name('simulators.state.get');
```

---

### 2. Сервис для работы с состоянием

#### 2.1. Обновить SimulatorService

**Файл**: `app/Services/SimulatorService.php`

**Методы**:
```php
/**
 * Обновить состояние сессии
 */
public function updateSessionState(SimulatorSession $session, array $state): SimulatorSession
{
    return DB::transaction(function () use ($session, $state) {
        $currentState = $session->state ?? [];
        
        // Мерджим состояния (сохраняем историю)
        $newState = array_merge_recursive($currentState, $state);
        
        $session->update([
            'state' => $newState
        ]);
        
        return $session->fresh();
    });
}

/**
 * Получить состояние сессии
 */
public function getSessionState(SimulatorSession $session): array
{
    return $session->state ?? [];
}
```

---

### 3. Композабл для работы с состоянием (Frontend)

#### 3.1. Создать useSimulatorState.js

**Файл**: `resources/js/Composables/useSimulatorState.js`

**Реализация**:
```javascript
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useSimulatorState(sessionId, initialState = {}) {
  const state = ref(initialState)
  const isLoading = ref(false)
  const error = ref(null)
  
  // Обновить состояние
  const updateState = async (updates) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await axios.post(
        route('simulators.state.update', sessionId),
        { state: updates }
      )
      
      // Обновить локальное состояние
      state.value = { ...state.value, ...updates }
      
      return response.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Ошибка обновления состояния'
      throw e
    } finally {
      isLoading.value = false
    }
  }
  
  // Загрузить состояние
  const loadState = async () => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await axios.get(
        route('simulators.state.get', sessionId)
      )
      
      state.value = response.data.state || {}
      
      return state.value
    } catch (e) {
      error.value = e.response?.data?.message || 'Ошибка загрузки состояния'
      throw e
    } finally {
      isLoading.value = false
    }
  }
  
  // Автосохранение (debounced)
  const autoSave = debounce((updates) => {
    updateState(updates).catch(() => {
      // Тихая ошибка при автосохранении
    })
  }, 2000)
  
  return {
    state,
    isLoading,
    error,
    updateState,
    loadState,
    autoSave
  }
}

// Простая реализация debounce
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}
```

---

### 4. Интеграция в компоненты

#### 4.1. Обновить BankSimulatorSession.vue

**Использовать композабл**:
```vue
<script setup>
import { onMounted, watch } from 'vue'
import { useSimulatorState } from '@/Composables/useSimulatorState'

const props = defineProps({
  session: Object
})

const { state, updateState, loadState, autoSave } = useSimulatorState(
  props.session.id,
  props.session.state || {}
)

// Загрузить состояние при монтировании
onMounted(() => {
  loadState()
})

// Обработчики событий
const onDialogueOptionSelect = async (optionId) => {
  const updates = {
    dialogue: {
      current_step: getNextStep(optionId),
      messages: [
        ...state.value.dialogue?.messages || [],
        {
          role: 'user',
          text: getOptionText(optionId),
          timestamp: new Date().toISOString()
        }
      ]
    }
  }
  
  await updateState(updates)
}

// Автосохранение при изменениях
watch(state, (newState) => {
  autoSave(newState)
}, { deep: true })
</script>
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `resources/js/Composables/useSimulatorState.js`
- [ ] `app/Http/Requests/Student/Simulator/UpdateStateRequest.php`

### Изменить:
- [ ] `app/Http/Controllers/Client/Student/SimulatorsController.php` (добавить методы)
- [ ] `app/Services/SimulatorService.php` (добавить методы)
- [ ] `routes/web.php` (добавить роуты)
- [ ] `resources/js/Pages/Client/Student/Simulators/BankSimulatorSession.vue` (интегрировать композабл)

---

## Критерии готовности

- [ ] API endpoints работают
- [ ] Состояние сохраняется в backend
- [ ] Состояние загружается при старте
- [ ] Автосохранение работает (debounced)
- [ ] Ошибки обрабатываются корректно
- [ ] Состояние синхронизируется между компонентами

---

## Тестирование

### Проверить синхронизацию:

1. Открыть симулятор
2. Выполнить действие (выбрать вариант ответа)
3. Проверить в БД что `state` обновился
4. Обновить страницу → состояние должно загрузиться
5. Проверить автосохранение (через 2 секунды должно сохраниться)

---

## Зависимости

**Требует**: 
- Модуль 02 (Data Structure)
- Модуль 09 (Scoring Integration)

---

## Следующий модуль

После завершения переходи к: **[13_EVALUATION_SYSTEM.md](13_EVALUATION_SYSTEM.md)**
