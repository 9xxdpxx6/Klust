<template>
  <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all case-card">
    <div class="p-6 space-y-4">
      <!-- Header with title and status -->
      <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
          <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ caseData.title }}</h3>
          <p class="text-sm text-gray-600 font-medium">{{ caseData.partner?.name || caseData.partner?.company_name || caseData.partner?.user?.name || 'Партнер не указан' }}</p>
        </div>
        <Badge :variant="statusVariant" :text="statusText" class="flex-shrink-0" />
      </div>
      
      <!-- Description -->
      <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
        {{ caseData.description || 'Описание отсутствует' }}
      </p>
      
      <!-- Skills -->
      <div v-if="caseData.skills && caseData.skills.length > 0" class="flex flex-wrap gap-2">
        <span
          v-for="skill in caseData.skills"
          :key="skill.id"
          class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full border border-gray-200"
        >
          {{ skill.name }}
        </span>
      </div>
      
      <!-- Footer info -->
      <div class="flex items-center justify-between text-sm text-gray-500 pt-2 border-t border-gray-100">
        <div class="flex items-center gap-1.5">
          <i class="pi pi-calendar text-gray-400" />
          <span v-if="caseData.deadline">
            До {{ formatDate(caseData.deadline) }}
          </span>
          <span v-else class="text-gray-400">Дедлайн не установлен</span>
        </div>
        <div v-if="caseData.applications_count !== undefined" class="flex items-center gap-1.5">
          <i class="pi pi-users text-gray-400" />
          <span>{{ caseData.applications_count }} заявок</span>
        </div>
      </div>
    </div>
    
    <!-- Actions -->
    <div v-if="showActions" class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between gap-3 relative z-10">
      <button
        v-if="canEdit"
        type="button"
        @click.stop.prevent="handleEdit"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors cursor-pointer relative z-10"
      >
        <i class="pi pi-pencil mr-2" />
        Редактировать
      </button>
      <div v-else class="flex-1"></div>
      <div class="flex gap-2 relative z-10">
        <Button
          v-if="canApply"
          variant="primary"
          size="sm"
          :loading="applying"
          @click.stop.prevent="handleApply"
        >
          <i class="pi pi-check mr-2" />
          Подать заявку
        </Button>
        <Button
          variant="primary"
          size="sm"
          @click.stop.prevent="handleView"
        >
          <i class="pi pi-eye mr-2" />
          Просмотр
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

const handleView = (event) => {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  emit('view');
};

const handleEdit = (event) => {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  emit('edit');
};

const handleApply = (event) => {
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  emit('apply');
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
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }) + ' г.';
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

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>


