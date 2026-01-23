<template>
  <TresGroup 
    ref="characterGroupRef"
    :position="position"
    :cast-shadow="true"
  >
    <!-- Голова -->
    <TresMesh :position="[0, 1.6, 0]" ref="headRef">
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
    
    <!-- Левая рука (цилиндр) -->
    <TresMesh :position="[-0.3, 1.3, 0]" :rotation-z="Math.PI / 4">
      <TresCylinderGeometry :args="[0.03, 0.03, 0.3, 8]" />
      <TresMeshStandardMaterial color="#F5DEB3" />
    </TresMesh>
    
    <!-- Правая рука (цилиндр) -->
    <TresMesh :position="[0.3, 1.3, 0]" :rotation-z="-Math.PI / 4">
      <TresCylinderGeometry :args="[0.03, 0.03, 0.3, 8]" />
      <TresMeshStandardMaterial color="#F5DEB3" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { shallowRef } from 'vue'
import { useLoop } from '@tresjs/core'

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

const characterGroupRef = shallowRef(null)
const headRef = shallowRef(null)

const { onBeforeRender } = useLoop()

// Анимация кивания и поворота головы при разговоре
onBeforeRender(({ elapsed }) => {
  if (!characterGroupRef.value || !headRef.value) return
  
  if (props.isSpeaking) {
    // Кивание (вращение группы по оси X)
    characterGroupRef.value.rotation.x = Math.sin(elapsed * 2) * 0.1
    
    // Легкий поворот головы (вращение головы по оси Y)
    headRef.value.rotation.y = Math.sin(elapsed * 1.5) * 0.05
  } else {
    // Сброс анимации когда не говорит
    characterGroupRef.value.rotation.x = 0
    headRef.value.rotation.y = 0
  }
})
</script>
