import { reactive, watch, computed } from 'vue'

/**
 * Composable for managing session state synchronization
 * 
 * @param {Object} options - Configuration options
 * @param {Object} options.sessionState - Session state from props
 * @param {Object} options.isLoading - Loading state ref
 * @param {Function} options.autoSave - Auto-save function
 * @returns {Object} Session state management functions and state
 */
export function useSessionState({ sessionState, isLoading, autoSave }) {
  /**
   * Local session state (using reactive for deep reactivity)
   */
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

  /**
   * Normalize current_step (if it became an array due to incorrect merge)
   */
  const normalizeCurrentStep = (step) => {
    if (Array.isArray(step)) {
      return step[step.length - 1] || 'greeting'
    }
    return step || 'greeting'
  }

  /**
   * Initialize state from props
   */
  watch(() => sessionState?.value, (newState) => {
    if (newState && !isLoading?.value) {
      // Update only if not loading
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

  /**
   * Handle bank interface active tab change
   */
  const onBankTabChange = (tab) => {
    if (!localSessionState.ui) {
      localSessionState.ui = {}
    }
    localSessionState.ui.activeTab = tab
    
    // Save to state via auto-save
    if (autoSave && !isLoading?.value) {
      autoSave({
        ui: {
          activeTab: tab
        }
      })
    }
  }

  /**
   * Auto-save state on localSessionState changes
   */
  watch(() => localSessionState, (newState) => {
    // Don't save during loading or if no auto-save function
    if (isLoading?.value || !autoSave) {
      return
    }
    
    // Check that state actually changed (not empty)
    if (!newState.dialogue && !newState.client && !newState.calculations) {
      return
    }
    
    // Normalize current_step (in case it became an array)
    const currentStep = normalizeCurrentStep(newState.dialogue?.current_step)
    
    // Create copy of state for auto-save
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
    
    autoSave(stateToSave)
  }, { deep: true })

  return {
    // State (reactive object, not computed)
    localSessionState,
    
    // Methods
    normalizeCurrentStep,
    onBankTabChange
  }
}
