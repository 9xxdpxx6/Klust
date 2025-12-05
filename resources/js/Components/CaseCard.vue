<template>
  <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all case-card">
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-start justify-between mb-4">
        <div class="flex-1 pr-4">
          <h3 class="text-lg font-bold text-gray-900 mb-1">{{ caseData.title }}</h3>
          <p class="text-sm text-gray-600">{{ caseData.partner?.name || caseData.partner?.company_name || 'Партнер не указан' }}</p>
        </div>
        <Badge :variant="statusVariant" :text="statusText" />
      </div>
      
      <!-- Description -->
      <p class="text-sm text-gray-600 line-clamp-2 mb-4">
        {{ caseData.description || 'Описание отсутствует' }}
      </p>
      
      <!-- Skills -->
      <div v-if="caseData.skills && caseData.skills.length > 0" class="flex flex-wrap gap-2 mb-4">
        <span
          v-for="skill in caseData.skills"
          :key="skill.id"
          class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full border border-gray-200"
        >
          {{ skill.name }}
        </span>
      </div>
      
      <!-- Footer info -->
      <div class="flex items-center justify-between text-sm text-gray-500 mb-4 pb-4 border-b border-gray-200">
        <div class="flex items-center">
          <i class="pi pi-calendar mr-2"></i>
          <span v-if="caseData.deadline">
            До {{ formatDate(caseData.deadline) }}
          </span>
          <span v-else>Дедлайн не установлен</span>
        </div>
        <div v-if="caseData.applications_count !== undefined" class="flex items-center">
          <i class="pi pi-users mr-2"></i>
          <span>{{ caseData.applications_count }} заявок</span>
        </div>
      </div>
      
      <!-- Actions -->
      <div v-if="showActions" class="flex items-center justify-end gap-3">
        <Button
          variant="outline"
          size="sm"
          @click="handleViewClick"
        >
          <i class="pi pi-eye mr-2"></i>
          Просмотр
        </Button>
        <Button
          v-if="canApply"
          variant="primary"
          size="sm"
          :loading="applying"
          @click="handleApplyClick"
        >
          <i class="pi pi-check mr-2"></i>
          Подать заявку
        </Button>
        <Button
          v-if="canEdit"
          variant="secondary"
          size="sm"
          @click="handleEditClick"
        >
          <i class="pi pi-pencil mr-2"></i>
          Редактировать
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
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

const emit = defineEmits(['view', 'apply', 'edit']);

const handleViewClick = () => {
  emit('view');
};

const handleApplyClick = () => {
  emit('apply');
};

const handleEditClick = () => {
  emit('edit');
};

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


