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
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold text-gray-900">{{ caseData.title }}</h1>
                        <span :class="[
                            'px-3 py-1 text-sm font-medium rounded-full',
                            getStatusBadgeClass(caseData.status)
                        ]">
                            {{ getStatusLabel(caseData.status) }}
                        </span>
                    </div>
                    <div class="flex space-x-3">
                        <Link
                            :href="route('admin.cases.edit', caseData.id)"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Редактировать
                        </Link>
                        <button
                            @click="confirmDelete"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Удалить
                        </button>
                    </div>
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

        <!-- Модальное окно подтверждения удаления -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-2">Подтверждение удаления</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Вы уверены, что хотите удалить кейс <strong>"{{ caseData.title }}"</strong>?
                        </p>
                        <p class="text-sm text-red-600 mt-2">
                            Это действие нельзя отменить. Все данные кейса будут удалены безвозвратно.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-4 mt-4">
                        <button
                            @click="showDeleteModal = false"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                        >
                            Отмена
                        </button>
                        <button
                            @click="deleteCase"
                            :disabled="processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="processing">Удаление...</span>
                            <span v-else>Удалить</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link, Head } from '@inertiajs/vue3'
import {
    ChevronRightIcon,
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline'
import ApplicationCard from './Partials/ApplicationCard.vue'
import { route } from "ziggy-js";

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

// Удаление кейса
const showDeleteModal = ref(false)
const processing = ref(false)

const confirmDelete = () => {
    showDeleteModal.value = true
}

const deleteCase = () => {
    processing.value = true
    router.delete(route('admin.cases.destroy', caseData.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            processing.value = false
        },
        onError: () => {
            alert('Произошла ошибка при удалении кейса')
            showDeleteModal.value = false
            processing.value = false
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

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
