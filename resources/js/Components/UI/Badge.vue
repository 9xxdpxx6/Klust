<template>
  <span
    :class="[
      'badge',
      `badge-${variant}`,
      `badge-${size}`,
    ]"
  >
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    default: null,
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger', 'secondary'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
});
</script>

<style scoped>
.badge {
  @apply inline-flex items-center font-medium rounded-full;
}

.badge-primary {
  @apply bg-primary text-kubgtu-white;
}

.badge-success {
  @apply bg-green-600 text-white;
}

.badge-warning {
  @apply bg-yellow-500 text-white;
}

.badge-danger {
  @apply bg-red-600 text-white;
}

.badge-secondary {
  @apply bg-surface text-text-primary border border-border;
}

.badge-sm {
  @apply px-2 py-0.5 text-xs;
}

.badge-md {
  @apply px-3 py-1 text-sm;
}

.badge-lg {
  @apply px-4 py-1.5 text-base;
}
</style>


