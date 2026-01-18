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
