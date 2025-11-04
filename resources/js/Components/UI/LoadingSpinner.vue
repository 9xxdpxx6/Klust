<template>
  <div
    :class="[
      'loading-spinner',
      {
        'loading-spinner-fullscreen': fullScreen,
        [`loading-spinner-${size}`]: !fullScreen,
      }
    ]"
  >
    <i class="pi pi-spin pi-spinner" />
    <p v-if="message" class="mt-4 text-text-secondary">
      {{ message }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  fullScreen: {
    type: Boolean,
    default: false,
  },
  message: {
    type: String,
    default: null,
  },
});
</script>

<style scoped>
.loading-spinner {
  @apply flex flex-col items-center justify-center;
}

.loading-spinner-sm {
  @apply py-4;
}

.loading-spinner-md {
  @apply py-8;
}

.loading-spinner-lg {
  @apply py-12;
}

.loading-spinner-fullscreen {
  @apply fixed inset-0 bg-background bg-opacity-75 z-50;
}

.loading-spinner i {
  @apply text-primary;
}

.loading-spinner-sm i {
  @apply text-2xl;
}

.loading-spinner-md i {
  @apply text-4xl;
}

.loading-spinner-lg i {
  @apply text-6xl;
}

.loading-spinner-fullscreen i {
  @apply text-6xl;
}
</style>


