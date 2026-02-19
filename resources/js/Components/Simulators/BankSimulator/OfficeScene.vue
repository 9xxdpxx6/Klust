<template>
  <TresCanvas 
    class="office-scene"
    :shadows="true"
    :clear-color="'#E0F6FF'"
    :dpr="[1, 2]"
    :output-color-space="lighting.outputColorSpace"
    :tone-mapping="lighting.toneMapping"
    :tone-mapping-exposure="lighting.toneMappingExposure"
    :use-legacy-lights="false"
    :shadow-map-type="lighting.shadowMapType"
  >
    <OfficeSceneSetup :dev-mode="devMode" :directionalLightRef="lighting.directionalLightRef">
      <!-- Мебель и объекты сцены -->
      <OfficeFurniture
        :laptop-color="laptopColor"
        :has-client="hasClientValue"
        :client-data="sessionState.localSessionState.client"
        :calculations="sessionState.localSessionState.calculations"
        :active-tab="sessionState.localSessionState.ui.activeTab"
        :is-phone-ringing="isPhoneRinging"
        :is-client-visible="isClientVisibleValue"
        :client-position="clientPositionValue"
        :client-rotation="clientRotationValue"
        :client-animation="clientAnimationValue"
        :is-client-speaking="isClientSpeakingValue"
        :preloaded-client-model="preloadedClientModelValue"
        :client-model-path="clientModelPathValue"
        @door-click="clientCharacter.onDoorClick"
        @phone-click="dialogs.onPhoneClick"
        @documents-click="dialogs.onDocumentsClick"
        @bank-tab-change="sessionState.onBankTabChange"
        @client-animation-finished="clientCharacter.onClientAnimationFinished"
    />
    
    <!-- CSS3DRenderer для 3D диалогов -->
    <CSS3DRendererPlugin />
    
    <!-- Единый 3D диалог с динамическим контентом -->
    <Dialog3D
      ref="mainDialogRef"
        :visible="isAnyDialogOpenValue"
        :header="activeDialogHeaderValue"
      :position="[-1.1, 1.15, 0.15]"
      :width="600"
      :height="400"
        @update:visible="dialogs.onDialogVisibilityChange"
        @close="dialogs.onMainDialogClose"
    />
    
    <!-- Диалог с клиентом -->
    <Dialog3D
      ref="dialogueDialogRef"
        :visible="showDialogueDialogValue"
      header="Диалог с клиентом"
      :position="[-0.9, 1.15, 0.15]"
      :width="600"
      :height="500"
        @update:visible="dialogs.onDialogueDialogVisibilityChange"
        @close="dialogs.onDialogueDialogClose"
    />
    </OfficeSceneSetup>
  </TresCanvas>
  
  <!-- Калькуляторы (PrimeVue Dialogs) -->
  <CreditCalculatorDialog
    :visible="showCreditCalculatorValue"
    @update:visible="(val) => dialogs.showCreditCalculator.value = val"
    :session-id="sessionId"
    :default-rate="sessionState.localSessionState.calculations?.interest_rate"
    @close="dialogs.onCreditCalculatorClose"
  />
  
  <DepositCalculatorDialog
    :visible="showDepositCalculatorValue"
    @update:visible="(val) => dialogs.showDepositCalculator.value = val"
    :session-id="sessionId"
    @close="dialogs.onDepositCalculatorClose"
  />
  
  <!-- Модалка подтверждения перезапуска -->
  <DangerConfirmDialog
    v-model:visible="showRestartConfirm"
    type="warning"
    title="Начать заново?"
    message="Вы уверены, что хотите начать заново?"
    confirm-text="Начать заново"
    cancel-text="Отмена"
    default-message="Весь прогресс будет сброшен."
    @confirm="handleRestartConfirm"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { TresCanvas } from '@tresjs/core'
import { useSceneLighting } from '@/Composables/Simulators/BankSimulator/useSceneLighting.js'
import { useDialogManager } from '@/Composables/Simulators/BankSimulator/useDialogManager.js'
import { useClientCharacter } from '@/Composables/Simulators/BankSimulator/useClientCharacter.js'
import { useDialogueSystem } from '@/Composables/Simulators/BankSimulator/useDialogueSystem.js'
import { useScoring } from '@/Composables/Simulators/BankSimulator/useScoring.js'
import { useSessionState } from '@/Composables/Simulators/BankSimulator/useSessionState.js'
import OfficeSceneSetup from './OfficeSceneSetup.vue'
import OfficeFurniture from './OfficeFurniture.vue'
import CreditCalculatorDialog from './CreditCalculatorDialog.vue'
import DepositCalculatorDialog from './DepositCalculatorDialog.vue'
import Dialog3D from './Dialog3D.vue'
import CSS3DRendererPlugin from './CSS3DRendererPlugin.vue'
import DangerConfirmDialog from '@/Components/UI/DangerConfirmDialog.vue'

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

// Refs for dialogs
const mainDialogRef = ref(null)
const dialogueDialogRef = ref(null)
const dialogueAppRef = ref(null)

// Use composables
const lighting = useSceneLighting()

// Session state composable
const sessionState = useSessionState({
  sessionState: computed(() => props.sessionState),
  isLoading: computed(() => props.isLoading),
  autoSave: props.autoSave
})

// Dialog manager composable (must be created before dialogue system)
const dialogs = useDialogManager({
  sessionState: sessionState.localSessionState,
  updateState: props.updateState,
  emit,
  refs: {
    mainDialogRef,
    dialogueDialogRef,
    dialogueAppRef
  }
})

// Scoring composable
const scoring = useScoring({
  sessionId: props.sessionId,
  localSessionState: sessionState.localSessionState,
  isLoading: computed(() => props.isLoading)
})

// Restart confirmation dialog
const showRestartConfirm = ref(false)

// Dialogue system composable (uses dialog manager's showDialogueDialog)
const dialogueSystem = useDialogueSystem({
  localSessionState: sessionState.localSessionState,
  showDialogueDialog: dialogs.showDialogueDialog,
  dialogueDialogRef,
  dialogueAppRef,
  closeDialogueDialog: dialogs.closeDialogueDialog,
  normalizeCurrentStep: sessionState.normalizeCurrentStep,
  performCalculations: scoring.performCalculations,
  closeOtherDialogs: () => {
    dialogs.showPhoneDialog.value = false
    dialogs.showCalculatorDialog.value = false
    dialogs.showDocumentsDialog.value = false
  },
  setActiveDialog: (value) => {
    dialogs.activeDialog.value = value
  },
  sessionId: props.sessionId,
  updateState: props.updateState,
  openCreditCalculator: dialogs.openCreditCalculator,
  openDepositCalculator: dialogs.openDepositCalculator,
  onPhoneClick: dialogs.onPhoneClick,
  onDocumentsClick: dialogs.onDocumentsClick,
  onRestartRequest: () => {
    showRestartConfirm.value = true
  }
})

const handleRestartConfirm = async () => {
  showRestartConfirm.value = false
  // Reset dialogue + backend state
  await dialogueSystem.handleRestartSession()
  // Reset client 3D character (hide model, return to idle)
  clientCharacter.resetClient()
}

// Client character composable
const clientCharacter = useClientCharacter({
  sessionId: props.sessionId,
  localSessionState: sessionState.localSessionState,
  openDialogueDialog: dialogueSystem.openDialogueDialog,
  normalizeCurrentStep: sessionState.normalizeCurrentStep,
  sessionState: computed(() => props.sessionState)
})

// Computed properties
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

// Computed wrappers for props - extract primitive values from refs/computed
// In Vue 3, computed automatically unwraps when read, so we just need to read the values
const hasClientValue = computed(() => {
  // hasClient is computed - Vue unwraps it automatically when we read it
  return clientCharacter.hasClient.value ?? false
})
const isClientVisibleValue = computed(() => {
  // isClientVisible is ref
  return clientCharacter.isClientVisible.value ?? false
})
const clientPositionValue = computed(() => {
  // clientPositionArray is computed - Vue unwraps it automatically when we read it
  return clientCharacter.clientPositionArray.value ?? [0, 0, 0]
})
const clientRotationValue = computed(() => {
  // clientRotation is ref
  const val = clientCharacter.clientRotation.value
  return Array.isArray(val) ? val : [0, 0, 0]
})
const clientAnimationValue = computed(() => {
  // clientAnimation is ref
  const val = clientCharacter.clientAnimation.value
  return typeof val === 'string' ? val : 'idle'
})
const isClientSpeakingValue = computed(() => {
  // isClientSpeaking is computed - Vue unwraps it automatically when we read it
  return clientCharacter.isClientSpeaking.value ?? false
})
const preloadedClientModelValue = computed(() => {
  // preloadedClientModel is shallowRef
  return clientCharacter.preloadedClientModel.value ?? null
})
const clientModelPathValue = computed(() => {
  // clientModelPath is ref
  const val = clientCharacter.clientModelPath.value
  return typeof val === 'string' ? val : '/models/characters/female1.glb'
})
const showDialogueDialogValue = computed(() => {
  // showDialogueDialog is ref
  return dialogs.showDialogueDialog.value ?? false
})
const showCreditCalculatorValue = computed(() => {
  // showCreditCalculator is ref
  return dialogs.showCreditCalculator.value ?? false
})
const showDepositCalculatorValue = computed(() => {
  // showDepositCalculator is ref
  return dialogs.showDepositCalculator.value ?? false
})
const isAnyDialogOpenValue = computed(() => {
  // isAnyDialogOpen is computed - Vue unwraps it automatically when we read it
  return dialogs.isAnyDialogOpen.value ?? false
})
const activeDialogHeaderValue = computed(() => {
  // activeDialogHeader is computed - Vue unwraps it automatically when we read it
  const val = dialogs.activeDialogHeader.value
  return typeof val === 'string' ? val : ''
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
