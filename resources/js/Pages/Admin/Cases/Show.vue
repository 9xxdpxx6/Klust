<template>
    <div class="p-6">
        <Head :title="`Кейс: ${caseData.title}`" />

        <!-- Хлебные крошки -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <Link :href="route('admin.cases.index')" class="text-gray-400 hover:text-gray-500">
                                <span class="text-sm font-medium">Кейсы</span>
                            </Link>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <ChevronRightIcon class="h-5 w-5 flex-shrink-0 text-gray-400" />
                            <span class="ml-4 text-sm font-medium text-gray-500">{{ caseData.title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Основная информация о кейсе -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">{{ caseData.title }}</h1>
                    <span :class="[
                        'px-3 py-1 text-sm font-medium rounded-full',
                        getStatusBadgeClass(caseData.status)
                    ]">
                        {{ getStatusLabel(caseData.status) }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Информация о кейсе -->
                    <div class="lg:col-span-2">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Описание кейса</h2>
                        <p class="text-gray-700 whitespace-pre-line">{{ caseData.description }}</p>

                        <!-- Навыки -->
                        <div class="mt-6">
                            <h3 class="text-md font-medium text-gray-900 mb-2">Требуемые навыки</h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="skill in caseData.skills"
                                    :key="skill.id"
                                    class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
                                >
                                    {{ skill.name }}
                                </span>
                                <span
                                    v-if="!caseData.skills || caseData.skills.length === 0"
                                    class="text-gray-500 text-sm"
                                >
                                    Навыки не указаны
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Детали кейса -->
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-md font-medium text-gray-900 mb-3">Детали кейса</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Партнер</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ caseData.partner?.company_name }}
                                        <span class="text-gray-500 block">{{ caseData.partner?.user?.name }}</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Дедлайн</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ formatDate(caseData.deadline) }}
                                        <span :class="[
                                            'block text-xs font-medium',
                                            isDeadlineSoon(caseData.deadline) ? 'text-red-600' : 'text-gray-500'
                                        ]">
                                            {{ daysUntilDeadline(caseData.deadline) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Размер команды</dt>
                                    <dd class="text-sm text-gray-900">{{ caseData.required_team_size }} человек</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Награда</dt>
                                    <dd class="text-sm text-gray-900">{{ caseData.reward }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Симулятор</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ caseData.simulator?.title || 'Не указан' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Дата создания</dt>
                                    <dd class="text-sm text-gray-900">{{ formatDate(caseData.created_at) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <DocumentTextIcon class="h-8 w-8 text-blue-600" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Всего заявок</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ statistics.total_applications }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <ClockIcon class="h-8 w-8 text-yellow-600" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">На рассмотрении</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ statistics.pending_applications }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <CheckCircleIcon class="h-8 w-8 text-green-600" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Одобрено</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ statistics.approved_applications }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <UserGroupIcon class="h-8 w-8 text-purple-600" />
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Средний размер команды</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                {{ Math.round(statistics.average_team_size * 10) / 10 || 0 }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Заявки по статусам -->
        <div class="space-y-6">
            <!-- Одобренные заявки -->
            <div v-if="applicationsByStatus.approved.length > 0">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Одобренные команды</h2>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                        <h3 class="text-lg font-medium text-green-900">Одобренные заявки ({{ applicationsByStatus.approved.length }})</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="application in applicationsByStatus.approved"
                            :key="application.id"
                            class="px-6 py-4"
                        >
                            <ApplicationCard :application="application" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Заявки на рассмотрении -->
            <div v-if="applicationsByStatus.pending.length > 0">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Заявки на рассмотрении</h2>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50">
                        <h3 class="text-lg font-medium text-yellow-900">На рассмотрении ({{ applicationsByStatus.pending.length }})</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="application in applicationsByStatus.pending"
                            :key="application.id"
                            class="px-6 py-4"
                        >
                            <ApplicationCard :application="application" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Отклоненные заявки -->
            <div v-if="applicationsByStatus.rejected.length > 0">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Отклоненные заявки</h2>
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                        <h3 class="text-lg font-medium text-red-900">Отклоненные ({{ applicationsByStatus.rejected.length }})</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="application in applicationsByStatus.rejected"
                            :key="application.id"
                            class="px-6 py-4"
                        >
                            <ApplicationCard :application="application" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Сообщение если нет заявок -->
            <div v-if="statistics.total_applications === 0" class="bg-white shadow rounded-lg p-8 text-center">
                <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">Заявок пока нет</h3>
                <p class="mt-1 text-sm text-gray-500">
                    На этот кейс еще не было подано заявок от студентов.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import {
    ChevronRightIcon,
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline'
import ApplicationCard from './Partials/ApplicationCard.vue'

const props = defineProps({
    case: {
        type: Object,
        default: () => ({})
    },
    statistics: {
        type: Object,
        default: () => ({})
    },
    applicationsByStatus: {
        type: Object,
        default: () => ({
            pending: [],
            approved: [],
            rejected: []
        })
    }
})

// Computed свойства для безопасного доступа
const caseData = computed(() => props.case || {})
const statistics = computed(() => props.statistics || {})
const applicationsByStatus = computed(() => props.applicationsByStatus || {})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
}

const daysUntilDeadline = (deadline) => {
    try {
        const today = new Date()
        const deadlineDate = new Date(deadline)
        const diffTime = deadlineDate - today
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

        if (diffDays < 0) return 'Просрочен'
        if (diffDays === 0) return 'Сегодня'
        if (diffDays === 1) return 'Завтра'
        return `Через ${diffDays} дн.`
    } catch (error) {
        return 'Ошибка даты'
    }
}

const isDeadlineSoon = (deadline) => {
    try {
        const today = new Date()
        const deadlineDate = new Date(deadline)
        const diffTime = deadlineDate - today
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
        return diffDays <= 3 && diffDays >= 0
    } catch (error) {
        return false
    }
}

const getStatusLabel = (status) => {
    const statusMap = {
        draft: 'Черновик',
        active: 'Активный',
        completed: 'Завершен',
        archived: 'Архивный',
    }
    return statusMap[status] || status
}

const getStatusBadgeClass = (status) => {
    const classMap = {
        draft: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        archived: 'bg-red-100 text-red-800',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800'
}
</script>
