<template>
  <nav v-if="items && items.length > 0" class="breadcrumbs">
    <ol class="flex items-center space-x-2">
      <li v-for="(item, index) in items" :key="index" class="flex items-center">
        <Link
          v-if="item.route && index < items.length - 1"
          :href="route(item.route)"
          class="text-text-muted hover:text-primary transition-colors"
        >
          {{ item.label }}
        </Link>
        <span
          v-else
          :class="[
            index === items.length - 1 ? 'text-text-primary font-medium' : 'text-text-muted'
          ]"
        >
          {{ item.label }}
        </span>
        <i
          v-if="index < items.length - 1"
          class="pi pi-angle-right mx-2 text-text-muted text-sm"
        />
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps({
  items: {
    type: Array,
    required: true,
    validator: (value) => {
      return value.every(item => item && typeof item.label === 'string');
    },
  },
});
</script>

<style scoped>
.breadcrumbs {
  @apply mb-4;
}
</style>

