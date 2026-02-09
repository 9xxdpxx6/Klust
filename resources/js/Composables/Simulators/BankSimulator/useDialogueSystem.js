import { computed, watch, nextTick, h } from 'vue'
import { createApp } from 'vue'
import DialogueInterface from '@/Components/Simulators/BankSimulator/DialogueInterface.vue'

/**
 * Composable for managing dialogue system (stages, messages, forms)
 * 
 * @param {Object} options - Configuration options
 * @param {Object} options.localSessionState - Local session state
 * @param {Object} options.showDialogueDialog - Ref for dialogue dialog visibility
 * @param {Object} options.dialogueDialogRef - Ref for dialogue dialog component
 * @param {Object} options.dialogueAppRef - Ref for dialogue Vue app instance
 * @param {Function} options.closeDialogueDialog - Function to close dialogue dialog
 * @param {Function} options.normalizeCurrentStep - Function to normalize current step
 * @param {Function} options.performCalculations - Function to perform calculations
 * @param {Function} options.closeOtherDialogs - Function to close other dialogs
 * @param {Function} options.setActiveDialog - Function to set active dialog
 * @returns {Object} Dialogue system functions and state
 */
export function useDialogueSystem({
  localSessionState,
  showDialogueDialog,
  dialogueDialogRef,
  dialogueAppRef,
  closeDialogueDialog,
  normalizeCurrentStep,
  performCalculations,
  closeOtherDialogs,
  setActiveDialog
}) {
  /**
   * Get stage configuration (stub - will be replaced with API call)
   */
  const getStageConfig = (stageId) => {
    // Stub - in reality will be DialogueService call via API
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

  /**
   * Current stage configuration
   */
  const currentStageConfig = computed(() => {
    const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    return getStageConfig(currentStep)
  })

  /**
   * Add client message
   */
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

  /**
   * Add user message
   */
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

  /**
   * Mount DialogueInterface in Dialog3D
   */
  const mountDialogueInterface = () => {
    if (!dialogueDialogRef.value || !showDialogueDialog.value) return
    
    // Wait for Dialog3D to create DOM elements
    const findDialogContent = () => {
      // Find content element of Dialog3D by class
      const dialogContent = document.querySelector('.dialog-3d-content')
      if (!dialogContent) {
        setTimeout(findDialogContent, 50)
        return
      }
      
      // Clear previous content
      if (dialogueAppRef.value) {
        dialogueAppRef.value.unmount()
        dialogueAppRef.value = null
      }
      
      // Create container for Vue component
      const container = document.createElement('div')
      container.id = 'dialogue-interface-container'
      container.style.width = '100%'
      container.style.height = '100%'
      
      // Clear content and add container
      dialogContent.innerHTML = ''
      dialogContent.appendChild(container)
      
      // Create Vue app with DialogueInterface
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
    
    // Start searching for content element
    nextTick(() => {
      findDialogContent()
    })
  }

  /**
   * Handle option selection
   */
  const handleOptionSelect = (choiceId) => {
    const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    const stageConfig = getStageConfig(currentStep)
    
    // Add user message
    const option = stageConfig.user_options?.find(opt => opt.id === choiceId)
    if (option) {
      addUserMessage(option.text)
    }
    
    // Determine next stage
    const nextStage = typeof stageConfig.next_stage === 'object' 
      ? stageConfig.next_stage[choiceId] 
      : stageConfig.next_stage
    
    if (nextStage) {
      localSessionState.dialogue.current_step = normalizeCurrentStep(nextStage)
      if (!Array.isArray(localSessionState.dialogue.selected_options)) {
        localSessionState.dialogue.selected_options = []
      }
      localSessionState.dialogue.selected_options.push(choiceId)
      
      // Get next stage configuration
      const nextStageConfig = getStageConfig(nextStage)
      
      // If this is final stage, close dialog after 3 seconds
      if (nextStageConfig.is_final) {
        addClientMessage(nextStageConfig.client_message)
        setTimeout(() => {
          closeDialogueDialog()
        }, 3000)
      } else if (nextStageConfig.client_message) {
        addClientMessage(nextStageConfig.client_message)
      }
      
      // Update component
      nextTick(() => {
        mountDialogueInterface()
      })
    }
  }

  /**
   * Handle data submission
   */
  const handleDataSubmit = (formData) => {
    const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    const stageConfig = getStageConfig(currentStep)
    
    // Update client data
    Object.keys(formData).forEach(key => {
      if (key === 'credit_amount' || key === 'deposit_amount' || key === 'deposit_period') {
        // These fields don't belong to client, save separately
        if (!localSessionState.dialogue.formData) {
          localSessionState.dialogue.formData = {}
        }
        localSessionState.dialogue.formData[key] = formData[key]
      } else {
        localSessionState.client[key] = formData[key]
      }
    })
    
    // Add user message with data
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
    
    // Move to next stage
    const nextStage = stageConfig.next_stage
    if (nextStage) {
      localSessionState.dialogue.current_step = normalizeCurrentStep(nextStage)
      
      const nextStageConfig = getStageConfig(nextStage)
      
      // If next stage is present_results, perform calculations
      if (nextStage === 'present_results') {
        if (performCalculations) {
          performCalculations()
        }
      }
      
      if (nextStageConfig.client_message) {
        addClientMessage(nextStageConfig.client_message)
      }
      
      // Update component
      nextTick(() => {
        mountDialogueInterface()
      })
    }
  }

  /**
   * Open dialogue dialog
   */
  const openDialogueDialog = () => {
    if (showDialogueDialog.value) return
    
    // Close other dialogs
    if (closeOtherDialogs) {
      closeOtherDialogs()
    }
    if (setActiveDialog) {
      setActiveDialog(null)
    }
    
    // Initialize dialogue with greeting if no messages yet
    if (localSessionState.dialogue.messages.length === 0) {
      const stageConfig = getStageConfig('greeting')
      addClientMessage(stageConfig.client_message)
    }
    
    showDialogueDialog.value = true
    
    // Mount DialogueInterface in Dialog3D
    nextTick(() => {
      mountDialogueInterface()
    })
  }

  // Watch for stage changes to update component
  watch(() => localSessionState.dialogue.current_step, () => {
    if (showDialogueDialog.value) {
      nextTick(() => {
        mountDialogueInterface()
      })
    }
  })

  return {
    // Methods
    getStageConfig,
    openDialogueDialog,
    mountDialogueInterface,
    handleOptionSelect,
    handleDataSubmit,
    addClientMessage,
    addUserMessage,
    
    // Computed
    currentStageConfig
  }
}
