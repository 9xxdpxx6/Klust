<template>
  <TresGroup
    v-if="isModelLoaded"
    :position="position"
    :scale="scale"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <primitive :object="gltfScene" />
  </TresGroup>
  <TresMesh 
    v-else
    :position="position"
    :scale="scale"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <TresBoxGeometry :args="[0.2, 0.05, 0.15]" />
    <TresMeshStandardMaterial 
      :color="isHovered ? '#86efac' : '#22c55e'"
      :metalness="0.2"
      :roughness="0.9"
    />
  </TresMesh>
</template>

<script setup>
import { ref, shallowRef, watchEffect } from 'vue'
import { useTres } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace } from 'three'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0.8, 0.9, -0.5]
  }
})

const emit = defineEmits(['click'])

const scale = ref(1)
const isHovered = ref(false)
const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const { renderer } = useTres()

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
  const gltfPromise = useGLTF('/models/cactus.glb')
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
</script>
