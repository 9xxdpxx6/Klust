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
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'outline', 'danger-outline'].includes(value),
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
  @apply inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none;
}

.btn-primary {
  @apply bg-primary text-kubgtu-white hover:bg-primary-light;
}

.btn-secondary {
  @apply bg-surface text-text-primary hover:bg-surface-hover;
}

.btn-danger {
  @apply bg-red-600 text-white hover:bg-red-700;
}

.btn-success {
  @apply bg-green-600 text-white hover:bg-green-700;
}

.btn-outline {
  @apply border-2 border-primary text-primary bg-transparent hover:bg-primary hover:text-kubgtu-white;
  box-sizing: border-box;
}

.btn-danger-outline {
  @apply border-2 border-red-600 text-red-600 bg-transparent hover:bg-red-600 hover:text-white;
  box-sizing: border-box;
}

.btn-sm {
  @apply text-sm;
  height: 2rem;
  padding: 0.375rem 0.75rem;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-outline.btn-sm {
  height: 2rem;
  padding: calc(0.375rem - 2px) calc(0.75rem - 2px);
}

.btn-danger-outline.btn-sm {
  height: 2rem;
  padding: calc(0.375rem - 2px) calc(0.75rem - 2px);
}

.btn-danger-outline {
  min-height: 2.5rem; /* Высота как у PrimeVue InputText */
}

.btn-md {
  @apply px-4 py-2 text-base;
  min-height: 2.5rem; /* Высота как у PrimeVue InputText */
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


