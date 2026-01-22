<template>
  <TresGroup :position="position" :scale="scale">
    <!-- Стек документов (несколько кубов) -->
    <TresMesh
      v-for="(doc, index) in documentStack"
      :key="index"
      :position="[0, index * 0.02, 0]"
      @click="onClick"
      @pointer-enter="onHoverEnter"
      @pointer-leave="onHoverLeave"
    >
      <TresBoxGeometry :args="[0.2, 0.02, 0.15]" />
      <TresMeshStandardMaterial :color="doc.color" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0.5, 0.9, -0.5]
  },
  count: {
    type: Number,
    default: 3
  }
})

const emit = defineEmits(['click'])

const scale = ref(1)
const isHovered = ref(false)

const documentStack = computed(() => {
  const colors = ['#ffffff', '#f5f5f5', '#e5e5e5']
  return Array.from({ length: props.count }, (_, i) => ({
    color: colors[i % colors.length]
  }))
})

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
