<template>
  <Card :hover="true" class="skill-card">
    <div class="flex items-start justify-between mb-4">
      <div class="flex-1">
        <h3 :class="[
          'font-semibold text-text-primary mb-1',
          isMobile ? 'text-base' : 'text-lg'
        ]">{{ skill.name }}</h3>
        <p v-if="skill.description" :class="[
          'text-text-secondary line-clamp-2',
          isMobile ? 'text-xs' : 'text-sm'
        ]">
          {{ skill.description }}
        </p>
      </div>
      <div v-if="icon" :class="[
        'ml-4',
        isMobile ? '' : ''
      ]">
        <i :class="[
          icon,
          isMobile ? 'text-xl' : 'text-2xl'
        ]" />
      </div>
    </div>
    
    <div v-if="level !== undefined || progress !== undefined" class="space-y-2">
      <div v-if="level !== undefined" class="flex items-center justify-between text-sm">
        <span class="text-text-secondary">Уровень:</span>
        <Badge variant="primary" size="sm">
          {{ level }}
        </Badge>
      </div>
      
      <div v-if="progress !== undefined">
        <div class="flex items-center justify-between text-sm mb-1">
          <span class="text-text-secondary">Прогресс:</span>
          <span class="font-medium text-text-primary">{{ progress }}%</span>
        </div>
        <ProgressBar :value="progress" color="primary" />
      </div>
      
      <div v-if="points !== undefined" class="text-sm text-text-muted">
        <i class="pi pi-star mr-1" />
        {{ points }} очков
      </div>
    </div>
  </Card>
</template>

<script setup>
import Card from './UI/Card.vue';
import Badge from './UI/Badge.vue';
import ProgressBar from './UI/ProgressBar.vue';
import { useResponsive } from '@/Composables/useResponsive';

defineProps({
  skill: {
    type: Object,
    required: true,
  },
  level: {
    type: Number,
    default: undefined,
  },
  progress: {
    type: Number,
    default: undefined,
  },
  points: {
    type: Number,
    default: undefined,
  },
  icon: {
    type: String,
    default: null,
  },
});

const { isMobile } = useResponsive();
</script>

<style scoped>
.skill-card {
  @apply h-full;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>


