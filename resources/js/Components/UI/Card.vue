<template>
  <div :class="['card', { 'card-hover': hover }]">
    <div v-if="title || $slots.header" class="card-header">
      <div>
        <h3 v-if="title" class="card-title">{{ title }}</h3>
        <p v-if="subtitle" class="card-subtitle">{{ subtitle }}</p>
      </div>
      <slot name="header" />
    </div>
    
    <div class="card-body">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: null,
  },
  subtitle: {
    type: String,
    default: null,
  },
  hover: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped>
.card {
  @apply bg-background border border-border rounded-lg shadow-sm overflow-hidden;
}

.card-hover {
  @apply transition-shadow hover:shadow-md;
}

.card-header {
  @apply px-6 py-4 border-b border-border flex items-center justify-between;
}

.card-title {
  @apply text-lg font-semibold text-text-primary;
}

.card-subtitle {
  @apply text-sm text-text-secondary mt-1;
}

.card-body {
  @apply px-6 py-4;
}

.card-footer {
  @apply px-6 py-4 border-t border-border bg-surface;
}
</style>


