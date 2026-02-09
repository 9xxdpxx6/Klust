<template>
  <!-- Камера -->
  <TresPerspectiveCamera 
    :position="devMode ? [0, 1.6, 0] : [-1.1, 1.07, -0.75]" 
    :fov="devMode ? 75 : 70"
    :near="0.1"
    :far="1000"
  />
  
  <!-- DEV: OrbitControls для свободного просмотра -->
  <OrbitControlsDev v-if="devMode" />
  
  <!-- PROD: Ограниченное движение камеры за курсором -->
  <HeadrestCamera 
    v-else
    :position="[-1.1, 1.07, -0.75]"
    :fov="70"
    :max-yaw-left="5"
    :max-yaw-right="20"
    :max-pitch-up="15"
    :max-pitch-down="25"
    :speed="4"
    :base-yaw="Math.PI - 0.2"
    :base-pitch="0"
  />
  
  <!-- Освещение -->
  <TresAmbientLight :intensity="0.3" />
  <TresHemisphereLight 
    :color="'#ffffff'"
    :ground-color="'#bfc8d3'"
    :intensity="0.25"
  />
  <TresDirectionalLight 
    :ref="(el) => { if (directionalLightRef) directionalLightRef.value = el }"
    :position="[-5, 15, -3]" 
    :intensity="2.5"
    :cast-shadow="true"
  />
  
  <!-- Slot для содержимого сцены -->
  <slot />
</template>

<script setup>
import OrbitControlsDev from './OrbitControlsDev.vue'
import HeadrestCamera from './HeadrestCamera.vue'

const props = defineProps({
  devMode: {
    type: Boolean,
    default: false
  },
  directionalLightRef: {
    type: Object,
    required: true
  }
})
</script>
