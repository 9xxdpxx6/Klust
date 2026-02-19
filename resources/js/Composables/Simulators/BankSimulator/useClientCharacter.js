import { ref, computed, shallowRef, watch, onMounted, onBeforeUnmount } from 'vue'
import { useGLTF } from '@tresjs/cientos'
import axios from 'axios'
import { route } from 'ziggy-js'

/**
 * Composable for managing client character (spawn, movement, animations)
 * 
 * @param {Object} options - Configuration options
 * @param {number} options.sessionId - Session ID
 * @param {Object} options.localSessionState - Local session state
 * @param {Function} options.openDialogueDialog - Function to open dialogue dialog
 * @param {Function} options.normalizeCurrentStep - Function to normalize current step
 * @param {Object} options.sessionState - Session state from props
 * @returns {Object} Client character state and methods
 */
export function useClientCharacter({ 
  sessionId, 
  localSessionState, 
  openDialogueDialog,
  normalizeCurrentStep,
  sessionState 
}) {
  // Movement constants
  const chairTarget = { x: -1.3, y: 0, z: 0.4 }
  const chairSeatOffset = { x: 0.0, y: -0.45, z: -0.05 }
  const chairRotationY = 2.7
  const spawnOffset = { x: 0, y: 0, z: 0.8 }
  const walkSpeed = 1.8
  const seatThreshold = 0.2
  const modelRotationOffset = 0

  // Client state
  const isClientVisible = ref(false)
  const clientState = ref('idle')
  const clientAnimation = ref('idle')
  const clientPosition = shallowRef({ x: 0, y: 0, z: 0 })
  const clientRotation = ref([0, 0, 0])

  // Model preloading
  const preloadedClientModel = shallowRef(null)
  const clientModelPath = ref('/models/characters/female1.glb')
  const isPreloadingModel = ref(false)

  // Animation frame tracking
  let rafId = null
  let lastTimestamp = null

  // Computed properties
  const clientPositionArray = computed(() => {
    const pos = clientPosition.value
    return [pos.x, pos.y, pos.z]
  })

  const isClientSpeaking = computed(() => {
    const dialogue = sessionState?.value?.dialogue
    if (!dialogue) return false
    
    // Check if current_step indicates client is speaking
    const currentStep = normalizeCurrentStep(dialogue.current_step)
    if (currentStep === 'client_speaking') {
      return true
    }
    
    // Check last message in dialogue
    const messages = dialogue.messages
    if (messages && messages.length > 0) {
      const lastMessage = messages[messages.length - 1]
      // If last message is from client, consider them speaking
      return lastMessage.role === 'client'
    }
    
    return false
  })

  const hasClient = computed(() => {
    // Экран ноутбука появляется когда клиент сел и есть тип клиента
    // Имя не требуется - оно раскрывается позже через диалог
    return !!(clientState.value === 'seated' && localSessionState.client?.type)
  })

  /**
   * Set client rotation towards target
   */
  const setClientRotationTowards = (target) => {
    const dx = target.x - clientPosition.value.x
    const dz = target.z - clientPosition.value.z
    const yaw = Math.atan2(dx, dz)
    clientRotation.value = [0, yaw + modelRotationOffset, 0]
  }

  /**
   * Set client rotation for seated position
   */
  const setClientRotationSeated = () => {
    clientRotation.value = [0, chairRotationY + modelRotationOffset, 0]
  }

  /**
   * Preload client model
   */
  const preloadClientModel = async (modelPath) => {
    if (isPreloadingModel.value || (preloadedClientModel.value && clientModelPath.value === modelPath)) {
      return
    }
    
    isPreloadingModel.value = true
    try {
      const gltfPromise = useGLTF(modelPath)
      if (gltfPromise && typeof gltfPromise.then === 'function') {
        const result = await gltfPromise
        if (result && clientModelPath.value === modelPath) {
          preloadedClientModel.value = result
        }
      } else {
        // If useGLTF returned result synchronously (cache)
        if (gltfPromise && clientModelPath.value === modelPath) {
          preloadedClientModel.value = gltfPromise
        }
      }
    } catch (error) {
      // Silent error handling
    } finally {
      isPreloadingModel.value = false
    }
  }

  /**
   * Spawn client at door position
   */
  const spawnClient = (doorWorldPosition) => {
    if (isClientVisible.value && clientState.value !== 'idle') return

    clientPosition.value = {
      x: doorWorldPosition.x + spawnOffset.x,
      y: doorWorldPosition.y + spawnOffset.y,
      z: doorWorldPosition.z + spawnOffset.z
    }
    isClientVisible.value = true
    clientState.value = 'walking'
    clientAnimation.value = 'walk'
    setClientRotationTowards(chairTarget)
    
    // Start movement animation
    if (!rafId) {
      lastTimestamp = null
      rafId = window.requestAnimationFrame(onFrame)
    }
  }

  /**
   * Handle door click - generate and spawn client
   */
  const onDoorClick = async (payload) => {
    if (!payload?.position) {
      return
    }
    
    // Check if client is already spawning
    if (isClientVisible.value && clientState.value !== 'idle') {
      return
    }
    
    // Generate client via API
    try {
      const url = route('student.simulators.generate-client', { session: sessionId })
      
      const response = await axios.post(url, {
        type: 'random'
      })
      
      const clientData = response.data
      
      // Save only non-dialogue client data to state.
      // Financial data (income, expenses, age, credit_history) will be
      // progressively collected through dialogue and revealed on the laptop screen.
      // Name is revealed at the passport stage near the end of the conversation.
      Object.assign(localSessionState.client, {
        type: clientData.type,
        name: null,
        age: null,
        income: null,
        expenses: null,
        credit_history: null,
        has_deposit: clientData.has_deposit ?? false,
        model_path: clientData.model_path,
        // Store generated name for reveal at passport stage
        _generated_name: clientData.name
      })
      
      // Update model path and preload
      if (clientData.model_path && clientData.model_path !== clientModelPath.value) {
        clientModelPath.value = clientData.model_path
        await preloadClientModel(clientData.model_path)
      } else if (!preloadedClientModel.value) {
        await preloadClientModel(clientData.model_path || clientModelPath.value)
      }
      
      // Spawn client
      const [x, y, z] = payload.position
      spawnClient({ x, y, z })
    } catch (error) {
      // Fallback: spawn client with default model
      const [x, y, z] = payload.position
      spawnClient({ x, y, z })
    }
  }

  /**
   * Update client movement
   */
  const updateClientMovement = (deltaSeconds) => {
    if (!isClientVisible.value) {
      // Stop animation if client is not visible
      if (rafId) {
        window.cancelAnimationFrame(rafId)
        rafId = null
      }
      return
    }
    
    if (clientState.value !== 'walking') {
      // Stop animation if client is not walking
      if (clientState.value === 'seated' && rafId) {
        window.cancelAnimationFrame(rafId)
        rafId = null
      }
      return
    }

    const dx = chairTarget.x - clientPosition.value.x
    const dz = chairTarget.z - clientPosition.value.z
    const distance = Math.hypot(dx, dz)

    if (distance <= seatThreshold) {
      clientPosition.value = {
        x: chairTarget.x + chairSeatOffset.x,
        y: chairTarget.y + chairSeatOffset.y,
        z: chairTarget.z + chairSeatOffset.z
      }
      clientState.value = 'sitting_down'
      clientAnimation.value = 'sit_down'
      setClientRotationSeated()
      
      // Stop animation when client sits down
      if (rafId) {
        window.cancelAnimationFrame(rafId)
        rafId = null
      }
      return
    }

    const step = Math.min(distance, walkSpeed * deltaSeconds)
    const nx = dx / distance
    const nz = dz / distance
    
    // Create new object for position update (shallowRef requires object replacement)
    // But only if client is still visible and walking
    if (isClientVisible.value && clientState.value === 'walking') {
      clientPosition.value = {
        x: clientPosition.value.x + nx * step,
        y: clientPosition.value.y,
        z: clientPosition.value.z + nz * step
      }
      setClientRotationTowards(chairTarget)
    }
  }

  /**
   * Animation frame handler
   */
  const onFrame = (timestamp) => {
    // Check that component is still mounted
    if (!rafId) return
    
    if (lastTimestamp === null) {
      lastTimestamp = timestamp
    }
    const deltaSeconds = (timestamp - lastTimestamp) / 1000
    lastTimestamp = timestamp
    
    updateClientMovement(deltaSeconds)
    
    // Continue animation only if client is walking
    if (isClientVisible.value && clientState.value === 'walking') {
      rafId = window.requestAnimationFrame(onFrame)
    } else {
      rafId = null
    }
  }

  /**
   * Handle client animation finished
   */
  const onClientAnimationFinished = (animationName) => {
    if (animationName === 'sit_down' && clientState.value === 'sitting_down') {
      clientState.value = 'seated'
      clientAnimation.value = 'sit'
      // Open dialogue dialog when client sits down
      if (openDialogueDialog) {
        openDialogueDialog()
      }
    }
  }

  /**
   * Restore client character from saved session state.
   * Called when page reloads and session already has client data.
   * Skips walking animation — immediately places client in chair.
   */
  const restoreClientFromSession = () => {
    // Set model path from session
    if (localSessionState.client?.model_path) {
      clientModelPath.value = localSessionState.client.model_path
      preloadClientModel(localSessionState.client.model_path)
    }

    // Place client directly in seated position (skip walking)
    clientPosition.value = {
      x: chairTarget.x + chairSeatOffset.x,
      y: chairTarget.y + chairSeatOffset.y,
      z: chairTarget.z + chairSeatOffset.z
    }
    isClientVisible.value = true
    clientState.value = 'seated'
    clientAnimation.value = 'sit'
    setClientRotationSeated()

    // Auto-open dialogue if there are messages (ongoing session)
    const hasMessages = (localSessionState.dialogue?.messages?.length || 0) > 0
    if (hasMessages && openDialogueDialog) {
      // Small delay to ensure 3D scene & Dialog3D are fully mounted
      setTimeout(() => {
        openDialogueDialog()
      }, 600)
    }
  }

  /**
   * Watch for session restore — if client data exists but character hasn't spawned.
   * Fires immediately so it catches initial state from server-side render.
   * Also catches late state from loadState() API call.
   */
  watch(
    () => localSessionState.client?.type,
    (newType) => {
      if (newType && clientState.value === 'idle' && !isClientVisible.value) {
        restoreClientFromSession()
      }
    },
    { immediate: true }
  )

  // Preload default client model on mount
  onMounted(() => {
    // Preload default client model when scene mounts (if client not yet generated)
    if (!localSessionState.client?.model_path && !isPreloadingModel.value && !preloadedClientModel.value) {
      preloadClientModel(clientModelPath.value)
    } else if (localSessionState.client?.model_path) {
      // If client already exists in state, load its model
      clientModelPath.value = localSessionState.client.model_path
      preloadClientModel(localSessionState.client.model_path)
    }
  })

  /**
   * Fully reset client character (used on "restart session").
   * Hides the 3D model, resets all internal state so a new client
   * can be generated by clicking the door again.
   */
  const resetClient = () => {
    // Stop any running animation frame
    if (rafId) {
      window.cancelAnimationFrame(rafId)
      rafId = null
    }
    lastTimestamp = null

    // Hide and reset character
    isClientVisible.value = false
    clientState.value = 'idle'
    clientAnimation.value = 'idle'
    clientPosition.value = { x: 0, y: 0, z: 0 }
    clientRotation.value = [0, 0, 0]

    // Reset model refs
    preloadedClientModel.value = null
    isPreloadingModel.value = false
    clientModelPath.value = '/models/characters/female1.glb'
  }

  // Cleanup on unmount
  onBeforeUnmount(() => {
    if (rafId) {
      window.cancelAnimationFrame(rafId)
    }
    rafId = null
    lastTimestamp = null
  })

  return {
    // State
    isClientVisible,
    clientState,
    clientAnimation,
    clientPosition,
    clientPositionArray,
    clientRotation,
    preloadedClientModel,
    clientModelPath,
    
    // Computed
    isClientSpeaking,
    hasClient,
    
    // Methods
    spawnClient,
    preloadClientModel,
    onDoorClick,
    onClientAnimationFinished,
    resetClient
  }
}
