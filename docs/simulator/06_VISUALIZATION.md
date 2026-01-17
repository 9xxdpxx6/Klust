# 🎮 Игровая визуализация

## Цель

Создать **игровую визуализацию** в стиле job simulator (3D/low-poly офис), а не визуальную новеллу.

**Важно**: Визуал должен быть **околоигровым** - интерактивная 3D сцена с элементами управления.

---

## Варианты реализации

### Вариант 1: Three.js (рекомендуется) ⭐

**Плюсы**:
- Полноценный 3D движок
- Можно создать 3D офисную сцену
- Анимации персонажей и объектов
- Интерактивность (клики по объектам)
- Широкое сообщество

**Минусы**:
- Требует изучения 3D
- Больше код (нужны модели/анимации)
- Производительность (нужна оптимизация)

**Что можно сделать**:
- 3D офисное кресло консультанта (first-person view)
- 3D монитор на столе с интерфейсом банковской системы
- 3D клиент на стуле напротив (виден верхняя часть)
- 3D предметы на столе (телефон, документы, калькулятор)
- Анимации при взаимодействии

**Библиотеки**:
```json
{
  "three": "^0.160.0",
  "@react-three/drei": "^9.88.0", // Если React
  "three-orbit-controls": "^82.1.3" // Управление камерой
}
```

**Пример интеграции**:
```vue
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import * as THREE from 'three'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'

const containerRef = ref(null)
let scene, camera, renderer, controls

onMounted(() => {
  // Создание сцены
  scene = new THREE.Scene()
  scene.background = new THREE.Color(0xf0f0f0)
  
  // Камера (first-person вид)
  camera = new THREE.PerspectiveCamera(75, containerRef.value.clientWidth / containerRef.value.clientHeight, 0.1, 1000)
  camera.position.set(0, 1.6, 0) // Высота глаз человека
  
  // Рендерер
  renderer = new THREE.WebGLRenderer({ antialias: true })
  renderer.setSize(containerRef.value.clientWidth, containerRef.value.clientHeight)
  containerRef.value.appendChild(renderer.domElement)
  
  // Управление камерой (для отладки, в игре можно отключить)
  controls = new OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  
  // Создание офисной сцены
  createOfficeScene()
  
  // Анимация
  animate()
})

function createOfficeScene() {
  // Стол консультанта
  const desk = createDesk()
  scene.add(desk)
  
  // Монитор на столе
  const monitor = createMonitor()
  monitor.position.set(0, 0.5, -0.3)
  scene.add(monitor)
  
  // Клиент (сидящий напротив)
  const client = createClient()
  client.position.set(0, 0, -2)
  scene.add(client)
  
  // Предметы на столе
  const phone = createPhone()
  phone.position.set(-0.5, 0.3, 0)
  scene.add(phone)
}

function createDesk() {
  const geometry = new THREE.BoxGeometry(2, 0.05, 1)
  const material = new THREE.MeshLambertMaterial({ color: 0x8B4513 })
  return new THREE.Mesh(geometry, material)
}

function createMonitor() {
  // Простой монитор из кубов (low-poly стиль)
  const group = new THREE.Group()
  
  // Экран
  const screen = new THREE.Mesh(
    new THREE.BoxGeometry(0.6, 0.4, 0.05),
    new THREE.MeshBasicMaterial({ color: 0x1e40af })
  )
  group.add(screen)
  
  // Подставка
  const stand = new THREE.Mesh(
    new THREE.BoxGeometry(0.2, 0.1, 0.2),
    new THREE.MeshLambertMaterial({ color: 0x333333 })
  )
  stand.position.y = -0.25
  group.add(stand)
  
  return group
}

function animate() {
  requestAnimationFrame(animate)
  controls.update()
  renderer.render(scene, camera)
}

onUnmounted(() => {
  renderer.dispose()
})
</script>

<template>
  <div ref="containerRef" class="w-full h-screen"></div>
</template>
```

---

### Вариант 2: CSS 3D Transforms (проще)

**Плюсы**:
- Простота реализации
- Хорошая производительность
- Не требует 3D библиотек
- Легко интегрировать с Vue

**Минусы**:
- Ограниченные возможности 3D
- Сложнее делать сложные сцены
- Нет управления освещением

**Что можно сделать**:
- Простая 3D офисная сцена
- Наклонные поверхности (стол, монитор)
- Простые анимации
- Flat design стиль (как на скриншоте с банковской кассой)

**Пример**:
```vue
<template>
  <div class="office-scene">
    <!-- Стол -->
    <div class="desk" :style="{ transform: 'perspective(1000px) rotateX(10deg)' }">
      <!-- Монитор -->
      <div class="monitor">
        <div class="monitor-screen">
          <!-- Интерфейс банковской системы -->
          <BankInterface />
        </div>
      </div>
      
      <!-- Телефон -->
      <div class="phone" @click="handlePhoneClick">
        📞
      </div>
    </div>
    
    <!-- Клиент (сидящий напротив) -->
    <div class="client" :class="{ 'animated': isClientSpeaking }">
      <div class="client-body">
        <div class="client-head"></div>
        <div class="client-torso"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.office-scene {
  perspective: 1000px;
  height: 100vh;
  background: linear-gradient(to bottom, #87CEEB 0%, #E0F6FF 100%);
}

.desk {
  position: absolute;
  bottom: 20%;
  left: 50%;
  transform: translateX(-50%) rotateX(10deg);
  width: 800px;
  height: 400px;
  background: #8B4513;
  border-radius: 10px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.monitor {
  position: absolute;
  top: 50px;
  left: 50%;
  transform: translateX(-50%);
  width: 600px;
  height: 400px;
  background: #1e40af;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.phone {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 80px;
  height: 80px;
  background: #ccc;
  border-radius: 10px;
  cursor: pointer;
  transition: transform 0.2s;
}

.phone:hover {
  transform: scale(1.1);
}

.client {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%) translateZ(-500px);
  transition: transform 0.3s;
}

.client.animated {
  animation: nod 0.5s ease-in-out;
}

@keyframes nod {
  0%, 100% { transform: translateX(-50%) translateZ(-500px) rotateX(0deg); }
  50% { transform: translateX(-50%) translateZ(-500px) rotateX(5deg); }
}
</style>
```

---

### Вариант 3: Canvas API + SVG (гибрид)

**Плюсы**:
- Кастомная отрисовка
- Можно комбинировать 2D/3D
- Хорошая производительность
- Легко добавлять интерактивность

**Минусы**:
- Нужно рисовать всё вручную
- Больше код

**Что можно сделать**:
- 2D офисная сцена с перспективой
- SVG элементы для объектов
- Canvas для анимаций
- Flat design стиль

---

## Рекомендуемый подход для MVP

### Этап 1: CSS 3D + Vue компоненты (быстро)

**Что делать**:
1. Создать базовую 3D сцену через CSS transforms
2. Использовать Vue компоненты для интерактивных элементов
3. Добавить простые анимации через CSS
4. Flat/low-poly стиль дизайна

**Время**: 2-3 дня

**Пример структуры**:
```vue
<template>
  <div class="bank-simulator-scene">
    <!-- 3D сцена через CSS -->
    <Office3DScene>
      <!-- Монитор с интерфейсом -->
      <MonitorScreen>
        <BankInterface />
      </MonitorScreen>
      
      <!-- Клиент напротив -->
      <ClientCharacter :isSpeaking="isClientSpeaking" />
      
      <!-- Предметы на столе -->
      <DeskItems @phoneClick="handlePhone" />
    </Office3DScene>
    
    <!-- UI панели поверх сцены -->
    <ClientProfilePanel />
    <CreditCalculatorPanel />
  </div>
</template>
```

### Этап 2: Three.js (если нужно более реалистично)

**Что делать**:
1. Интегрировать Three.js
2. Создать 3D модели (low-poly)
3. Добавить освещение и камеру
4. Интерактивность через raycasting

**Время**: 1-2 недели

---

## Структура компонентов для игровой визуализации

```
resources/js/
├── Components/
│   └── BankSimulator/
│       ├── Office3DScene.vue          # Основная 3D сцена (Three.js или CSS)
│       ├── MonitorScreen.vue          # Монитор на столе
│       ├── ClientCharacter.vue        # 3D/2D модель клиента
│       ├── DeskItems.vue              # Предметы на столе (телефон, документы)
│       ├── BankInterface.vue          # Интерфейс банковской системы (на мониторе)
│       └── OfficeEnvironment.vue      # Фон (окна, стены, календарь)
│
└── Pages/Client/Student/Simulators/
    └── BankSimulatorSession.vue       # Главный компонент симулятора
```

---

## Стиль визуализации

### Low-poly / Stylized

**Характеристики**:
- Простые геометрические формы (кубы, цилиндры)
- Минималистичные цвета
- Плоские поверхности (без детализации)
- Мягкие тени

**Цветовая палитра**:
- Стол: коричневый (#8B4513)
- Монитор: синий (#1e40af)
- Клиент: бежевый (#F5DEB3) с темным костюмом (#2C3E50)
- Фон: светлый градиент (#E0F6FF → #FFFFFF)

### Flat Design (для UI на мониторе)

**Характеристики**:
- Плоские цвета без градиентов
- Простые иконки
- Четкие границы
- Минимум теней

---

## Интерактивность

### Клики по объектам

```vue
<script setup>
const handleObjectClick = (objectType) => {
  switch(objectType) {
    case 'phone':
      // Показать меню телефона
      showPhoneMenu.value = true
      break
    case 'document':
      // Открыть документ
      openDocument.value = true
      break
    case 'calculator':
      // Показать калькулятор
      showCalculator.value = true
      break
  }
}
</script>

<template>
  <div class="interactive-object" @click="handleObjectClick('phone')">
    📞 Телефон
  </div>
</template>
```

### Анимации персонажей

```vue
<script setup>
const isClientSpeaking = ref(false)

watch(() => props.dialogue.currentMessage, () => {
  // Анимация когда клиент говорит
  isClientSpeaking.value = true
  setTimeout(() => {
    isClientSpeaking.value = false
  }, 2000)
})
</script>

<template>
  <div class="client" :class="{ 'speaking': isClientSpeaking }">
    <!-- Клиент -->
  </div>
</template>

<style scoped>
.client.speaking {
  animation: speak 0.5s ease-in-out infinite;
}

@keyframes speak {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}
</style>
```

---

## Интеграция с логикой симулятора

### Связь визуализации с state

```vue
<script setup>
const props = defineProps({
  session: Object // SimulatorSession
})

const state = computed(() => props.session.state)

// Визуальные эффекты на основе состояния
const sceneMood = computed(() => {
  if (state.value.calculations.credit_score < 0.3) {
    return 'tense' // Красные оттенки, мигание
  }
  if (state.value.calculations.credit_score > 0.8) {
    return 'positive' // Зеленые оттенки, позитивные анимации
  }
  return 'neutral'
})
</script>

<template>
  <div class="office-scene" :class="`mood-${sceneMood}`">
    <!-- Сцена -->
  </div>
</template>
```

---

## Производительность

### Оптимизация для Three.js

- Использовать `THREE.Geometry` вместо `THREE.BufferGeometry` для простых объектов
- LOD (Level of Detail) для сложных моделей
- Ограничить количество полигонов
- Использовать простые материалы

### Оптимизация для CSS 3D

- Использовать `transform: translateZ(0)` для GPU ускорения
- Минимизировать перерисовки
- Использовать `will-change` для анимируемых элементов

---

## Чеклист для визуализации

- [ ] Выбрать подход (CSS 3D или Three.js)
- [ ] Создать базовую офисную сцену
- [ ] Добавить монитор с интерфейсом
- [ ] Создать модель клиента
- [ ] Добавить интерактивные объекты (телефон, документы)
- [ ] Интегрировать анимации
- [ ] Добавить звуковые эффекты (опционально)
- [ ] Оптимизировать производительность

---

## Рекомендации

### Для MVP:
✅ Начать с **CSS 3D** - быстро, достаточно для демонстрации концепции

### Для полноценной версии:
✅ Использовать **Three.js** - более реалистично, лучше визуал

---

**Следующий шаг**: Решить какой подход использовать и начать прототипирование сцены
