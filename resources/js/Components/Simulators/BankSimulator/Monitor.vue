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
// TresJS компоненты доступны глобально, импорт не требуется

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
