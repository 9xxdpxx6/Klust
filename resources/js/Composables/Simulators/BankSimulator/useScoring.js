import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import axios from 'axios'
import { route } from 'ziggy-js'

/**
 * Composable for managing scoring calculations
 * 
 * @param {Object} options - Configuration options
 * @param {number} options.sessionId - Session ID
 * @param {Object} options.localSessionState - Local session state
 * @param {Object} options.isLoading - Loading state ref
 * @returns {Object} Scoring functions and state
 */
export function useScoring({ sessionId, localSessionState, isLoading }) {
  const isCalculating = ref(false)

  /**
   * Calculate scoring via API
   */
  const calculateScoring = async () => {
    const client = localSessionState.client
    
    // Check that all required data is present and valid
    if (!client.income || client.income <= 0 ||
        !client.expenses || client.expenses < 0 ||
        !client.age || client.age < 18 || client.age > 100 ||
        !client.credit_history) {
      return
    }
    
    // Don't call during loading
    if (isLoading?.value) {
      return
    }
    
    isCalculating.value = true
    
    try {
      const url = route('student.simulators.calculate-scoring', { session: sessionId })
      const response = await axios.post(url, {
        income: Number(client.income),
        expenses: Number(client.expenses),
        age: Number(client.age),
        credit_history: String(client.credit_history),
        has_deposit: Boolean(client.has_deposit || false)
      })
      
      const scoringData = response.data
      
      // Update calculations in state
      Object.assign(localSessionState.calculations, {
        credit_score: scoringData.credit_score,
        decision: scoringData.decision,
        interest_rate: scoringData.interest_rate,
        credit_limit: scoringData.credit_limit
      })
    } catch (error) {
      // Silent error handling (don't log to avoid cluttering console)
      if (error.response?.status !== 422) {
        console.error('Ошибка расчета скоринга:', error)
      }
    } finally {
      isCalculating.value = false
    }
  }

  /**
   * Debounced scoring calculation (500ms delay)
   */
  const debouncedCalculateScoring = useDebounceFn(calculateScoring, 500)

  /**
   * Perform calculations (called when transitioning to present_results)
   */
  const performCalculations = () => {
    // Call scoring calculation
    calculateScoring()
  }

  // Watch for client data changes and automatically calculate scoring
  watch(() => [
    localSessionState.client.income,
    localSessionState.client.expenses,
    localSessionState.client.age,
    localSessionState.client.credit_history,
    localSessionState.client.has_deposit
  ], () => {
    // Don't call calculation during loading
    if (isLoading?.value) {
      return
    }
    
    // Check that all required data is present
    const client = localSessionState.client
    if (!client.income || !client.expenses || !client.age || !client.credit_history) {
      return
    }
    
    // Call debounced scoring calculation
    debouncedCalculateScoring()
  }, { deep: true })

  return {
    // State
    isCalculating,
    
    // Methods
    calculateScoring,
    debouncedCalculateScoring,
    performCalculations
  }
}
