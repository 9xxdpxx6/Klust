<template>
  <div class="dialogue-history">
    <h3 class="text-lg font-semibold mb-4">История диалога</h3>
    
    <div ref="messagesContainerRef" class="messages-container">
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
      
      <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
        История диалога пуста
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'

const props = defineProps({
  messages: {
    type: Array,
    default: () => []
  }
})

const messagesContainerRef = ref(null)

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

const scrollToBottom = () => {
  if (messagesContainerRef.value) {
    messagesContainerRef.value.scrollTop = messagesContainerRef.value.scrollHeight
  }
}

watch(() => props.messages, () => {
  nextTick(() => {
    scrollToBottom()
  })
}, { deep: true })

onMounted(() => {
  scrollToBottom()
})
</script>

<style scoped>
.dialogue-history {
  padding: 1rem;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding-right: 0.5rem;
  scrollbar-width: thin;
  scrollbar-color: #fbbf24 #1e40af;
}

.messages-container::-webkit-scrollbar {
  width: 8px;
}

.messages-container::-webkit-scrollbar-track {
  background: #1e40af;
  border-radius: 10px;
}

.messages-container::-webkit-scrollbar-thumb {
  background-color: #fbbf24;
  border-radius: 10px;
  border: 2px solid #1e40af;
}

.message {
  margin-bottom: 15px;
  padding: 10px 15px;
  border-radius: 12px;
  max-width: 85%;
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.message-client {
  background: rgba(255, 255, 255, 0.15);
  align-self: flex-start;
  border-bottom-left-radius: 4px;
  color: white;
}

.message-user {
  background: #fbbf24;
  color: #1f2937;
  align-self: flex-end;
  border-bottom-right-radius: 4px;
}

.message-content {
  font-size: 0.95rem;
  line-height: 1.4;
}

.message-time {
  font-size: 0.75rem;
  opacity: 0.7;
  margin-top: 5px;
  text-align: right;
}

.message-user .message-time {
  color: #1f2937;
}
</style>
