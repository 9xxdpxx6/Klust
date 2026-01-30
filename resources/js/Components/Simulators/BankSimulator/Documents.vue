<template>
  <TresGroup
    v-if="isModelLoaded"
    :position="position"
    :scale="scale"
    @click="onClick"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <primitive
      v-for="(instance, index) in documentInstances"
      :key="index"
      :object="instance"
      :position="[0, index * 0.02, 0]"
    />
  </TresGroup>
  <TresGroup v-else :position="position" :scale="scale">
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
import { ref, computed, shallowRef, watchEffect } from 'vue'
import { useTres } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace } from 'three'

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
const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const documentInstances = shallowRef([])
const { renderer } = useTres()

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

const cloneScene = (scene) => {
  const clone = scene.clone(true)
  configureScene(clone)
  return clone
}

try {
  const gltfPromise = useGLTF('/models/document.glb')
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
    documentInstances.value = []
    return
  }
  if (gltfScene.value !== loadedScene) {
    configureScene(loadedScene)
    gltfScene.value = loadedScene
  }
  documentInstances.value = Array.from({ length: props.count }, () => cloneScene(gltfScene.value))
  isModelLoaded.value = true
})
</script>
