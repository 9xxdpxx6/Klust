<template>
  <div :class="[
    'bg-kubgtu-white rounded-xl shadow-sm border border-border-light hover:shadow-lg transition-shadow flex flex-col case-card',
    isMobile ? 'p-4' : 'p-6'
  ]">
    <!-- Case Header -->
    <div class="mb-4">
      <h3 :class="[
        'font-semibold text-text-primary mb-2',
        isMobile ? 'text-lg' : 'text-xl'
      ]">
        {{ caseData.title }}
      </h3>
    </div>

    <!-- Description -->
    <p class="text-text-secondary mb-4 line-clamp-3">
      {{ caseData.description }}
    </p>

    <!-- Skills -->
    <div class="flex flex-wrap gap-2 mb-4">
      <span
        v-for="skill in caseData.skills.slice(0, 4)"
        :key="skill.id"
        class="px-2 py-1 text-xs font-medium bg-primary/10 text-primary rounded"
      >
        {{ skill.name }}
      </span>
      <span
        v-if="caseData.skills.length > 4"
        class="px-2 py-1 text-xs font-medium bg-gray-100 text-text-secondary rounded"
      >
        + ещё {{ caseData.skills.length - 4 }}
      </span>
    </div>

    <!-- Info above border -->
    <div class="flex items-center justify-between text-sm text-text-secondary mt-auto pb-2">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span>{{ getPartnerName() }}</span>
      </div>
      <div v-if="showTeamSize" class="flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span>{{ caseData.required_team_size }} чел.</span>
      </div>
      <div v-else class="flex items-center gap-1.5">
        <i class="pi pi-calendar text-gray-400" />
        <span v-if="caseData.deadline">
          До {{ formatDate(caseData.deadline) }}
        </span>
        <span v-else class="text-gray-400">Дедлайн не установлен</span>
      </div>
    </div>

    <!-- Footer with button -->
    <div class="border-t border-border-light pt-3">
      <Link
        v-if="showLink && linkUrl"
        :href="linkUrl"
        class="w-full px-4 py-2 text-sm font-medium text-center text-kubgtu-white bg-primary rounded-lg hover:bg-primary-dark transition-colors inline-block"
      >
        Подробнее
      </Link>
      <button
        v-else-if="!showStudentActions"
        @click="handleView"
        class="w-full px-4 py-2 text-sm font-medium text-center text-kubgtu-white bg-primary rounded-lg hover:bg-primary-dark transition-colors"
      >
        Подробнее
      </button>
      <div :class="[
        'flex gap-2',
        isMobile ? 'flex-col' : 'justify-end'
      ]">
        <div v-if="hasApplication" :class="[
          statusBadgeClasses,
          'inline-flex items-center justify-center rounded-md',
          isMobile ? 'px-2 h-7 text-xs w-full justify-center' : 'px-3 h-8 text-sm'
        ]">
          <i :class="[
            statusIconClasses,
            isMobile ? '' : 'mr-2'
          ]"></i>
          <span :class="statusTextClasses" class="font-medium">{{ applicationStatusLabel }}</span>
        </div>
              <!-- Всегда показываем кнопку "Просмотр" -->
              <Button
                variant="outline"
                :size="isMobile ? 'sm' : 'sm'"
                :class="isMobile ? 'w-full' : ''"
                @click.stop.prevent="handleView"
              >
                <i class="pi pi-eye" :class="isMobile ? '' : 'mr-2'" />
                Просмотр
              </Button>
              <!-- Показываем кнопку "Подать заявку" только если заявки нет -->
              <Button
                v-if="!hasApplication && canApply"
                variant="primary"
                :size="isMobile ? 'sm' : 'sm'"
                :class="isMobile ? 'w-full' : ''"
                :loading="applying"
                @click.stop.prevent="handleApply"
              >
                <i class="pi pi-check" :class="isMobile ? '' : 'mr-2'" />
                Подать заявку
              </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Button from './UI/Button.vue'
import { useResponsive } from '@/Composables/useResponsive'

const props = defineProps({
  caseData: {
    type: Object,
    required: true,
  },
  showLink: {
    type: Boolean,
    default: true,
  },
  linkUrl: {
    type: String,
    default: null,
  },
  showTeamSize: {
    type: Boolean,
    default: true,
  },
  showStudentActions: {
    type: Boolean,
    default: false,
  },
  canApply: {
    type: Boolean,
    default: false,
  },
  applying: {
    type: Boolean,
    default: false,
  },
  hasApplication: {
    type: Boolean,
    default: false,
  },
  applicationStatus: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['view', 'apply'])

const { isMobile } = useResponsive()

const handleView = () => {
  emit('view')
}

const handleApply = () => {
  emit('apply')
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }) + ' г.'
}

const applicationStatusLabel = computed(() => {
  if (!props.applicationStatus) return 'Заявка подана'
  
  const status = props.applicationStatus.status
  const statusMap = {
    pending: 'Заявка подана',
    accepted: 'Заявка принята',
    rejected: 'Заявка отклонена',
  }
  
  return statusMap[status] || props.applicationStatus.status_label || 'Заявка подана'
})

const isApplicationAccepted = computed(() => {
  if (!props.hasApplication || !props.applicationStatus) return false
  return props.applicationStatus.status === 'accepted'
})

const statusBadgeClasses = computed(() => {
  if (!props.hasApplication || !props.applicationStatus) return ''
  
  const status = props.applicationStatus.status
  if (status === 'accepted') {
    return 'bg-green-50 border border-green-200'
  } else if (status === 'rejected') {
    return 'bg-red-50 border border-red-200'
  }
  // pending - по умолчанию синий
  return 'bg-blue-50 border border-blue-200'
})

const statusIconClasses = computed(() => {
  if (!props.hasApplication || !props.applicationStatus) return 'pi pi-check-circle text-blue-600'
  
  const status = props.applicationStatus.status
  if (status === 'accepted') {
    return 'pi pi-check-circle text-green-600'
  } else if (status === 'rejected') {
    return 'pi pi-times-circle text-red-600'
  }
  // pending
  return 'pi pi-check-circle text-blue-600'
})

const statusTextClasses = computed(() => {
  if (!props.hasApplication || !props.applicationStatus) return 'text-blue-700'
  
  const status = props.applicationStatus.status
  if (status === 'accepted') {
    return 'text-green-700'
  } else if (status === 'rejected') {
    return 'text-red-700'
  }
  // pending
  return 'text-blue-700'
})

const getPartnerName = () => {
  const { caseData } = props
  
  // Вариант 1: partner_user.partner_profile.company_name (из getRecommendedCases)
  if (caseData.partner_user?.partner_profile?.company_name) {
    return caseData.partner_user.partner_profile.company_name
  }
  
  // Вариант 2: partner.company_name (из контроллера recommended)
  if (caseData.partner?.company_name) {
    return caseData.partner.company_name
  }
  
  // Вариант 3: partner_user.name (если нет профиля)
  if (caseData.partner_user?.name) {
    return caseData.partner_user.name
  }
  
  // Вариант 4: partner.name (старый формат)
  if (caseData.partner?.name) {
    return caseData.partner.name
  }
  
  // Fallback
  return 'Партнер'
}
</script>

<style scoped>
.case-card {
  @apply h-full;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

