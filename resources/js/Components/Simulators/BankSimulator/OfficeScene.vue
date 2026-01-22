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
    
    <!-- TODO: DEV ONLY - OrbitControls для 360 просмотра (временно для разработки, убрать в продакшене) -->
    <OrbitControlsDev />
    
    <!-- Освещение -->
    <TresAmbientLight :intensity="0.6" />
    <TresDirectionalLight 
      :position="[5, 10, 5]" 
      :intensity="0.8"
      :cast-shadow="true"
    />
    
    <!-- Пол -->
    <TresMesh :position="[0, 0, -5]" :rotation-x="-Math.PI / 2" :receive-shadow="true">
      <TresPlaneGeometry :args="[20, 20]" />
      <TresMeshStandardMaterial color="#cccccc" />
    </TresMesh>
    
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
import { TresCanvas } from '@tresjs/core'
import Desk from './Desk.vue'
import Monitor from './Monitor.vue'
// TODO: DEV ONLY - Временное решение для разработки, убрать в продакшене
import OrbitControlsDev from './OrbitControlsDev.vue'

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
  height: 100%;
  display: block;
  margin: 0;
  padding: 0;
}
</style>
