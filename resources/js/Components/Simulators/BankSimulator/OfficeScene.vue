<template>
  <TresCanvas 
    class="office-scene"
    :shadows="true"
    :clear-color="'#E0F6FF'"
    :dpr="[1, 2]"
    :output-color-space="outputColorSpace"
    :tone-mapping="toneMapping"
    :tone-mapping-exposure="toneMappingExposure"
    :use-legacy-lights="false"
    :shadow-map-type="shadowMapType"
  >
    <!-- Камера -->
    <TresPerspectiveCamera 
      :position="devMode ? [0, 1.6, 0] : [-1.1, 1.07, -0.75]" 
      :fov="devMode ? 75 : 70"
      :near="0.1"
      :far="1000"
    />
    
    <!-- DEV: OrbitControls для свободного просмотра -->
    <OrbitControlsDev v-if="devMode" />
    
    <!-- PROD: Ограниченное движение камеры за курсором -->
    <HeadrestCamera 
      v-else
      :position="[-1.1, 1.07, -0.75]"
      :fov="70"
      :max-yaw-left="5"
      :max-yaw-right="20"
      :max-pitch-up="15"
      :max-pitch-down="25"
      :speed="4"
      :base-yaw="Math.PI - 0.2"
      :base-pitch="0"
    />
    
    <!-- Освещение -->
    <TresAmbientLight :intensity="0.3" />
    <TresHemisphereLight 
      :color="'#ffffff'"
      :ground-color="'#bfc8d3'"
      :intensity="0.25"
    />
    <TresDirectionalLight 
      ref="directionalLightRef"
      :position="[-5, 15, -3]" 
      :intensity="2.5"
      :cast-shadow="true"
    />
    
    <!-- Кабинет (сборка из GLB) -->
    <OfficeInterior 
      :position="[0, 0, 0]" 
      :rotation="[0, -Math.PI / 2, 0]" 
      :scale="[1, 1, 1]"
      :window-position="[3.2, 0, 0]"
      :door-position="[3.95, 0, 0.5]"
      :palm-left-position="[2.6, 0, 2.1]"
      :palm-right-position="[2.6, 0, -2.1]"
      :plant-position="[-1.2, 0, -3.6]"
      :sofa-position="[0, 0, 0]"
      @door-click="onDoorClick"
    />
    
    <!-- Стол -->
    <Desk 
      :position="[-0.9, 0, -0.4]"
      :rotation="[0, Math.PI, 0]"
      :scale="[1, 1, 1]"
    />
    
    <!-- Ноутбук на столе -->
    <Laptop 
      :position="[-0.98, 0.7974, -0.4]"
      :rotation="[0, 3.2, 0]"
      :color="laptopColor"
    />
    
    <!-- Экран ноутбука с интерфейсом банковской системы (показывается только когда есть клиент) -->
    <LaptopScreen
      v-if="hasClient"
      :client="localSessionState.client"
      :calculations="localSessionState.calculations"
      :dialogue-messages="localSessionState.dialogue.messages || []"
      :active-tab="localSessionState.ui.activeTab"
      @update:active-tab="onBankTabChange"
      :position="[-1.35, 1.15, -0.3]"
      :rotation="[0.0, 2.8, 0]"
      :width="600"
      :height="400"
      :scale="0.0005"
    />
    
    <!-- Телефон -->
    <Phone 
      :position="[-1.4, 0.7955, -0.55]"
      :base-scale="[0.001, 0.001, 0.001]"
      :base-rotation="[Math.PI / 2, 0, 0.5]"
      :is-ringing="isPhoneRinging"
      @click="onPhoneClick"
    />
    
    <!-- Документы -->
    <Documents 
      :position="[-1.27, 0.79, -0.37]"
      :rotation="[0, 3.25, 0]"
      :count="1"
      :scale="[0.45, 0.45, 0.45]"
      @click="onDocumentsClick"
    />
    
    <!-- Кактус -->
    <Cactus 
      :position="[1.3, 0.0, -1.55]"
      :rotation="[0, 0, 0]"
      :scale="[0.7, 0.7, 0.7]"
    />

    <!-- Кресло работника -->
    <Armchair 
      :position="[-0.95, 0, -0.95]"
      :rotation="[0, -0.25, 0]"
      :scale="[1, 1, 1]"
    />

    <!-- Кресла клиентов (2 шт) -->
    <Chair 
      :position="[-1.3, 0, 0.4]"
      :rotation="[0, 2.7, 0]"
      :scale="[1, 1, 1]"
    />
    <Chair 
      :position="[-0.3, 0, 0.4]"
      :rotation="[0, -2.65, 0]"
      :scale="[1, 1, 1]"
    />
    
    <!-- Клиент напротив -->
    <ClientCharacter 
      v-if="isClientVisible"
      :position="clientPositionArray"
      :rotation="clientRotation"
      :animation="clientAnimation"
      :is-speaking="isClientSpeaking"
      :preloaded-model="preloadedClientModel"
      :model-path="clientModelPath"
      @animation-finished="onClientAnimationFinished"
    />
    
    <!-- CSS3DRenderer для 3D диалогов -->
    <CSS3DRendererPlugin />
    
    <!-- Единый 3D диалог с динамическим контентом -->
    <!-- Позиция: дальше от камеры [-0.95, 1.15, 0.15], уменьшенный размер -->
    <Dialog3D
      ref="mainDialogRef"
      :visible="isAnyDialogOpen"
      :header="activeDialogHeader"
      :position="[-1.1, 1.15, 0.15]"
      :width="600"
      :height="400"
      @update:visible="onDialogVisibilityChange"
      @close="onMainDialogClose"
    />
    
    <!-- Диалог с клиентом -->
    <Dialog3D
      ref="dialogueDialogRef"
      :visible="showDialogueDialog"
      header="Диалог с клиентом"
      :position="[-0.9, 1.15, 0.15]"
      :width="600"
      :height="500"
      @update:visible="onDialogueDialogVisibilityChange"
      @close="onDialogueDialogClose"
    />
  </TresCanvas>
  
  <!-- Калькуляторы (PrimeVue Dialogs) -->
  <CreditCalculatorDialog
    v-model:visible="showCreditCalculator"
    :session-id="sessionId"
    :default-rate="localSessionState.calculations?.interest_rate"
    @close="onCreditCalculatorClose"
  />
  
  <DepositCalculatorDialog
    v-model:visible="showDepositCalculator"
    :session-id="sessionId"
    @close="onDepositCalculatorClose"
  />
</template>

<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount, shallowRef, watchEffect, nextTick, reactive, h } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { TresCanvas } from '@tresjs/core'
import { LinearToneMapping, PCFSoftShadowMap, SRGBColorSpace } from 'three'
import Armchair from './Armchair.vue'
import Chair from './Chair.vue'
import Desk from './Desk.vue'
import Laptop from './Laptop.vue'
import LaptopScreen from './LaptopScreen.vue'
import CreditCalculatorDialog from './CreditCalculatorDialog.vue'
import DepositCalculatorDialog from './DepositCalculatorDialog.vue'
import OfficeInterior from './OfficeInterior.vue'
import Phone from './Phone.vue'
import Documents from './Documents.vue'
import Cactus from './Cactus.vue'
import Dialog3D from './Dialog3D.vue'
import CSS3DRendererPlugin from './CSS3DRendererPlugin.vue'
import ClientCharacter from './ClientCharacter.vue'
import HeadrestCamera from './HeadrestCamera.vue'
import DialogueInterface from './DialogueInterface.vue'
// DEV ONLY - Переключить на false для продакшена
import OrbitControlsDev from './OrbitControlsDev.vue'
import { createApp } from 'vue'
import { useGLTF } from '@tresjs/cientos'
import axios from 'axios'
import { route } from 'ziggy-js'
const devMode = false // TODO: переключить на false для прода

const props = defineProps({
  sessionState: {
    type: Object,
    default: () => ({})
  },
  sessionId: {
    type: Number,
    required: true
  },
  updateState: {
    type: Function,
    default: null
  },
  autoSave: {
    type: Function,
    default: null
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['phoneClick', 'documentsClick', 'calculatorClick'])

const outputColorSpace = SRGBColorSpace
const toneMapping = LinearToneMapping
const toneMappingExposure = 1.0
const shadowMapType = PCFSoftShadowMap
const directionalLightRef = shallowRef(null)
const shadowConfigured = ref(false)

watchEffect(() => {
  const light = directionalLightRef.value
  const shadow = light?.shadow
  if (!shadow || shadowConfigured.value) return

  shadow.mapSize?.set(4096, 4096)
  shadow.radius = 6
  shadow.bias = -0.0005
  shadow.normalBias = 0.03

  if (shadow.camera) {
    shadow.camera.near = 0.1
    shadow.camera.far = 25
    shadow.camera.left = -6
    shadow.camera.right = 6
    shadow.camera.top = 6
    shadow.camera.bottom = -6
    shadow.camera.updateProjectionMatrix()
  }

  shadow.needsUpdate = true
  shadowConfigured.value = true
})

// Состояния диалогов
const showPhoneDialog = ref(false)
const showCalculatorDialog = ref(false)
const showDocumentsDialog = ref(false)
const showDialogueDialog = ref(false)
const showCreditCalculator = ref(false)
const showDepositCalculator = ref(false)

// Ref для диалогов
const mainDialogRef = ref(null)
const dialogueDialogRef = ref(null)
const dialogueAppRef = ref(null)

// Активный диалог (для единого Dialog3D)
const activeDialog = ref(null) // 'phone' | 'calculator' | 'documents' | 'dialogue' | null

// Заголовки диалогов
const dialogHeaders = {
  phone: 'Телефон',
  calculator: 'Калькулятор',
  documents: 'Документы'
}

// Вычисляемые свойства для единого диалога (без диалога диалога - он использует отдельный Dialog3D)
const isAnyDialogOpen = computed(() => {
  return showPhoneDialog.value || showCalculatorDialog.value || showDocumentsDialog.value
})

const activeDialogHeader = computed(() => {
  if (showDialogueDialog.value) return 'Диалог с клиентом'
  if (showPhoneDialog.value) return dialogHeaders.phone
  if (showCalculatorDialog.value) return dialogHeaders.calculator
  if (showDocumentsDialog.value) return dialogHeaders.documents
  return ''
})

const hasClient = computed(() => {
  return clientState.value === 'seated' && localSessionState.client?.name && localSessionState.client?.type
})

// Обработчик закрытия единого диалога
const onMainDialogClose = () => {
  showPhoneDialog.value = false
  showCalculatorDialog.value = false
  showDocumentsDialog.value = false
  activeDialog.value = null
  saveDialogState(null)
}

// Обработчик изменения видимости диалога
const onDialogVisibilityChange = (visible) => {
  if (!visible) {
    onMainDialogClose()
  }
}

// Восстанавливаем состояние диалогов из sessionState при монтировании
onMounted(() => {
  const activeDialog = props.sessionState?.ui?.activeDialog
  if (activeDialog === 'phone') {
    showPhoneDialog.value = true
  } else if (activeDialog === 'calculator') {
    showCalculatorDialog.value = true
  } else if (activeDialog === 'documents') {
    showDocumentsDialog.value = true
  }
})

// Сохраняем состояние открытого диалога
const saveDialogState = (dialogName) => {
  if (!props.updateState) return
  
  const updates = {
    ui: {
      activeDialog: dialogName || null
    }
  }
  
  props.updateState(updates).catch((error) => {
    console.error('Ошибка сохранения состояния диалога:', error)
  })
}

// Проверка на несохраненные изменения (заглушка)
const hasUnsavedChanges = (dialogName) => {
  // TODO: Реализовать проверку несохраненных изменений
  return false
}

// Закрытие других диалогов с предупреждением
const closeOtherDialogsWithWarning = (newDialogName) => {
  const dialogsToClose = []
  
  if (newDialogName !== 'phone' && showPhoneDialog.value) {
    dialogsToClose.push({ name: 'phone', ref: showPhoneDialog })
  }
  if (newDialogName !== 'calculator' && showCalculatorDialog.value) {
    dialogsToClose.push({ name: 'calculator', ref: showCalculatorDialog })
  }
  if (newDialogName !== 'documents' && showDocumentsDialog.value) {
    dialogsToClose.push({ name: 'documents', ref: showDocumentsDialog })
  }
  
  if (dialogsToClose.length > 0) {
    // Проверяем на несохраненные изменения
    const hasUnsaved = dialogsToClose.some(dialog => hasUnsavedChanges(dialog.name))
    
    if (hasUnsaved) {
      // TODO: Показать диалог подтверждения (реализовать позже)
      const confirmed = confirm('У вас есть несохраненные изменения. Вы уверены, что хотите закрыть этот диалог?')
      if (!confirmed) {
        return false
      }
    }
    
    // Закрываем другие диалоги
    dialogsToClose.forEach(dialog => {
      dialog.ref.value = false
    })
  }
  
  return true
}

const laptopColor = computed(() => {
  const score = props.sessionState?.calculations?.credit_score
  if (score >= 0.8) return '#4ade80'
  if (score >= 0.5) return '#fbbf24'
  if (score >= 0.3) return '#f97316'
  return '#1e40af' // Дефолтный синий
})

const isPhoneRinging = computed(() => {
  return props.sessionState?.phone?.isRinging === true
})

// Определяем, говорит ли клиент в данный момент
const isClientSpeaking = computed(() => {
  const dialogue = props.sessionState?.dialogue
  if (!dialogue) return false
  
  // Проверяем, если current_step указывает на то, что клиент говорит
  const currentStep = normalizeCurrentStep(dialogue.current_step)
  if (currentStep === 'client_speaking') {
    return true
  }
  
  // Проверяем последнее сообщение в диалоге
  const messages = dialogue.messages
  if (messages && messages.length > 0) {
    const lastMessage = messages[messages.length - 1]
    // Если последнее сообщение от клиента, считаем что он говорит
    // (в реальной системе можно добавить таймаут для определения "активного" состояния)
    return lastMessage.role === 'client'
  }
  
  return false
})

const chairTarget = { x: -1.3, y: 0, z: 0.4 }
const chairSeatOffset = { x: 0.0, y: -0.45, z: -0.05 }
const chairRotationY = 2.7
const spawnOffset = { x: 0, y: 0, z: 0.8 }
const walkSpeed = 1.8
const seatThreshold = 0.2
const modelRotationOffset = 0

const isClientVisible = ref(false)
const clientState = ref('idle')
const clientAnimation = ref('idle')
// Используем shallowRef для уменьшения реактивности
const clientPosition = shallowRef({ x: 0, y: 0, z: 0 })
const clientRotation = ref([0, 0, 0])

// Предзагрузка модели клиента
const preloadedClientModel = shallowRef(null)
const clientModelPath = ref('/models/characters/female1.glb')
const isPreloadingModel = ref(false)

const clientPositionArray = computed(() => {
  const pos = clientPosition.value
  return [pos.x, pos.y, pos.z]
})

const setClientRotationTowards = (target) => {
  const dx = target.x - clientPosition.value.x
  const dz = target.z - clientPosition.value.z
  const yaw = Math.atan2(dx, dz)
  clientRotation.value = [0, yaw + modelRotationOffset, 0]
}

const setClientRotationSeated = () => {
  clientRotation.value = [0, chairRotationY + modelRotationOffset, 0]
}

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
  
  // Запускаем анимацию движения
  if (!rafId) {
    lastTimestamp = null
    rafId = window.requestAnimationFrame(onFrame)
  }
}

const onDoorClick = async (payload) => {
  if (!payload?.position) {
    return
  }
  
  // Проверяем, не спавнится ли уже клиент
  if (isClientVisible.value && clientState.value !== 'idle') {
    return
  }
  
  // Генерируем клиента через API
  try {
    const url = route('student.simulators.generate-client', { session: props.sessionId })
    
    const response = await axios.post(url, {
      type: 'random'
    })
    
    const clientData = response.data
    
    // Сохраняем данные клиента в состояние
    Object.assign(localSessionState.client, {
      type: clientData.type,
      name: clientData.name,
      age: clientData.age,
      income: clientData.income,
      expenses: clientData.expenses,
      credit_history: clientData.credit_history,
      has_deposit: clientData.has_deposit,
      model_path: clientData.model_path
    })
    
    // Обновляем путь к модели и предзагружаем её
    if (clientData.model_path && clientData.model_path !== clientModelPath.value) {
      clientModelPath.value = clientData.model_path
      await preloadClientModel(clientData.model_path)
    } else if (!preloadedClientModel.value) {
      await preloadClientModel(clientData.model_path || clientModelPath.value)
    }
    
    // Спавним клиента
    const [x, y, z] = payload.position
    spawnClient({ x, y, z })
  } catch (error) {
    // Fallback: спавним клиента с дефолтной моделью
    const [x, y, z] = payload.position
    spawnClient({ x, y, z })
  }
}

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
      // Если useGLTF вернул результат синхронно (кеш)
      if (gltfPromise && clientModelPath.value === modelPath) {
        preloadedClientModel.value = gltfPromise
      }
    }
  } catch (error) {
  } finally {
    isPreloadingModel.value = false
  }
}

let rafId = null
let lastTimestamp = null

const updateClientMovement = (deltaSeconds) => {
  if (!isClientVisible.value) {
    // Останавливаем анимацию если клиент не виден
    if (rafId) {
      window.cancelAnimationFrame(rafId)
      rafId = null
    }
    return
  }
  
  if (clientState.value !== 'walking') {
    // Останавливаем анимацию если клиент не идет
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
    
    // Останавливаем анимацию когда клиент садится
    if (rafId) {
      window.cancelAnimationFrame(rafId)
      rafId = null
    }
    return
  }

  const step = Math.min(distance, walkSpeed * deltaSeconds)
  const nx = dx / distance
  const nz = dz / distance
  
  // Создаем новый объект для обновления позиции (shallowRef требует замены объекта)
  // Но только если клиент еще виден и идет
  if (isClientVisible.value && clientState.value === 'walking') {
    clientPosition.value = {
      x: clientPosition.value.x + nx * step,
      y: clientPosition.value.y,
      z: clientPosition.value.z + nz * step
    }
    setClientRotationTowards(chairTarget)
  }
}

const onClientAnimationFinished = (animationName) => {
  if (animationName === 'sit_down' && clientState.value === 'sitting_down') {
    clientState.value = 'seated'
    clientAnimation.value = 'sit'
    // Открываем диалог когда клиент садится
    openDialogueDialog()
  }
}

const onFrame = (timestamp) => {
  // Проверяем что компонент еще смонтирован
  if (!rafId) return
  
  if (lastTimestamp === null) {
    lastTimestamp = timestamp
  }
  const deltaSeconds = (timestamp - lastTimestamp) / 1000
  lastTimestamp = timestamp
  
  updateClientMovement(deltaSeconds)
  
  // Продолжаем анимацию только если клиент идет
  if (isClientVisible.value && clientState.value === 'walking') {
    rafId = window.requestAnimationFrame(onFrame)
  } else {
    rafId = null
  }
}

onMounted(() => {
  // Не запускаем requestAnimationFrame сразу - только когда клиент появится
  // rafId = window.requestAnimationFrame(onFrame)
  
  // Предзагружаем дефолтную модель клиента при монтировании сцены (если клиент еще не сгенерирован)
  if (!localSessionState.client?.model_path && !isPreloadingModel.value && !preloadedClientModel.value) {
    preloadClientModel(clientModelPath.value)
  } else if (localSessionState.client?.model_path) {
    // Если клиент уже есть в состоянии, загружаем его модель
    clientModelPath.value = localSessionState.client.model_path
    preloadClientModel(localSessionState.client.model_path)
  }
})

onBeforeUnmount(() => {
  if (rafId) {
    window.cancelAnimationFrame(rafId)
  }
  rafId = null
  lastTimestamp = null
})

// Переключение на новый диалог с анимацией
const switchToDialog = (dialogName, dialogRef, emitEvent) => {
  // Закрываем диалог диалога если открыт
  if (showDialogueDialog.value) {
    closeDialogueDialog()
  }
  
  const wasDialogOpen = isAnyDialogOpen.value
  const previousDialog = activeDialog.value
  
  // Если открыт другой диалог - анимируем переключение
  if (wasDialogOpen && previousDialog !== dialogName) {
    // Анимируем смену заголовка, не закрывая диалог
    if (mainDialogRef.value?.animateContentChange) {
      mainDialogRef.value.animateContentChange(dialogHeaders[dialogName], () => {
        // После анимации переключаем флаги диалогов
        showPhoneDialog.value = dialogName === 'phone'
        showCalculatorDialog.value = dialogName === 'calculator'
        showDocumentsDialog.value = dialogName === 'documents'
        activeDialog.value = dialogName
        saveDialogState(dialogName)
        emitEvent()
      })
    } else {
      // Fallback без анимации
      showPhoneDialog.value = dialogName === 'phone'
      showCalculatorDialog.value = dialogName === 'calculator'
      showDocumentsDialog.value = dialogName === 'documents'
      activeDialog.value = dialogName
      saveDialogState(dialogName)
      emitEvent()
    }
  } else if (!wasDialogOpen) {
    // Диалог не был открыт - просто открываем
    dialogRef.value = true
    activeDialog.value = dialogName
    saveDialogState(dialogName)
    emitEvent()
  }
}

const onPhoneClick = () => {
  if (!showPhoneDialog.value) {
    switchToDialog('phone', showPhoneDialog, () => emit('phoneClick'))
  }
}

const onDocumentsClick = () => {
  if (!showDocumentsDialog.value) {
    switchToDialog('documents', showDocumentsDialog, () => emit('documentsClick'))
  }
}

const onCalculatorClick = () => {
  // Открываем кредитный калькулятор по умолчанию
  if (!showCreditCalculator.value) {
    closeOtherDialogsWithWarning('credit_calculator')
    showCreditCalculator.value = true
    emit('calculatorClick')
  }
}

const onCreditCalculatorClose = () => {
  showCreditCalculator.value = false
}

const onDepositCalculatorClose = () => {
  showDepositCalculator.value = false
}

// Функции для открытия конкретных калькуляторов (можно вызывать из других мест)
const openCreditCalculator = () => {
  closeOtherDialogsWithWarning('credit_calculator')
  showCreditCalculator.value = true
}

const openDepositCalculator = () => {
  closeOtherDialogsWithWarning('deposit_calculator')
  showDepositCalculator.value = true
}

// Обработчики закрытия диалогов
const onPhoneDialogClose = () => {
  showPhoneDialog.value = false
  saveDialogState(null)
}

const onCalculatorDialogClose = () => {
  showCalculatorDialog.value = false
  saveDialogState(null)
}

const onDocumentsDialogClose = () => {
  showDocumentsDialog.value = false
  saveDialogState(null)
}

// Локальное состояние диалога (используем reactive для глубокой реактивности)
const localSessionState = reactive({
  dialogue: {
    messages: [],
    current_step: 'greeting',
    selected_options: []
  },
  client: {
    age: 0,
    income: 0,
    expenses: 0,
    credit_history: 'none',
    has_deposit: false
  },
  calculations: {},
  ui: {
    activeTab: '0'
  }
})

// Нормализация current_step (если стал массивом из-за неправильного merge)
const normalizeCurrentStep = (step) => {
  if (Array.isArray(step)) {
    return step[step.length - 1] || 'greeting'
  }
  return step || 'greeting'
}

// Инициализация состояния из props
watch(() => props.sessionState, (newState) => {
  if (newState && !props.isLoading) {
    // Обновляем только если не идет загрузка
    const currentStep = normalizeCurrentStep(newState.dialogue?.current_step)
    
    Object.assign(localSessionState, {
      dialogue: {
        messages: Array.isArray(newState.dialogue?.messages) ? newState.dialogue.messages : [],
        current_step: currentStep,
        selected_options: Array.isArray(newState.dialogue?.selected_options) ? newState.dialogue.selected_options : [],
        formData: newState.dialogue?.formData || {}
      },
      client: {
        ...localSessionState.client,
        ...(newState.client || {})
      },
      calculations: newState.calculations || {},
      ui: {
        activeTab: newState.ui?.activeTab || localSessionState.ui?.activeTab || '0',
        activeDialog: newState.ui?.activeDialog || localSessionState.ui?.activeDialog || null
      }
    })
  }
}, { immediate: true, deep: true })

// Обработчик изменения активной вкладки банковского интерфейса
const onBankTabChange = (tab) => {
  if (!localSessionState.ui) {
    localSessionState.ui = {}
  }
  localSessionState.ui.activeTab = tab
  
  // Сохраняем в состояние через автосохранение
  if (props.autoSave && !props.isLoading) {
    props.autoSave({
      ui: {
        activeTab: tab
      }
    })
  }
}

// Получение конфигурации этапа (заглушка - будет заменено на API вызов)
const getStageConfig = (stageId) => {
  // Заглушка - в реальности будет вызов DialogueService через API
  const stages = {
    greeting: {
      client_message: 'Здравствуйте! Чем могу помочь?',
      user_options: [
        { id: 'credit_card', text: 'Мне нужна кредитная карта' },
        { id: 'deposit', text: 'Хочу открыть вклад' },
        { id: 'consultation', text: 'Нужна консультация' }
      ],
      next_stage: {
        credit_card: 'credit_inquiry',
        deposit: 'deposit_inquiry',
        consultation: 'completion'
      }
    },
    credit_inquiry: {
      client_message: 'Отлично! Расскажите, какую сумму вы хотели бы получить?',
      required_data: ['credit_amount'],
      next_stage: 'collect_income'
    },
    deposit_inquiry: {
      client_message: 'Какую сумму вы хотите внести на вклад?',
      required_data: ['deposit_amount', 'deposit_period'],
      next_stage: 'collect_income'
    },
    collect_income: {
      client_message: 'Расскажите о вашем доходе',
      required_data: ['income'],
      next_stage: 'collect_expenses'
    },
    collect_expenses: {
      client_message: 'Каковы ваши ежемесячные расходы?',
      required_data: ['expenses'],
      next_stage: 'collect_age_and_history'
    },
    collect_age_and_history: {
      client_message: 'Сколько вам лет и какая у вас кредитная история?',
      required_data: ['age', 'credit_history'],
      next_stage: 'present_results'
    },
    present_results: {
      client_message: 'На основе ваших данных, мы можем предложить...',
      user_options: [
        { id: 'accept', text: 'Принять предложение' },
        { id: 'reject', text: 'Отклонить' }
      ],
      next_stage: {
        accept: 'completion',
        reject: 'completion'
      },
      show_calculations: true
    },
    completion: {
      client_message: 'Спасибо за визит! Обращайтесь еще!',
      is_final: true
    }
  }
  return stages[stageId] || {}
}

// Текущая конфигурация этапа
const currentStageConfig = computed(() => {
  const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
  return getStageConfig(currentStep)
})

// Открытие диалога
const openDialogueDialog = () => {
  if (showDialogueDialog.value) return
  
  // Закрываем другие диалоги
  showPhoneDialog.value = false
  showCalculatorDialog.value = false
  showDocumentsDialog.value = false
  activeDialog.value = null
  
  // Инициализируем диалог с greeting если еще нет сообщений
  if (localSessionState.dialogue.messages.length === 0) {
    const stageConfig = getStageConfig('greeting')
    addClientMessage(stageConfig.client_message)
  }
  
  showDialogueDialog.value = true
  
  // Монтируем DialogueInterface в Dialog3D
  nextTick(() => {
    mountDialogueInterface()
  })
}

// Watch для обновления компонента при изменении этапа
watch(() => localSessionState.dialogue.current_step, () => {
  if (showDialogueDialog.value) {
    nextTick(() => {
      mountDialogueInterface()
    })
  }
})

// Монтирование DialogueInterface в Dialog3D
const mountDialogueInterface = () => {
  if (!dialogueDialogRef.value || !showDialogueDialog.value) return
  
  // Ждем пока Dialog3D создаст DOM элементы
  const findDialogContent = () => {
    // Ищем content элемент Dialog3D по классу
    const dialogContent = document.querySelector('.dialog-3d-content')
    if (!dialogContent) {
      setTimeout(findDialogContent, 50)
      return
    }
    
    // Очищаем предыдущий контент
    if (dialogueAppRef.value) {
      dialogueAppRef.value.unmount()
      dialogueAppRef.value = null
    }
    
    // Создаем контейнер для Vue компонента
    const container = document.createElement('div')
    container.id = 'dialogue-interface-container'
    container.style.width = '100%'
    container.style.height = '100%'
    
    // Очищаем content и добавляем контейнер
    dialogContent.innerHTML = ''
    dialogContent.appendChild(container)
    
    // Создаем Vue приложение с DialogueInterface
    const currentStageComputed = computed(() => localSessionState.dialogue.current_step)
    const stageConfigComputed = computed(() => getStageConfig(localSessionState.dialogue.current_step))
    
    const app = createApp({
      components: {
        DialogueInterface
      },
      setup() {
        return {
          sessionState: localSessionState,
          currentStage: currentStageComputed,
          stageConfig: stageConfigComputed,
          handleOptionSelect,
          handleDataSubmit
        }
      },
      render() {
        return h(DialogueInterface, {
          sessionState: localSessionState,
          currentStage: currentStageComputed.value,
          stageConfig: stageConfigComputed.value,
          onOptionSelect: handleOptionSelect,
          onDataSubmit: handleDataSubmit
        })
      }
    })
    
    app.mount(container)
    dialogueAppRef.value = app
  }
  
  // Начинаем поиск content элемента
  nextTick(() => {
    findDialogContent()
  })
}

// Обработка выбора опции
const handleOptionSelect = (choiceId) => {
  const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
  const stageConfig = getStageConfig(currentStep)
  
  // Добавляем сообщение пользователя
  const option = stageConfig.user_options?.find(opt => opt.id === choiceId)
  if (option) {
    addUserMessage(option.text)
  }
  
  // Определяем следующий этап
  const nextStage = typeof stageConfig.next_stage === 'object' 
    ? stageConfig.next_stage[choiceId] 
    : stageConfig.next_stage
  
  if (nextStage) {
    localSessionState.dialogue.current_step = normalizeCurrentStep(nextStage)
    if (!Array.isArray(localSessionState.dialogue.selected_options)) {
      localSessionState.dialogue.selected_options = []
    }
    localSessionState.dialogue.selected_options.push(choiceId)
    
    // Получаем конфигурацию следующего этапа
    const nextStageConfig = getStageConfig(nextStage)
    
    // Если это финальный этап, закрываем диалог через 3 секунды
    if (nextStageConfig.is_final) {
      addClientMessage(nextStageConfig.client_message)
      setTimeout(() => {
        closeDialogueDialog()
      }, 3000)
    } else if (nextStageConfig.client_message) {
      addClientMessage(nextStageConfig.client_message)
    }
    
    // Обновляем компонент
    nextTick(() => {
      mountDialogueInterface()
    })
  }
}

// Обработка отправки данных
const handleDataSubmit = (formData) => {
  const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
  const stageConfig = getStageConfig(currentStep)
  
  // Обновляем данные клиента
  Object.keys(formData).forEach(key => {
    if (key === 'credit_amount' || key === 'deposit_amount' || key === 'deposit_period') {
      // Эти поля не входят в client, сохраняем отдельно
      if (!localSessionState.dialogue.formData) {
        localSessionState.dialogue.formData = {}
      }
      localSessionState.dialogue.formData[key] = formData[key]
    } else {
      localSessionState.client[key] = formData[key]
    }
  })
  
  // Добавляем сообщение пользователя с данными
  const dataText = Object.entries(formData)
    .map(([key, value]) => {
      const labels = {
        income: 'Доход',
        expenses: 'Расходы',
        age: 'Возраст',
        credit_history: 'Кредитная история',
        credit_amount: 'Сумма кредита',
        deposit_amount: 'Сумма вклада',
        deposit_period: 'Срок вклада'
      }
      return `${labels[key] || key}: ${value}`
    })
    .join(', ')
  addUserMessage(dataText)
  
  // Переходим к следующему этапу
  const nextStage = stageConfig.next_stage
  if (nextStage) {
    localSessionState.dialogue.current_step = normalizeCurrentStep(nextStage)
    
    const nextStageConfig = getStageConfig(nextStage)
    
    // Если следующий этап - present_results, выполняем расчеты
    if (nextStage === 'present_results') {
      performCalculations()
    }
    
    if (nextStageConfig.client_message) {
      addClientMessage(nextStageConfig.client_message)
    }
    
    // Обновляем компонент
    nextTick(() => {
      mountDialogueInterface()
    })
  }
}

// Добавление сообщения клиента
const addClientMessage = (text) => {
  if (!localSessionState.dialogue.messages) {
    localSessionState.dialogue.messages = []
  }
  localSessionState.dialogue.messages.push({
    role: 'client',
    text: text,
    timestamp: new Date().toISOString()
  })
}

// Добавление сообщения пользователя
const addUserMessage = (text) => {
  if (!localSessionState.dialogue.messages) {
    localSessionState.dialogue.messages = []
  }
  localSessionState.dialogue.messages.push({
    role: 'user',
    text: text,
    timestamp: new Date().toISOString()
  })
}

const calculateScoring = async () => {
  const client = localSessionState.client
  
  // Проверяем что все необходимые данные есть и валидны
  if (!client.income || client.income <= 0 ||
      !client.expenses || client.expenses < 0 ||
      !client.age || client.age < 18 || client.age > 100 ||
      !client.credit_history) {
    return
  }
  
  // Не вызываем во время загрузки
  if (props.isLoading) {
    return
  }
  
  try {
    const url = route('student.simulators.calculate-scoring', { session: props.sessionId })
    const response = await axios.post(url, {
      income: Number(client.income),
      expenses: Number(client.expenses),
      age: Number(client.age),
      credit_history: String(client.credit_history),
      has_deposit: Boolean(client.has_deposit || false)
    })
    
    const scoringData = response.data
    
    // Обновляем calculations в состоянии
    Object.assign(localSessionState.calculations, {
      credit_score: scoringData.credit_score,
      decision: scoringData.decision,
      interest_rate: scoringData.interest_rate,
      credit_limit: scoringData.credit_limit
    })
  } catch (error) {
    // Тихая обработка ошибок (не логируем, чтобы не засорять консоль)
    if (error.response?.status !== 422) {
      console.error('Ошибка расчета скоринга:', error)
    }
  }
}

// Debounced функция для расчета скоринга (500ms задержка)
const debouncedCalculateScoring = useDebounceFn(calculateScoring, 500)

// Watch для отслеживания изменений данных клиента и автоматического расчета скоринга
watch(() => [
  localSessionState.client.income,
  localSessionState.client.expenses,
  localSessionState.client.age,
  localSessionState.client.credit_history,
  localSessionState.client.has_deposit
], () => {
  // Не вызываем расчет во время загрузки
  if (props.isLoading) {
    return
  }
  
  // Проверяем что все необходимые данные есть
  const client = localSessionState.client
  if (!client.income || !client.expenses || !client.age || !client.credit_history) {
    return
  }
  
  // Вызываем debounced функцию расчета скоринга
  debouncedCalculateScoring()
}, { deep: true })

// Выполнение расчетов (вызывается при переходе к present_results)
const performCalculations = () => {
  // Вызываем расчет скоринга
  calculateScoring()
}

// Закрытие диалога
const closeDialogueDialog = () => {
  showDialogueDialog.value = false
  activeDialog.value = null
  
  // Размонтируем Vue приложение
  if (dialogueAppRef.value) {
    dialogueAppRef.value.unmount()
    dialogueAppRef.value = null
  }
}

const onDialogueDialogClose = () => {
  closeDialogueDialog()
}

const onDialogueDialogVisibilityChange = (visible) => {
  if (!visible) {
    closeDialogueDialog()
  }
}

// Watch для автоматического сохранения состояния диалогов
watch([showPhoneDialog, showCalculatorDialog, showDocumentsDialog], ([phone, calc, docs]) => {
  if (phone) {
    saveDialogState('phone')
  } else if (calc) {
    saveDialogState('calculator')
  } else if (docs) {
    saveDialogState('documents')
  } else {
    saveDialogState(null)
  }
})

// Автосохранение состояния при изменениях localSessionState
watch(() => localSessionState, (newState) => {
  // Не сохраняем во время загрузки или если нет функции автосохранения
  if (props.isLoading || !props.autoSave) {
    return
  }
  
  // Проверяем, что состояние действительно изменилось (не пустое)
  if (!newState.dialogue && !newState.client && !newState.calculations) {
    return
  }
  
  // Нормализуем current_step (на случай если стал массивом)
  const currentStep = normalizeCurrentStep(newState.dialogue?.current_step)
  
  // Создаем копию состояния для автосохранения
  const stateToSave = {
    dialogue: {
      messages: Array.isArray(newState.dialogue?.messages) ? newState.dialogue.messages : [],
      current_step: currentStep,
      selected_options: Array.isArray(newState.dialogue?.selected_options) ? newState.dialogue.selected_options : [],
      formData: newState.dialogue?.formData || {}
    },
    client: newState.client || {},
    calculations: newState.calculations || {},
    ui: {
      activeTab: newState.ui?.activeTab || '0',
      activeDialog: newState.ui?.activeDialog || null
    }
  }
  
  props.autoSave(stateToSave)
}, { deep: true })
</script>

<style scoped>
.office-scene {
  width: 100%;
  height: 100%;
  display: block;
  margin: 0;
  padding: 0;
}
</style>
