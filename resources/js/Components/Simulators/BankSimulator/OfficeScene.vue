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
    <!-- Камера (first-person view из офисного кресла) -->
    <TresPerspectiveCamera 
      :position="[0, 1.6, 0]" 
      :fov="75"
      :near="0.1"
      :far="1000"
    />
    
    <!-- TODO: DEV ONLY - OrbitControls для 360 просмотра (временно для разработки, убрать в продакшене) -->
    <OrbitControlsDev />
    
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
      :door-position="[0, 0, -4.2]"
      :palm-left-position="[2.6, 0, 2.1]"
      :palm-right-position="[2.6, 0, -2.1]"
      :plant-position="[-1.2, 0, -3.6]"
      :sofa-position="[0, 0, 0]"
    />
    
    <!-- Стол -->
    <Desk 
      :position="[-0.9, 0, -0.4]"
      :rotation="[0, Math.PI, 0]"
      :scale="[1, 1, 1]"
    />
    
    <!-- Ноутбук на столе -->
    <Laptop 
      :position="[-1.005, 0.7974, -0.45]"
      :rotation="[0, Math.PI, 0]"
      :color="laptopColor"
    />
    
    <!-- Телефон -->
    <Phone 
      :position="[-1.3, 0.7955, -0.5]"
      :base-scale="[0.001, 0.001, 0.001]"
      :base-rotation="[Math.PI / 2, 0, 0.5]"
      :is-ringing="isPhoneRinging"
      @click="onPhoneClick"
    />
    
    <!-- Документы -->
    <Documents 
      :position="[0.5, 0.9, -0.5]"
      :rotation="[0, Math.PI, 0]"
      :count="3"
      @click="onDocumentsClick"
    />
    
    <!-- Кактус (опционально) -->
    <Calculator 
      :position="[0.7, 0.9, -0.55]"
      @click="onCalculatorClick"
    />

    <!-- Кресло работника -->
    <Armchair 
      :position="[0, 0, -2.6]"
      :rotation="[0, 0, 0]"
      :scale="[1, 1, 1]"
    />

    <!-- Кресла клиентов (2 шт) -->
    <Chair 
      :position="[-0.4, 0, 2.2]"
      :rotation="[0, 2.7, 0]"
      :scale="[1, 1, 1]"
    />
    <Chair 
      :position="[0.4, 0, 2.2]"
      :rotation="[0, -2.65, 0]"
      :scale="[1, 1, 1]"
    />
    
    <!-- Клиент напротив -->
    <ClientCharacter 
      :position="[0, 0, 2]"
      :is-speaking="isClientSpeaking"
    />
    
    <!-- CSS3DRenderer для 3D диалогов -->
    <CSS3DRendererPlugin />
    
    <!-- 3D диалоги (рядом с монитором, в одной плоскости, чуть больше экрана) -->
    <!-- Все диалоги в одной плоскости Z=-0.6, но с небольшим смещением по Z для предотвращения перекрытия -->
    <Dialog3D
      v-if="showPhoneDialog"
      :visible="showPhoneDialog"
      header="Телефон"
      :position="[0, 1.2, -0.6]"
      :width="500"
      :height="350"
      @update:visible="showPhoneDialog = $event"
      @close="onPhoneDialogClose"
    />
    
    <Dialog3D
      v-if="showCalculatorDialog"
      :visible="showCalculatorDialog"
      header="Калькулятор"
      :position="[0, 1.2, -0.6]"
      :width="500"
      :height="350"
      @update:visible="showCalculatorDialog = $event"
      @close="onCalculatorDialogClose"
    />
    
    <Dialog3D
      v-if="showDocumentsDialog"
      :visible="showDocumentsDialog"
      header="Документы"
      :position="[0, 1.2, -0.6]"
      :width="500"
      :height="350"
      @update:visible="showDocumentsDialog = $event"
      @close="onDocumentsDialogClose"
    />
  </TresCanvas>
</template>

<script setup>
import { computed, ref, watch, onMounted, shallowRef, watchEffect } from 'vue'
import { TresCanvas } from '@tresjs/core'
import { LinearToneMapping, PCFSoftShadowMap, SRGBColorSpace } from 'three'
import Armchair from './Armchair.vue'
import Chair from './Chair.vue'
import Desk from './Desk.vue'
import Laptop from './Laptop.vue'
import OfficeInterior from './OfficeInterior.vue'
import Phone from './Phone.vue'
import Documents from './Documents.vue'
import Calculator from './Calculator.vue'
import Dialog3D from './Dialog3D.vue'
import CSS3DRendererPlugin from './CSS3DRendererPlugin.vue'
import ClientCharacter from './ClientCharacter.vue'
// TODO: DEV ONLY - Временное решение для разработки, убрать в продакшене
import OrbitControlsDev from './OrbitControlsDev.vue'

const props = defineProps({
  sessionState: {
    type: Object,
    default: () => ({})
  },
  sessionId: {
    type: Number,
    required: true
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
  const currentState = props.sessionState || {}
  const newState = {
    ...currentState,
    ui: {
      ...currentState.ui,
      activeDialog: dialogName || null
    }
  }
  
  // Отправляем на backend (заглушка - будет реализовано позже)
  // router.put(route('simulators.state.update', props.sessionId), {
  //   state: newState
  // }, {
  //   preserveState: true,
  //   preserveScroll: true,
  //   only: ['session']
  // })
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
  if (dialogue.current_step === 'client_speaking') {
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

const onPhoneClick = () => {
  if (closeOtherDialogsWithWarning('phone')) {
    showPhoneDialog.value = true
    saveDialogState('phone')
    emit('phoneClick')
  }
}

const onDocumentsClick = () => {
  if (closeOtherDialogsWithWarning('documents')) {
    showDocumentsDialog.value = true
    saveDialogState('documents')
    emit('documentsClick')
  }
}

const onCalculatorClick = () => {
  if (closeOtherDialogsWithWarning('calculator')) {
    showCalculatorDialog.value = true
    saveDialogState('calculator')
    emit('calculatorClick')
  }
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

// Watch для автоматического сохранения состояния
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
