<template>
  <TresMesh 
    :position="position"
    :scale="scale"
    :rotation="[0, 0, phoneRotation]"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <TresBoxGeometry :args="[0.15, 0.05, 0.2]" />
    <TresMeshStandardMaterial 
      :color="phoneColor"
      :emissive="emissiveColor"
      :emissiveIntensity="emissiveIntensity"
    />
  </TresMesh>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useLoop } from '@tresjs/core'

const props = defineProps({
  position: {
    type: Array,
    default: () => [-0.5, 0.9, -0.5]
  },
  isRinging: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const scale = ref(1)
const phoneRotation = ref(0)
const isHovered = ref(false)

const phoneColor = computed(() => {
  if (props.isRinging) return '#4ade80'
  if (isHovered.value) return '#9ca3af'
  return '#cccccc'
})

const emissiveColor = computed(() => {
  return props.isRinging ? '#4ade80' : '#000000'
})

const emissiveIntensity = computed(() => {
  return props.isRinging ? 0.5 : 0
})

const onHoverEnter = () => {
  scale.value = 1.15
  isHovered.value = true
}

const onHoverLeave = () => {
  scale.value = 1
  isHovered.value = false
}

const onClick = () => {
  emit('click')
}

// Анимация вибрации если звонит
const { onBeforeRender } = useLoop()

onBeforeRender(({ elapsed }) => {
  if (props.isRinging) {
    phoneRotation.value = Math.sin(elapsed * 10) * 0.1
  } else {
    phoneRotation.value = 0
  }
})
</script>
