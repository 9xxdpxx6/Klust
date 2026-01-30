<template>
  <TresGroup
    v-if="isModelLoaded"
    :position="position"
    :scale="computedScale"
    :rotation="computedRotation"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <primitive :object="gltfScene" />
  </TresGroup>
  <TresMesh 
    v-else
    :position="position"
    :scale="computedScale"
    :rotation="computedRotation"
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
import { ref, computed, shallowRef, watchEffect } from 'vue'
import { useLoop, useTres } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace } from 'three'

const props = defineProps({
  position: {
    type: Array,
    default: () => [-0.5, 0.9, -0.5]
  },
  baseScale: {
    type: Array,
    default: () => [0.01, 0.01, 0.01]
  },
  baseRotation: {
    type: Array,
    default: () => [0, 0, 0]
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
const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const { renderer } = useTres()

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

const computedScale = computed(() => {
  return props.baseScale.map((value) => value * scale.value)
})

const computedRotation = computed(() => {
  return [
    props.baseRotation[0],
    props.baseRotation[1],
    props.baseRotation[2] + phoneRotation.value
  ]
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

const configureScene = (scene) => {
  scene.traverse((object) => {
    if (!object.isMesh) return
    object.castShadow = true
    object.receiveShadow = true
    const materials = Array.isArray(object.material) ? object.material : [object.material]
    materials.forEach((material) => {
      if (!material) return
      const maxAnisotropy = renderer.value?.capabilities?.getMaxAnisotropy?.() ?? 1
      const maps = [material.map, material.emissiveMap].filter(Boolean)
      maps.forEach((map) => {
        map.colorSpace = SRGBColorSpace
        map.anisotropy = maxAnisotropy
        map.generateMipmaps = true
        map.minFilter = LinearMipMapLinearFilter
        map.magFilter = LinearFilter
        map.needsUpdate = true
      })
      material.needsUpdate = true
    })
  })
}

try {
  const gltfPromise = useGLTF('/models/phone.glb')
  if (gltfPromise && typeof gltfPromise.then === 'function') {
    gltfPromise
      .then((result) => {
        gltfResult.value = result
      })
      .catch(() => {
        // SAFE FALLBACK
      })
  }
} catch (error) {
  // SAFE FALLBACK
}

watchEffect(() => {
  const loadedScene = gltfResult.value?.scene?.value ?? gltfResult.value?.scene ?? null
  const hasError = gltfResult.value?.error?.value ?? gltfResult.value?.error ?? null
  if (hasError || !loadedScene) {
    isModelLoaded.value = false
    return
  }
  if (gltfScene.value !== loadedScene) {
    configureScene(loadedScene)
    gltfScene.value = loadedScene
  }
  isModelLoaded.value = true
})

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
