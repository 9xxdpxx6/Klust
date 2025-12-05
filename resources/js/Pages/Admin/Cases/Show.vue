<template>
    <div class="space-y-6">
        <Head :title="`Кейс: ${caseData.title}`" />

        <!-- Заголовок с действиями -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <nav class="flex mb-4" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2">
                                <li>
                                    <Link :href="route('admin.cases.index')" class="text-indigo-200 hover:text-white transition-colors">
                                        <span class="text-sm font-medium">Кейсы</span>
                                    </Link>
                                </li>
                                <li>
                                    <ChevronRightIcon class="h-4 w-4 text-indigo-300" />
                                </li>
                                <li>
                                    <span class="text-sm font-medium text-white">{{ caseData.title }}</span>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-3xl font-bold text-white mb-3">{{ caseData.title }}</h1>
                        <div class="flex items-center gap-3">
                            <span :class="[
                                'px-4 py-1.5 text-sm font-semibold rounded-full shadow-sm',
                                getStatusBadgeClass(caseData.status)
                            ]">
                                {{ getStatusLabel(caseData.status) }}
                            </span>
                            <span class="text-indigo-100 text-sm">
                                Создан {{ formatDate(caseData.created_at) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <Link
                            :href="route('admin.cases.edit', caseData.id)"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium"
                        >
                            <i class="pi pi-pencil"></i>
                            Редактировать
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика - карточки с градиентами -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-md border border-blue-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Всего заявок</p>
                        <p class="text-3xl font-bold text-blue-900">{{ statistics.total_applications || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl">
                        <DocumentTextIcon class="h-8 w-8 text-white" />
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 shadow-md border border-amber-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 mb-1">На рассмотрении</p>
                        <p class="text-3xl font-bold text-amber-900">{{ statistics.pending_applications || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl">
                        <ClockIcon class="h-8 w-8 text-white" />
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-md border border-green-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Одобрено</p>
                        <p class="text-3xl font-bold text-green-900">{{ statistics.approved_applications || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl">
                        <CheckCircleIcon class="h-8 w-8 text-white" />
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-md border border-purple-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 mb-1">Средний размер команды</p>
                        <p class="text-3xl font-bold text-purple-900">
                            {{ Math.round(statistics.average_team_size * 10) / 10 || 0 }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl">
                        <UserGroupIcon class="h-8 w-8 text-white" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Описание и навыки -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Описание кейса -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-file-edit text-indigo-600"></i>
                            Описание кейса
                        </h2>
                    </div>
                    <div class="px-6 py-6">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ caseData.description || 'Описание не указано' }}</p>
                    </div>
                </div>

                <!-- Требуемые навыки -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-star text-amber-500"></i>
                            Требуемые навыки
                        </h2>
                    </div>
                    <div class="px-6 py-6">
                        <div v-if="caseData.skills && caseData.skills.length > 0" class="flex flex-wrap gap-3">
                            <span
                                v-for="skill in caseData.skills"
                                :key="skill.id"
                                class="px-4 py-2 bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-800 text-sm font-medium rounded-lg border border-indigo-200 shadow-sm"
                            >
                                {{ skill.name }}
                            </span>
                        </div>
                        <div v-else class="text-center py-8">
                            <i class="pi pi-info-circle text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 text-sm">Навыки не указаны</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Детали кейса - боковая панель -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-info-circle text-indigo-600"></i>
                            Детали кейса
                        </h2>
                    </div>
                    <div class="px-6 py-6 space-y-5">
                        <!-- Партнер -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-indigo-100 rounded-lg">
                                    <i class="pi pi-building text-indigo-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Партнер</p>
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ caseData.partner?.company_name || caseData.partner?.name || 'Не указан' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ caseData.partner?.contact_person || caseData.partner?.user?.name || '' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Дедлайн -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <i class="pi pi-calendar text-red-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Дедлайн</p>
                                    <p class="text-sm font-medium text-gray-900">{{ formatDate(caseData.deadline) || 'Не указан' }}</p>
                                    <p :class="[
                                        'text-xs font-medium mt-1',
                                        isDeadlineSoon(caseData.deadline) ? 'text-red-600' : 'text-gray-500'
                                    ]">
                                        {{ daysUntilDeadline(caseData.deadline) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Размер команды -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="pi pi-users text-blue-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Размер команды</p>
                                    <p class="text-sm font-medium text-gray-900">{{ caseData.required_team_size || 1 }} человек</p>
                                </div>
                            </div>
                        </div>

                        <!-- Награда -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-amber-100 rounded-lg">
                                    <i class="pi pi-gift text-amber-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Награда</p>
                                    <p class="text-sm font-medium text-gray-900">{{ caseData.reward || 'Не указана' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Симулятор -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <i class="pi pi-desktop text-purple-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Симулятор</p>
                                    <p class="text-sm font-medium text-gray-900">{{ caseData.simulator?.title || 'Не указан' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Дата создания -->
                        <div>
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-gray-100 rounded-lg">
                                    <i class="pi pi-clock text-gray-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Дата создания</p>
                                    <p class="text-sm font-medium text-gray-900">{{ formatDate(caseData.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Заявки -->
        <div class="space-y-6">
            <!-- Одобренные заявки -->
            <div v-if="applicationsByStatus.approved && applicationsByStatus.approved.length > 0">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <button
                        @click="toggleSection('approved')"
                        class="w-full px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200 hover:from-green-100 hover:to-emerald-100 transition-colors flex items-center justify-between"
                    >
                        <h2 class="text-xl font-bold text-green-900 flex items-center gap-2">
                            <CheckCircleIcon class="h-6 w-6" />
                            Одобренные команды ({{ applicationsByStatus.approved.length }})
                        </h2>
                        <ChevronDownIcon
                            :class="[
                                'h-5 w-5 text-green-700 transition-transform duration-200',
                                collapsedSections.approved ? 'transform rotate-180' : ''
                            ]"
                        />
                    </button>
                    <Transition name="slide">
                        <div
                            v-if="!collapsedSections.approved"
                            class="divide-y divide-gray-100"
                        >
                            <div
                                v-for="application in applicationsByStatus.approved"
                                :key="application.id"
                                class="px-6 py-5 hover:bg-gray-50 transition-colors"
                            >
                                <ApplicationCard :application="application" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Заявки на рассмотрении -->
            <div v-if="applicationsByStatus.pending && applicationsByStatus.pending.length > 0">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <button
                        @click="toggleSection('pending')"
                        class="w-full px-6 py-4 bg-gradient-to-r from-amber-50 to-yellow-50 border-b border-amber-200 hover:from-amber-100 hover:to-yellow-100 transition-colors flex items-center justify-between"
                    >
                        <h2 class="text-xl font-bold text-amber-900 flex items-center gap-2">
                            <ClockIcon class="h-6 w-6" />
                            Заявки на рассмотрении ({{ applicationsByStatus.pending.length }})
                        </h2>
                        <ChevronDownIcon
                            :class="[
                                'h-5 w-5 text-amber-700 transition-transform duration-200',
                                collapsedSections.pending ? 'transform rotate-180' : ''
                            ]"
                        />
                    </button>
                    <Transition name="slide">
                        <div
                            v-if="!collapsedSections.pending"
                            class="divide-y divide-gray-100"
                        >
                            <div
                                v-for="application in applicationsByStatus.pending"
                                :key="application.id"
                                class="px-6 py-5 hover:bg-gray-50 transition-colors"
                            >
                                <ApplicationCard :application="application" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Отклоненные заявки -->
            <div v-if="applicationsByStatus.rejected && applicationsByStatus.rejected.length > 0">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <button
                        @click="toggleSection('rejected')"
                        class="w-full px-6 py-4 bg-gradient-to-r from-red-50 to-rose-50 border-b border-red-200 hover:from-red-100 hover:to-rose-100 transition-colors flex items-center justify-between"
                    >
                        <h2 class="text-xl font-bold text-red-900 flex items-center gap-2">
                            <i class="pi pi-times-circle text-xl"></i>
                            Отклоненные заявки ({{ applicationsByStatus.rejected.length }})
                        </h2>
                        <ChevronDownIcon
                            :class="[
                                'h-5 w-5 text-red-700 transition-transform duration-200',
                                collapsedSections.rejected ? 'transform rotate-180' : ''
                            ]"
                        />
                    </button>
                    <Transition name="slide">
                        <div
                            v-if="!collapsedSections.rejected"
                            class="divide-y divide-gray-100"
                        >
                            <div
                                v-for="application in applicationsByStatus.rejected"
                                :key="application.id"
                                class="px-6 py-5 hover:bg-gray-50 transition-colors"
                            >
                                <ApplicationCard :application="application" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Сообщение если нет заявок -->
            <div v-if="statistics.total_applications === 0" class="bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                        <DocumentTextIcon class="h-10 w-10 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Заявок пока нет</h3>
                    <p class="text-sm text-gray-500">
                        На этот кейс еще не было подано заявок от студентов.
                    </p>
                </div>
            </div>
        </div>

        <!-- Секция опасных действий -->
        <div class="bg-white rounded-xl shadow-md border border-red-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-red-200">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-red-600"></i>
                    Опасные действия
                </h2>
            </div>
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Удаление кейса</h3>
                        <p class="text-sm text-gray-600">
                            Это действие нельзя отменить. Все данные кейса, включая заявки и связанную информацию, будут удалены безвозвратно.
                        </p>
                    </div>
                    <button
                        @click="confirmDelete"
                        class="ml-6 inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none transition-colors font-medium shadow-sm"
                    >
                        <i class="pi pi-trash"></i>
                        Удалить кейс
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно подтверждения удаления -->
        <DangerConfirmDialog
            :visible="showDeleteModal"
            @update:visible="showDeleteModal = $event"
            @confirm="deleteCase"
            type="delete"
            title="Подтверждение удаления"
            message="Вы уверены, что хотите удалить кейс"
            :item-name="caseData.title"
            confirm-text="Удалить"
            cancel-text="Отмена"
            :loading="processing"
            loading-text="Удаление..."
            :disabled="blockingData?.has_blocking_data"
        >
            <div v-if="blockingData?.has_blocking_data" class="mb-4">
                <p class="text-sm text-red-600 font-semibold mb-2">
                    Внимание! Невозможно удалить кейс:
                </p>
                <div class="text-xs text-red-700 bg-red-50 rounded-lg p-3">
                    • На кейс подано {{ blockingData.active_applications_count }} 
                    {{ pluralize(blockingData.active_applications_count, 'активная заявка', 'активные заявки', 'активных заявок') }} от студентов
                </div>
            </div>
            <p v-else class="text-xs text-red-600 bg-red-50 rounded-lg p-3">
                Это действие нельзя отменить. Все данные кейса будут удалены безвозвратно.
            </p>
        </DangerConfirmDialog>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link, Head } from '@inertiajs/vue3'
import {
    ChevronRightIcon,
    ChevronDownIcon,
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline'
import ApplicationCard from './Partials/ApplicationCard.vue'
import DangerConfirmDialog from '@/Components/UI/DangerConfirmDialog.vue'
import { route } from "ziggy-js";

const props = defineProps({
    caseData: {
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
    },
    blockingData: {
        type: Object,
        default: () => ({
            has_blocking_data: false,
            active_applications_count: 0,
        })
    }
})

// Computed свойства для безопасного доступа
const caseData = computed(() => props.caseData || {})
const statistics = computed(() => props.statistics || {})
const applicationsByStatus = computed(() => props.applicationsByStatus || {})

// Удаление кейса
const showDeleteModal = ref(false)
const processing = ref(false)

// Состояние сворачивания секций заявок (по умолчанию все развернуты)
const collapsedSections = ref({
    approved: false,
    pending: false,
    rejected: false
})

// Переключение состояния секции
const toggleSection = (section) => {
    collapsedSections.value[section] = !collapsedSections.value[section]
}

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
        draft: 'bg-gray-100 text-gray-800 border border-gray-200',
        active: 'bg-green-100 text-green-800 border border-green-200',
        completed: 'bg-blue-100 text-blue-800 border border-blue-200',
        archived: 'bg-red-100 text-red-800 border border-red-200',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800 border border-gray-200'
}

const pluralize = (count, one, two, five) => {
    const mod10 = count % 10
    const mod100 = count % 100

    if (mod100 >= 11 && mod100 <= 19) {
        return five
    }
    if (mod10 === 1) {
        return one
    }
    if (mod10 >= 2 && mod10 <= 4) {
        return two
    }
    return five
}
</script>

<style scoped>
/* Анимация слайда вверх/вниз */
.slide-enter-active {
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.slide-enter-from {
    max-height: 0;
    opacity: 0;
    transform: translateY(-20px);
}

.slide-enter-to {
    max-height: 10000px;
    opacity: 1;
    transform: translateY(0);
}

.slide-leave-from {
    max-height: 10000px;
    opacity: 1;
    transform: translateY(0);
}

.slide-leave-to {
    max-height: 0;
    opacity: 0;
    transform: translateY(-20px);
}
</style>
