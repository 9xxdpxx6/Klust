<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 transform translate-y-2"
    enter-to-class="opacity-100 transform translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="message && isVisible"
      :class="[
        'flash-message',
        `flash-message-${type}`,
      ]"
    >
      <div class="flex items-center">
        <i :class="['mr-3 text-lg', iconClass]" />
        <p class="flex-1">{{ message }}</p>
        <button
          @click="close"
          class="ml-4 text-current opacity-70 hover:opacity-100 transition-opacity"
        >
          <i class="pi pi-times" />
        </button>
      </div>
      <!-- Полоска обратного прогресс-бара -->
      <div 
        v-if="showProgress"
        class="flash-progress-bar"
        :class="`flash-progress-bar-${type}`"
      ></div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, ref, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isVisible = ref(true);
const showProgress = ref(false);

const message = computed(() => {
  return page.props.flash?.message ||
         page.props.flash?.success ||
         page.props.flash?.error ||
         page.props.flash?.warning ||
         page.props.flash?.info;
});

const type = computed(() => {
  if (page.props.flash?.error) return 'error';
  if (page.props.flash?.success) return 'success';
  if (page.props.flash?.warning) return 'warning';
  if (page.props.flash?.info) return 'info';
  return 'info';
});

const iconClass = computed(() => {
  const icons = {
    success: 'pi pi-check-circle',
    error: 'pi pi-times-circle',
    warning: 'pi pi-exclamation-triangle',
    info: 'pi pi-info-circle',
  };
  return icons[type.value] || icons.info;
});

let autoCloseTimer = null;

const close = () => {
  if (autoCloseTimer) {
    clearTimeout(autoCloseTimer);
    autoCloseTimer = null;
  }
  showProgress.value = false;
  isVisible.value = false;
};

// Автоматическое закрытие через 3.5 секунды
watch(message, (newMessage) => {
  if (newMessage) {
    // Сбрасываем видимость при новом сообщении
    isVisible.value = true;
    showProgress.value = false;
    
    // Очищаем предыдущий таймер, если есть
    if (autoCloseTimer) {
      clearTimeout(autoCloseTimer);
    }
    
    // Небольшая задержка перед началом анимации для плавности
    setTimeout(() => {
      showProgress.value = true;
    }, 50);
    
    // Устанавливаем новый таймер на 3.5 секунды (3500ms)
    autoCloseTimer = setTimeout(() => {
      close();
    }, 3500);
  } else {
    // Если сообщение исчезло, скрываем компонент
    isVisible.value = false;
    showProgress.value = false;
  }
}, { immediate: true });

onUnmounted(() => {
  close();
});
</script>

<style scoped>
.flash-message {
  @apply rounded-md p-4 mb-4 shadow-md;
}

.flash-message-success {
  @apply bg-green-50 border border-green-200 text-green-800;
}

.flash-message-error {
  @apply bg-red-50 border border-red-200 text-red-800;
}

.flash-message-warning {
  @apply bg-yellow-50 border border-yellow-200 text-yellow-800;
}

.flash-message-info {
  @apply bg-blue-50 border border-blue-200 text-blue-800;
}

/* Полоска обратного прогресс-бара */
.flash-progress-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: rgba(0, 0, 0, 0.1);
  border-radius: 0 0 0.375rem 0.375rem;
  overflow: hidden;
}

.flash-progress-bar::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  animation: progress-shrink 3.5s linear forwards;
}

.flash-progress-bar-success::after {
  background: rgb(34, 197, 94); /* green-500 */
}

.flash-progress-bar-error::after {
  background: rgb(239, 68, 68); /* red-500 */
}

.flash-progress-bar-warning::after {
  background: rgb(234, 179, 8); /* yellow-500 */
}

.flash-progress-bar-info::after {
  background: rgb(59, 130, 246); /* blue-500 */
}

@keyframes progress-shrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

.flash-message {
  position: relative;
  overflow: hidden;
}
</style>


