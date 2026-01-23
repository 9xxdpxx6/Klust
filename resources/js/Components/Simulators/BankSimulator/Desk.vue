<template>
  <primitive
    v-if="isModelLoaded"
    :object="gltfScene"
    :position="position"
    :rotation="rotation"
    :scale="scale"
  />
  <TresGroup
    v-else
    :position="position"
    :rotation="rotation"
    :scale="scale"
  >

    <!-- Столешница -->
    <TresMesh :position="[0, 0.75, 0]" :cast-shadow="true" :receive-shadow="true">
      <TresBoxGeometry :args="[2, 0.04, 0.8]" />
      <TresMeshStandardMaterial
        attach="material-0"
        color="#5a4632"
        :roughness="0.7"
      />
      <TresMeshStandardMaterial
        attach="material-1"
        color="#5a4632"
        :roughness="0.7"
      />
      <TresMeshStandardMaterial
        attach="material-2"
        color="#c8b29a"   
        :roughness="0.8"
      />
      <TresMeshStandardMaterial
        attach="material-3"
        color="#c8b29a"
        :roughness="0.8"
      />
      <TresMeshStandardMaterial
        attach="material-4"
        color="#5a4632"
        :roughness="0.7"
      />
      <TresMeshStandardMaterial
        attach="material-5"
        color="#5a4632"
        :roughness="0.7"
      />
    </TresMesh>


    <!-- Левая боковая опора -->
    <TresMesh :position="[-0.95, 0.375, 0]" :cast-shadow="true">
      <TresBoxGeometry :args="[0.04, 0.75, 0.8]" />
      <TresMeshStandardMaterial
        color="#c8b29a"
        :roughness="0.8"
      />
    </TresMesh>

    <!-- Правая боковая опора -->
    <TresMesh :position="[0.95, 0.375, 0]" :cast-shadow="true">
      <TresBoxGeometry :args="[0.04, 0.75, 0.8]" />
      <TresMeshStandardMaterial
        color="#c8b29a"
        :roughness="0.8"
      />
    </TresMesh>

    <!-- Передняя панель -->
    <TresMesh :position="[0, 0.57, 0.38]" :cast-shadow="true">
      <TresBoxGeometry :args="[1.92, 0.35, 0.02]" />
      <TresMeshStandardMaterial
        color="#c8b29a"
        :roughness="0.85"
      />
    </TresMesh>

  </TresGroup>
</template>


<script setup>
import { ref, shallowRef, watchEffect } from 'vue'
import { useTres } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace } from 'three'

defineProps({
  position: {
    type: Array,
    default: () => [0, 0, 0]
  },
  rotation: {
    type: Array,
    default: () => [0, 0, 0]
  },
  scale: {
    type: Array,
    default: () => [1, 1, 1]
  }
})

const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const { renderer } = useTres()

try {
  const gltfPromise = useGLTF('/models/desk.glb')
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
    loadedScene.traverse((object) => {
      if (object.isMesh) {
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
      }
    })
    gltfScene.value = loadedScene
  }

  isModelLoaded.value = true
})
</script>
