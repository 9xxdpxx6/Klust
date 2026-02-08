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
    
    <!-- Ввод данных (если требуется) -->
    <div v-if="requiredData.length > 0" class="dialogue-input">
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
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

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

const emit = defineEmits(['optionSelect', 'dataSubmit'])

const messages = computed(() => {
  return props.sessionState?.dialogue?.messages || []
})

const currentOptions = computed(() => {
  return props.stageConfig?.user_options || []
})

const requiredData = computed(() => {
  return props.stageConfig?.required_data || []
})

const showCalculations = computed(() => {
  return props.stageConfig?.show_calculations === true
})

const calculations = computed(() => {
  return props.sessionState?.calculations || {}
})

const formData = ref({})
const isProcessing = ref(false)
const messagesContainerRef = ref(null)

// Инициализация formData для всех требуемых полей
watch(requiredData, (fields) => {
  if (fields.length > 0) {
    const newFormData = {}
    fields.forEach(field => {
      if (!(field in formData.value)) {
        newFormData[field] = field === 'credit_history' ? '' : null
      } else {
        newFormData[field] = formData.value[field]
      }
    })
    formData.value = newFormData
  }
}, { immediate: true })

// Валидация формы
const isFormValid = computed(() => {
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

// Прокрутка к последнему сообщению
watch(messages, async () => {
  await nextTick()
  if (messagesContainerRef.value) {
    messagesContainerRef.value.scrollTop = messagesContainerRef.value.scrollHeight
  }
}, { deep: true })

const onOptionSelect = (optionId) => {
  if (isProcessing.value) return
  
  isProcessing.value = true
  emit('optionSelect', optionId)
  
  // Сброс состояния обработки через небольшую задержку
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
</script>

<style scoped>
.dialogue-interface {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #1e40af;
  color: white;
  padding: 20px;
  border-radius: 8px;
  overflow: hidden;
}

.dialogue-messages {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 20px;
  padding-right: 8px;
  min-height: 200px;
  max-height: 300px;
}

.dialogue-messages::-webkit-scrollbar {
  width: 6px;
}

.dialogue-messages::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.dialogue-messages::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
}

.dialogue-messages::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.5);
}

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
  background: rgba(255, 255, 255, 0.15);
  align-self: flex-start;
  border-left: 3px solid #fbbf24;
}

.message-user {
  background: rgba(255, 255, 255, 0.25);
  align-self: flex-end;
  margin-left: auto;
  max-width: 80%;
  border-right: 3px solid #4ade80;
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
  background: #fbbf24;
  color: #000;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 1rem;
  font-weight: 500;
  text-align: left;
}

.option-button:hover:not(:disabled) {
  background: #f59e0b;
  transform: translateX(4px);
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
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1rem;
  transition: all 0.2s;
}

.data-input:focus {
  outline: none;
  border-color: #fbbf24;
  background: rgba(255, 255, 255, 0.15);
}

.data-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.data-select {
  cursor: pointer;
}

.data-select option {
  background: #1e40af;
  color: white;
}

.submit-button {
  padding: 12px 24px;
  background: #4ade80;
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
  background: #22c55e;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.submit-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.calculations-display {
  margin-top: 20px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.calculations-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 12px;
  color: #fbbf24;
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
  color: #4ade80;
}
</style>
