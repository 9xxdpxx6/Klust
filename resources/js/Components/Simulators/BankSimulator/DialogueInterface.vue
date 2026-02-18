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
        <div v-if="message.role !== 'system'" class="message-time">
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

    <!-- Индикатор завершения симулятора -->
    <div v-if="isFinalStage" class="completion-indicator">
      <div class="completion-content">
        <h3 class="completion-title">🎉 Симулятор завершен!</h3>
        <div class="completion-stats">
          <div class="stat-item">
            <span class="stat-label">Ваш результат:</span>
            <span class="stat-value">{{ formatScorePoints(currentScore) }}</span>
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
          💡 Результаты расчёта отображены на экране ноутбука. Продолжите диалог с клиентом.
        </p>
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

const emit = defineEmits(['optionSelect', 'completeSession', 'restartSession'])

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

const isProcessing = ref(false)
const messagesContainerRef = ref(null)

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

const formatScorePoints = (score) => {
  if (score === null || score === undefined) return '0/100'
  const currentScore = Math.max(0, Math.round(score))
  
  // Определяем максимальный балл автоматически
  // Если балл <= 100, то max = 100
  // Если балл > 100, округляем до ближайшего большего "круглого" числа
  let maxScore = 100
  if (currentScore > 100) {
    // Круглые числа: 200, 500, 1000, 2000, 5000, 10000...
    const roundNumbers = [200, 500, 1000, 2000, 5000, 10000, 20000, 50000, 100000]
    for (const roundNum of roundNumbers) {
      if (currentScore <= roundNum) {
        maxScore = roundNum
        break
      }
    }
    // Если балл больше всех круглых чисел, округляем до ближайшей тысячи
    if (currentScore > roundNumbers[roundNumbers.length - 1]) {
      maxScore = Math.ceil(currentScore / 1000) * 1000
    }
  }
  
  return `${currentScore}/${maxScore}`
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

:global(.dialog-3d-container) {
  scrollbar-gutter: stable;
  scrollbar-width: thin;
  scrollbar-color: rgba(107, 114, 128, 0.6) transparent;
}

:global(.dialog-3d-container::-webkit-scrollbar) {
  width: 10px;
}

:global(.dialog-3d-container::-webkit-scrollbar-track) {
  background: transparent;
  margin: 8px 0;
  border-radius: 999px;
}

:global(.dialog-3d-container::-webkit-scrollbar-thumb) {
  background: rgba(107, 114, 128, 0.6);
  border: 2px solid transparent;
  background-clip: padding-box;
  border-radius: 999px;
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

.message-system {
  background: #f0f9ff;
  border-left: 3px solid #3b82f6;
  color: #1e40af;
  font-style: italic;
  font-size: 0.9rem;
  padding: 8px 14px;
  margin-bottom: 8px;
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

.score-details {
  margin-top: 16px;
  text-align: left;
}

.score-details-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 12px;
  color: white;
  text-align: left;
}

.score-history {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: left;
}

.score-history-item {
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  text-align: left;
}

.score-history-reason {
  font-size: 14px;
  color: white;
  display: block;
  text-align: left;
}

</style>
