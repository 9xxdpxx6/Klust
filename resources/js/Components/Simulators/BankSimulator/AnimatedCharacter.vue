<template>
  <TresGroup 
    v-if="isModelLoaded && visible"
    :position="controller.position.value"
    :rotation="controller.rotation.value"
    :cast-shadow="true"
  >
    <primitive :object="gltfScene" />
  </TresGroup>
</template>

<script setup>
import { ref, computed, watch, shallowRef, watchEffect, onMounted, onUnmounted } from 'vue'
import { useTres, useLoop } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace, AnimationMixer } from 'three'
import { useCharacterController, CharacterState } from '@/Composables/Simulators/BankSimulator/useCharacterController.js'

const props = defineProps({
  modelPath: {
    type: String,
    required: true
  },
  spawnPosition: {
    type: Array,
    default: () => [0, 0, -4.2]
  },
  targetChair: {
    type: Array,
    default: () => [-1.3, 0, 0.4]
  },
  visible: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['sitting-complete', 'standing-complete', 'despawned'])

// Загрузка модели
const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const { renderer } = useTres()

// AnimationMixer и анимации
const mixer = ref(null)
const animations = ref([])
const actions = ref({})

// Референсы для отслеживания одноразовых анимаций
const sitDownActionRef = ref(null)
const standUpActionRef = ref(null)

// Контроллер персонажа
const controller = useCharacterController(props.spawnPosition, props.targetChair)

// Настройка сцены
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

// Загрузка модели
try {
  const gltfPromise = useGLTF(props.modelPath)
  if (gltfPromise && typeof gltfPromise.then === 'function') {
    gltfPromise
      .then((result) => {
        gltfResult.value = result
      })
      .catch((error) => {
        console.error('Failed to load character model:', error)
      })
  } else {
    gltfResult.value = gltfPromise
  }
} catch (error) {
  console.error('Error loading character model:', error)
}

// Обработка загруженной модели
watchEffect(() => {
  const result = gltfResult.value?.state?.value ?? gltfResult.value
  const loadedScene = result?.scene?.value ?? result?.scene ?? null
  const hasError = result?.error?.value ?? result?.error ?? null
  
  if (hasError || !loadedScene) {
    isModelLoaded.value = false
    return
  }
  
  if (gltfScene.value !== loadedScene) {
    configureScene(loadedScene)
    gltfScene.value = loadedScene
    
    // Создаем AnimationMixer и инициализируем анимации
    if (loadedScene && !mixer.value) {
      mixer.value = new AnimationMixer(loadedScene)
      
      // Получаем анимации из результата
      const loadedAnimations = result?.animations || []
      if (Array.isArray(loadedAnimations) && loadedAnimations.length > 0) {
        animations.value = loadedAnimations
        
        // Создаем actions для каждой анимации
        const newActions = {}
        loadedAnimations.forEach((clip) => {
          if (clip && clip.name) {
            const action = mixer.value.clipAction(clip)
            newActions[clip.name] = action
          }
        })
        actions.value = newActions
      }
    }
  }
  
  isModelLoaded.value = true
})

// Инициализация анимаций при загрузке
watch(() => Object.keys(actions.value).length, (count) => {
  if (count === 0) return
  
  // Настраиваем все анимации
  Object.values(actions.value).forEach((action) => {
    if (action) {
      action.setLoop(1) // LoopRepeat по умолчанию
      action.clampWhenFinished = false
    }
  })
}, { immediate: true })

// Управление анимациями в зависимости от состояния
watch(() => controller.state.value, (newState, oldState) => {
  console.log('Character state changed:', oldState, '->', newState)
  if (!actions.value || Object.keys(actions.value).length === 0 || !mixer.value) {
    console.warn('Actions not ready or mixer not initialized')
    return
  }
  
  switch (newState) {
    case CharacterState.IDLE:
      controller.playAnimation(actions.value, 'idle', true)
      break
      
    case CharacterState.WALKING_TO_CHAIR:
      controller.playAnimation(actions.value, 'walk', true)
      break
      
    case CharacterState.SITTING_DOWN:
      {
        // Сначала останавливаем walk анимацию, если она еще играет
        const walkAction = actions.value.walk
        if (walkAction && walkAction.isRunning()) {
          walkAction.stop()
          walkAction.fadeOut(0.1)
        }
        
        const action = controller.playAnimation(actions.value, 'sit_down', false)
        if (action) {
          // Убеждаемся, что анимация одноразовая
          action.setLoop(0) // LoopOnce = 0
          action.clampWhenFinished = true
          action.paused = false
          // Сохраняем ссылку на action для проверки завершения
          sitDownActionRef.value = action
          console.log('sit_down action started, loop:', action.loop, 'clampWhenFinished:', action.clampWhenFinished)
        }
      }
      break
      
    case CharacterState.SITTING:
      {
        // Останавливаем sit_down, если еще играет
        const sitDownAction = actions.value.sit_down
        if (sitDownAction && sitDownAction.isRunning()) {
          sitDownAction.stop()
          sitDownAction.fadeOut(0.1)
        }
        // Воспроизводим sit (зацикленную)
        controller.playAnimation(actions.value, 'sit', true)
      }
      break
      
    case CharacterState.STANDING_UP:
      {
        const action = controller.playAnimation(actions.value, 'stand_up', false)
        if (action) {
          action.setLoop(0) // LoopOnce
          action.clampWhenFinished = true
          // Сохраняем ссылку на action для проверки завершения
          standUpActionRef.value = action
        }
      }
      break
      
    case CharacterState.WALKING_AWAY:
      controller.playAnimation(actions.value, 'walk', true)
      break
      
    case CharacterState.DESPAWNED:
      emit('despawned')
      break
  }
}, { immediate: true })

// Обновление движения и анимаций каждый кадр
const { onBeforeRender } = useLoop()

onBeforeRender(({ elapsed, delta }) => {
  if (!mixer.value || !isModelLoaded.value) return
  
  // Обновляем AnimationMixer
  mixer.value.update(delta)
  
  // Проверяем завершение одноразовых анимаций
  if (sitDownActionRef.value) {
    const action = sitDownActionRef.value
    const clip = action.getClip()
    if (clip) {
      // Для одноразовой анимации с clampWhenFinished время зажимается на duration
      // Проверяем, что время достигло или превысило duration
      // Используем небольшую погрешность для надежности
      if (action.time >= clip.duration - 0.05) {
        console.log('sit_down animation finished, time:', action.time, 'duration:', clip.duration)
        // Останавливаем анимацию явно
        if (action.isRunning()) {
          action.paused = true
        }
        sitDownActionRef.value = null
        controller.onAnimationComplete('sit_down')
        emit('sitting-complete')
      }
    }
  }
  
  if (standUpActionRef.value) {
    const action = standUpActionRef.value
    const clip = action.getClip()
    if (clip) {
      if (action.time >= clip.duration - 0.05) {
        console.log('stand_up animation finished, time:', action.time, 'duration:', clip.duration)
        if (action.isRunning()) {
          action.paused = true
        }
        standUpActionRef.value = null
        controller.onAnimationComplete('stand_up')
        emit('standing-complete')
      }
    }
  }
  
  // Обновляем движение персонажа только если он в состоянии движения И еще не достиг цели
  // Это предотвращает движение после достижения цели
  const currentState = controller.state.value
  const isWalking = currentState === CharacterState.WALKING_TO_CHAIR || currentState === CharacterState.WALKING_AWAY
  if (isWalking) {
    controller.updateMovement(delta)
  }
})

// Очистка при размонтировании
onUnmounted(() => {
  try {
    if (mixer.value) {
      // Останавливаем все анимации
      Object.values(actions.value).forEach((action) => {
        if (action && typeof action.stop === 'function') {
          try {
            action.stop()
          } catch (e) {
            // Игнорируем ошибки при остановке анимаций
          }
        }
      })
      mixer.value = null
    }
    actions.value = {}
    animations.value = []
  } catch (e) {
    // Игнорируем ошибки при очистке
  }
})

// Метод для начала последовательности (для использования извне)
const start = () => {
  controller.start()
}

// Метод для заставить встать
const makeStandUp = () => {
  controller.makeStandUp()
}

// Экспортируем методы для использования извне
defineExpose({
  start,
  makeStandUp,
  state: computed(() => controller.state.value)
})

// Автоматический старт при загрузке модели и видимости
watch([() => isModelLoaded.value, () => props.visible], ([loaded, visible]) => {
  if (loaded && visible) {
    // Небольшая задержка для инициализации анимаций
    setTimeout(() => {
      start()
    }, 300)
  }
}, { immediate: true })
</script>
