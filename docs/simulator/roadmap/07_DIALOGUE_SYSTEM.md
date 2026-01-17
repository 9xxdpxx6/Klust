# Модуль 07: Система диалога

## Цель модуля

Реализовать систему диалога с клиентом:
- Branching dialogue (дерево выбора реплик)
- UI на мониторе для диалога
- Отображение истории сообщений
- Выбор вариантов ответа

---

## Что нужно сделать

### 1. Сервис диалога (Backend)

#### 1.1. Создать DialogueService

**Файл**: `app/Services/BankSimulator/DialogueService.php`

**Методы**:
```php
class DialogueService
{
    public function getStage(string $stageId, array $context = []): array
    {
        // Заглушка: возвращает этап диалога
        // Полная реализация будет использовать JSON/YAML структуру
    }
    
    public function processUserChoice(string $choice, array $context): array
    {
        // Заглушка: обрабатывает выбор пользователя
        // Возвращает следующий этап
    }
    
    public function getResponseOptions(string $stageId, array $context): array
    {
        // Заглушка: возвращает варианты ответов
    }
}
```

#### 1.2. Создать структуру диалога (JSON)

**Файл**: `config/bank_simulator_dialogue.json`

**Структура**:
```json
{
  "stages": {
    "greeting": {
      "client_message": "Здравствуйте! Чем могу помочь?",
      "user_options": [
        {
          "id": "credit_card",
          "text": "Мне нужна кредитная карта"
        },
        {
          "id": "deposit",
          "text": "Хочу открыть вклад"
        },
        {
          "id": "consultation",
          "text": "Нужна консультация"
        }
      ],
      "next_stage": {
        "credit_card": "credit_inquiry",
        "deposit": "deposit_inquiry",
        "consultation": "consultation"
      }
    },
    "credit_inquiry": {
      "client_message": "Отлично! Расскажите о вашем доходе.",
      "required_data": ["income"],
      "next_stage": "collect_financial_data"
    }
  }
}
```

---

### 2. Компонент диалога (Frontend)

#### 2.1. Создать DialogueInterface.vue

**Файл**: `resources/js/Components/BankSimulator/DialogueInterface.vue`

**Реализация**:
```vue
<template>
  <div class="dialogue-interface">
    <!-- История сообщений -->
    <div class="dialogue-messages">
      <div 
        v-for="(message, index) in messages" 
        :key="index"
        class="message"
        :class="`message-${message.role}`"
      >
        <div class="message-content">
          {{ message.text }}
        </div>
        <div class="message-time">
          {{ formatTime(message.timestamp) }}
        </div>
      </div>
    </div>
    
    <!-- Варианты ответов -->
    <div v-if="currentOptions.length > 0" class="dialogue-options">
      <button
        v-for="option in currentOptions"
        :key="option.id"
        @click="onOptionSelect(option.id)"
        class="option-button"
      >
        {{ option.text }}
      </button>
    </div>
    
    <!-- Ввод данных (если требуется) -->
    <div v-if="requiredData.length > 0" class="dialogue-input">
      <input
        v-for="field in requiredData"
        :key="field"
        v-model="formData[field]"
        :placeholder="getFieldPlaceholder(field)"
        type="number"
        class="data-input"
      />
      <button @click="onDataSubmit" class="submit-button">
        Отправить
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  sessionState: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['optionSelect', 'dataSubmit'])

const messages = computed(() => {
  return props.sessionState?.dialogue?.messages || []
})

const currentOptions = computed(() => {
  const stageId = props.sessionState?.dialogue?.current_step
  // Получить варианты из DialogueService
  return []
})

const requiredData = computed(() => {
  const stageId = props.sessionState?.dialogue?.current_step
  // Получить требуемые данные из DialogueService
  return []
})

const formData = ref({})

const onOptionSelect = (optionId) => {
  emit('optionSelect', optionId)
}

const onDataSubmit = () => {
  emit('dataSubmit', formData.value)
  formData.value = {}
}

const formatTime = (timestamp) => {
  // Форматирование времени
  return new Date(timestamp).toLocaleTimeString('ru-RU')
}

const getFieldPlaceholder = (field) => {
  const placeholders = {
    income: 'Введите доход',
    expenses: 'Введите расходы',
    age: 'Введите возраст'
  }
  return placeholders[field] || field
}
</script>

<style scoped>
.dialogue-interface {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #1e40af;
  color: white;
  padding: 20px;
}

.dialogue-messages {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 20px;
}

.message {
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 8px;
}

.message-client {
  background: rgba(255, 255, 255, 0.1);
  align-self: flex-start;
}

.message-user {
  background: rgba(255, 255, 255, 0.2);
  align-self: flex-end;
}

.dialogue-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.option-button {
  padding: 12px 20px;
  background: #fbbf24;
  color: #000;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.option-button:hover {
  background: #f59e0b;
}

.dialogue-input {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.data-input {
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.submit-button {
  padding: 10px 20px;
  background: #4ade80;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>
```

---

### 3. Интеграция на монитор

#### 3.1. Обновить Monitor.vue

**Добавить UI поверх монитора**:
```vue
<template>
  <TresGroup :position="position">
    <!-- Экран (3D) -->
    <TresMesh>
      <TresBoxGeometry :args="[0.6, 0.4, 0.05]" />
      <TresMeshStandardMaterial :color="color" />
    </TresMesh>
    
    <!-- UI интерфейс (2D поверх 3D) -->
    <Html 
      :position="[0, 0, 0.03]" 
      :center="true"
      :transform="true"
      :distance-factor="0.5"
    >
      <div class="monitor-screen">
        <DialogueInterface 
          :session-state="sessionState"
          @option-select="onOptionSelect"
          @data-submit="onDataSubmit"
        />
      </div>
    </Html>
    
    <!-- Подставка -->
    <TresMesh :position="[0, -0.25, 0]">
      <TresBoxGeometry :args="[0.2, 0.1, 0.2]" />
      <TresMeshStandardMaterial color="#333333" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { Html } from '@tresjs/core'
import DialogueInterface from './DialogueInterface.vue'

const props = defineProps({
  sessionState: Object
})

const emit = defineEmits(['optionSelect', 'dataSubmit'])

const onOptionSelect = (optionId) => {
  emit('optionSelect', optionId)
}

const onDataSubmit = (data) => {
  emit('dataSubmit', data)
}
</script>

<style scoped>
.monitor-screen {
  width: 600px;
  height: 400px;
  background: #1e40af;
  border-radius: 8px;
  overflow: hidden;
}
</style>
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `app/Services/BankSimulator/DialogueService.php`
- [ ] `config/bank_simulator_dialogue.json`
- [ ] `resources/js/Components/BankSimulator/DialogueInterface.vue`

### Изменить:
- [ ] `resources/js/Components/BankSimulator/Monitor.vue` (добавить DialogueInterface)

---

## Критерии готовности

- [ ] DialogueService создан (даже если заглушка)
- [ ] Структура диалога в JSON
- [ ] UI диалога отображается на мониторе
- [ ] Варианты ответов кликабельны
- [ ] История сообщений отображается
- [ ] Ввод данных работает

---

## Тестирование

### Проверить диалог:

1. Открыть симулятор
2. На мониторе должен отображаться диалог
3. Кликнуть на вариант ответа → должен появиться следующий этап
4. Ввести данные → должны сохраниться в state

---

## Зависимости

**Требует**: 
- Модуль 05 (Interactive Objects)
- Модуль 02 (Data Structure)

---

## Следующий модуль

После завершения переходи к: **[08_CLIENT_GENERATION.md](08_CLIENT_GENERATION.md)**
