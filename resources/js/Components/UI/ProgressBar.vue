<template>
  <div class="progress-bar-wrapper">
    <div class="progress-bar-container">
      <div
        :class="[
          'progress-bar',
          `progress-bar-${color}`,
        ]"
        :style="{ width: `${Math.min(100, Math.max(0, value))}%` }"
      >
        <span v-if="showLabel" class="progress-bar-label">
          {{ value }}%
        </span>
      </div>
    </div>
    <p v-if="label" class="progress-bar-text">
      {{ label }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  value: {
    type: Number,
    default: 0,
    validator: (value) => value >= 0 && value <= 100,
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger'].includes(value),
  },
  showLabel: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: null,
  },
});
</script>

<style scoped>
.progress-bar-wrapper {
  @apply w-full;
}

.progress-bar-container {
  @apply w-full bg-surface rounded-full h-2.5 overflow-hidden;
}

.progress-bar {
  @apply h-full rounded-full transition-all duration-300 ease-out flex items-center justify-end pr-2;
}

.progress-bar-primary {
  @apply bg-primary;
}

.progress-bar-success {
  @apply bg-green-600;
}

.progress-bar-warning {
  @apply bg-yellow-500;
}

.progress-bar-danger {
  @apply bg-red-600;
}

.progress-bar-label {
  @apply text-xs text-white font-medium;
}

.progress-bar-text {
  @apply mt-1 text-sm text-text-secondary;
}
</style>


