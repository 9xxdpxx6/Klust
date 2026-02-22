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
        @door-click="onDoorClick"
        @laptop-click="onLaptopClick"
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
  
  <!-- Меню выбора варианта симулятора -->
  <VariantSelector
    :visible="showVariantSelector"
    :variants-progress="sessionState.localSessionState.variants_progress || {}"
    @select="onVariantSelected"
    @close="showVariantSelector = false"
    @complete="handleCompleteSimulator"
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
import VariantSelector from './VariantSelector.vue'

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

const emit = defineEmits(['completeSimulator'])

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

// ── Score aggregation across all 4 variants ──
const VARIANT_KEYS = ['credit_card', 'mortgage', 'consumer_loan', 'deposit']

/**
 * Calculate aggregated final score from all completed variants.
 * Rule: sum of normalized scores; if sum > 100, use average instead.
 * Returns { score, allCompleted, completedCount, total }
 */
const calculateFinalScore = () => {
  const progress = sessionState.localSessionState.variants_progress || {}
  const completedVariants = VARIANT_KEYS.filter(k => progress[k]?.status === 'completed')
  const scores = completedVariants.map(k => progress[k]?.normalized_score ?? 0)
  const sum = scores.reduce((a, b) => a + b, 0)
  const avg = completedVariants.length > 0 ? sum / completedVariants.length : 0

  return {
    score: sum <= 100 ? Math.round(sum) : Math.round(avg),
    allCompleted: completedVariants.length === VARIANT_KEYS.length,
    completedCount: completedVariants.length,
    total: VARIANT_KEYS.length
  }
}

/**
 * Handle "Завершить сессию" from DialogueInterface or VariantSelector.
 * If all 4 variants completed → emit aggregated score to parent page.
 * Otherwise → close dialogue, show variant selector with remaining variants.
 */
const handleCompleteSimulator = () => {
  const result = calculateFinalScore()

  if (result.allCompleted) {
    // Save aggregated max_score=100 in state so ProgressLogService normalizes correctly
    if (props.updateState) {
      props.updateState({ max_score: 100 })
    }
    emit('completeSimulator', {
      score: result.score,
      variants_progress: sessionState.localSessionState.variants_progress
    })
  } else {
    // Not all variants done — close current dialogue, show variant selector
    dialogs.closeDialogueDialog()
    if (dialogs.showPhoneDialog) dialogs.showPhoneDialog.value = false
    if (dialogs.showCalculatorDialog) dialogs.showCalculatorDialog.value = false
    if (dialogs.showDocumentsDialog) dialogs.showDocumentsDialog.value = false

    // Open variant selector so student can pick the next one
    pendingDoorPayload.value = lastDoorPayload.value || DEFAULT_DOOR_PAYLOAD
    showVariantSelector.value = true
  }
}

// Restart confirmation dialog
const showRestartConfirm = ref(false)

// Variant selector state
const showVariantSelector = ref(false)
const pendingDoorPayload = ref(null)
const lastDoorPayload = ref(null)
// Door world position (local [3.95, 0, 0.5] rotated by -PI/2 → world [-0.5, 0, 3.95])
const DEFAULT_DOOR_PAYLOAD = { position: [-0.5, 0, 3.95] }

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
  },
  onCompleteSession: handleCompleteSimulator
})

const handleRestartConfirm = async () => {
  showRestartConfirm.value = false

  // Close dialogue UI first
  dialogs.closeDialogueDialog()
  if (dialogs.showPhoneDialog) dialogs.showPhoneDialog.value = false
  if (dialogs.showCalculatorDialog) dialogs.showCalculatorDialog.value = false
  if (dialogs.showDocumentsDialog) dialogs.showDocumentsDialog.value = false

  // Animate client exit: stand up → walk to door → disappear
  if (clientCharacter.isClientVisible.value && clientCharacter.clientState.value === 'seated') {
    await clientCharacter.exitClient()
  }

  // Reset dialogue + backend state
  await dialogueSystem.handleRestartSession()
  // Reset client 3D character (hide model, return to idle)
  clientCharacter.resetClient()

  // Auto-open variant selector after client fully exits on restart
  pendingDoorPayload.value = lastDoorPayload.value || DEFAULT_DOOR_PAYLOAD
  showVariantSelector.value = true
}

// Door click — show variant selector instead of directly generating client
const onDoorClick = (payload) => {
  // If client is already present, ignore
  if (clientCharacter.isClientVisible.value && clientCharacter.clientState.value !== 'idle') {
    return
  }
  // Save door payload and show variant selector
  pendingDoorPayload.value = payload
  lastDoorPayload.value = payload
  showVariantSelector.value = true
}

// Variant selected — generate client with chosen dialogue type
const onVariantSelected = (dialogueType) => {
  showVariantSelector.value = false
  if (pendingDoorPayload.value) {
    clientCharacter.onDoorClick(pendingDoorPayload.value, dialogueType)
    pendingDoorPayload.value = null
  }
}

// Laptop click — reopen dialogue dialog if client is seated
const onLaptopClick = () => {
  // Only reopen if there's a client present (dialogue is ongoing)
  if (clientCharacter.hasClient.value && !dialogs.showDialogueDialog.value) {
    dialogueSystem.openDialogueDialog()
  }
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
