# Модуль 05: Интерактивные объекты

## Цель модуля

Добавить интерактивные объекты на стол:
- Телефон (кликабельный)
- Документы (кликабельные)
- Калькулятор (кликабельный)
- Hover эффекты на всех объектах
- Click события (emit в Vue)

---

## Что нужно сделать

### 1. Компонент телефона

#### 1.1. Создать Phone.vue

**Файл**: `resources/js/Components/BankSimulator/Phone.vue`

**Реализация**:
```vue
<template>
  <TresMesh 
    :position="position"
    :scale="scale"
    :rotation="[0, 0, phoneRotation]"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <TresBoxGeometry :args="[0.15, 0.05, 0.2]" />
    <TresMeshStandardMaterial 
      :color="phoneColor"
      :emissive="emissiveColor"
      :emissiveIntensity="emissiveIntensity"
    />
  </TresMesh>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRenderLoop } from '@tresjs/core'

const props = defineProps({
  position: {
    type: Array,
    default: () => [-0.5, 0.9, -0.5]
  },
  isRinging: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const scale = ref(1)
const phoneRotation = ref(0)
const isHovered = ref(false)

const phoneColor = computed(() => {
  if (props.isRinging) return '#4ade80'
  if (isHovered.value) return '#9ca3af'
  return '#cccccc'
})

const emissiveColor = computed(() => {
  return props.isRinging ? '#4ade80' : '#000000'
})

const emissiveIntensity = computed(() => {
  return props.isRinging ? 0.5 : 0
})

const onHoverEnter = () => {
  scale.value = 1.15
  isHovered.value = true
}

const onHoverLeave = () => {
  scale.value = 1
  isHovered.value = false
}

const onClick = () => {
  emit('click')
}

// Анимация вибрации если звонит
const { onLoop } = useRenderLoop()

if (props.isRinging) {
  onLoop(({ elapsed }) => {
    phoneRotation.value = Math.sin(elapsed * 10) * 0.1
  })
}
</script>
```

---

### 2. Компонент документов

#### 2.1. Создать Documents.vue

**Файл**: `resources/js/Components/BankSimulator/Documents.vue`

**Реализация**:
```vue
<template>
  <TresGroup :position="position" :scale="scale">
    <!-- Стек документов (несколько кубов) -->
    <TresMesh
      v-for="(doc, index) in documentStack"
      :key="index"
      :position="[0, index * 0.02, 0]"
      @click="onClick"
      @pointer-enter="onHoverEnter"
      @pointer-leave="onHoverLeave"
    >
      <TresBoxGeometry :args="[0.2, 0.02, 0.15]" />
      <TresMeshStandardMaterial :color="doc.color" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0.5, 0.9, -0.5]
  },
  count: {
    type: Number,
    default: 3
  }
})

const emit = defineEmits(['click'])

const scale = ref(1)
const isHovered = ref(false)

const documentStack = computed(() => {
  const colors = ['#ffffff', '#f5f5f5', '#e5e5e5']
  return Array.from({ length: props.count }, (_, i) => ({
    color: colors[i % colors.length]
  }))
})

const onHoverEnter = () => {
  scale.value = 1.1
  isHovered.value = true
}

const onHoverLeave = () => {
  scale.value = 1
  isHovered.value = false
}

const onClick = () => {
  emit('click')
}
</script>
```

---

### 3. Компонент калькулятора

#### 3.1. Создать Calculator.vue

**Файл**: `resources/js/Components/BankSimulator/Calculator.vue`

**Реализация**:
```vue
<template>
  <TresMesh 
    :position="position"
    :scale="scale"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <TresBoxGeometry :args="[0.2, 0.05, 0.15]" />
    <TresMeshStandardMaterial 
      :color="isHovered ? '#fbbf24' : '#f59e0b'"
      :metalness="0.3"
      :roughness="0.7"
    />
  </TresMesh>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0.8, 0.9, -0.5]
  }
})

const emit = defineEmits(['click'])

const scale = ref(1)
const isHovered = ref(false)

const onHoverEnter = () => {
  scale.value = 1.1
  isHovered.value = true
}

const onHoverLeave = () => {
  scale.value = 1
  isHovered.value = false
}

const onClick = () => {
  emit('click')
}
</script>
```

---

### 4. Интеграция в OfficeScene

#### 4.1. Обновить OfficeScene.vue

**Добавить объекты**:
```vue
<template>
  <TresCanvas>
    <!-- ... existing code ... -->
    
    <!-- Телефон -->
    <Phone 
      :position="[-0.5, 0.9, -0.5]"
      :is-ringing="isPhoneRinging"
      @click="onPhoneClick"
    />
    
    <!-- Документы -->
    <Documents 
      :position="[0.5, 0.9, -0.5]"
      :count="3"
      @click="onDocumentsClick"
    />
    
    <!-- Калькулятор -->
    <Calculator 
      :position="[0.8, 0.9, -0.5]"
      @click="onCalculatorClick"
    />
  </TresCanvas>
</template>

<script setup>
import Phone from './Phone.vue'
import Documents from './Documents.vue'
import Calculator from './Calculator.vue'

const emit = defineEmits(['phoneClick', 'documentsClick', 'calculatorClick'])

const isPhoneRinging = computed(() => {
  return props.sessionState?.phone?.isRinging === true
})

const onPhoneClick = () => {
  emit('phoneClick')
}

const onDocumentsClick = () => {
  emit('documentsClick')
}

const onCalculatorClick = () => {
  emit('calculatorClick')
}
</script>
```

---

### 5. Интеграция в главную страницу

#### 5.1. Обновить BankSimulatorSession.vue

**Добавить обработчики**:
```vue
<script setup>
const showPhoneDialog = ref(false)
const showCalculatorDialog = ref(false)
const showDocumentsDialog = ref(false)

const onPhoneClick = () => {
  showPhoneDialog.value = true
}

const onCalculatorClick = () => {
  showCalculatorDialog.value = true
}

const onDocumentsClick = () => {
  showDocumentsDialog.value = true
}
</script>

<template>
  <div class="simulator-container">
    <OfficeScene 
      :session-state="sessionState"
      @phone-click="onPhoneClick"
      @calculator-click="onCalculatorClick"
      @documents-click="onDocumentsClick"
    />
    
    <!-- PrimeVue диалоги -->
    <Dialog v-model:visible="showPhoneDialog" header="Телефон">
      <p>Интерфейс телефона...</p>
    </Dialog>
    
    <Dialog v-model:visible="showCalculatorDialog" header="Калькулятор">
      <p>Интерфейс калькулятора...</p>
    </Dialog>
    
    <Dialog v-model:visible="showDocumentsDialog" header="Документы">
      <p>Интерфейс документов...</p>
    </Dialog>
  </div>
</template>
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `resources/js/Components/BankSimulator/Phone.vue`
- [ ] `resources/js/Components/BankSimulator/Documents.vue`
- [ ] `resources/js/Components/BankSimulator/Calculator.vue`

### Изменить:
- [ ] `resources/js/Components/BankSimulator/OfficeScene.vue` (добавить объекты)
- [ ] `resources/js/Pages/Client/Student/Simulators/BankSimulatorSession.vue` (обработчики событий)

---

## Критерии готовности

- [ ] Все интерактивные объекты созданы
- [ ] Hover эффекты работают (объекты увеличиваются)
- [ ] Click события эмитятся корректно
- [ ] PrimeVue диалоги открываются при клике
- [ ] Анимация телефона работает (если звонит)
- [ ] Объекты реагируют на `sessionState` (цвет телефона)

---

## Тестирование

### Проверить интерактивность:

1. Навести мышку на телефон → должен увеличиться
2. Кликнуть на телефон → должен открыться диалог
3. Навести на документы → должны увеличиться
4. Кликнуть на калькулятор → должен открыться диалог

### Проверить состояния:

```vue
<OfficeScene :session-state="{ phone: { isRinging: true } }" />
<!-- Телефон должен светиться зеленым и вибрировать -->
```

---

## Зависимости

**Требует**: Модуль 04 (3D Scene Basic)

---

## Следующий модуль

После завершения переходи к: **[06_CLIENT_CHARACTER.md](06_CLIENT_CHARACTER.md)**
