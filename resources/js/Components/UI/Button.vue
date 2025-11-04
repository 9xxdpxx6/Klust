<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'btn',
      `btn-${variant}`,
      `btn-${size}`,
      {
        'btn-disabled': disabled || loading,
        'btn-loading': loading,
      }
    ]"
    @click="$emit('click', $event)"
  >
    <i v-if="icon && !loading" :class="['mr-2', icon]" />
    <i v-if="loading" class="pi pi-spin pi-spinner mr-2" />
    <slot />
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'outline'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  icon: {
    type: String,
    default: null,
  },
  type: {
    type: String,
    default: 'button',
  },
});

defineEmits(['click']);
</script>

<style scoped>
.btn {
  @apply inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
  @apply bg-primary text-kubgtu-white hover:bg-primary-light focus:ring-primary;
}

.btn-secondary {
  @apply bg-surface text-text-primary hover:bg-surface-hover focus:ring-border;
}

.btn-danger {
  @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
}

.btn-success {
  @apply bg-green-600 text-white hover:bg-green-700 focus:ring-green-500;
}

.btn-outline {
  @apply border-2 border-primary text-primary bg-transparent hover:bg-primary hover:text-kubgtu-white focus:ring-primary;
}

.btn-sm {
  @apply px-3 py-1.5 text-sm;
}

.btn-md {
  @apply px-4 py-2 text-base;
}

.btn-lg {
  @apply px-6 py-3 text-lg;
}

.btn-disabled {
  @apply opacity-50 cursor-not-allowed;
}

.btn-loading {
  @apply cursor-wait;
}
</style>


