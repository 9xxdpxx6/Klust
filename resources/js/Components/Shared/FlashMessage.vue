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
      v-if="message"
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
    </div>
  </Transition>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

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
};

watch(message, (newMessage) => {
  if (newMessage) {
    autoCloseTimer = setTimeout(() => {
      close();
    }, 5000);
  }
});

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
</style>


