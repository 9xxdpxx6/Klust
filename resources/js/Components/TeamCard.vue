<template>
  <Card :hover="true" class="team-card">
    <template #header>
      <div>
        <h3 class="text-lg font-semibold text-text-primary mb-1">{{ caseTitle || 'Команда' }}</h3>
        <p v-if="team.leader" class="text-sm text-text-secondary">
          Лидер: {{ team.leader.name }}
        </p>
      </div>
    </template>
    
    <div class="space-y-4">
      <div v-if="team.members && team.members.length > 0">
        <p class="text-sm font-medium text-text-secondary mb-2">Участники:</p>
        <div class="flex flex-wrap gap-2">
          <UserAvatar
            v-for="member in team.members"
            :key="member.id"
            :user="member.user || member"
            size="sm"
          />
        </div>
      </div>
      
      <div v-if="team.progress !== undefined">
        <div class="flex items-center justify-between text-sm mb-1">
          <span class="text-text-secondary">Прогресс команды:</span>
          <span class="font-medium text-text-primary">{{ team.progress }}%</span>
        </div>
        <ProgressBar :value="team.progress" color="primary" />
      </div>
      
      <div v-if="status" class="flex items-center justify-between">
        <span class="text-sm text-text-secondary">Статус:</span>
        <Badge :variant="statusVariant" :text="statusText" />
      </div>
    </div>
  </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from './UI/Card.vue';
import Badge from './UI/Badge.vue';
import ProgressBar from './UI/ProgressBar.vue';
import UserAvatar from './Shared/UserAvatar.vue';

const props = defineProps({
  team: {
    type: Object,
    required: true,
  },
  caseTitle: {
    type: String,
    default: null,
  },
});

const status = computed(() => props.team.status);

const statusVariant = computed(() => {
  const statusValue = status.value?.toLowerCase();
  if (statusValue === 'active' || statusValue === 'активна') return 'success';
  if (statusValue === 'completed' || statusValue === 'завершена') return 'primary';
  return 'secondary';
});

const statusText = computed(() => {
  const statusValue = status.value;
  const statusMap = {
    active: 'Активна',
    completed: 'Завершена',
    'активна': 'Активна',
    'завершена': 'Завершена',
  };
  return statusMap[statusValue] || statusValue || 'Неизвестно';
});
</script>

<style scoped>
.team-card {
  @apply h-full;
}
</style>


