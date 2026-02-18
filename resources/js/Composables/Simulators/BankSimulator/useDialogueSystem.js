import { computed, watch, nextTick, h, ref } from 'vue'
import { createApp } from 'vue'
import axios from 'axios'
import { route } from 'ziggy-js'
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
 * @param {number} options.sessionId - Session ID for API calls
 * @param {Function} options.updateState - Function to update session state
 * @param {Function} options.openCreditCalculator - Function to open credit calculator
 * @param {Function} options.openDepositCalculator - Function to open deposit calculator
 * @param {Function} options.onPhoneClick - Function to open phone dialog
 * @param {Function} options.onDocumentsClick - Function to open documents dialog
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
  setActiveDialog,
  sessionId,
  updateState,
  openCreditCalculator,
  openDepositCalculator,
  onPhoneClick,
  onDocumentsClick,
  onRestartRequest
}) {
  // Cache for stage configurations (reactive)
  const stageConfigCache = ref({})

  /**
   * Get stage configuration from backend API
   */
  const getStageConfig = async (stageId) => {
    if (!stageId) return {}
    
    // Check cache first
    if (stageConfigCache.value[stageId]) {
      return stageConfigCache.value[stageId]
    }

    // If no sessionId, return empty config (fallback)
    if (!sessionId) {
      return {}
    }

    try {
      const context = {
        client: localSessionState.client || {},
        calculations: localSessionState.calculations || {},
        dialogue: localSessionState.dialogue || {}
      }

      const response = await axios.get(
        route('student.simulators.dialogue.stage', { session: sessionId }),
        {
          params: {
            stage_id: stageId,
            context
          }
        }
      )

      if (response.data.success && response.data.stage) {
        const config = response.data.stage
        // Cache the configuration reactively
        stageConfigCache.value[stageId] = config
        return config
      }

      return {}
    } catch (error) {
      return {}
    }
  }

  /**
   * Get stage configuration (synchronous version for computed)
   * Uses cached value or returns empty if not loaded yet
   */
  const getStageConfigSync = (stageId) => {
    if (!stageId) return {}
    return stageConfigCache.value[stageId] || {}
  }

  /**
   * Current stage configuration
   */
  const currentStageConfig = computed(() => {
    const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    return getStageConfigSync(currentStep)
  })

  /**
   * Load stage configuration for current step
   */
  const loadCurrentStageConfig = async () => {
    const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    if (currentStep) {
      await getStageConfig(currentStep)
    }
  }

  /**
   * Execute backend actions via API
   *
   * @param {string} stageId - Current stage ID
   * @param {string|null} optionId - Selected option ID (if any)
   * @returns {Promise<Object>} Result from backend
   */
  const executeBackendActions = async (stageId, optionId = null) => {
    try {
      const context = {
        client: localSessionState.client || {},
        calculations: localSessionState.calculations || {},
        dialogue: localSessionState.dialogue || {},
        score: localSessionState.score || 0,
        score_history: localSessionState.score_history || []
      }

      const response = await axios.post(
        route('student.simulators.dialogue.actions', { session: sessionId }),
        {
          stage_id: stageId,
          option_id: optionId,
          context
        }
      )

      return response.data
      } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || error.message,
        effects: [],
        updates: {},
        messages: []
      }
    }
  }

  /**
   * Execute action effects on frontend
   *
   * @param {Array} effects - Array of effects from backend
   */
  const executeActions = (effects) => {
    if (!Array.isArray(effects)) return

    effects.forEach(effect => {
      switch (effect.type) {
        case 'open_calculator':
          if (effect.calculator === 'credit' && openCreditCalculator) {
            openCreditCalculator()
          } else if (effect.calculator === 'deposit' && openDepositCalculator) {
            openDepositCalculator()
          }
          break

        case 'open_phone':
          if (onPhoneClick) {
            onPhoneClick()
          }
          break

        case 'open_documents':
          if (onDocumentsClick) {
            onDocumentsClick()
          }
          break

        case 'show_message':
          if (effect.role === 'client') {
            addClientMessage(effect.message)
          } else if (effect.role === 'system') {
            addSystemMessage(effect.message)
          } else {
            addUserMessage(effect.message)
          }
          break

        default:
          // Unknown effect type, skipping
      }
    })
  }

  /**
   * Add client message (with duplicate check)
   */
  const addClientMessage = (text) => {
    if (!localSessionState.dialogue.messages) {
      localSessionState.dialogue.messages = []
    }
    
    // Check for duplicates - don't add if message already exists
    const messageExists = localSessionState.dialogue.messages.some(
      m => m.text === text && m.role === 'client'
    )
    
    if (messageExists) {
      // Message already exists, skipping
      return
    }
    
    localSessionState.dialogue.messages.push({
      role: 'client',
      text: text,
      timestamp: new Date().toISOString()
    })
  }

  /**
   * Add user message (with duplicate check)
   */
  const addUserMessage = (text) => {
    if (!localSessionState.dialogue.messages) {
      localSessionState.dialogue.messages = []
    }
    
    // Check for duplicates - don't add if message already exists
    const messageExists = localSessionState.dialogue.messages.some(
      m => m.text === text && m.role === 'user'
    )
    
    if (messageExists) {
      return
    }
    
    localSessionState.dialogue.messages.push({
      role: 'user',
      text: text,
      timestamp: new Date().toISOString()
    })
  }

  /**
   * Add system message (BKI checks, notifications, etc.)
   */
  const addSystemMessage = (text) => {
    if (!localSessionState.dialogue.messages) {
      localSessionState.dialogue.messages = []
    }
    
    // Check for duplicates
    const messageExists = localSessionState.dialogue.messages.some(
      m => m.text === text && m.role === 'system'
    )
    
    if (messageExists) {
      return
    }
    
    localSessionState.dialogue.messages.push({
      role: 'system',
      text: text,
      timestamp: new Date().toISOString()
    })
  }

  /**
   * Mount DialogueInterface in Dialog3D
   */
  const mountDialogueInterface = () => {
    if (!dialogueDialogRef.value || !showDialogueDialog.value) return
    
    // Wait for Dialog3D to create DOM elements for the specific dialogue dialog
    const findDialogContent = () => {
      const dialogComponent = dialogueDialogRef.value
      const dialogContent = dialogComponent?.getContentElement?.()
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
      // Use reactive computed properties that will update automatically
      const currentStageComputed = computed(() => normalizeCurrentStep(localSessionState.dialogue.current_step))
      // Make stageConfig reactive to cache changes
      const stageConfigComputed = computed(() => {
        const stageId = normalizeCurrentStep(localSessionState.dialogue.current_step)
        // Access cache.value to make it reactive
        return stageConfigCache.value[stageId] || {}
      })
      
      const app = createApp({
        components: {
          DialogueInterface
        },
        setup() {
          // Return reactive computed properties
          return {
            sessionState: localSessionState,
            currentStage: currentStageComputed,
            stageConfig: stageConfigComputed,
            handleOptionSelect
          }
        },
        render() {
          // Render function will be called reactively when computed values change
          return h(DialogueInterface, {
            sessionState: localSessionState,
            currentStage: currentStageComputed.value,
            stageConfig: stageConfigComputed.value,
            onOptionSelect: handleOptionSelect,
            onCompleteSession: () => {
              // Emit event to parent component to handle session completion
              // This will be handled by the parent component (BankSimulatorSession)
            },
            onRestartSession: () => {
              if (onRestartRequest) {
                onRestartRequest()
              } else {
                handleRestartSession()
              }
            }
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
   * Apply backend updates directly to localSessionState for immediate reactivity.
   * This ensures data set via on_enter_actions or action results
   * is available locally before the next API call.
   */
  const applyBackendUpdates = (updates) => {
    if (!updates || typeof updates !== 'object') return

    // Apply client data updates
    if (updates.client && typeof updates.client === 'object') {
      Object.keys(updates.client).forEach(key => {
        const value = updates.client[key]
        if (value !== undefined) {
          localSessionState.client[key] = value
        }
      })
    }

    // Apply calculations updates
    if (updates.calculations && typeof updates.calculations === 'object') {
      if (!localSessionState.calculations) {
        localSessionState.calculations = {}
      }
      Object.keys(updates.calculations).forEach(key => {
        const value = updates.calculations[key]
        if (value !== undefined) {
          localSessionState.calculations[key] = value
        }
      })
    }

    // Apply score (take last value if it became an array due to merge)
    if (updates.score !== undefined) {
      const score = Array.isArray(updates.score)
        ? updates.score[updates.score.length - 1]
        : updates.score
      localSessionState.score = score
    }

    // Apply score_history
    if (updates.score_history !== undefined) {
      localSessionState.score_history = Array.isArray(updates.score_history)
        ? updates.score_history
        : []
    }
  }

  /**
   * Handle option selection
   */
  const handleOptionSelect = async (choiceId) => {
    const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    
    // Load stage config if not cached
    let stageConfig = getStageConfigSync(currentStep)
    if (!stageConfig || Object.keys(stageConfig).length === 0) {
      stageConfig = await getStageConfig(currentStep)
    }
    
    // Add user message (only if it doesn't already exist)
    const option = stageConfig.user_options?.find(opt => opt.id === choiceId)
    if (option) {
      // Check if this exact message already exists in history
      const messageExists = localSessionState.dialogue.messages?.some(
        m => m.text === option.text && m.role === 'user'
      )
      
      if (!messageExists) {
        addUserMessage(option.text)
      }
    }
    
    // Determine next stage from config
    const nextStage = typeof stageConfig.next_stage === 'object' 
      ? stageConfig.next_stage[choiceId] 
      : stageConfig.next_stage
    
    // Update selected options FIRST (before backend call)
    if (!Array.isArray(localSessionState.dialogue.selected_options)) {
      localSessionState.dialogue.selected_options = []
    }
    if (!localSessionState.dialogue.selected_options.includes(choiceId)) {
      localSessionState.dialogue.selected_options.push(choiceId)
    }
    
    // Execute backend actions for this option
    if (sessionId && updateState) {
      try {
        const result = await executeBackendActions(currentStep, choiceId)
        
        // Execute frontend effects
        if (result.effects) {
          executeActions(result.effects)
        }
        
        // Show messages from backend
        if (result.messages) {
          result.messages.forEach(msg => {
            if (msg.role === 'client') {
              addClientMessage(msg.message)
            } else if (msg.role === 'system') {
              addSystemMessage(msg.message)
            } else {
              addUserMessage(msg.message)
            }
          })
        }
        
        // Apply backend updates DIRECTLY to localSessionState (immediate reactivity)
        applyBackendUpdates(result.updates)
        
        // Use next_stage from backend if available, otherwise use config
        const finalNextStage = result.next_stage || nextStage
        
        if (finalNextStage) {
          // Transition to next stage FIRST
          await transitionToStage(finalNextStage)
          
          // Then update state with ALL current data: messages, selected_options, current_step
          const stateUpdate = {
            dialogue: {
              messages: Array.isArray(localSessionState.dialogue.messages) 
                ? localSessionState.dialogue.messages 
                : [],
              current_step: normalizeCurrentStep(finalNextStage),
              selected_options: Array.isArray(localSessionState.dialogue.selected_options)
                ? localSessionState.dialogue.selected_options
                : [],
              formData: localSessionState.dialogue.formData || {}
            },
            ...(result.updates || {})
          }
          
          // Merge backend updates if they exist
          if (result.updates && Object.keys(result.updates).length > 0) {
            if (result.updates.dialogue) {
              stateUpdate.dialogue = {
                ...stateUpdate.dialogue,
                ...result.updates.dialogue
              }
            }
            // Merge other updates (client, calculations, score, etc.)
            Object.keys(result.updates).forEach(key => {
              if (key !== 'dialogue') {
                stateUpdate[key] = result.updates[key]
              }
            })
          }
          
          await updateState(stateUpdate)
        } else {
          // No next stage, but save current state (messages, selected_options)
          const stateUpdate = {
            dialogue: {
              messages: Array.isArray(localSessionState.dialogue.messages) 
                ? localSessionState.dialogue.messages 
                : [],
              selected_options: Array.isArray(localSessionState.dialogue.selected_options)
                ? localSessionState.dialogue.selected_options
                : [],
              formData: localSessionState.dialogue.formData || {}
            },
            ...(result.updates || {})
          }
          
          if (result.updates && Object.keys(result.updates).length > 0) {
            if (result.updates.dialogue) {
              stateUpdate.dialogue = {
                ...stateUpdate.dialogue,
                ...result.updates.dialogue
              }
            }
            Object.keys(result.updates).forEach(key => {
              if (key !== 'dialogue') {
                stateUpdate[key] = result.updates[key]
              }
            })
          }
          
          await updateState(stateUpdate)
        }
      } catch (error) {
        // Fallback to local processing - CRITICAL: always transition even if backend fails
        if (nextStage) {
          try {
            await transitionToStage(nextStage)
            // Save state even in fallback
            await updateState({
              dialogue: {
                messages: Array.isArray(localSessionState.dialogue.messages) 
                  ? localSessionState.dialogue.messages 
                  : [],
                current_step: normalizeCurrentStep(nextStage),
                selected_options: Array.isArray(localSessionState.dialogue.selected_options)
                  ? localSessionState.dialogue.selected_options
                  : [],
                formData: localSessionState.dialogue.formData || {}
              }
            }, true)
          } catch (transitionError) {
            // Last resort: try to update current_step directly
            localSessionState.dialogue.current_step = normalizeCurrentStep(nextStage)
            // Still try to save
            await updateState({
              dialogue: {
                messages: Array.isArray(localSessionState.dialogue.messages) 
                  ? localSessionState.dialogue.messages 
                  : [],
                current_step: normalizeCurrentStep(nextStage),
                selected_options: Array.isArray(localSessionState.dialogue.selected_options)
                  ? localSessionState.dialogue.selected_options
                  : []
              }
            }, true)
          }
        } else {
          // No next stage, but save current messages and selected_options
          await updateState({
            dialogue: {
              messages: Array.isArray(localSessionState.dialogue.messages) 
                ? localSessionState.dialogue.messages 
                : [],
              selected_options: Array.isArray(localSessionState.dialogue.selected_options)
                ? localSessionState.dialogue.selected_options
                : []
            }
          }, true)
        }
      }
    } else {
      // Fallback: local processing without backend
      if (nextStage) {
        await transitionToStage(nextStage)
      }
    }
  }

  /**
   * Extract data from client message text
   * Tries to find numbers and keywords in client_message
   * 
   * @param {string} message - Client message text
   * @param {string} field - Field name to extract (income, expenses, age, credit_amount, etc.)
   * @returns {number|string|null} Extracted value or null
   */
  const extractDataFromMessage = (message, field) => {
    if (!message) return null
    
    const lowerMessage = message.toLowerCase()
    
    // Extract numbers from message
    const numbers = message.match(/\d[\d\s]*[\d]/g)?.map(n => parseInt(n.replace(/\s/g, ''))) || []
    
    switch (field) {
      case 'income':
      case 'expenses':
        // Look for keywords like "доход", "зарплата", "расходы", "трачу"
        if (field === 'income' && (lowerMessage.includes('доход') || lowerMessage.includes('зарплата') || lowerMessage.includes('получаю'))) {
          return numbers.length > 0 ? numbers[0] : null
        }
        if (field === 'expenses' && (lowerMessage.includes('расход') || lowerMessage.includes('трачу') || lowerMessage.includes('траты'))) {
          return numbers.length > 0 ? numbers[0] : null
        }
        return numbers.length > 0 ? numbers[0] : null
        
      case 'age':
        if (lowerMessage.includes('лет') || lowerMessage.includes('год')) {
          return numbers.length > 0 ? numbers[0] : null
        }
        return numbers.length > 0 && numbers[0] >= 18 && numbers[0] <= 100 ? numbers[0] : null
        
      case 'credit_amount':
      case 'deposit_amount':
        if (lowerMessage.includes('рубл') || lowerMessage.includes('сумм')) {
          return numbers.length > 0 ? numbers[0] : null
        }
        return numbers.length > 0 ? numbers[0] : null
        
      case 'credit_history':
        // Сначала проверяем контекст: "просрочек не было" / "вовремя" → хорошая история
        if (lowerMessage.includes('просрочек не было') || lowerMessage.includes('вовремя') || lowerMessage.includes('без просрочек')) {
          return 'good'
        }
        if (lowerMessage.includes('были просрочки') || lowerMessage.includes('были задержки')) {
          return 'poor'
        }
        if (lowerMessage.includes('отличн')) return 'excellent'
        if (lowerMessage.includes('хорош')) return 'good'
        if (lowerMessage.includes('средн')) return 'fair'
        if (lowerMessage.includes('плох')) return 'poor'
        // "нет" без контекста о просрочках = нет кредитной истории
        if (lowerMessage.includes('нет') && !lowerMessage.includes('просроч')) return 'none'
        return null
        
      default:
        return numbers.length > 0 ? numbers[0] : null
    }
  }

  /**
   * Transition to a new stage
   * Automatically shows client message and processes on_enter_actions
   *
   * @param {string} stageId - Stage ID to transition to
   */
  const transitionToStage = async (stageId) => {
    if (!stageId) {
      return
    }
    
    const normalizedStageId = normalizeCurrentStep(stageId)
    
    // Load stage config FIRST before updating current_step
    // This ensures stageConfigComputed will have the config when it recalculates
    const loadedConfig = await getStageConfig(normalizedStageId)
    const stageConfig = getStageConfigSync(normalizedStageId)
    
    if (!stageConfig || Object.keys(stageConfig).length === 0) {
      return
    }
    
    // Now update current_step - this will trigger stageConfigComputed to recalculate
    // Use Vue's reactive assignment to ensure reactivity
    if (!localSessionState.dialogue) {
      localSessionState.dialogue = {}
    }
    
    localSessionState.dialogue.current_step = normalizedStageId
    
    // Wait for reactivity to update
    await nextTick()
    
    // Verify current_step is still correct after nextTick
    const actualStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    
    if (actualStep !== normalizedStageId) {
      // Force update again
      localSessionState.dialogue.current_step = normalizedStageId
      await nextTick()
    }
    
    // Reveal client name at passport stage (ФИО в конце, при предъявлении паспорта)
    if (normalizedStageId === 'collect_passport' && localSessionState.client._generated_name) {
      localSessionState.client.name = localSessionState.client._generated_name
    }

    // Automatically show client message (client speaks automatically)
    if (stageConfig.client_message) {
      // Check if this message was already shown (avoid duplicates)
      const messageExists = localSessionState.dialogue.messages?.some(
        m => m.text === stageConfig.client_message && m.role === 'client'
      )
      if (!messageExists) {
        addClientMessage(stageConfig.client_message)
        
        // Auto-extract data from client message if required_data is present
        if (stageConfig.required_data && Array.isArray(stageConfig.required_data)) {
          stageConfig.required_data.forEach(field => {
            const extractedValue = extractDataFromMessage(stageConfig.client_message, field)
            if (extractedValue !== null) {
              // Save extracted data
              if (field === 'credit_amount' || field === 'deposit_amount' || field === 'deposit_period') {
                if (!localSessionState.dialogue.formData) {
                  localSessionState.dialogue.formData = {}
                }
                localSessionState.dialogue.formData[field] = extractedValue
              } else {
                localSessionState.client[field] = extractedValue
              }
            }
          })
        }
      }
    }
    
    // Process on_enter_actions for the stage
    await processStageEnterActions(normalizedStageId)
  }

  /**
   * Process on_enter_actions for a stage
   *
   * @param {string} stageId - Stage ID to process enter actions for
   */
  const processStageEnterActions = async (stageId) => {
    if (!sessionId || !updateState) return
    
    try {
      const result = await executeBackendActions(stageId, null)
      
      // Execute frontend effects
      if (result.effects) {
        executeActions(result.effects)
      }
      
      // Show messages from backend
      if (result.messages) {
        result.messages.forEach(msg => {
          if (msg.role === 'client') {
            addClientMessage(msg.message)
          } else if (msg.role === 'system') {
            addSystemMessage(msg.message)
          } else {
            addUserMessage(msg.message)
          }
        })
      }
      
      // Update state with backend updates
      if (result.updates && Object.keys(result.updates).length > 0) {
        await updateState(result.updates)
      }
      } catch (error) {
        // Error processing stage enter actions, continue anyway
      }
  }

  /**
   * Handle data submission
   */
  const handleDataSubmit = async (formData) => {
    const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step)
    
    // Load stage config if not cached
    let stageConfig = getStageConfigSync(currentStep)
    if (!stageConfig || Object.keys(stageConfig).length === 0) {
      stageConfig = await getStageConfig(currentStep)
    }
    
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
      await transitionToStage(nextStage)
      
      // Save full state after transition (includes all messages)
      if (sessionId && updateState) {
        await updateState({
          dialogue: {
            messages: Array.isArray(localSessionState.dialogue.messages)
              ? localSessionState.dialogue.messages
              : [],
            current_step: normalizeCurrentStep(nextStage),
            selected_options: Array.isArray(localSessionState.dialogue.selected_options)
              ? localSessionState.dialogue.selected_options
              : [],
            formData: localSessionState.dialogue.formData || {}
          },
          client: localSessionState.client || {}
        }, true)
      }
    }
  }

  /**
   * Determine current stage from dialogue messages if current_step is missing or reset
   * This helps recover from state desynchronization
   */
  const inferCurrentStageFromMessages = () => {
    const messages = localSessionState.dialogue.messages || []
    if (messages.length === 0) {
      return 'greeting'
    }
    
    // Check last few messages to infer stage
    const lastMessages = messages.slice(-3)
    
    // Look for keywords that indicate specific stages
    for (let i = lastMessages.length - 1; i >= 0; i--) {
      const msg = lastMessages[i]
      const text = msg.text?.toLowerCase() || ''
      
      // Check for completion stage
      if (text.includes('спасибо') && text.includes('обращайтесь')) {
        return 'completion'
      }
      
      // Check for decision stage
      if (text.includes('звучит интересно') || text.includes('давайте оформим')) {
        return 'client_decision'
      }
      
      // Check for waiting results stage
      if (text.includes('что вы можете') || text.includes('предложить')) {
        return 'client_waiting_results'
      }
      
      // Check for age/history stage
      if (text.includes('лет') && (text.includes('кредитн') || text.includes('истори'))) {
        return 'client_age_history'
      }
      
      // Check for expenses stage
      if (text.includes('трачу') || text.includes('расход')) {
        return 'client_expenses'
      }
      
      // Check for income stage
      if (text.includes('доход') && text.includes('рубл')) {
        return 'client_income'
      }
      
      // Check for credit amount stage
      if (text.includes('хотел бы получить') || text.includes('сумм')) {
        return 'client_credit_amount'
      }
    }
    
    // Default: if we have messages but can't infer, try to use last known stage or greeting
    return localSessionState.dialogue.current_step || 'greeting'
  }

  /**
   * Open dialogue dialog
   */
  const openDialogueDialog = async () => {
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
      // Set initial stage to greeting
      localSessionState.dialogue.current_step = 'greeting'
      
      // Transition to greeting stage (automatically shows client message)
      await transitionToStage('greeting')
    } else {
      // If there are messages, try to restore current_step from messages if it's missing or reset
      const currentStep = normalizeCurrentStep(localSessionState.dialogue.current_step || 'greeting')
      
      // If current_step is 'greeting' but we have messages, try to infer the correct stage
      if (currentStep === 'greeting' && localSessionState.dialogue.messages.length > 1) {
        const inferredStage = inferCurrentStageFromMessages()
        
        if (inferredStage !== 'greeting') {
          localSessionState.dialogue.current_step = inferredStage
          await loadCurrentStageConfig()
        } else {
          await loadCurrentStageConfig()
        }
      } else {
        await loadCurrentStageConfig()
      }
      
      // Check if client message for current stage is already shown
      // IMPORTANT: Don't add messages if they already exist in loaded state
      // This prevents duplicates when restoring from backend
      const stageConfig = getStageConfigSync(currentStep)
      if (stageConfig && stageConfig.client_message) {
        const messageExists = localSessionState.dialogue.messages?.some(
          m => m.text === stageConfig.client_message && m.role === 'client'
        )
        if (!messageExists) {
          addClientMessage(stageConfig.client_message)
        }
      }
    }
    
    showDialogueDialog.value = true
    
    // Mount DialogueInterface in Dialog3D
    nextTick(() => {
      mountDialogueInterface()
      
      // Прокручиваем вниз при открытии, если есть история сообщений
      if (localSessionState.dialogue.messages.length > 0) {
        setTimeout(() => {
          const externalContainer = document.querySelector('.dialog-3d-container')
          if (externalContainer) {
            externalContainer.scrollTop = externalContainer.scrollHeight
          }
        }, 300)
      }
    })
  }

  // Watch for stage changes to load config (but don't remount - causes scroll reset)
  watch(() => localSessionState.dialogue.current_step, async (newStep) => {
    if (newStep) {
      // Load stage config when step changes
      await getStageConfig(newStep)
      // Component will update automatically via reactivity, no need to remount
    }
  })

  /**
   * Handle restart session
   */
  const handleRestartSession = async () => {
    // Reset local state
    localSessionState.dialogue.messages = []
    localSessionState.dialogue.current_step = 'greeting'
    localSessionState.dialogue.selected_options = []
    localSessionState.dialogue.formData = {}
    localSessionState.client = {
      name: null,
      age: null,
      income: null,
      expenses: null,
      credit_history: null,
      has_deposit: false
    }
    localSessionState.calculations = {}
    localSessionState.score = 0
    localSessionState.score_history = []
    
    // Reset backend state
    if (sessionId && updateState) {
      await updateState({
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
        score: 0,
        score_history: []
      })
    }
    
    // Clear stage config cache to force reload
    stageConfigCache.value = {}
    
    // Unmount and remount dialogue interface to ensure clean state
    if (dialogueAppRef.value) {
      dialogueAppRef.value.unmount()
      dialogueAppRef.value = null
    }
    
    // Close dialogue and reopen to start fresh
    closeDialogueDialog()
    setTimeout(() => {
      openDialogueDialog()
    }, 500)
  }

  return {
    // Methods
    getStageConfig,
    openDialogueDialog,
    mountDialogueInterface,
    handleOptionSelect,
    handleDataSubmit,
    addClientMessage,
    addUserMessage,
    addSystemMessage,
    executeBackendActions,
    executeActions,
    processStageEnterActions,
    handleRestartSession,
    
    // Computed
    currentStageConfig
  }
}
