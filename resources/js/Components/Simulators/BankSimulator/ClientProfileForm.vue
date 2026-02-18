<template>
  <div class="client-profile-form">
    <h3 class="form-title">Данные клиента</h3>
    
    <div class="fields-list">
      <div class="field-row" v-for="field in fields" :key="field.key">
        <span class="field-label">{{ field.label }}</span>
        <div class="field-value-wrapper">
          <span 
            class="field-value"
            :class="{ 
              'field-empty': !displayTexts[field.key], 
              'field-typing': typingActive[field.key],
              'field-filled': displayTexts[field.key] && !typingActive[field.key]
            }"
          >
            {{ displayTexts[field.key] || '—' }}
          </span>
          <span v-if="typingActive[field.key]" class="typing-cursor">|</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onUnmounted } from 'vue'

const props = defineProps({
  client: {
    type: Object,
    required: true
  },
  editable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:client'])

// Определение полей с метками и функциями форматирования
const fields = [
  { key: 'name', label: 'Имя', format: (v) => v || '' },
  { key: 'age', label: 'Возраст', format: (v) => v ? `${v} лет` : '' },
  { key: 'income', label: 'Доход', format: (v) => v ? formatCurrency(v) : '' },
  { key: 'expenses', label: 'Расходы', format: (v) => v ? formatCurrency(v) : '' },
  { key: 'credit_history', label: 'Кредитная история', format: (v) => formatCreditHistory(v) },
  { key: 'has_deposit', label: 'Вклад в банке', format: (v) => v === true ? 'Да' : (v === false ? '' : '') }
]

// Текущий отображаемый текст для каждого поля (для анимации печатания)
const displayTexts = reactive({})
// Флаги активной анимации печатания
const typingActive = reactive({})
// Таймеры для очистки при unmount
const typingTimers = ref([])

// Предыдущие значения для отслеживания изменений
const prevValues = reactive({})

// Инициализация
fields.forEach(f => {
  displayTexts[f.key] = ''
  typingActive[f.key] = false
  prevValues[f.key] = null
})

/**
 * Форматирование валюты
 */
function formatCurrency(value) {
  if (value === null || value === undefined) return ''
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

/**
 * Форматирование кредитной истории
 */
function formatCreditHistory(value) {
  const labels = {
    excellent: 'Отличная',
    good: 'Хорошая',
    fair: 'Средняя',
    poor: 'Плохая',
    none: 'Нет кредитной истории'
  }
  return labels[value] || ''
}

/**
 * Анимация печатания текста
 */
function typeText(fieldKey, fullText, charDelay = 40) {
  // Очищаем предыдущую анимацию для этого поля
  clearFieldTimers(fieldKey)
  
  if (!fullText) {
    displayTexts[fieldKey] = ''
    typingActive[fieldKey] = false
    return
  }
  
  displayTexts[fieldKey] = ''
  typingActive[fieldKey] = true
  
  const chars = fullText.split('')
  let currentIndex = 0
  
  const typeNextChar = () => {
    if (currentIndex < chars.length) {
      displayTexts[fieldKey] = fullText.substring(0, currentIndex + 1)
      currentIndex++
      const timer = setTimeout(typeNextChar, charDelay)
      typingTimers.value.push({ fieldKey, timer })
    } else {
      typingActive[fieldKey] = false
    }
  }
  
  // Небольшая начальная задержка перед началом печатания
  const startTimer = setTimeout(typeNextChar, 200)
  typingTimers.value.push({ fieldKey, timer: startTimer })
}

/**
 * Очистка таймеров для конкретного поля
 */
function clearFieldTimers(fieldKey) {
  typingTimers.value = typingTimers.value.filter(t => {
    if (t.fieldKey === fieldKey) {
      clearTimeout(t.timer)
      return false
    }
    return true
  })
}

/**
 * Наблюдаем за каждым полем клиента
 */
fields.forEach(field => {
  watch(
    () => props.client?.[field.key],
    (newVal) => {
      const prevVal = prevValues[field.key]
      const formattedText = field.format(newVal)
      
      // Если значение изменилось с пустого на заполненное — запускаем анимацию
      const wasEmpty = prevVal === null || prevVal === undefined || prevVal === '' || prevVal === 0 || prevVal === false
      const isNowFilled = newVal !== null && newVal !== undefined && newVal !== '' && newVal !== 0
      
      if (wasEmpty && isNowFilled && formattedText) {
        typeText(field.key, formattedText)
      } else if (formattedText) {
        // Значение изменилось, но не с пустого — просто обновляем без анимации
        displayTexts[field.key] = formattedText
        typingActive[field.key] = false
      } else {
        // Значение стало пустым — очищаем
        displayTexts[field.key] = ''
        typingActive[field.key] = false
      }
      
      prevValues[field.key] = newVal
    },
    { immediate: true }
  )
})

// Очищаем все таймеры при размонтировании
onUnmounted(() => {
  typingTimers.value.forEach(t => clearTimeout(t.timer))
  typingTimers.value = []
})
</script>

<style scoped>
.client-profile-form {
  padding: 1rem;
}

.form-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #1e293b;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 0.5rem;
}

.fields-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.field-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.65rem 0.5rem;
  border-bottom: 1px solid #f1f5f9;
  transition: background-color 0.2s;
}

.field-row:last-child {
  border-bottom: none;
}

.field-row:hover {
  background-color: #f8fafc;
}

.field-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
  flex-shrink: 0;
}

.field-value-wrapper {
  display: flex;
  align-items: center;
  gap: 1px;
}

.field-value {
  font-size: 0.9rem;
  font-weight: 600;
  text-align: right;
  transition: color 0.3s;
  font-variant-numeric: tabular-nums;
}

.field-empty {
  color: #cbd5e1;
  font-weight: 400;
}

.field-typing {
  color: #1e40af;
}

.field-filled {
  color: #0f172a;
}

.typing-cursor {
  display: inline-block;
  color: #1e40af;
  font-weight: 300;
  animation: blink 0.6s infinite;
  margin-left: 1px;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}
</style>
