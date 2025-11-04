<template>
  <Card :hover="!!link" :class="{ 'cursor-pointer': link }" @click="handleClick">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-text-secondary font-medium">{{ title }}</p>
          <h3 class="text-2xl font-bold text-text-primary mt-1">{{ formattedValue }}</h3>
        </div>
        <div v-if="icon" :class="['stats-icon', `stats-icon-${variant}`]">
          <i :class="icon" />
        </div>
      </div>
    </template>
    
    <div v-if="trend !== null" class="flex items-center mt-2">
      <i
        :class="[
          'mr-1',
          trend > 0 ? 'pi pi-arrow-up text-green-600' : trend < 0 ? 'pi pi-arrow-down text-red-600' : 'pi pi-minus text-text-muted'
        ]"
      />
      <span
        :class="[
          'text-sm font-medium',
          trend > 0 ? 'text-green-600' : trend < 0 ? 'text-red-600' : 'text-text-muted'
        ]"
      >
        {{ Math.abs(trend) }}%
      </span>
      <span class="text-sm text-text-muted ml-1">vs предыдущий период</span>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Card from './Card.vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [Number, String],
    required: true,
  },
  icon: {
    type: String,
    default: null,
  },
  trend: {
    type: Number,
    default: null,
  },
  link: {
    type: String,
    default: null,
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'danger'].includes(value),
  },
});

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString('ru-RU');
  }
  return props.value;
});

const handleClick = () => {
  if (props.link) {
    router.visit(props.link);
  }
};
</script>

<style scoped>
.stats-icon {
  @apply w-12 h-12 rounded-full flex items-center justify-center text-2xl;
}

.stats-icon-primary {
  @apply bg-primary bg-opacity-10 text-primary;
}

.stats-icon-success {
  @apply bg-green-600 bg-opacity-10 text-green-600;
}

.stats-icon-warning {
  @apply bg-yellow-500 bg-opacity-10 text-yellow-600;
}

.stats-icon-danger {
  @apply bg-red-600 bg-opacity-10 text-red-600;
}
</style>


