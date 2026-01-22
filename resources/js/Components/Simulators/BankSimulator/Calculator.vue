<template>
  <TresMesh 
    :position="position"
    :scale="scale"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <TresBoxGeometry :args="[0.2, 0.05, 0.15]" />
    <TresMeshStandardMaterial 
      :color="isHovered ? '#fbbf24' : '#f59e0b'"
      :metalness="0.3"
      :roughness="0.7"
    />
  </TresMesh>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0.8, 0.9, -0.5]
  }
})

const emit = defineEmits(['click'])

const scale = ref(1)
const isHovered = ref(false)

const onHoverEnter = () => {
  scale.value = 1.1
  isHovered.value = true
}

const onHoverLeave = () => {
  scale.value = 1
  isHovered.value = false
}

const onClick = () => {
  emit('click')
}
</script>
