<template>
  <Card :hover="true" class="case-card">
    <template #header>
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-text-primary mb-1">{{ caseData.title }}</h3>
          <p class="text-sm text-text-secondary">{{ caseData.partner?.name || 'Партнер не указан' }}</p>
        </div>
        <Badge :variant="statusVariant" :text="statusText" />
      </div>
    </template>
    
    <div class="space-y-3">
      <p class="text-sm text-text-secondary line-clamp-2">
        {{ caseData.description || 'Описание отсутствует' }}
      </p>
      
      <div v-if="caseData.skills && caseData.skills.length > 0" class="flex flex-wrap gap-2">
        <Badge
          v-for="skill in caseData.skills"
          :key="skill.id"
          variant="secondary"
          size="sm"
        >
          {{ skill.name }}
        </Badge>
      </div>
      
      <div class="flex items-center justify-between text-sm text-text-muted">
        <div class="flex items-center">
          <i class="pi pi-calendar mr-2" />
          <span v-if="caseData.deadline">
            До {{ formatDate(caseData.deadline) }}
          </span>
          <span v-else>Дедлайн не установлен</span>
        </div>
        <div v-if="caseData.applications_count !== undefined" class="flex items-center">
          <i class="pi pi-users mr-2" />
          <span>{{ caseData.applications_count }} заявок</span>
        </div>
      </div>
    </div>
    
    <template #footer v-if="showActions">
      <div class="flex items-center justify-between">
        <Button
          variant="outline"
          size="sm"
          @click="$emit('view')"
        >
          <i class="pi pi-eye mr-2" />
          Просмотр
        </Button>
        <div class="flex gap-2">
          <Button
            v-if="canApply"
            variant="primary"
            size="sm"
            :loading="applying"
            @click="$emit('apply')"
          >
            <i class="pi pi-check mr-2" />
            Подать заявку
          </Button>
          <Button
            v-if="canEdit"
            variant="secondary"
            size="sm"
            @click="$emit('edit')"
          >
            <i class="pi pi-pencil mr-2" />
            Редактировать
          </Button>
        </div>
      </div>
    </template>
  </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from './UI/Card.vue';
import Badge from './UI/Badge.vue';
import Button from './UI/Button.vue';

const props = defineProps({
  caseData: {
    type: Object,
    required: true,
  },
  showActions: {
    type: Boolean,
    default: true,
  },
  canApply: {
    type: Boolean,
    default: false,
  },
  canEdit: {
    type: Boolean,
    default: false,
  },
  applying: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['view', 'apply', 'edit']);

const statusVariant = computed(() => {
  const status = props.caseData.status?.toLowerCase();
  if (status === 'active' || status === 'активен') return 'success';
  if (status === 'completed' || status === 'завершен') return 'primary';
  if (status === 'draft' || status === 'черновик') return 'secondary';
  if (status === 'archived' || status === 'архив') return 'secondary';
  return 'secondary';
});

const statusText = computed(() => {
  const status = props.caseData.status;
  const statusMap = {
    active: 'Активен',
    completed: 'Завершен',
    draft: 'Черновик',
    archived: 'Архив',
    'активен': 'Активен',
    'завершен': 'Завершен',
    'черновик': 'Черновик',
    'архив': 'Архив',
  };
  return statusMap[status] || status || 'Неизвестно';
});

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
};
</script>

<style scoped>
.case-card {
  @apply h-full flex flex-col;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>


