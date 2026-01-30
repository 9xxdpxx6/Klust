<template>
  <TresGroup v-if="isModelLoaded" :position="position" :rotation="rotation" :scale="scale">
    <primitive :object="gltfScene" />
  </TresGroup>
  <TresGroup v-else :position="position" :rotation="rotation">
    <!-- Экран -->
    <TresMesh :position="[0, 0, 0]">
      <TresBoxGeometry :args="[0.6, 0.4, 0.05]" />
      <TresMeshStandardMaterial :color="color" />
    </TresMesh>
    
    <!-- Подставка -->
    <TresMesh :position="[0, -0.25, 0]">
      <TresBoxGeometry :args="[0.2, 0.1, 0.2]" />
      <TresMeshStandardMaterial color="#333333" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { ref, shallowRef, watchEffect } from 'vue'
import { useTres } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace } from 'three'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0, 1.2, -0.8]
  },
  rotation: {
    type: Array,
    default: () => [0, 0, 0]
  },
  color: {
    type: String,
    default: '#1e40af'
  }
})

const scale = ref([1, 1, 1])
const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const { renderer } = useTres()

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
  const gltfPromise = useGLTF('/models/laptop.glb')
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
