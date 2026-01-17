# Модуль 04: Базовая 3D сцена

## Цель модуля

Создать базовую 3D сцену офиса консультанта:
- Стол перед камерой
- Монитор на столе
- Освещение
- Камера (first-person view)
- Фон (окна, стены)

---

## Что нужно сделать

### 1. Базовая сцена с камерой

#### 1.1. Обновить OfficeScene.vue

**Файл**: `resources/js/Components/BankSimulator/OfficeScene.vue`

**Реализация**:
```vue
<template>
  <TresCanvas 
    class="office-scene"
    :shadows="true"
    :clear-color="'#E0F6FF'"
  >
    <!-- Камера (first-person view из офисного кресла) -->
    <TresPerspectiveCamera 
      :position="[0, 1.6, 0]" 
      :fov="75"
      :near="0.1"
      :far="1000"
    />
    
    <!-- Освещение -->
    <TresAmbientLight :intensity="0.6" />
    <TresDirectionalLight 
      :position="[5, 10, 5]" 
      :intensity="0.8"
      :cast-shadow="true"
    />
    
    <!-- Стол -->
    <Desk />
    
    <!-- Монитор на столе -->
    <Monitor 
      :position="[0, 1.2, -0.8]"
      :color="monitorColor"
    />
  </TresCanvas>
</template>

<script setup>
import { computed } from 'vue'
import Desk from './Desk.vue'
import Monitor from './Monitor.vue'

const props = defineProps({
  sessionState: {
    type: Object,
    default: () => ({})
  }
})

const monitorColor = computed(() => {
  const score = props.sessionState?.calculations?.credit_score
  if (score >= 0.8) return '#4ade80'
  if (score >= 0.5) return '#fbbf24'
  if (score >= 0.3) return '#f97316'
  return '#1e40af' // Дефолтный синий
})
</script>

<style scoped>
.office-scene {
  width: 100%;
  height: 100vh;
}
</style>
```

---

### 2. Компонент стола

#### 2.1. Создать Desk.vue

**Файл**: `resources/js/Components/BankSimulator/Desk.vue`

**Реализация**:
```vue
<template>
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
</template>

<script setup>
// Простой стол из одного куба
</script>
```

---

### 3. Компонент монитора

#### 3.1. Создать Monitor.vue

**Файл**: `resources/js/Components/BankSimulator/Monitor.vue`

**Реализация**:
```vue
<template>
  <TresGroup :position="position">
    <!-- Экран -->
    <TresMesh :position="[0, 0, 0]">
      <TresBoxGeometry :args="[0.6, 0.4, 0.05]" />
      <TresMeshStandardMaterial :color="color" />
    </TresMesh>
    
    <!-- Подставка -->
    <TresMesh :position="[0, -0.25, 0]">
      <TresBoxGeometry :args="[0.2, 0.1, 0.2]" />
      <TresMeshStandardMaterial color="#333333" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
const props = defineProps({
  position: {
    type: Array,
    default: () => [0, 1.2, -0.8]
  },
  color: {
    type: String,
    default: '#1e40af'
  }
})
</script>
```

---

### 4. Фон сцены (опционально)

#### 4.1. Добавить фон в OfficeScene.vue

**Можно добавить**:
- Простые стены (кубы)
- Окна (прозрачные кубы с фоном)
- Пол (плоскость)

**Пример**:
```vue
<!-- Пол -->
<TresMesh :position="[0, 0, -5]" :rotation-x="-Math.PI / 2">
  <TresPlaneGeometry :args="[20, 20]" />
  <TresMeshStandardMaterial color="#cccccc" />
</TresMesh>

<!-- Стены (опционально) -->
<TresMesh :position="[0, 2.5, -5]">
  <TresBoxGeometry :args="[20, 5, 0.1]" />
  <TresMeshStandardMaterial color="#f5f5f5" />
</TresMesh>
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `resources/js/Components/BankSimulator/Desk.vue`
- [ ] `resources/js/Components/BankSimulator/Monitor.vue`

### Изменить:
- [ ] `resources/js/Components/BankSimulator/OfficeScene.vue` (полная реализация)

---

## Критерии готовности

- [ ] 3D сцена рендерится корректно
- [ ] Стол виден перед камерой
- [ ] Монитор отображается на столе
- [ ] Освещение работает (не черный экран)
- [ ] Камера настроена (first-person view)
- [ ] Цвет монитора меняется в зависимости от `sessionState`

---

## Тестирование

### Проверить визуально:

1. Открыть страницу симулятора
2. Должен быть виден коричневый стол перед камерой
3. На столе должен быть монитор (синий/цветной)
4. Освещение должно быть нормальным (не слишком темно/светло)

### Проверить через props:

```vue
<OfficeScene :session-state="{ calculations: { credit_score: 0.8 } }" />
<!-- Монитор должен быть зеленым -->

<OfficeScene :session-state="{ calculations: { credit_score: 0.3 } }" />
<!-- Монитор должен быть оранжевым/красным -->
```

---

## Зависимости

**Требует**: Модуль 03 (Frontend Setup)

---

## Следующий модуль

После завершения переходи к: **[05_INTERACTIVE_OBJECTS.md](05_INTERACTIVE_OBJECTS.md)**
