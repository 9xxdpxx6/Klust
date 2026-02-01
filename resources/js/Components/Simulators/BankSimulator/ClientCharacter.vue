<template>
  <TresGroup
    v-if="isModelLoaded"
    :position="position"
    :rotation="rotation"
    :scale="scale"
    :cast-shadow="true"
  >
    <primitive :object="gltfScene" />
  </TresGroup>

  <TresGroup
    v-else
    ref="characterGroupRef"
    :position="position"
    :rotation="rotation"
    :scale="scale"
    :cast-shadow="true"
  >
    <!-- Голова -->
    <TresMesh :position="[0, 1.6, 0]" ref="headRef">
      <TresSphereGeometry :args="[0.15, 16, 16]" />
      <TresMeshStandardMaterial 
        color="#F5DEB3"
        :metalness="0.1"
        :roughness="0.9"
      />
    </TresMesh>
    
    <!-- Торс (куб) -->
    <TresMesh :position="[0, 1.3, 0]">
      <TresBoxGeometry :args="[0.4, 0.6, 0.2]" />
      <TresMeshStandardMaterial 
        color="#2C3E50"
        :metalness="0.2"
        :roughness="0.8"
      />
    </TresMesh>
    
    <!-- Левая рука (цилиндр) -->
    <TresMesh :position="[-0.3, 1.3, 0]" :rotation-z="Math.PI / 4">
      <TresCylinderGeometry :args="[0.03, 0.03, 0.3, 8]" />
      <TresMeshStandardMaterial color="#F5DEB3" />
    </TresMesh>
    
    <!-- Правая рука (цилиндр) -->
    <TresMesh :position="[0.3, 1.3, 0]" :rotation-z="-Math.PI / 4">
      <TresCylinderGeometry :args="[0.03, 0.03, 0.3, 8]" />
      <TresMeshStandardMaterial color="#F5DEB3" />
    </TresMesh>
  </TresGroup>
</template>

<script setup>
import { ref, shallowRef, watch, watchEffect, onMounted, onBeforeUnmount } from 'vue'
import { useGLTF } from '@tresjs/cientos'
import {
  AnimationClip,
  AnimationMixer,
  LinearFilter,
  LinearMipMapLinearFilter,
  LoopOnce,
  LoopRepeat,
  SRGBColorSpace
} from 'three'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0, 0, -2]
  },
  rotation: {
    type: Array,
    default: () => [0, 0, 0]
  },
  scale: {
    type: Array,
    default: () => [1, 1, 1]
  },
  animation: {
    type: String,
    default: 'idle'
  },
  modelPath: {
    type: String,
    default: '/models/characters/male1.glb'
  },
  isSpeaking: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['animationFinished'])

const characterGroupRef = shallowRef(null)
const headRef = shallowRef(null)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const isModelLoaded = ref(false)
const animationMixer = shallowRef(null)
const animationActions = shallowRef({})
const activeAction = shallowRef(null)
let rafId = null
let lastTimestamp = null

const configureScene = (scene) => {
  scene.traverse((object) => {
    if (!object.isMesh) return
    object.castShadow = true
    object.receiveShadow = true
    const materials = Array.isArray(object.material) ? object.material : [object.material]
    materials.forEach((material) => {
      if (!material) return
      const maps = [material.map, material.emissiveMap].filter(Boolean)
      maps.forEach((map) => {
        map.colorSpace = SRGBColorSpace
        map.anisotropy = 1
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
  const gltfPromise = useGLTF(props.modelPath)
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

watchEffect(() => {
  if (animationMixer.value || !gltfScene.value) return
  const animations = gltfResult.value?.animations?.value ?? gltfResult.value?.animations ?? []
  if (!animations || animations.length === 0) return
  const sanitizedAnimations = animations.map((clip) => {
    const tracks = clip.tracks.filter((track) => !track.name.endsWith('.position'))
    return new AnimationClip(clip.name, clip.duration, tracks)
  })
  const mixer = new AnimationMixer(gltfScene.value)
  
  // Listen for animation finished events
  mixer.addEventListener('finished', (e) => {
    const finishedClipName = e.action.getClip().name.toLowerCase()
    emit('animationFinished', finishedClipName)
  })
  
  animationMixer.value = mixer
  const actions = {}
  sanitizedAnimations.forEach((clip) => {
    if (!clip?.name) return
    const action = mixer.clipAction(clip)
    actions[clip.name.toLowerCase()] = action
  })
  animationActions.value = actions
  // Play initial animation once mixer is ready
  playAnimation(props.animation)
})

const playAnimation = (name) => {
  const actions = animationActions.value
  if (!actions) return
  const key = (name || '').toLowerCase()
  const nextAction = actions[key] || actions.idle
  if (!nextAction || activeAction.value === nextAction) return

  const fadeDuration = 0.2
  const prevAction = activeAction.value

  if (key === 'sit_down' || key === 'stand_up') {
    nextAction.setLoop(LoopOnce, 1)
    nextAction.clampWhenFinished = true
  } else {
    nextAction.setLoop(LoopRepeat, Infinity)
    nextAction.clampWhenFinished = false
  }

  nextAction.reset()
  nextAction.setEffectiveTimeScale(1)
  nextAction.setEffectiveWeight(1)
  nextAction.play()

  if (prevAction && prevAction !== nextAction) {
    nextAction.crossFadeFrom(prevAction, fadeDuration, true)
  } else {
    nextAction.fadeIn(fadeDuration)
  }

  activeAction.value = nextAction
}

watch(
  () => props.animation,
  (value) => {
    if (!animationMixer.value) return
    playAnimation(value)
  }
)

const onFrame = (timestamp) => {
  if (lastTimestamp === null) {
    lastTimestamp = timestamp
  }
  const delta = (timestamp - lastTimestamp) / 1000
  lastTimestamp = timestamp

  if (animationMixer.value) {
    animationMixer.value.update(delta)
  }

  if (characterGroupRef.value && headRef.value && !isModelLoaded.value) {
    if (props.isSpeaking) {
      characterGroupRef.value.rotation.x = Math.sin(timestamp * 0.002) * 0.1
      headRef.value.rotation.y = Math.sin(timestamp * 0.0015) * 0.05
    } else {
      characterGroupRef.value.rotation.x = 0
      headRef.value.rotation.y = 0
    }
  }

  rafId = window.requestAnimationFrame(onFrame)
}

onMounted(() => {
  rafId = window.requestAnimationFrame(onFrame)
})

onBeforeUnmount(() => {
  if (rafId) {
    window.cancelAnimationFrame(rafId)
  }
  rafId = null
  lastTimestamp = null
})
</script>
