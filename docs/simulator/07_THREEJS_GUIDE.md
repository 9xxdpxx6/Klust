# 🎮 Three.js для веб-разработчика

> **Для кого**: Веб-девы без опыта в 3D/геймдеве  
> **Цель**: Интегрировать минималистичную 3D сцену в Vue 3 приложение  
> **Инструменты**: TresJS (Vue wrapper для Three.js)

---

## Что такое Three.js?

Three.js - это JavaScript библиотека для создания 3D графики в браузере.

**Ключевая идея**: Ты создаешь 3D объекты (как HTML элементы, но в 3D пространстве).

---

## Базовые концепции (для веб-дева)

### Сцена → Это как `<div>` контейнер

```vue
<TresScene>
  <!-- Все 3D объекты здесь -->
</TresScene>
```

### Объект (Mesh) → Это как компонент Vue

```vue
<TresMesh>
  <!-- Геометрия (форма) -->
  <TresBoxGeometry :args="[1, 1, 1]" />
  <!-- Материал (цвет, текстура) -->
  <TresMeshStandardMaterial color="#8B4513" />
</TresMesh>
```

**Простая аналогия**:
- `BoxGeometry` = форма куба (как `<div>` без стилей)
- `MeshStandardMaterial` = стили (цвет, как CSS `background`)

### Камера → Это как точка зрения

```vue
<TresPerspectiveCamera :position="[0, 1.6, 5]" />
```

**Что это**: Откуда смотришь на сцену. `[x, y, z]` = координаты.

### Свет → Это как `box-shadow`

```vue
<TresAmbientLight :intensity="0.5" />
<TresDirectionalLight :position="[5, 5, 5]" :intensity="1" />
```

**Что это**: Освещение объектов. Без света ничего не видно (черный экран).

---

## Установка и настройка

### 1. Установить зависимости

```bash
npm install three @tresjs/core
```

### 2. Создать плагин TresJS

```javascript
// resources/js/plugins/tresjs.js
import { TresPlugin } from '@tresjs/core'

export function setupTresJS(app) {
  app.use(TresPlugin)
}
```

### 3. Зарегистрировать в app.js

```javascript
// resources/js/app.js
import { setupTresJS } from '@/plugins/tresjs'

const app = createApp({})

setupTresJS(app)
```

---

## Первая 3D сцена (минимальный пример)

```vue
<template>
  <TresCanvas>
    <!-- Камера -->
    <TresPerspectiveCamera 
      :position="[0, 1.6, 5]" 
      :fov="75"
      :near="0.1"
      :far="1000"
    />
    
    <!-- Освещение -->
    <TresAmbientLight :intensity="0.5" />
    <TresDirectionalLight :position="[5, 5, 5]" :intensity="1" />
    
    <!-- Простой куб -->
    <TresMesh>
      <TresBoxGeometry :args="[1, 1, 1]" />
      <TresMeshStandardMaterial color="#8B4513" />
    </TresMesh>
  </TresCanvas>
</template>

<script setup>
// Ничего дополнительного не нужно!
</script>
```

**Что происходит**: Создается 3D сцена с одним коричневым кубом.

---

## Офисная сцена (практический пример)

### Стол консультанта

```vue
<template>
  <TresCanvas class="office-scene">
    <!-- Камера (first-person view) -->
    <TresPerspectiveCamera 
      :position="[0, 1.6, 0]" 
      :fov="75"
    />
    
    <!-- Освещение -->
    <TresAmbientLight :intensity="0.6" />
    <TresDirectionalLight 
      :position="[5, 10, 5]" 
      :intensity="0.8"
      :cast-shadow="true"
    />
    
    <!-- Стол -->
    <TresMesh 
      :position="[0, 0.7, -1]"
      @pointer-enter="onDeskHover"
      @pointer-leave="onDeskLeave"
    >
      <TresBoxGeometry :args="[2, 0.1, 1]" />
      <TresMeshStandardMaterial 
        :color="deskColor"
        :metalness="0.1"
        :roughness="0.8"
      />
    </TresMesh>
    
    <!-- Монитор на столе -->
    <TresMesh 
      :position="[0, 1.2, -0.8]"
      @click="onMonitorClick"
      @pointer-enter="onMonitorHover"
      @pointer-leave="onMonitorLeave"
    >
      <TresGroup>
        <!-- Экран -->
        <TresMesh>
          <TresBoxGeometry :args="[0.6, 0.4, 0.05]" />
          <TresMeshStandardMaterial :color="monitorColor" />
        </TresMesh>
        <!-- Подставка -->
        <TresMesh :position="[0, -0.25, 0]">
          <TresBoxGeometry :args="[0.2, 0.1, 0.2]" />
          <TresMeshStandardMaterial color="#333333" />
        </TresMesh>
      </TresGroup>
    </TresMesh>
    
    <!-- Телефон на столе -->
    <TresMesh 
      :position="[-0.5, 0.9, -0.5]"
      :scale="phoneScale"
      @click="onPhoneClick"
      @pointer-enter="onPhoneHover"
      @pointer-leave="onPhoneLeave"
    >
      <TresBoxGeometry :args="[0.15, 0.05, 0.2]" />
      <TresMeshStandardMaterial 
        :color="isPhoneRinging ? '#4ade80' : '#cccccc'"
        :emissive="isPhoneRinging ? '#4ade80' : '#000000'"
        :emissiveIntensity="isPhoneRinging ? 0.5 : 0"
      />
    </TresMesh>
  </TresCanvas>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRenderLoop } from '@tresjs/core'

const props = defineProps({
  sessionState: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['monitorClick', 'phoneClick'])

// Hover состояния
const deskColor = ref('#8B4513')
const monitorColor = ref('#1e40af')
const phoneScale = ref(1)
const isPhoneRinging = computed(() => props.sessionState?.phone?.isRinging)

// Hover эффекты
const onDeskHover = () => {
  deskColor.value = '#a0522d' // Светлее при hover
}

const onDeskLeave = () => {
  deskColor.value = '#8B4513'
}

const onMonitorHover = (event) => {
  monitorColor.value = '#2563eb' // Светлее синий
  event.stopPropagation()
}

const onMonitorLeave = () => {
  monitorColor.value = '#1e40af'
}

const onPhoneHover = () => {
  phoneScale.value = 1.15 // Увеличение
}

const onPhoneLeave = () => {
  phoneScale.value = 1
}

// Клики
const onMonitorClick = () => {
  emit('monitorClick')
}

const onPhoneClick = () => {
  emit('phoneClick')
}

// Анимация телефона (если звонит)
const { onLoop } = useRenderLoop()
const phoneRotation = ref(0)

if (isPhoneRinging.value) {
  onLoop(() => {
    phoneRotation.value += 0.1
  })
}
</script>

<style scoped>
.office-scene {
  width: 100%;
  height: 100vh;
}
</style>
```

---

## Клиент напротив (простой персонаж)

```vue
<template>
  <TresMesh :position="[0, 0, -2]">
    <TresGroup>
      <!-- Голова -->
      <TresMesh :position="[0, 1.6, 0]" :rotation-y="clientRotation">
        <TresSphereGeometry :args="[0.15, 16, 16]" />
        <TresMeshStandardMaterial color="#F5DEB3" />
      </TresMesh>
      
      <!-- Торс (куб) -->
      <TresMesh :position="[0, 1.3, 0]">
        <TresBoxGeometry :args="[0.4, 0.6, 0.2]" />
        <TresMeshStandardMaterial color="#2C3E50" />
      </TresMesh>
      
      <!-- Анимация кивания при разговоре -->
      <TresMesh 
        v-if="isClientSpeaking"
        :rotation-x="nodRotation"
      >
        <!-- Весь персонаж вращается при nod -->
      </TresMesh>
    </TresGroup>
  </TresMesh>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRenderLoop } from '@tresjs/core'

const props = defineProps({
  sessionState: Object
})

const isClientSpeaking = computed(() => {
  return props.sessionState?.dialogue?.current_step === 'client_speaking'
})

// Анимация кивания
const nodRotation = ref(0)
const clientRotation = ref(0)

const { onLoop } = useRenderLoop()

if (isClientSpeaking.value) {
  onLoop(({ elapsed }) => {
    nodRotation.value = Math.sin(elapsed * 2) * 0.1 // Плавное кивание
    clientRotation.value = Math.sin(elapsed * 1.5) * 0.05 // Легкий поворот
  })
}
</script>
```

---

## Интеграция с PrimeVue Dialog

```vue
<template>
  <div class="simulator-container">
    <!-- 3D сцена -->
    <OfficeScene 
      :session-state="sessionState"
      @monitor-click="showBankInterface = true"
      @phone-click="showPhoneDialog = true"
    />
    
    <!-- UI поверх сцены -->
    <div class="ui-overlay">
      <ClientProfilePanel :client="sessionState?.client" />
    </div>
    
    <!-- PrimeVue диалоги -->
    <Dialog 
      v-model:visible="showBankInterface"
      header="Банковская система"
      :modal="true"
      :style="{ width: '80vw' }"
    >
      <BankInterface :session-state="sessionState" />
    </Dialog>
    
    <Dialog 
      v-model:visible="showPhoneDialog"
      header="Телефон"
      :modal="true"
    >
      <PhoneInterface />
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Dialog } from 'primevue/dialog'
import OfficeScene from '@/Components/BankSimulator/OfficeScene.vue'
import BankInterface from '@/Components/BankSimulator/BankInterface.vue'
import PhoneInterface from '@/Components/BankSimulator/PhoneInterface.vue'

const props = defineProps({
  session: Object
})

const sessionState = computed(() => props.session.state || {})
const showBankInterface = ref(false)
const showPhoneDialog = ref(false)
</script>

<style scoped>
.simulator-container {
  width: 100%;
  height: 100vh;
  position: relative;
}

.ui-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none; /* Клики проходят через overlay */
}

.ui-overlay > * {
  pointer-events: auto; /* Но элементы внутри кликабельны */
}
</style>
```

---

## Синхронизация с SimulatorSession.state

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
  sessionState: {
    type: Object,
    required: true
  }
})

// Цвет монитора меняется в зависимости от результата скоринга
const monitorColor = computed(() => {
  const score = props.sessionState?.calculations?.credit_score
  
  if (score >= 0.8) return '#4ade80' // Зеленый (одобрено)
  if (score >= 0.5) return '#fbbf24' // Желтый (условно)
  if (score >= 0.3) return '#f97316' // Оранжевый (на рассмотрении)
  return '#ef4444' // Красный (отказ)
})

// Видимость индикаторов на основе состояния
const showWarningIndicator = computed(() => {
  return props.sessionState?.calculations?.credit_score < 0.5
})

// Позиция объектов на основе данных
const clientPosition = computed(() => {
  // Можно двигать клиента в зависимости от состояния диалога
  const step = props.sessionState?.dialogue?.current_step
  if (step === 'collecting_data') {
    return [0, 0, -1.8] // Ближе
  }
  return [0, 0, -2] // Стандартная позиция
})
</script>

<template>
  <TresCanvas>
    <!-- Монитор с динамическим цветом -->
    <TresMesh :position="[0, 1.2, -0.8]">
      <TresBoxGeometry :args="[0.6, 0.4, 0.05]" />
      <TresMeshStandardMaterial :color="monitorColor" />
    </TresMesh>
    
    <!-- Индикатор предупреждения (появляется при низком скоре) -->
    <TresMesh 
      v-if="showWarningIndicator"
      :position="[0.8, 1.4, -0.8]"
    >
      <TresSphereGeometry :args="[0.1, 8, 8]" />
      <TresMeshStandardMaterial 
        color="#ef4444"
        :emissive="'#ef4444'"
        :emissiveIntensity="0.8"
      />
    </TresMesh>
    
    <!-- Клиент с динамической позицией -->
    <ClientCharacter :position="clientPosition" />
  </TresCanvas>
</template>
```

---

## Структура компонентов

```
resources/js/
├── Components/
│   └── BankSimulator/
│       ├── OfficeScene.vue          # Главная 3D сцена
│       ├── Desk.vue                 # Стол (опционально, для модульности)
│       ├── Monitor.vue              # Монитор (интерактивный объект)
│       ├── Phone.vue                # Телефон (интерактивный объект)
│       ├── ClientCharacter.vue      # Клиент (простой персонаж)
│       └── Documents.vue            # Документы на столе
│
└── Pages/Client/Student/Simulators/
    └── BankSimulatorSession.vue     # Главный компонент (интеграция всего)
```

---

## Полный пример компонента (OfficeScene.vue)

```vue
<template>
  <TresCanvas 
    class="office-scene"
    :shadows="true"
  >
    <!-- Камера (first-person view) -->
    <TresPerspectiveCamera 
      :position="[0, 1.6, 0]" 
      :fov="75"
    />
    
    <!-- Освещение -->
    <TresAmbientLight :intensity="0.6" />
    <TresDirectionalLight 
      :position="[5, 10, 5]" 
      :intensity="0.8"
      :cast-shadow="true"
    />
    
    <!-- Стол -->
    <TresMesh 
      :position="[0, 0.7, -1]"
      :receive-shadow="true"
    >
      <TresBoxGeometry :args="[2, 0.1, 1]" />
      <TresMeshStandardMaterial 
        color="#8B4513"
        :metalness="0.1"
        :roughness="0.8"
      />
    </TresMesh>
    
    <!-- Монитор -->
    <Monitor 
      :color="monitorColor"
      :position="[0, 1.2, -0.8]"
      @click="onMonitorClick"
    />
    
    <!-- Телефон -->
    <Phone 
      :is-ringing="isPhoneRinging"
      :position="[-0.5, 0.9, -0.5]"
      @click="onPhoneClick"
    />
    
    <!-- Клиент напротив -->
    <ClientCharacter 
      :position="[0, 0, -2]"
      :is-speaking="isClientSpeaking"
    />
  </TresCanvas>
</template>

<script setup>
import { computed } from 'vue'
import Monitor from './Monitor.vue'
import Phone from './Phone.vue'
import ClientCharacter from './ClientCharacter.vue'

const props = defineProps({
  sessionState: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['monitorClick', 'phoneClick'])

// Computed свойства на основе состояния
const monitorColor = computed(() => {
  const score = props.sessionState?.calculations?.credit_score
  if (score >= 0.8) return '#4ade80'
  if (score >= 0.5) return '#fbbf24'
  if (score >= 0.3) return '#f97316'
  return '#ef4444'
})

const isPhoneRinging = computed(() => {
  return props.sessionState?.phone?.isRinging === true
})

const isClientSpeaking = computed(() => {
  return props.sessionState?.dialogue?.current_step === 'client_speaking'
})

// Обработчики событий
const onMonitorClick = () => {
  emit('monitorClick')
}

const onPhoneClick = () => {
  emit('phoneClick')
}
</script>

<style scoped>
.office-scene {
  width: 100%;
  height: 100vh;
  background: linear-gradient(to bottom, #87CEEB 0%, #E0F6FF 100%);
}
</style>
```

---

## Полезные ссылки

- **TresJS Docs**: https://tresjs.org/
- **Three.js Docs**: https://threejs.org/docs/
- **TresJS Examples**: https://tresjs.org/examples/

---

## Чеклист для реализации

- [ ] Установить `three` и `@tresjs/core`
- [ ] Настроить TresJS плагин в app.js
- [ ] Создать базовую сцену с камерой и светом
- [ ] Добавить стол (TresBoxGeometry)
- [ ] Добавить монитор (интерактивный объект)
- [ ] Добавить телефон с hover эффектами
- [ ] Создать простого клиента
- [ ] Интегрировать с `sessionState` через props
- [ ] Подключить PrimeVue диалоги
- [ ] Добавить анимации на основе состояния

---

**Следующий шаг**: Начать с простой статичной сцены (стол + монитор), потом добавлять интерактивность.
