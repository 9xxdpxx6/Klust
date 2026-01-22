<template>
  <TresCanvas 
    class="office-scene"
    :shadows="true"
    :clear-color="'#E0F6FF'"
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
    <TresAmbientLight :intensity="0.6" />
    <TresDirectionalLight 
      :position="[5, 10, 5]" 
      :intensity="0.8"
      :cast-shadow="true"
    />
    
    <!-- Пол -->
    <TresMesh :position="[0, 0, -5]" :rotation-x="-Math.PI / 2" :receive-shadow="true">
      <TresPlaneGeometry :args="[20, 20]" />
      <TresMeshStandardMaterial color="#cccccc" />
    </TresMesh>
    
    <!-- Стол -->
    <Desk />
    
    <!-- Монитор на столе -->
    <Monitor 
      :position="[0, 1.2, -0.8]"
      :color="monitorColor"
    />
    
    <!-- Телефон -->
    <Phone 
      :position="[-0.5, 0.9, -0.5]"
      :is-ringing="isPhoneRinging"
      @click="onPhoneClick"
    />
    
    <!-- Документы -->
    <Documents 
      :position="[0.5, 0.9, -0.5]"
      :count="3"
      @click="onDocumentsClick"
    />
    
    <!-- Калькулятор -->
    <Calculator 
      :position="[0.8, 0.9, -0.5]"
      @click="onCalculatorClick"
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
import { computed, ref, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { TresCanvas } from '@tresjs/core'
import Desk from './Desk.vue'
import Monitor from './Monitor.vue'
import Phone from './Phone.vue'
import Documents from './Documents.vue'
import Calculator from './Calculator.vue'
import Dialog3D from './Dialog3D.vue'
import CSS3DRendererPlugin from './CSS3DRendererPlugin.vue'
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

const monitorColor = computed(() => {
  const score = props.sessionState?.calculations?.credit_score
  if (score >= 0.8) return '#4ade80'
  if (score >= 0.5) return '#fbbf24'
  if (score >= 0.3) return '#f97316'
  return '#1e40af' // Дефолтный синий
})

const isPhoneRinging = computed(() => {
  return props.sessionState?.phone?.isRinging === true
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
