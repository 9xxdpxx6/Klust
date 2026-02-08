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
import { ref, shallowRef, watch, watchEffect, onMounted, onBeforeUnmount, getCurrentInstance, nextTick } from 'vue'
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
    default: '/models/characters/female1.glb'
  },
  isSpeaking: {
    type: Boolean,
    default: false
  },
  preloadedModel: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['animationFinished'])

const instance = getCurrentInstance()
const isMounted = ref(false)

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

// Флаг чтобы useGLTF не вызывался дважды
const isModelLoading = ref(false)
const hasTriedLoad = ref(false)

// Watch для предзагруженной модели
watch(() => props.preloadedModel, (preloaded) => {
  if (preloaded) {
    // Используем предзагруженную модель сразу
    if (gltfResult.value !== preloaded) {
      gltfResult.value = preloaded
    }
    isModelLoading.value = false
    hasTriedLoad.value = true
    
    // Сразу проверяем и устанавливаем isModelLoaded если модель готова
    const loadedScene = preloaded?.scene?.value ?? preloaded?.scene ?? null
    const hasError = preloaded?.error?.value ?? preloaded?.error ?? null
    
    if (!hasError && loadedScene) {
      if (gltfScene.value !== loadedScene) {
        configureScene(loadedScene)
        gltfScene.value = loadedScene
        
        // Инициализируем анимации сразу если они еще не инициализированы
        if (!animationMixer.value) {
          const animations = preloaded?.animations?.value ?? preloaded?.animations ?? []
          if (animations && animations.length > 0) {
            const sanitizedAnimations = animations.map((clip) => {
              const tracks = clip.tracks.filter((track) => !track.name.endsWith('.position'))
              return new AnimationClip(clip.name, clip.duration, tracks)
            })
            const mixer = new AnimationMixer(loadedScene)
            
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
            // Play initial animation once mixer is ready (используем nextTick чтобы убедиться что actions установлены)
            nextTick(() => {
              playAnimation(props.animation)
            })
          }
        }
      }
      isModelLoaded.value = true
    }
  }
}, { immediate: true })


// Используем watch вместо watchEffect для более точного контроля
watch(() => gltfResult.value, (result, oldResult) => {
  // Избегаем повторных срабатываний если результат не изменился и модель уже загружена
  if (result === oldResult && gltfScene.value && isModelLoaded.value) return
  
  if (!result) {
    // Не сбрасываем isModelLoaded если модель уже была загружена
    if (!gltfScene.value) {
      isModelLoaded.value = false
    }
    return
  }
  
  const loadedScene = result?.scene?.value ?? result?.scene ?? null
  const hasError = result?.error?.value ?? result?.error ?? null
  
  if (hasError || !loadedScene) {
    // Не сбрасываем isModelLoaded если модель уже была загружена
    if (!gltfScene.value) {
    isModelLoaded.value = false
    }
    return
  }
  
  // Обновляем только если сцена изменилась
  if (gltfScene.value !== loadedScene) {
    configureScene(loadedScene)
    gltfScene.value = loadedScene
    isModelLoaded.value = true
  } else if (!isModelLoaded.value) {
    // Если сцена уже установлена но isModelLoaded false, устанавливаем в true
  isModelLoaded.value = true
  }
}, { immediate: true, deep: false })

// Используем watch для более точного контроля инициализации анимаций
watch(() => gltfScene.value, (scene, oldScene) => {
  // Избегаем повторных срабатываний если сцена не изменилась
  if (scene === oldScene && animationMixer.value) return
  
  // Инициализируем анимации только один раз когда сцена готова
  if (animationMixer.value || !scene || !gltfResult.value) return
  
  const animations = gltfResult.value?.animations?.value ?? gltfResult.value?.animations ?? []
  if (!animations || animations.length === 0) return
  
  const sanitizedAnimations = animations.map((clip) => {
    const tracks = clip.tracks.filter((track) => !track.name.endsWith('.position'))
    return new AnimationClip(clip.name, clip.duration, tracks)
  })
  const mixer = new AnimationMixer(scene)
  
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
            // Play initial animation once mixer is ready (используем nextTick чтобы убедиться что actions установлены)
            nextTick(() => {
  playAnimation(props.animation)
})
}, { immediate: true })

const playAnimation = (name) => {
  const actions = animationActions.value
  if (!actions || Object.keys(actions).length === 0) {
    // Если actions еще не готовы, пробуем через небольшую задержку
    setTimeout(() => {
      playAnimation(name)
    }, 50)
    return
  }
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
  isMounted.value = true
  
  // Загружаем модель если предзагруженная не передана (только один раз)
  if (!props.preloadedModel && !gltfResult.value && !isModelLoading.value && !hasTriedLoad.value) {
    isModelLoading.value = true
    hasTriedLoad.value = true
    try {
      const gltfPromise = useGLTF(props.modelPath)
      if (gltfPromise && typeof gltfPromise.then === 'function') {
        gltfPromise
          .then((result) => {
            // Проверяем что компонент еще смонтирован и модель все еще не предзагружена
            if (isMounted.value && !props.preloadedModel && gltfResult.value !== result) {
              gltfResult.value = result
            }
            isModelLoading.value = false
          })
          .catch(() => {
            isModelLoading.value = false
            // SAFE FALLBACK
          })
      } else {
        // Если useGLTF вернул результат синхронно (кеш)
        if (isMounted.value && !props.preloadedModel && gltfResult.value !== gltfPromise) {
          gltfResult.value = gltfPromise
        }
        isModelLoading.value = false
      }
    } catch (error) {
      isModelLoading.value = false
      // SAFE FALLBACK
    }
  }
  
  rafId = window.requestAnimationFrame(onFrame)
})

onBeforeUnmount(() => {
  isMounted.value = false
  if (rafId) {
    window.cancelAnimationFrame(rafId)
  }
  rafId = null
  lastTimestamp = null
})
</script>
