<template>
  <TresGroup :position="position">
    <TresGroup
      v-if="isModelLoaded"
      :scale="scaleArray"
    >
      <primitive
        v-for="(instance, index) in documentInstances"
        :key="index"
        :object="instance"
        :position="[0, index * 0.02, 0]"
      />
    </TresGroup>
    <TresGroup v-else :scale="scaleArray">
      <!-- Стек документов (несколько кубов) -->
      <TresMesh
        v-for="(doc, index) in documentStack"
        :key="index"
        :position="[0, index * 0.02, 0]"
      >
        <TresBoxGeometry :args="[0.2, 0.02, 0.15]" />
        <TresMeshStandardMaterial :color="doc.color" />
      </TresMesh>
    </TresGroup>
    
    <!-- Невидимый hitbox для hover/click (вне группы со scale) -->
    <TresMesh
      :position="[0, hitboxHeight / 2, 0]"
      @click="onClick"
      @pointer-enter="onHoverEnter"
      @pointer-leave="onHoverLeave"
    >
      <TresBoxGeometry :args="[0.42, hitboxHeight, 0.6]" />
      <TresMeshBasicMaterial :transparent="true" :opacity="0" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { ref, computed, shallowRef, watchEffect } from 'vue'
import { useLoop, useTres } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace, MathUtils } from 'three'

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
const targetScale = ref(1)
const isHovered = ref(false)
const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const documentInstances = shallowRef([])
const { renderer } = useTres()

// Computed scale как массив для реактивности TresGroup
const scaleArray = computed(() => [scale.value, scale.value, scale.value])

const documentStack = computed(() => {
  const colors = ['#ffffff', '#f5f5f5', '#e5e5e5']
  return Array.from({ length: props.count }, (_, i) => ({
    color: colors[i % colors.length]
  }))
})

// Высота hitbox в зависимости от количества документов
const hitboxHeight = computed(() => {
  return Math.max(0.004, props.count * 0.002 + 0.02)
})

const onHoverEnter = () => {
  targetScale.value = 1.07
  isHovered.value = true
}

const onHoverLeave = () => {
  targetScale.value = 1
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
    
    // КРИТИЧНО: Отключаем raycast на самих документах (hitbox его обрабатывает)
    // Это предотвращает дорогие расчеты raycast на сложной геометрии
    object.raycast = () => {}
    
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

const { onBeforeRender } = useLoop()

onBeforeRender(({ delta }) => {
  scale.value = MathUtils.damp(scale.value, targetScale.value, 18, delta)
})
</script>
