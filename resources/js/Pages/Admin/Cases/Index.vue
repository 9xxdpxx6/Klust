<template>
    <div>
        <Head title="Управление кейсами"/>


        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Управление кейсами</h1>
                <p class="text-gray-600 mt-2">Список всех кейсов в системе</p>
            </div>
            <Button
                @click="router.visit(route('admin.cases.create'))"
                label="Создать кейс"
                icon="pi pi-plus"
                unstyled
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors inline-flex items-center justify-center gap-2"
            />

        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Поиск -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Название или описание"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                        @input="handleSearch"
                    />
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

                <!-- Фильтр по размеру команды -->
                <div>
                    <Select
                        v-model="filters.team_size"
                        label="Размер команды"
                        :options="teamSizeFilterOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Любой"
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
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                >
                    Сбросить фильтры
                </button>
            </div>
        </div>

        <!-- Таблица кейсов -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="text-sm text-gray-600">
                    Всего кейсов: {{ casesTotal }}
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Кейс
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Партнер
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Дедлайн
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Команда
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Статус
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Заявки
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Создан
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr
                    v-for="caseItem in casesData"
                    :key="caseItem.id"
                    class="hover:bg-gray-50 cursor-pointer transition-colors"
                    @click="goToCase(caseItem.id)"
                >
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                                {{ caseItem.title }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1 line-clamp-2">
                                {{ caseItem.description }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ caseItem.partner?.company_name || 'Не указан' }}</div>
                        <div class="text-sm text-gray-500">{{
                                caseItem.partner?.user?.name || 'Без контакта'
                            }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ formatDate(caseItem.deadline) }}</div>
                        <div
                            :class="[
                                'text-xs font-medium mt-1',
                                isDeadlineSoon(caseItem.deadline) ? 'text-red-600' : 'text-gray-500'
                            ]"
                        >
                            {{ daysUntilDeadline(caseItem.deadline) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ caseItem.required_team_size }} чел.
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                getStatusBadgeClass(caseItem.status)
                            ]"
                        >
                            {{ getStatusLabel(caseItem.status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">
                            {{ caseItem.applications_count }} заявок
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatDate(caseItem.created_at) }}
                    </td>

                </tr>

                </tbody>
            </table>

            <!-- Пагинация -->
            <Pagination v-if="casesLinks && casesLinks.length > 0" :links="casesLinks" class="mt-4"/>
        </div>

        <!-- Сообщение если нет кейсов -->
        <div v-if="!casesData || casesData.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">Кейсы не найдены</p>
            <button
                v-if="hasActiveFilters"
                @click="resetFilters"
                class="mt-4 px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-md transition-colors"
            >
                Сбросить фильтры
            </button>
        </div>
    </div>
</template>

<script setup>
import {ref, computed} from 'vue'
import {router} from '@inertiajs/vue3'
import {Head} from '@inertiajs/vue3'
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

// Включить отладку
const debug = ref(import.meta.env.DEV)

// Computed свойства для безопасного доступа
const casesData = computed(() => props.cases?.data || [])
const casesTotal = computed(() => props.cases?.total || 0)
const casesLinks = computed(() => props.cases?.links || [])

// Проверка активных фильтров
const hasActiveFilters = computed(() => {
    return filters.value.search !== '' ||
        filters.value.status !== '' ||
        filters.value.partner_id !== '' ||
        filters.value.team_size !== '' ||
        filters.value.perPage !== '25'
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    partner_id: props.filters?.partner_id || '',
    team_size: props.filters?.team_size || '',
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

const teamSizeFilterOptions = computed(() => [
    { label: 'Любой', value: '' },
    { label: '1 человек', value: '1' },
    { label: '2 человека', value: '2' },
    { label: '3 человека', value: '3' },
    { label: '4 человека', value: '4' },
    { label: '5+ человек', value: '5' },
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
        team_size: '',
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
        draft: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        archived: 'bg-red-100 text-red-800',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800'
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
