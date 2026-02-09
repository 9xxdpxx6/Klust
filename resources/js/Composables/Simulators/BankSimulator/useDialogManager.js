import { ref, computed, watch, onMounted } from 'vue'

/**
 * Composable for managing dialog state and transitions
 * 
 * @param {Object} options - Configuration options
 * @param {Object} options.sessionState - Current session state
 * @param {Function} options.updateState - Function to update session state
 * @param {Function} options.emit - Vue emit function
 * @param {Object} options.refs - Dialog refs object
 * @returns {Object} Dialog management functions and state
 */
export function useDialogManager({ sessionState, updateState, emit, refs }) {
  // Dialog visibility states
  const showPhoneDialog = ref(false)
  const showCalculatorDialog = ref(false)
  const showDocumentsDialog = ref(false)
  const showDialogueDialog = ref(false)
  const showCreditCalculator = ref(false)
  const showDepositCalculator = ref(false)

  // Active dialog (for unified Dialog3D)
  const activeDialog = ref(null) // 'phone' | 'calculator' | 'documents' | 'dialogue' | null

  // Dialog headers
  const dialogHeaders = {
    phone: 'Телефон',
    calculator: 'Калькулятор',
    documents: 'Документы'
  }

  // Computed properties
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

  /**
   * Save dialog state to session
   */
  const saveDialogState = (dialogName) => {
    if (!updateState) return
    
    const updates = {
      ui: {
        activeDialog: dialogName || null
      }
    }
    
    updateState(updates).catch((error) => {
      console.error('Ошибка сохранения состояния диалога:', error)
    })
  }

  /**
   * Check for unsaved changes (stub - to be implemented)
   */
  const hasUnsavedChanges = (dialogName) => {
    // TODO: Реализовать проверку несохраненных изменений
    return false
  }

  /**
   * Close other dialogs with warning if needed
   */
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
      // Check for unsaved changes
      const hasUnsaved = dialogsToClose.some(dialog => hasUnsavedChanges(dialog.name))
      
      if (hasUnsaved) {
        // TODO: Show confirmation dialog (implement later)
        const confirmed = confirm('У вас есть несохраненные изменения. Вы уверены, что хотите закрыть этот диалог?')
        if (!confirmed) {
          return false
        }
      }
      
      // Close other dialogs
      dialogsToClose.forEach(dialog => {
        dialog.ref.value = false
      })
    }
    
    return true
  }

  /**
   * Close dialogue dialog
   */
  const closeDialogueDialog = () => {
    showDialogueDialog.value = false
    activeDialog.value = null
    
    // Unmount Vue application
    if (refs.dialogueAppRef?.value) {
      refs.dialogueAppRef.value.unmount()
      refs.dialogueAppRef.value = null
    }
  }

  /**
   * Switch to a different dialog with animation
   */
  const switchToDialog = (dialogName, dialogRef, emitEvent) => {
    // Close dialogue dialog if open
    if (showDialogueDialog.value) {
      closeDialogueDialog()
    }
    
    const wasDialogOpen = isAnyDialogOpen.value
    const previousDialog = activeDialog.value
    
    // If another dialog is open - animate transition
    if (wasDialogOpen && previousDialog !== dialogName) {
      // Animate header change without closing dialog
      if (refs.mainDialogRef?.value?.animateContentChange) {
        refs.mainDialogRef.value.animateContentChange(dialogHeaders[dialogName], () => {
          // After animation, switch dialog flags
          showPhoneDialog.value = dialogName === 'phone'
          showCalculatorDialog.value = dialogName === 'calculator'
          showDocumentsDialog.value = dialogName === 'documents'
          activeDialog.value = dialogName
          saveDialogState(dialogName)
          if (emitEvent) emitEvent()
        })
      } else {
        // Fallback without animation
        showPhoneDialog.value = dialogName === 'phone'
        showCalculatorDialog.value = dialogName === 'calculator'
        showDocumentsDialog.value = dialogName === 'documents'
        activeDialog.value = dialogName
        saveDialogState(dialogName)
        if (emitEvent) emitEvent()
      }
    } else if (!wasDialogOpen) {
      // Dialog was not open - just open it
      dialogRef.value = true
      activeDialog.value = dialogName
      saveDialogState(dialogName)
      if (emitEvent) emitEvent()
    }
  }

  /**
   * Close main dialog
   */
  const onMainDialogClose = () => {
    showPhoneDialog.value = false
    showCalculatorDialog.value = false
    showDocumentsDialog.value = false
    activeDialog.value = null
    saveDialogState(null)
  }

  /**
   * Handle dialog visibility change
   */
  const onDialogVisibilityChange = (visible) => {
    if (!visible) {
      onMainDialogClose()
    }
  }

  /**
   * Event handlers
   */
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
    // Open credit calculator by default
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

  const openCreditCalculator = () => {
    closeOtherDialogsWithWarning('credit_calculator')
    showCreditCalculator.value = true
  }

  const openDepositCalculator = () => {
    closeOtherDialogsWithWarning('deposit_calculator')
    showDepositCalculator.value = true
  }

  const onDialogueDialogClose = () => {
    closeDialogueDialog()
  }

  const onDialogueDialogVisibilityChange = (visible) => {
    if (!visible) {
      closeDialogueDialog()
    }
  }

  // Restore dialog state from sessionState on mount
  // Note: We don't restore 'documents' dialog as it's not a persistent dialog
  onMounted(() => {
    const activeDialogFromState = sessionState?.ui?.activeDialog
    if (activeDialogFromState === 'phone') {
      showPhoneDialog.value = true
    } else if (activeDialogFromState === 'calculator') {
      showCalculatorDialog.value = true
    }
    // Don't restore 'documents' dialog - it should not persist across page loads
  })

  // Watch for automatic dialog state saving
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

  return {
    // State
    showPhoneDialog,
    showCalculatorDialog,
    showDocumentsDialog,
    showDialogueDialog,
    showCreditCalculator,
    showDepositCalculator,
    activeDialog,
    
    // Computed
    isAnyDialogOpen,
    activeDialogHeader,
    
    // Methods
    switchToDialog,
    closeDialogueDialog,
    closeOtherDialogsWithWarning,
    saveDialogState,
    onMainDialogClose,
    onDialogVisibilityChange,
    
    // Event handlers
    onPhoneClick,
    onDocumentsClick,
    onCalculatorClick,
    onCreditCalculatorClose,
    onDepositCalculatorClose,
    openCreditCalculator,
    openDepositCalculator,
    onDialogueDialogClose,
    onDialogueDialogVisibilityChange
  }
}
