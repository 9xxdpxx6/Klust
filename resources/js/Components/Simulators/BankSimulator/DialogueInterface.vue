<template>
  <div class="dialogue-interface">
    <!-- История сообщений -->
    <div class="dialogue-messages" ref="messagesContainerRef">
      <div 
        v-for="(message, index) in messages" 
        :key="index"
        class="message"
        :class="`message-${message.role}`"
      >
        <div class="message-content">
          {{ message.text }}
        </div>
        <div class="message-time">
          {{ formatTime(message.timestamp) }}
        </div>
      </div>
    </div>
    
    <!-- Варианты ответов -->
    <div v-if="currentOptions.length > 0" class="dialogue-options">
      <button
        v-for="option in currentOptions"
        :key="option.id"
        @click="onOptionSelect(option.id)"
        class="option-button"
        :disabled="isProcessing"
      >
        {{ option.text }}
      </button>
    </div>
    
    <!-- Ввод данных (если требуется и данные еще не заполнены автоматически) -->
    <div v-if="requiredData.length > 0 && !allDataFilled" class="dialogue-input">
      <div
        v-for="field in requiredData"
        :key="field"
        class="input-group"
      >
        <label :for="field" class="input-label">
          {{ getFieldLabel(field) }}
        </label>
        <input
          v-if="field !== 'credit_history'"
          :id="field"
          v-model.number="formData[field]"
          :placeholder="getFieldPlaceholder(field)"
          :type="getInputType(field)"
          class="data-input"
          :min="getFieldMin(field)"
          :max="getFieldMax(field)"
        />
        <select
          v-else
          :id="field"
          v-model="formData[field]"
          class="data-input data-select"
        >
          <option value="">Выберите...</option>
          <option value="excellent">Отличная</option>
          <option value="good">Хорошая</option>
          <option value="fair">Средняя</option>
          <option value="poor">Плохая</option>
          <option value="none">Нет кредитной истории</option>
        </select>
      </div>
      <button 
        @click="onDataSubmit" 
        class="submit-button"
        :disabled="!isFormValid || isProcessing"
      >
        Отправить
      </button>
    </div>

    <!-- Отображение результатов расчетов -->
    <div v-if="showCalculations && calculations" class="calculations-display">
      <h3 class="calculations-title">Результаты расчета:</h3>
      <div v-if="calculations.credit_score !== null && calculations.credit_score !== undefined" class="calculation-item">
        <span class="calculation-label">Кредитный скоринг:</span>
        <span class="calculation-value">{{ formatScore(calculations.credit_score) }}</span>
      </div>
      <div v-if="calculations.credit_limit" class="calculation-item">
        <span class="calculation-label">Кредитный лимит:</span>
        <span class="calculation-value">{{ formatCurrency(calculations.credit_limit) }}</span>
      </div>
      <div v-if="calculations.interest_rate" class="calculation-item">
        <span class="calculation-label">Процентная ставка:</span>
        <span class="calculation-value">{{ calculations.interest_rate }}%</span>
      </div>
      <div v-if="calculations.monthly_payment" class="calculation-item">
        <span class="calculation-label">Ежемесячный платеж:</span>
        <span class="calculation-value">{{ formatCurrency(calculations.monthly_payment) }}</span>
      </div>
      <div v-if="calculations.deposit_result" class="calculation-item">
        <span class="calculation-label">Сумма вклада:</span>
        <span class="calculation-value">{{ formatCurrency(calculations.deposit_result) }}</span>
      </div>
    </div>

    <!-- Индикатор завершения симулятора -->
    <div v-if="isFinalStage" class="completion-indicator">
      <div class="completion-content">
        <h3 class="completion-title">🎉 Симулятор завершен!</h3>
        <div class="completion-stats">
          <div class="stat-item">
            <span class="stat-label">Ваш результат:</span>
            <span class="stat-value">{{ currentScore !== null ? currentScore : 0 }} баллов</span>
          </div>
          <div v-if="currentScore !== null" class="stat-item">
            <span class="stat-label">Оценка:</span>
            <span class="stat-value" :class="getScoreClass(currentScore)">
              {{ getScoreText(currentScore) }}
            </span>
          </div>
        </div>
        <p class="completion-message">
          {{ getCompletionMessage(currentScore) }}
        </p>
        
        <!-- Отображение ошибок (только отрицательные действия) -->
        <div v-if="negativeScoreHistory.length > 0" class="score-details">
          <h4 class="score-details-title">⚠️ Ошибки в консультации:</h4>
          <div class="score-history">
            <div 
              v-for="(entry, index) in negativeScoreHistory" 
              :key="index"
              class="score-history-item score-negative"
            >
              <span class="score-history-points">{{ entry.points }} баллов</span>
              <span class="score-history-reason">{{ entry.reason }}</span>
            </div>
          </div>
        </div>
        
        <div class="completion-buttons">
          <button 
            @click="handleRestartSession" 
            class="completion-button restart-button"
          >
            Пройти заново
          </button>
          <button 
            @click="handleCompleteSession" 
            class="completion-button"
          >
            Завершить сессию
          </button>
        </div>
      </div>
    </div>

    <!-- Индикатор прогресса (если не финальная стадия, но показываются расчеты) -->
    <div v-else-if="showCalculations && calculations && Object.keys(calculations).length > 0" class="progress-indicator">
      <div class="progress-content">
        <p class="progress-message">
          💡 Результаты расчета готовы. Продолжите диалог с клиентом, выбрав один из вариантов ответа выше.
        </p>
        <div v-if="currentScore !== null" class="progress-score">
          <span class="progress-score-label">Текущие баллы:</span>
          <span class="progress-score-value">{{ currentScore }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'

const props = defineProps({
  sessionState: {
    type: Object,
    required: true
  },
  currentStage: {
    type: String,
    default: 'greeting'
  },
  stageConfig: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['optionSelect', 'dataSubmit', 'completeSession', 'restartSession'])

const messages = computed(() => {
  return props.sessionState?.dialogue?.messages || []
})

// Показывать опции только для текущей стадии и только если еще не выбрана опция
const currentOptions = computed(() => {
  // Если стадия финальная - опций нет
  if (props.stageConfig?.is_final) {
    return []
  }
  
  // Получаем опции для текущей стадии
  const options = props.stageConfig?.user_options || []
  if (options.length === 0) {
    return []
  }
  
  // Проверяем, была ли уже выбрана опция для текущей стадии
  // Скрываем опции, если последнее сообщение от пользователя (банкира)
  // НО: если есть client_message для текущей стадии и оно уже показано, значит клиент ответил - показываем опции
  const messages = props.sessionState?.dialogue?.messages || []
  if (messages.length > 0) {
    const lastMessage = messages[messages.length - 1]
    
    // Если последнее сообщение от пользователя (банкира)
    if (lastMessage.role === 'user') {
      // Проверяем, есть ли client_message для текущей стадии
      const clientMessage = props.stageConfig?.client_message
      
      if (clientMessage) {
        // Проверяем, было ли уже показано сообщение клиента для этой стадии
        // Ищем сообщение клиента, которое соответствует client_message текущей стадии
        const clientMessageExists = messages.some(
          m => m.text === clientMessage && m.role === 'client'
        )
        
        if (clientMessageExists) {
          // Клиент уже ответил - показываем опции
          return options
        } else {
          // Клиент еще не ответил - скрываем опции, ждем ответа клиента
          // НО: если прошло больше 1 секунды с последнего сообщения банкира, возможно переход не произошел
          // В этом случае показываем опции, чтобы пользователь мог продолжить
          const lastMessageTime = new Date(lastMessage.timestamp).getTime()
          const now = Date.now()
          const timeSinceLastMessage = now - lastMessageTime
          
          if (timeSinceLastMessage > 2000) {
            // Прошло больше 2 секунд - возможно переход застрял, показываем опции
            return options
          }
          
          return []
        }
      } else {
        // Нет client_message - возможно стадия не требует ответа клиента
        // Показываем опции, если прошло больше 1 секунды
        const lastMessageTime = new Date(lastMessage.timestamp).getTime()
        const now = Date.now()
        const timeSinceLastMessage = now - lastMessageTime
        
        if (timeSinceLastMessage > 1000) {
          return options
        }
        
        return []
      }
    }
  }
  
  return options
})

const requiredData = computed(() => {
  return props.stageConfig?.required_data || []
})

const showCalculations = computed(() => {
  return props.stageConfig?.show_calculations === true
})

const isFinalStage = computed(() => {
  return props.stageConfig?.is_final === true
})

const calculations = computed(() => {
  return props.sessionState?.calculations || {}
})

const currentScore = computed(() => {
  return props.sessionState?.score ?? null
})

const scoreHistory = computed(() => {
  return props.sessionState?.score_history || []
})

const negativeScoreHistory = computed(() => {
  return scoreHistory.value.filter(entry => entry.points < 0)
})

const formData = ref({})
const isProcessing = ref(false)
const messagesContainerRef = ref(null)

// Инициализация formData для всех требуемых полей
// Предзаполняем данными из client_message или из sessionState
watch([requiredData, () => props.stageConfig?.client_message, () => props.sessionState?.client, () => props.sessionState?.dialogue?.formData], ([fields, clientMessage, clientData, formDataState]) => {
  if (fields && fields.length > 0) {
    const newFormData = {}
    fields.forEach(field => {
      // Сначала проверяем, есть ли данные в sessionState
      if (field === 'credit_amount' || field === 'deposit_amount' || field === 'deposit_period') {
        // Эти поля в formData
        if (formDataState && formDataState[field] !== undefined) {
          newFormData[field] = formDataState[field]
        } else if (!(field in formData.value)) {
          newFormData[field] = null
        } else {
          newFormData[field] = formData.value[field]
        }
      } else {
        // Эти поля в client
        if (clientData && clientData[field] !== undefined) {
          newFormData[field] = clientData[field]
        } else if (!(field in formData.value)) {
          newFormData[field] = field === 'credit_history' ? '' : null
        } else {
          newFormData[field] = formData.value[field]
        }
      }
    })
    formData.value = newFormData
  }
}, { immediate: true, deep: true })

// Проверка, все ли данные уже заполнены (извлечены из client_message)
const allDataFilled = computed(() => {
  if (requiredData.value.length === 0) return true
  
  return requiredData.value.every(field => {
    const value = formData.value[field]
    if (value === null || value === undefined || value === '') return false
    
    // Проверка числовых полей
    if (['income', 'expenses', 'age', 'credit_amount', 'deposit_amount', 'deposit_period'].includes(field)) {
      return typeof value === 'number' && value > 0
    }
    
    return true
  })
})

// Валидация формы
const isFormValid = computed(() => {
  return allDataFilled.value
})

// Прокрутка к последнему сообщению при добавлении новых сообщений
const lastMessageCount = ref(0)

// Инициализация счетчика сообщений
watch(() => props.sessionState?.dialogue?.messages?.length, (newLength) => {
  if (newLength !== undefined) {
    lastMessageCount.value = newLength
  }
}, { immediate: true })

// Функция прокрутки внешнего контейнера вниз
const scrollToBottom = () => {
  // Ищем внешний контейнер Dialog3D
  const externalContainer = messagesContainerRef.value?.closest('.dialog-3d-container')
  if (externalContainer) {
    // Используем requestAnimationFrame для плавной прокрутки
    requestAnimationFrame(() => {
      externalContainer.scrollTo({
        top: externalContainer.scrollHeight,
        behavior: 'smooth'
      })
    })
  }
}

// Прокрутка при изменении сообщений
watch(messages, async (newMessages) => {
  const newCount = newMessages.length
  // Прокручиваем только если добавились новые сообщения
  if (newCount > lastMessageCount.value) {
    // Прокручиваем несколько раз с задержками для надежности
    scrollToBottom()
    setTimeout(() => {
      scrollToBottom()
    }, 100)
    setTimeout(() => {
      scrollToBottom()
    }, 300)
    lastMessageCount.value = newCount
  }
}, { deep: true, flush: 'post' })

// Прокручиваем при монтировании, если есть сообщения
watch(() => messagesContainerRef.value, (container) => {
  if (container && messages.value.length > 0) {
    scrollToBottom()
  }
}, { immediate: true })

// Прокручиваем при открытии диалога (если есть история)
onMounted(() => {
  if (messages.value.length > 0) {
    scrollToBottom()
  }
})

const onOptionSelect = (optionId) => {
  if (isProcessing.value) return
  
  isProcessing.value = true
  emit('optionSelect', optionId)
  
  // Прокручиваем вниз сразу после выбора опции
  // Используем несколько попыток с задержками для надежности
  nextTick(() => {
    scrollToBottom()
    setTimeout(() => {
      scrollToBottom()
    }, 200)
    setTimeout(() => {
      scrollToBottom()
    }, 500)
  })
  
  // Сброс состояния обработки через небольшую задержку
  // Опции скроются автоматически при смене стадии через реактивность
  setTimeout(() => {
    isProcessing.value = false
  }, 300)
}

const onDataSubmit = () => {
  if (!isFormValid.value || isProcessing.value) return
  
  isProcessing.value = true
  
  // Копируем данные формы
  const dataToSubmit = { ...formData.value }
  
  emit('dataSubmit', dataToSubmit)
  
  // Очистка формы после отправки
  setTimeout(() => {
    formData.value = {}
    isProcessing.value = false
  }, 300)
}

const formatTime = (timestamp) => {
  if (!timestamp) return ''
  try {
    const date = new Date(timestamp)
    return date.toLocaleTimeString('ru-RU', { 
      hour: '2-digit', 
      minute: '2-digit' 
    })
  } catch (e) {
    return ''
  }
}

const getFieldPlaceholder = (field) => {
  const placeholders = {
    income: 'Введите доход в рублях',
    expenses: 'Введите расходы в рублях',
    age: 'Введите возраст',
    credit_amount: 'Введите сумму кредита',
    deposit_amount: 'Введите сумму вклада',
    deposit_period: 'Введите срок в месяцах',
    credit_history: 'Выберите кредитную историю'
  }
  return placeholders[field] || field
}

const getFieldLabel = (field) => {
  const labels = {
    income: 'Доход (руб.)',
    expenses: 'Расходы (руб.)',
    age: 'Возраст',
    credit_amount: 'Сумма кредита (руб.)',
    deposit_amount: 'Сумма вклада (руб.)',
    deposit_period: 'Срок вклада (мес.)',
    credit_history: 'Кредитная история'
  }
  return labels[field] || field
}

const getInputType = (field) => {
  if (['income', 'expenses', 'age', 'credit_amount', 'deposit_amount', 'deposit_period'].includes(field)) {
    return 'number'
  }
  return 'text'
}

const getFieldMin = (field) => {
  if (field === 'age') return 18
  if (['income', 'expenses', 'credit_amount', 'deposit_amount'].includes(field)) return 0
  if (field === 'deposit_period') return 1
  return undefined
}

const getFieldMax = (field) => {
  if (field === 'age') return 100
  return undefined
}

const formatCurrency = (value) => {
  if (value === null || value === undefined) return ''
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

const formatScore = (score) => {
  if (score === null || score === undefined) return ''
  return (score * 100).toFixed(1) + '%'
}

const getScoreClass = (score) => {
  if (score === null || score === undefined) return ''
  if (score >= 80) return 'score-excellent'
  if (score >= 60) return 'score-good'
  if (score >= 40) return 'score-average'
  return 'score-poor'
}

const getScoreText = (score) => {
  if (score === null || score === undefined) return 'Не оценено'
  if (score >= 80) return 'Отлично'
  if (score >= 60) return 'Хорошо'
  if (score >= 40) return 'Удовлетворительно'
  return 'Неудовлетворительно'
}

const getCompletionMessage = (score) => {
  if (score === null || score === undefined) {
    return 'Диалог завершен.'
  }
  if (score >= 80) {
    return 'Отличная работа! Вы успешно провели консультацию с клиентом, продемонстрировав высокий уровень профессионализма.'
  }
  if (score >= 60) {
    return 'Хорошая работа! Вы провели консультацию с клиентом, но есть моменты, которые можно улучшить.'
  }
  if (score >= 40) {
    return 'Консультация завершена. Есть существенные моменты, которые требуют улучшения в вашей работе.'
  }
  return 'Консультация завершена. Рекомендуется пройти обучение и повторить попытку для улучшения результатов.'
}

const handleCompleteSession = () => {
  // Emit event to parent to complete session
  emit('completeSession')
}

const handleRestartSession = () => {
  // Emit event to parent to restart session (parent will show confirmation)
  emit('restartSession')
}
</script>

<style scoped>
.dialogue-interface {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  color: #1f2937;
  padding: 20px;
  border-radius: 8px;
  overflow: hidden;
}

.dialogue-messages {
  flex: 1;
  overflow: visible;
  margin-bottom: 20px;
  padding-right: 8px;
  /* Убрали overflow-y: auto и max-height - скролл будет только у внешнего контейнера */
}

/* Убрали стили скроллбара - скролл теперь только у внешнего контейнера */

.message {
  margin-bottom: 15px;
  padding: 12px 16px;
  border-radius: 8px;
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message-client {
  background: #f3f4f6;
  align-self: flex-start;
  border-left: 3px solid #9ca3af;
  color: #1f2937;
}

.message-user {
  background: #e5e7eb;
  align-self: flex-end;
  margin-left: auto;
  max-width: 80%;
  border-right: 3px solid #6b7280;
  color: #1f2937;
}

.message-content {
  font-size: 1rem;
  line-height: 1.5;
  margin-bottom: 4px;
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.7;
  margin-top: 4px;
}

.dialogue-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.option-button {
  padding: 12px 20px;
  background: #f9fafb;
  color: #1f2937;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1rem;
  font-weight: 500;
  text-align: left;
}

.option-button:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #9ca3af;
  transform: translateX(2px);
}

.option-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.dialogue-input {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.input-label {
  font-size: 0.875rem;
  opacity: 0.9;
  font-weight: 500;
}

.data-input {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #ffffff;
  color: #1f2937;
  font-size: 1rem;
  transition: all 0.2s;
}

.data-input:focus {
  outline: none;
  border-color: #6b7280;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1);
}

.data-input::placeholder {
  color: #9ca3af;
}

.data-select {
  cursor: pointer;
}

.data-select option {
  background: #ffffff;
  color: #1f2937;
}

.submit-button {
  padding: 12px 24px;
  background: #374151;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.2s;
  margin-top: 8px;
}

.submit-button:hover:not(:disabled) {
  background: #4b5563;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.submit-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.completion-indicator {
  margin-top: 20px;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  color: white;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.completion-content {
  text-align: center;
}

.completion-title {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 16px;
  color: white;
}

.completion-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
}

.stat-label {
  font-size: 14px;
  opacity: 0.9;
}

.stat-value {
  font-size: 16px;
  font-weight: bold;
}

.score-excellent {
  color: #10b981;
}

.score-good {
  color: #3b82f6;
}

.score-average {
  color: #f59e0b;
}

.score-poor {
  color: #ef4444;
}

.progress-indicator {
  margin-top: 20px;
  padding: 16px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 8px;
  border-left: 4px solid #3b82f6;
}

.progress-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.progress-message {
  font-size: 14px;
  color: #1e40af;
  line-height: 1.5;
  margin: 0;
}

.progress-score {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid #bae6fd;
}

.progress-score-label {
  font-size: 13px;
  color: #64748b;
}

.progress-score-value {
  font-size: 16px;
  font-weight: bold;
  color: #1e40af;
}

.completion-message {
  margin: 16px 0;
  font-size: 14px;
  opacity: 0.9;
  line-height: 1.5;
}

.completion-buttons {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 16px;
}

.completion-button {
  padding: 12px 24px;
  background: white;
  color: #667eea;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
}

.restart-button {
  background: transparent;
  color: white;
  border: 2px solid white;
}

.restart-button:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: white;
}

.completion-button:hover {
  background: #f3f4f6;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.completion-button:active {
  transform: translateY(0);
}

.calculations-display {
  margin-top: 20px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.calculations-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 12px;
  color: #374151;
}

.calculation-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.calculation-item:last-child {
  border-bottom: none;
}

.calculation-label {
  font-size: 0.875rem;
  opacity: 0.9;
}

.calculation-value {
  font-size: 1rem;
  font-weight: 600;
  color: #059669;
}

</style>
