<template>
  <TresGroup :position="position" :rotation="rotation" :scale="scale">
    <primitive v-if="roomScene" 
        :object="roomScene" 
        :position="[0, 0.02, 0]" 
        :rotation="[0, 0, 0]" 
    />
    <primitive v-if="ceilingScene" :object="ceilingScene" />
    <primitive v-if="windowScene"
        :object="windowScene" 
        :position="[2.675, 0.15, 2.72]" 
        :rotation="[0, Math.PI, 0]" 
    />
    <primitive v-if="doorScene" 
        :object="doorScene" 
        :position="[3.95, 0, 0.5]" 
        :rotation="[0, Math.PI, 0]" 
    />
    <primitive v-if="sofaScene" 
        :object="sofaScene" 
        :position="[1.3, 0, -1]" 
        :rotation="[0, 0, 0]" 
    />
    <primitive v-if="palmLeftScene" 
        :object="palmLeftScene" 
        :position="[-3.35, 0, 2.5]" 
        :rotation="[0, 10, 0]" 
    />
    <primitive v-if="palmRightScene" 
        :object="palmRightScene" 
        :position="[3.35, 0, 2.4]" 
        :rotation="[0, 0, 0]" 
        :scale="[1.3, 1.3, 1.3]"
    />
    <primitive v-if="plantScene" 
        :object="plantScene" 
        :position="[3.35, 0, -1.5]" 
        :rotation="[0, Math.PI, 0]" 
    />
  </TresGroup>
</template>

<script setup>
import { shallowRef } from 'vue'
import { useTres } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace } from 'three'

const props = defineProps({
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
  },
  windowPosition: {
    type: Array,
    default: () => [2.8, 0, 0]
  },
  doorPosition: {
    type: Array,
    default: () => [0, 0, -4]
  },
  palmLeftPosition: {
    type: Array,
    default: () => [2.4, 0, 2.0]
  },
  palmRightPosition: {
    type: Array,
    default: () => [2.4, 0, -2.0]
  },
  plantPosition: {
    type: Array,
    default: () => [-1.6, 0, -3.5]
  },
  sofaPosition: {
    type: Array,
    default: () => [0, 0, 0]
  }
})

const { renderer } = useTres()

const roomScene = shallowRef(null)
const ceilingScene = shallowRef(null)
const windowScene = shallowRef(null)
const doorScene = shallowRef(null)
const sofaScene = shallowRef(null)
const palmLeftScene = shallowRef(null)
const palmRightScene = shallowRef(null)
const plantScene = shallowRef(null)

const configureScene = (scene, options = { castShadow: true, receiveShadow: true }) => {
  scene.traverse((object) => {
    if (!object.isMesh) return
    object.castShadow = options.castShadow
    object.receiveShadow = options.receiveShadow
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

const loadModel = (path, targetRef, options) => {
  try {
    const gltfPromise = useGLTF(path)
    if (gltfPromise && typeof gltfPromise.then === 'function') {
      gltfPromise
        .then((result) => {
          const loadedScene = result?.scene?.value ?? result?.scene ?? null
          if (!loadedScene) return
          configureScene(loadedScene, options)
          targetRef.value = loadedScene
        })
        .catch(() => {
          // SAFE FALLBACK
        })
    }
  } catch (error) {
    // SAFE FALLBACK
  }
}

loadModel('/models/office/room.glb', roomScene, { castShadow: true, receiveShadow: true })
loadModel('/models/office/ceiling.glb', ceilingScene, { castShadow: false, receiveShadow: false })
loadModel('/models/office/window.glb', windowScene, { castShadow: true, receiveShadow: true })
loadModel('/models/office/door.glb', doorScene, { castShadow: true, receiveShadow: true })
loadModel('/models/office/sofa.glb', sofaScene, { castShadow: true, receiveShadow: true })
loadModel('/models/office/palm.glb', palmLeftScene, { castShadow: true, receiveShadow: true })
loadModel('/models/office/palm.glb', palmRightScene, { castShadow: true, receiveShadow: true })
loadModel('/models/office/plant.glb', plantScene, { castShadow: true, receiveShadow: true })

const windowPosition = props.windowPosition
const doorPosition = props.doorPosition
const sofaPosition = props.sofaPosition
const palmLeftPosition = props.palmLeftPosition
const palmRightPosition = props.palmRightPosition
const plantPosition = props.plantPosition
</script>
