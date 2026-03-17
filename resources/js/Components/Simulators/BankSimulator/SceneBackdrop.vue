<script setup>
import { onBeforeUnmount, watch } from 'vue'
import { useTres } from '@tresjs/core'
import {
  Euler,
  EquirectangularReflectionMapping,
  SRGBColorSpace,
  TextureLoader,
} from 'three'

const props = defineProps({
  texturePath: {
    type: String,
    required: true,
  },
  rotation: {
    type: Array,
    default: () => [0, 0, 0],
  },
  intensity: {
    type: Number,
    default: 1,
  },
})

const { scene } = useTres()
const textureLoader = new TextureLoader()

let activeTexture = null
let previousBackground = null
let previousBackgroundIntensity = 1
let previousBackgroundRotation = new Euler()

const applyBackground = (texture) => {
  if (!scene.value || !texture) return

  if (!previousBackground) {
    previousBackground = scene.value.background
    previousBackgroundIntensity = scene.value.backgroundIntensity ?? 1
    previousBackgroundRotation.copy(scene.value.backgroundRotation ?? new Euler())
  }

  texture.mapping = EquirectangularReflectionMapping
  texture.colorSpace = SRGBColorSpace
  texture.needsUpdate = true

  scene.value.background = texture
  scene.value.backgroundIntensity = props.intensity
  scene.value.backgroundRotation.set(...props.rotation)
}

watch(
  () => props.texturePath,
  (path, _, onCleanup) => {
    if (!path) return

    let cancelled = false

    textureLoader.load(path, (texture) => {
      if (cancelled) {
        texture.dispose()
        return
      }

      if (activeTexture && activeTexture !== texture) {
        activeTexture.dispose()
      }

      activeTexture = texture
      applyBackground(texture)
    })

    onCleanup(() => {
      cancelled = true
    })
  },
  { immediate: true },
)

watch(
  () => props.rotation,
  (rotation) => {
    if (!scene.value) return
    scene.value.backgroundRotation.set(...rotation)
  },
  { deep: true },
)

watch(
  () => props.intensity,
  (intensity) => {
    if (!scene.value) return
    scene.value.backgroundIntensity = intensity
  },
  { immediate: true },
)

onBeforeUnmount(() => {
  if (scene.value) {
    scene.value.background = previousBackground
    scene.value.backgroundIntensity = previousBackgroundIntensity
    scene.value.backgroundRotation.copy(previousBackgroundRotation)
  }

  if (activeTexture) {
    activeTexture.dispose()
    activeTexture = null
  }
})
</script>

<template>
  <TresGroup />
</template>
