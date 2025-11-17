<template>
    <div class="space-y-6">
        <Head title="Управление кейсами"/>

        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Управление кейсами</h1>
                        <p class="text-indigo-100">Список всех кейсов в системе</p>
                    </div>
                    <Link
                        :href="route('admin.cases.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium"
                    >
                        <i class="pi pi-plus"></i>
                        Создать кейс
                    </Link>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-filter text-indigo-600"></i>
                    Фильтры поиска
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Поиск -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                        <div class="relative">
                            <i class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Название или описание"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                                @input="handleSearch"
                            />
                        </div>
                    </div>

                    <!-- Фильтр по статусу -->
                    <div>
                        <Select
                            v-model="filters.status"
                            label="Статус"
                            :options="statusFilterOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Все статусы"
                            @update:modelValue="updateFilters"
                        />
                    </div>

                    <!-- Фильтр по партнеру -->
                    <div>
                        <Select
                            v-model="filters.partner_id"
                            label="Партнер"
                            :options="partnerFilterOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Все партнеры"
                            @update:modelValue="updateFilters"
                        />
                    </div>

                    <!-- Количество на странице -->
                    <div>
                        <Select
                            v-model="filters.perPage"
                            label="На странице"
                            :options="perPageOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Выберите количество"
                            @update:modelValue="updateFilters"
                        />
                    </div>
                </div>

                <!-- Кнопка сброса фильтров -->
                <div class="mt-4 flex justify-end">
                    <button
                        @click="resetFilters"
                        :disabled="!hasActiveFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="pi pi-refresh"></i>
                        Сбросить фильтры
                    </button>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 shadow-md border border-blue-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Всего кейсов</p>
                        <p class="text-2xl font-bold text-blue-900">{{ casesTotal }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl">
                        <i class="pi pi-briefcase text-white text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 shadow-md border border-green-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Активных</p>
                        <p class="text-2xl font-bold text-green-900">
                            {{ casesData.filter(c => c.status === 'active').length }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl">
                        <i class="pi pi-check-circle text-white text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-5 shadow-md border border-amber-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 mb-1">Черновиков</p>
                        <p class="text-2xl font-bold text-amber-900">
                            {{ casesData.filter(c => c.status === 'draft').length }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl">
                        <i class="pi pi-file text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Таблица кейсов -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-list text-indigo-600"></i>
                        Список кейсов
                    </h2>
                    <span class="text-sm text-gray-600 bg-white px-3 py-1 rounded-lg border border-gray-200">
                        Всего: {{ casesTotal }}
                    </span>
                </div>
            </div>

            <div v-if="casesData && casesData.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Кейс
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Партнер
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Дедлайн
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Команда
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Статус
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Заявки
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Создан
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    <tr
                        v-for="caseItem in casesData"
                        :key="caseItem.id"
                        class="hover:bg-indigo-50/50 cursor-pointer transition-all group"
                        @click="goToCase(caseItem.id)"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors">
                                    <i class="pi pi-briefcase text-indigo-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        {{ caseItem.title }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        {{ caseItem.description }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-gray-100 rounded-lg">
                                    <i class="pi pi-building text-gray-600 text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ caseItem.partner?.company_name || 'Не указан' }}</div>
                                    <div class="text-xs text-gray-500">{{
                                            caseItem.partner?.user?.name || 'Без контакта'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-red-100 rounded-lg">
                                    <i class="pi pi-calendar text-red-600 text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ formatDate(caseItem.deadline) }}</div>
                                    <div
                                        :class="[
                                            'text-xs font-medium mt-0.5',
                                            isDeadlineSoon(caseItem.deadline) ? 'text-red-600' : 'text-gray-500'
                                        ]"
                                    >
                                        {{ daysUntilDeadline(caseItem.deadline) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 border border-blue-200">
                                <i class="pi pi-users text-xs"></i>
                                {{ caseItem.required_team_size }} чел.
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border',
                                    getStatusBadgeClass(caseItem.status)
                                ]"
                            >
                                {{ getStatusLabel(caseItem.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-purple-100 rounded-lg">
                                    <i class="pi pi-file-edit text-purple-600 text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ caseItem.applications_count || 0 }} заявок
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ formatDate(caseItem.created_at) }}</div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div v-if="casesLinks && casesLinks.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <Pagination :links="casesLinks" />
            </div>
        </div>

        <!-- Сообщение если нет кейсов -->
        <div v-if="!casesData || casesData.length === 0" class="bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                    <i class="pi pi-inbox text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Кейсы не найдены</h3>
                <p class="text-sm text-gray-500 mb-6">
                    <span v-if="hasActiveFilters">Попробуйте изменить параметры фильтрации</span>
                    <span v-else>Создайте первый кейс, чтобы начать работу</span>
                </p>
                <div class="flex gap-3 justify-center">
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Сбросить фильтры
                    </button>
                    <Link
                        v-else
                        :href="route('admin.cases.create')"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                    >
                        Создать кейс
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, computed} from 'vue'
import {router, Link, Head} from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import Select from '@/Components/UI/Select.vue'
import Button from 'primevue/button'
import {route} from "ziggy-js";

const props = defineProps({
    cases: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    partners: {
        type: Array,
        default: () => []
    },
})

// Computed свойства для безопасного доступа
const casesData = computed(() => props.cases?.data || [])
const casesTotal = computed(() => props.cases?.total || 0)
const casesLinks = computed(() => props.cases?.links || [])

// Проверка активных фильтров
const hasActiveFilters = computed(() => {
    return filters.value.search !== '' ||
        filters.value.status !== '' ||
        filters.value.partner_id !== '' ||
        filters.value.perPage !== '25'
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    partner_id: props.filters?.partner_id || '',
    perPage: props.filters?.perPage ? String(props.filters.perPage) : '25',
})

// Функция перехода к конкретному кейсу
const goToCase = (caseId) => {
    router.get(route('admin.cases.show', caseId))
}

// Функция для отображения имени партнера
const getPartnerDisplayName = (partner) => {
    if (!partner) return 'Неизвестный партнер'

    const companyName = partner.company_name || 'Без названия'
    const contactPerson = partner.contact_person || 'Без контакта'

    return `${companyName} (${contactPerson})`
}

// Таймер для дебаунса
let searchTimeout = null

// Обработчик поиска с дебаунсом
const handleSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        updateFilters()
    }, 500)
}

const updateFilters = () => {
    router.get(route('admin.cases.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Черновик', value: 'draft' },
    { label: 'Активные', value: 'active' },
    { label: 'Завершенные', value: 'completed' },
    { label: 'Архивные', value: 'archived' },
])

const partnerFilterOptions = computed(() => [
    { label: 'Все партнеры', value: '' },
    ...props.partners.map(partner => ({
        label: getPartnerDisplayName(partner),
        value: partner.id
    }))
])

const perPageOptions = computed(() => [
    { label: 'Отображать по 10', value: '10' },
    { label: 'Отображать по 15', value: '15' },
    { label: 'Отображать по 25', value: '25' },
    { label: 'Отображать по 50', value: '50' },
])

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        partner_id: '',
        perPage: '25',
    }
    updateFilters()
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
        draft: 'bg-gray-100 text-gray-800 border-gray-200',
        active: 'bg-green-100 text-green-800 border-green-200',
        completed: 'bg-blue-100 text-blue-800 border-blue-200',
        archived: 'bg-red-100 text-red-800 border-red-200',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800 border-gray-200'
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
