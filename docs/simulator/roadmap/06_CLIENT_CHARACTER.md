# Модуль 06: Клиент (персонаж)

## Цель модуля

Создать простого 3D персонажа (клиента) сидящего напротив:
- Простая 3D модель клиента (голова + торс)
- Анимации (кивание, жесты)
- Реакция на состояние диалога

---

## Что нужно сделать

### 1. Компонент клиента

#### 1.1. Создать ClientCharacter.vue

**Файл**: `resources/js/Components/BankSimulator/ClientCharacter.vue`

**Реализация**:
```vue
<template>
  <TresGroup :position="position" :rotation-x="nodRotation">
    <!-- Голова -->
    <TresMesh :position="[0, 1.6, 0]" :rotation-y="headRotation">
      <TresSphereGeometry :args="[0.15, 16, 16]" />
      <TresMeshStandardMaterial 
        color="#F5DEB3"
        :metalness="0.1"
        :roughness="0.9"
      />
    </TresMesh>
    
    <!-- Торс (куб) -->
    <TresMesh :position="[0, 1.3, 0]">
      <TresBoxGeometry :args="[0.4, 0.6, 0.2]" />
      <TresMeshStandardMaterial 
        color="#2C3E50"
        :metalness="0.2"
        :roughness="0.8"
      />
    </TresMesh>
    
    <!-- Руки (опционально, простые цилиндры) -->
    <TresMesh :position="[-0.3, 1.3, 0]" :rotation-z="Math.PI / 4">
      <TresCylinderGeometry :args="[0.03, 0.03, 0.3, 8]" />
      <TresMeshStandardMaterial color="#F5DEB3" />
    </TresMesh>
    
    <TresMesh :position="[0.3, 1.3, 0]" :rotation-z="-Math.PI / 4">
      <TresCylinderGeometry :args="[0.03, 0.03, 0.3, 8]" />
      <TresMeshStandardMaterial color="#F5DEB3" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRenderLoop } from '@tresjs/core'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0, 0, -2]
  },
  isSpeaking: {
    type: Boolean,
    default: false
  }
})

const nodRotation = ref(0)
const headRotation = ref(0)

// Анимация кивания при разговоре
const { onLoop } = useRenderLoop()

if (props.isSpeaking) {
  onLoop(({ elapsed }) => {
    nodRotation.value = Math.sin(elapsed * 2) * 0.1 // Плавное кивание
    headRotation.value = Math.sin(elapsed * 1.5) * 0.05 // Легкий поворот головы
  })
} else {
  // Сброс анимации когда не говорит
  nodRotation.value = 0
  headRotation.value = 0
}
</script>
```

---

### 2. Интеграция в OfficeScene

#### 2.1. Обновить OfficeScene.vue

**Добавить клиента**:
```vue
<template>
  <TresCanvas>
    <!-- ... existing code ... -->
    
    <!-- Клиент напротив -->
    <ClientCharacter 
      :position="[0, 0, -2]"
      :is-speaking="isClientSpeaking"
    />
  </TresCanvas>
</template>

<script setup>
import ClientCharacter from './ClientCharacter.vue'

const isClientSpeaking = computed(() => {
  return props.sessionState?.dialogue?.current_step === 'client_speaking'
})
</script>
```

---

## Файлы для создания/изменения

### Создать:
- [ ] `resources/js/Components/BankSimulator/ClientCharacter.vue`

### Изменить:
- [ ] `resources/js/Components/BankSimulator/OfficeScene.vue` (добавить клиента)

---

## Критерии готовности

- [ ] Клиент отображается напротив стола
- [ ] Анимация кивания работает когда говорит
- [ ] Клиент реагирует на состояние диалога
- [ ] Простая модель (голова + торс) выглядит адекватно

---

## Тестирование

### Проверить визуально:

1. Клиент должен быть виден напротив стола
2. При изменении `isSpeaking` на `true` → клиент должен кивать
3. При изменении `isSpeaking` на `false` → анимация останавливается

---

## Зависимости

**Требует**: Модуль 04 (3D Scene Basic)

---

## Следующий модуль

После завершения переходи к: **[07_DIALOGUE_SYSTEM.md](07_DIALOGUE_SYSTEM.md)**
