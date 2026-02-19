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
      selected_options: [],
      formData: {}
    },
    client: {
      name: null,
      age: null,
      income: null,
      expenses: null,
      credit_history: null,
      has_deposit: false
    },
    calculations: {},
    ui: {
      activeTab: '0'
    },
    score: 0,
    score_history: []
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
   * Stage order for ALL stages in the dialogue config
   * Used to determine which stage is "more advanced"
   * For branching flows, all branches are listed after their common entry point
   */
  const stageOrder = [
    'greeting',
    'client_pushback',
    'client_need',
    'client_income',
    'client_expenses',
    'client_debts',
    'client_history',
    'bki_check',
    'client_age',
    'scoring_result',
    'client_concern',
    'client_reluctant',
    'client_satisfied',
    'collect_passport',
    'collect_passport_risky',
    'completion',
    'completion_no_docs',
    'future_default_event',
  ]

  /**
   * Merge two message arrays, deduplicating by role+text
   * Keeps all unique messages from both arrays, sorted by timestamp
   */
  const mergeMessages = (backendMessages, localMessages) => {
    const messageMap = new Map()

    // Add backend messages first (stable timestamps)
    if (Array.isArray(backendMessages)) {
      backendMessages.forEach(msg => {
        const key = `${msg.role}::${msg.text}`
        if (!messageMap.has(key)) {
          messageMap.set(key, msg)
        }
      })
    }

    // Add local messages (may have been added after last backend save)
    if (Array.isArray(localMessages)) {
      localMessages.forEach(msg => {
        const key = `${msg.role}::${msg.text}`
        if (!messageMap.has(key)) {
          messageMap.set(key, msg)
        }
      })
    }

    // Sort by timestamp
    return Array.from(messageMap.values()).sort((a, b) => {
      if (!a.timestamp || !b.timestamp) return 0
      return new Date(a.timestamp) - new Date(b.timestamp)
    })
  }

  /**
   * Initialize state from props (watches for changes from backend)
   * CRITICAL: MERGES messages and selected_options instead of replacing them
   * to avoid losing locally-added messages during async operations
   */
  watch(() => sessionState?.value, (newState) => {
    if (newState && !isLoading?.value) {
      const newCurrentStep = normalizeCurrentStep(newState.dialogue?.current_step)
      const localCurrentStep = normalizeCurrentStep(localSessionState.dialogue?.current_step)

      // MERGE messages: combine local + backend, deduped by role+text
      // This prevents losing messages that were added locally but not yet saved to backend
      const localMessages = localSessionState.dialogue?.messages || []
      const backendMessages = Array.isArray(newState.dialogue?.messages) ? newState.dialogue.messages : []
      const mergedMessages = mergeMessages(backendMessages, localMessages)

      // MERGE selected_options: union of local and backend
      const localOptions = Array.isArray(localSessionState.dialogue?.selected_options)
        ? localSessionState.dialogue.selected_options
        : []
      const backendOptions = Array.isArray(newState.dialogue?.selected_options)
        ? newState.dialogue.selected_options
        : []
      const mergedOptions = [...new Set([...backendOptions, ...localOptions])]

      // Determine which current_step to use
      const localIndex = stageOrder.indexOf(localCurrentStep)
      const newIndex = stageOrder.indexOf(newCurrentStep)

      // Keep local current_step if:
      // 1. Backend would downgrade us to 'greeting' but we have messages (state desync)
      // 2. Local is at a known stage that is more advanced than backend
      // 3. Local has more merged messages than backend had (local is ahead of backend)
      const backendWouldDowngrade = localCurrentStep !== 'greeting' && newCurrentStep === 'greeting'
      const localIsMoreAdvancedInOrder = localIndex >= 0 && newIndex >= 0 && localIndex > newIndex
      const localHasMoreData = mergedMessages.length > backendMessages.length && localCurrentStep !== 'greeting'

      const shouldKeepLocal = backendWouldDowngrade || localIsMoreAdvancedInOrder || localHasMoreData

      const finalCurrentStep = shouldKeepLocal ? localCurrentStep : newCurrentStep

      // MERGE formData: local takes precedence (user may have entered data locally)
      const mergedFormData = {
        ...(newState.dialogue?.formData || {}),
        ...(localSessionState.dialogue?.formData || {})
      }

      // Merge client data
      // Display fields (income, expenses, age, credit_history, name) are collected
      // progressively through dialogue — LOCAL values take priority for these.
      // Non-display / internal fields (type, model_path, has_deposit) use backend values.
      const displayFields = new Set(['name', 'income', 'expenses', 'age', 'credit_history'])
      const localClient = localSessionState.client || {}
      const backendClient = newState.client || {}
      const mergedClient = {}
      const allClientKeys = new Set([...Object.keys(localClient), ...Object.keys(backendClient)])
      allClientKeys.forEach(key => {
        const localVal = localClient[key]
        const backendVal = backendClient[key]
        if (displayFields.has(key)) {
          // For display fields: keep local value (even null) — dialogue fills these
          // Only use backend value if local was never set AND there are no messages yet
          const hasDialogueStarted = (localSessionState.dialogue?.messages?.length || 0) > 0
          if (localVal !== null && localVal !== undefined) {
            mergedClient[key] = localVal
          } else if (!hasDialogueStarted && backendVal !== null && backendVal !== undefined) {
            // Restore from backend only on initial load before dialogue starts
            mergedClient[key] = backendVal
          } else {
            mergedClient[key] = localVal ?? null
          }
        } else if (backendVal !== null && backendVal !== undefined) {
          mergedClient[key] = backendVal
        } else if (localVal !== null && localVal !== undefined) {
          mergedClient[key] = localVal
        } else {
          mergedClient[key] = backendVal ?? null
        }
      })

      Object.assign(localSessionState, {
        dialogue: {
          messages: mergedMessages,
          current_step: finalCurrentStep,
          selected_options: mergedOptions,
          formData: mergedFormData
        },
        client: mergedClient,
        calculations: newState.calculations || localSessionState.calculations || {},
        // Score and score_history come from backend (source of truth)
        score: newState.score ?? localSessionState.score ?? 0,
        score_history: Array.isArray(newState.score_history)
          ? newState.score_history
          : (localSessionState.score_history || []),
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
   * Auto-save state on localSessionState changes (debounced)
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
      score: newState.score ?? 0,
      score_history: Array.isArray(newState.score_history) ? newState.score_history : [],
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
