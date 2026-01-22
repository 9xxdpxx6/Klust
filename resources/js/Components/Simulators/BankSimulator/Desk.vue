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
// TresJS компоненты доступны глобально, импорт не требуется
</script>
