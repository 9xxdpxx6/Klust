import { ref, computed } from 'vue'
import { Vector3 } from 'three'

// Состояния персонажа
export const CharacterState = {
  IDLE: 'idle',
  WALKING_TO_CHAIR: 'walking_to_chair',
  SITTING_DOWN: 'sitting_down',
  SITTING: 'sitting',
  STANDING_UP: 'standing_up',
  WALKING_AWAY: 'walking_away',
  DESPAWNED: 'despawned'
}

  // Константы движения
  const WALK_SPEED = 1.5 // единиц в секунду
  const ARRIVAL_DISTANCE = 0.1 // расстояние для достижения цели
  const ANIMATION_TRANSITION_DURATION = 0.3 // секунды

export function useCharacterController(spawnPosition, targetChair) {
  // Состояние - начинаем с IDLE, start() переведет в WALKING_TO_CHAIR
  const state = ref(CharacterState.IDLE)
  const position = ref([...spawnPosition])
  const rotation = ref([0, 0, 0])
  
  // Внутренние переменные
  const targetPosition = ref([...targetChair])
  const isMoving = ref(false)
  const currentAction = ref(null)
  const hasReachedTargetFlag = ref(false) // Флаг для предотвращения повторных вызовов onReachedTarget
  
  // Вычисляемое направление движения
  const direction = computed(() => {
    if (!isMoving.value) return 0
    
    const [x1, y1, z1] = position.value
    const [x2, y2, z2] = targetPosition.value
    
    const deltaX = x2 - x1
    const deltaZ = z2 - z1
    
    // Вычисляем угол поворота по Y оси (в радианах)
    return Math.atan2(deltaX, deltaZ)
  })
  
  // Вычисляемое расстояние до цели
  const distanceToTarget = computed(() => {
    const [x1, y1, z1] = position.value
    const [x2, y2, z2] = targetPosition.value
    
    const dx = x2 - x1
    const dy = y2 - y1
    const dz = z2 - z1
    
    return Math.sqrt(dx * dx + dy * dy + dz * dz)
  })
  
  // Проверка достижения цели
  const hasReachedTarget = computed(() => {
    return distanceToTarget.value < ARRIVAL_DISTANCE
  })
  
  /**
   * Установить состояние персонажа
   */
  const setState = (newState) => {
    // Если переходим в состояние, где не должно быть движения, останавливаем движение
    if (newState === CharacterState.SITTING_DOWN || 
        newState === CharacterState.SITTING || 
        newState === CharacterState.STANDING_UP ||
        newState === CharacterState.DESPAWNED) {
      isMoving.value = false
      // Фиксируем позицию при переходе в состояния без движения
      if (newState === CharacterState.SITTING_DOWN || newState === CharacterState.SITTING) {
        position.value = [...targetPosition.value]
      }
    }
    state.value = newState
  }
  
  /**
   * Начать последовательность действий
   */
  const start = () => {
    console.log('Character start called, spawn:', spawnPosition, 'target:', targetChair)
    // Сбрасываем флаг достижения цели
    hasReachedTargetFlag.value = false
    
    // Сбрасываем позицию на начальную
    position.value = [...spawnPosition]
    rotation.value = [0, 0, 0]
    
    // Устанавливаем цель и начинаем движение
    setState(CharacterState.WALKING_TO_CHAIR)
    targetPosition.value = [...targetChair]
    isMoving.value = true
    console.log('Character started, position:', position.value, 'target:', targetPosition.value, 'isMoving:', isMoving.value)
  }
  
  /**
   * Обновить движение персонажа
   * @param {number} delta - время в секундах с последнего кадра
   */
  const updateMovement = (delta) => {
    // Не двигаемся, если уже достигли цели
    if (hasReachedTargetFlag.value || !isMoving.value) {
      return
    }
    
    // Не двигаемся, если не в состоянии движения
    if (state.value !== CharacterState.WALKING_TO_CHAIR && state.value !== CharacterState.WALKING_AWAY) {
      isMoving.value = false
      return
    }
    
    const [x1, y1, z1] = position.value
    const [x2, y2, z2] = targetPosition.value
    
    // Вычисляем направление к цели
    const dx = x2 - x1
    const dy = y2 - y1
    const dz = z2 - z1
    const distance = Math.sqrt(dx * dx + dy * dy + dz * dz)
    
    // Если достигли цели - останавливаемся
    if (distance < ARRIVAL_DISTANCE) {
      position.value = [x2, y2, z2]
      isMoving.value = false
      hasReachedTargetFlag.value = true
      
      if (state.value === CharacterState.WALKING_TO_CHAIR || state.value === CharacterState.WALKING_AWAY) {
        onReachedTarget()
      }
      return
    }
    
    // Двигаемся к цели
    const moveDistance = WALK_SPEED * delta
    const moveRatio = Math.min(moveDistance / distance, 1)
    
    position.value = [
      x1 + dx * moveRatio,
      y1 + dy * moveRatio,
      z1 + dz * moveRatio
    ]
    
    // Обновляем поворот
    rotation.value = [0, direction.value, 0]
  }
  
  /**
   * Обработка достижения цели
   */
  const onReachedTarget = () => {
    console.log('onReachedTarget called, state:', state.value, 'hasReachedFlag:', hasReachedTargetFlag.value)
    
    // Убеждаемся, что флаг установлен
    if (!hasReachedTargetFlag.value) {
      hasReachedTargetFlag.value = true
    }
    
    // Убеждаемся, что движение остановлено и позиция зафиксирована
    isMoving.value = false
    position.value = [...targetPosition.value]
    
    console.log('Processing state transition, current state:', state.value)
    
    if (state.value === CharacterState.WALKING_TO_CHAIR) {
      console.log('Transitioning to SITTING_DOWN')
      // Останавливаем текущую анимацию walk (если есть) - КРИТИЧНО для предотвращения циклирования
      if (currentAction.value) {
        console.log('Stopping walk animation in onReachedTarget')
        currentAction.value.stop()
        currentAction.value.fadeOut(0.05) // Быстрое затухание
        currentAction.value = null
      }
      setState(CharacterState.SITTING_DOWN)
    } else if (state.value === CharacterState.WALKING_AWAY) {
      console.log('Transitioning to DESPAWNED')
      if (currentAction.value) {
        currentAction.value.stop()
        currentAction.value.fadeOut(0.05)
        currentAction.value = null
      }
      setState(CharacterState.DESPAWNED)
    }
  }
  
  /**
   * Воспроизвести анимацию
   * @param {Object} actions - объект с анимациями
   * @param {string} animationName - имя анимации
   * @param {boolean} loop - зациклить ли анимацию
   */
  const playAnimation = (actions, animationName, loop = true) => {
    if (!actions || !actions[animationName]) {
      console.warn(`Animation "${animationName}" not found in actions:`, Object.keys(actions || {}))
      return null
    }
    
    const action = actions[animationName]
    
    // Останавливаем текущую анимацию
    if (currentAction.value && currentAction.value !== action) {
      currentAction.value.fadeOut(ANIMATION_TRANSITION_DURATION)
    }
    
    // Настраиваем новую анимацию
    action.reset()
    // 1 = LoopRepeat, 0 = LoopOnce (из three.js)
    action.setLoop(loop ? 1 : 0)
    action.clampWhenFinished = !loop // Если не зациклена, зажимаем в конце
    action.setEffectiveWeight(1)
    action.play()
    action.fadeIn(ANIMATION_TRANSITION_DURATION)
    
    currentAction.value = action
    
    return action
  }
  
  /**
   * Плавный переход между анимациями
   * @param {Object} actions - объект с анимациями
   * @param {string} fromAnimation - имя текущей анимации
   * @param {string} toAnimation - имя новой анимации
   * @param {number} duration - длительность перехода
   */
  const transitionAnimation = (actions, fromAnimation, toAnimation, duration = ANIMATION_TRANSITION_DURATION) => {
    if (!actions) return
    
    const fromAction = fromAnimation ? actions[fromAnimation] : null
    const toAction = actions[toAnimation]
    
    if (!toAction) {
      console.warn(`Animation "${toAnimation}" not found`)
      return
    }
    
    // Fade out текущей анимации
    if (fromAction && fromAction.isRunning()) {
      fromAction.fadeOut(duration)
    }
    
    // Fade in новой анимации
    toAction.reset()
    toAction.setEffectiveWeight(1)
    toAction.play()
    toAction.fadeIn(duration)
    
    currentAction.value = toAction
  }
  
  /**
   * Обработка завершения одноразовой анимации
   * @param {string} animationName - имя завершенной анимации
   */
  const onAnimationComplete = (animationName) => {
    if (animationName === 'sit_down' && state.value === CharacterState.SITTING_DOWN) {
      // Фиксируем позицию при переходе в SITTING (не даем двигаться)
      position.value = [...targetPosition.value]
      isMoving.value = false
      setState(CharacterState.SITTING)
    } else if (animationName === 'stand_up' && state.value === CharacterState.STANDING_UP) {
      // Сбрасываем флаг достижения цели для нового движения
      hasReachedTargetFlag.value = false
      setState(CharacterState.WALKING_AWAY)
      targetPosition.value = [...spawnPosition]
      isMoving.value = true
    }
  }
  
  /**
   * Заставить персонажа встать и уйти
   */
  const makeStandUp = () => {
    if (state.value === CharacterState.SITTING) {
      setState(CharacterState.STANDING_UP)
    }
  }
  
  /**
   * Сброс к начальному состоянию
   */
  const reset = () => {
    state.value = CharacterState.IDLE
    position.value = [...spawnPosition]
    rotation.value = [0, 0, 0]
    targetPosition.value = [...targetChair]
    isMoving.value = false
    currentAction.value = null
  }
  
  return {
    // Состояние
    state,
    position,
    rotation,
    
    // Вычисляемые значения
    direction,
    distanceToTarget,
    hasReachedTarget,
    isMoving,
    
    // Методы
    start,
    setState,
    updateMovement,
    playAnimation,
    transitionAnimation,
    onAnimationComplete,
    makeStandUp,
    reset
  }
}
