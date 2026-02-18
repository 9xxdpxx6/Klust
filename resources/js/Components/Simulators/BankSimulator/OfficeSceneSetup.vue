<template>
  <!-- Камера -->
  <TresPerspectiveCamera 
    :position="devMode ? DEV_CAMERA_POSITION : PROD_CAMERA_POSITION" 
    :fov="devMode ? DEV_CAMERA_FOV : PROD_CAMERA_FOV"
    :near="0.1"
    :far="1000"
  />
  
  <!-- DEV: OrbitControls для свободного просмотра -->
  <OrbitControlsDev v-if="devMode" />
  
  <!-- PROD: Ограниченное движение камеры за курсором -->
  <HeadrestCamera 
    v-else
    :position="PROD_CAMERA_POSITION"
    :fov="PROD_CAMERA_FOV"
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

const DEV_CAMERA_POSITION = [0, 1.6, 0]
const PROD_CAMERA_POSITION = [-1.1, 1.07, -0.75]
const DEV_CAMERA_FOV = 75
const PROD_CAMERA_FOV = 70
</script>
