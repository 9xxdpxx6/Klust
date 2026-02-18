import { ref, reactive } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import axios from 'axios'
import { route } from 'ziggy-js'

/**
 * Composable for managing simulator session state synchronization
 * 
 * @param {number} sessionId - Session ID
 * @param {Object} initialState - Initial state object
 * @returns {Object} State management functions and reactive state
 */
export function useSimulatorState(sessionId, initialState = {}) {
  const state = reactive(initialState)
  const isLoading = ref(false)
  const error = ref(null)

  /**
   * Update state on backend
   * 
   * @param {Object} updates - State updates to merge
   * @param {boolean} silent - Если true, не логирует ошибки 422
   * @returns {Promise<Object>} Response data
   */
  const updateState = async (updates, silent = false) => {
    isLoading.value = true
    error.value = null

    try {
      const url = route('student.simulators.state.update', { session: sessionId })
      const response = await axios.post(url, {
        state: updates
      })

      // Deep merge updates into local state
      // Object.assign does shallow merge, so we need to handle nested objects
      const deepMerge = (target, source) => {
        Object.keys(source).forEach(key => {
          if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key]) && 
              target[key] && typeof target[key] === 'object' && !Array.isArray(target[key])) {
            deepMerge(target[key], source[key])
          } else {
            // Special handling for score and score_history - always use the new value directly
            if (key === 'score' || key === 'score_history') {
              target[key] = source[key]
            } else {
              target[key] = source[key]
            }
          }
        })
        return target
      }
      deepMerge(state, updates)
      
      // Score updates handled silently

      return response.data
    } catch (e) {
      error.value = e.response?.data?.message || 'Ошибка обновления состояния'
      
      // Логируем ошибки только если не silent
      if (!silent && e.response?.status !== 422) {
        console.error('Ошибка обновления состояния:', e)
      }
      
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Load state from backend
   * 
   * @returns {Promise<Object>} Loaded state
   */
  const loadState = async () => {
    isLoading.value = true
    error.value = null

    try {
      const url = route('student.simulators.state.get', { session: sessionId })
      const response = await axios.get(url)

      const loadedState = response.data.state || {}

      // Replace local state with loaded state (без триггера watch)
      // Используем Object.keys и delete для полной замены
      const keysToDelete = Object.keys(state)
      keysToDelete.forEach(key => {
        delete state[key]
      })
      
      // Добавляем новые ключи
      Object.keys(loadedState).forEach(key => {
        if (Array.isArray(loadedState[key])) {
          state[key] = [...loadedState[key]]
        } else if (typeof loadedState[key] === 'object' && loadedState[key] !== null) {
          state[key] = { ...loadedState[key] }
        } else {
          state[key] = loadedState[key]
        }
      })

      return loadedState
    } catch (e) {
      error.value = e.response?.data?.message || 'Ошибка загрузки состояния'
      throw e
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Auto-save state (debounced)
   * 
   * @param {Object} updates - State updates to save
   */
  const autoSave = useDebounceFn((updates) => {
    // Используем silent=true чтобы не логировать ошибки валидации при автосохранении
    updateState(updates, true).catch(() => {
      // Silent error handling for auto-save
      // Error is already stored in error.value
    })
  }, 2000)

  return {
    state,
    isLoading,
    error,
    updateState,
    loadState,
    autoSave
  }
}
