<template>
    <MobileContainer :padding="false">
        <div :class="isMobile ? 'space-y-4' : 'space-y-6'">
        <Head title="Кейсы" />
            
            <!-- Заголовок с градиентом -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <div :class="[
                        'flex items-center justify-between',
                        isMobile ? 'flex-col gap-4' : ''
                    ]">
                        <div>
                            <h1 :class="[
                                'font-bold text-white mb-2',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">Кейсы</h1>
                            <p class="text-indigo-100" :class="isMobile ? 'text-sm' : ''">
                                Управление вашими кейсами
                            </p>
                        </div>
                        <Link
                            :href="route('partner.cases.create')"
                            :class="[
                                'inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium',
                                isMobile ? 'px-4 py-2 text-sm w-full justify-center' : 'px-6 py-3'
                            ]"
                        >
                            <i class="pi pi-plus"></i>
                            Создать кейс
                        </Link>
                    </div>
                </div>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200 overflow-x-auto overflow-y-hidden">
                <nav class="-mb-px flex space-x-8 whitespace-nowrap min-w-max">
                    <Link 
                        v-for="tab in tabs" 
                        :key="tab.key" 
                        :href="getTabUrl(tab.key)"
                        :class="[
                            currentTab === tab.key
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                        :preserve-state="false"
                    >
                        {{ tab.label }}
                        <span 
                            v-if="tab.count !== undefined && tab.count > 0" 
                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="currentTab === tab.key ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                        >
                            {{ tab.count }}
                        </span>
                    </Link>
                </nav>
            </div>
        </div>

            <!-- Фильтры -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-filter text-indigo-600"></i>
                        Фильтры поиска
                    </h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 4 }">
                        <!-- Поиск -->
                <div>
                    <SearchInput
                        v-model="filters.search"
                                label="Поиск"
                                placeholder="Название или описание"
                        @input="debounceSearch"
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
                        @update:modelValue="applyFilters"
                    />
                </div>

                        <!-- Дата создания с -->
                <div>
                    <DatePicker
                        v-model="filters.date_from"
                        label="Дата создания с"
                        placeholder="Дата начала"
                        @date-select="applyFilters"
                    />
                </div>

                        <!-- Дата создания по -->
                <div>
                    <DatePicker
                        v-model="filters.date_to"
                        label="Дата создания по"
                        placeholder="Дата окончания"
                        @date-select="applyFilters"
                    />
                </div>
                    </ResponsiveGrid>
            
            <!-- Кнопка сброса фильтров -->
                    <div :class="[
                        'mt-4 flex',
                        isMobile ? 'justify-center' : 'justify-end'
                    ]">
                <button
                    @click="resetFilters"
                    :disabled="!hasActiveFilters"
                            :class="[
                                'inline-flex items-center gap-2 font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
                                isMobile ? 'px-3 py-2 text-xs w-full justify-center' : 'px-4 py-2 text-sm'
                            ]"
                >
                    <i class="pi pi-refresh"></i>
                    Сбросить фильтры
                </button>
            </div>
            </div>
        </div>

            <!-- Таблица кейсов -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <div :class="[
                        'flex items-center',
                        isMobile ? 'flex-col items-start gap-2' : 'flex-row justify-between gap-4'
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-lg'
                        ]">
                            <i class="pi pi-list text-indigo-600"></i>
                            Список кейсов
                        </h2>
                        <span :class="[
                            'text-gray-600 bg-white rounded-lg border border-gray-200 whitespace-nowrap',
                            isMobile ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1'
                        ]">
                            Всего: {{ casesTotal }}
                        </span>
            </div>
        </div>

                <div v-if="casesData && casesData.length > 0">
                    <!-- Заголовки для планшетной/лаптопной версии (md до xl) -->
                    <div class="hidden md:grid xl:hidden md:grid-cols-5 gap-4 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Кейс
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Дедлайн
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Статус
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Заявки / Команды
                </div>
            </div>

                    <!-- Заголовки для десктопной версии (xl+) -->
                    <div class="hidden xl:grid xl:grid-cols-7 gap-4 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Кейс
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Дедлайн
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Статус
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Заявки
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Команды
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Создан
                        </div>
                    </div>

                    <!-- Список кейсов -->
                    <div class="divide-y divide-gray-200">
                        <!-- Карточка кейса (мобильная) / Строка кейса (десктоп) -->
                        <div
                            v-for="caseItem in casesData"
                            :key="caseItem.id"
                            class="hover:bg-indigo-50/50 cursor-pointer transition-all group"
                            @click="goToCase(caseItem.id)"
                        >
                            <!-- Мобильная версия (карточка) - только для маленьких экранов -->
                            <div class="md:hidden p-5 sm:p-6">
                                <div class="flex items-start gap-4 mb-5">
                                    <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors flex-shrink-0">
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

                                <div class="space-y-3.5">
                                    <!-- Дедлайн -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Дедлайн:</span>
                                        <div class="flex items-center gap-2 flex-1">
                                            <div class="w-8 h-8 flex items-center justify-center bg-red-100 rounded-lg flex-shrink-0">
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
                                    </div>

                                    <!-- Статус -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Статус:</span>
                                        <span
                                            :class="[
                                                'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border',
                                                getStatusBadgeClass(caseItem.status)
                                            ]"
                                        >
                                            {{ getStatusLabel(caseItem.status) }}
                                        </span>
                                    </div>

                                    <!-- Заявки -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Заявки:</span>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 flex items-center justify-center bg-purple-100 rounded-lg flex-shrink-0">
                                                <i class="pi pi-file-edit text-purple-600 text-xs"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ caseItem.applications_count || 0 }} заявок
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Команды -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Команды:</span>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 flex items-center justify-center bg-blue-100 rounded-lg flex-shrink-0">
                                                <i class="pi pi-users text-blue-600 text-xs"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ caseItem.teams_count || 0 }} команд
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Дата создания -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Создан:</span>
                                        <div class="text-sm text-gray-500">{{ formatDate(caseItem.created_at) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Планшетная/лаптопная версия (md до xl) - упрощенная таблица -->
                            <div class="hidden md:grid xl:hidden md:grid-cols-5 gap-4 px-4 py-4 items-center">
                                <!-- Кейс -->
                                <div class="col-span-2">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors flex-shrink-0">
                                            <i class="pi pi-briefcase text-indigo-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                                {{ caseItem.title }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1 line-clamp-1">
                                                {{ caseItem.description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Дедлайн -->
                                <div class="col-span-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 flex items-center justify-center bg-red-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-calendar text-red-600 text-xs"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-xs font-medium text-gray-900 truncate">{{ formatDate(caseItem.deadline) }}</div>
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
                                </div>

                                <!-- Статус -->
                                <div class="col-span-1">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 inline-flex text-xs font-semibold rounded-lg border whitespace-nowrap',
                                            getStatusBadgeClass(caseItem.status)
                                        ]"
                                    >
                                        {{ getStatusLabel(caseItem.status) }}
                                    </span>
                                </div>

                                <!-- Заявки / Команды -->
                                <div class="col-span-1">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <i class="pi pi-file-edit text-purple-600 text-xs"></i>
                                            <span class="text-xs font-medium text-gray-900">{{ caseItem.applications_count || 0 }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i class="pi pi-users text-blue-600 text-xs"></i>
                                            <span class="text-xs font-medium text-gray-900">{{ caseItem.teams_count || 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Десктопная версия (xl+) - полная таблица -->
                            <div class="hidden xl:grid xl:grid-cols-7 gap-4 px-6 py-5 items-center">
                                <!-- Кейс -->
                                <div class="col-span-2">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors flex-shrink-0">
                                            <i class="pi pi-briefcase text-indigo-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                                {{ caseItem.title }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                {{ caseItem.description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Дедлайн -->
                                <div class="col-span-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 flex items-center justify-center bg-red-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-calendar text-red-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 whitespace-nowrap">{{ formatDate(caseItem.deadline) }}</div>
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
                                </div>

                                <!-- Статус -->
                                <div class="col-span-1">
                                <span
                                        :class="[
                                            'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border whitespace-nowrap',
                                            getStatusBadgeClass(caseItem.status)
                                        ]"
                                >
                                    {{ getStatusLabel(caseItem.status) }}
                                </span>
                                </div>

                                <!-- Заявки -->
                                <div class="col-span-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 flex items-center justify-center bg-purple-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-file-edit text-purple-600 text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ caseItem.applications_count || 0 }} заявок
                                        </span>
                                    </div>
                                </div>

                                <!-- Команды -->
                                <div class="col-span-1">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-users text-blue-600 text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ caseItem.teams_count || 0 }} команд
                                        </span>
                                    </div>
                                </div>

                                <!-- Дата создания -->
                                <div class="col-span-1">
                                    <div class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(caseItem.created_at) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пагинация -->
                <div v-if="casesLinks && casesLinks.length > 0" :class="['px-6 py-4 border-t border-gray-200 bg-gray-50', isMobile ? 'px-4' : '']">
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
                        <span v-else>
                            {{ currentTab === 'all' ? 'Пока нет кейсов.' : `Нет кейсов со статусом "${currentTab}".` }}
                        </span>
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
                            :href="route('partner.cases.create')"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                        >
                            Создать кейс
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </MobileContainer>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import DatePicker from '@/Components/UI/DatePicker.vue';
import Select from '@/Components/UI/Select.vue';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue';
import { useResponsive } from '@/Composables/useResponsive';
import { route } from 'ziggy-js';
import { parseLocalDate, formatDateForServer } from '@/Composables/useDateHelper';

const props = defineProps({
    cases: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            status: null,
            search: null
        })
    },
    statistics: {
        type: Object,
        default: () => ({})
    }
});

// Computed свойства для безопасного доступа
const { isMobile, padding } = useResponsive();

const casesData = computed(() => props.cases?.data || []);
const casesTotal = computed(() => props.cases?.total || 0);
const casesLinks = computed(() => props.cases?.links || []);

// Initialize filters with props
const filters = ref({
    status: props.filters.status || '',
    search: props.filters.search || '',
    date_from: parseLocalDate(props.filters.date_from),
    date_to: parseLocalDate(props.filters.date_to)
});

const currentTab = computed(() => {
    // Если status не указан или пустой, значит это "все"
    const status = props.filters?.status;
    return (status && status !== '') ? status : 'all';
});

const tabs = computed(() => {
    const statistics = props.statistics || {};
    const allTabs = [
        { key: 'all', label: 'Все кейсы' },
        { key: 'draft', label: 'Черновики', count: statistics.draft_count },
        { key: 'active', label: 'Активные', count: statistics.active_count },
        { key: 'completed', label: 'Завершенные', count: statistics.completed_count },
        { key: 'archived', label: 'Архив', count: statistics.archived_count }
    ];

    return allTabs.map(tab => ({
        ...tab,
        count: tab.count !== undefined && tab.count > 0 ? tab.count : undefined
    }));
});

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Черновик', value: 'draft' },
    { label: 'Активен', value: 'active' },
    { label: 'Завершен', value: 'completed' },
    { label: 'Архив', value: 'archived' },
]);

const getStatusBadgeClass = (status) => {
    const classMap = {
        draft: 'bg-gray-100 text-gray-800 border-gray-200',
        active: 'bg-green-100 text-green-800 border-green-200',
        completed: 'bg-blue-100 text-blue-800 border-blue-200',
        archived: 'bg-purple-100 text-purple-800 border-purple-200',
    };
    return classMap[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Черновик';
        case 'active':
            return 'Активен';
        case 'completed':
            return 'Завершен';
        case 'archived':
            return 'Архив';
        default:
            return status;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Не указан';
    return new Date(dateString).toLocaleDateString('ru-RU');
};

const daysUntilDeadline = (deadline) => {
    try {
        const today = new Date();
        const deadlineDate = new Date(deadline);
        const diffTime = deadlineDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays < 0) return 'Просрочен';
        if (diffDays === 0) return 'Сегодня';
        if (diffDays === 1) return 'Завтра';
        return `Через ${diffDays} дн.`;
    } catch (error) {
        return 'Ошибка даты';
    }
};

const isDeadlineSoon = (deadline) => {
    try {
        const today = new Date();
        const deadlineDate = new Date(deadline);
        const diffTime = deadlineDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays <= 3 && diffDays >= 0;
    } catch (error) {
        return false;
    }
};

// Получить URL для таба с сохранением partner_id
const getTabUrl = (tabKey) => {
    const urlParams = new URLSearchParams(window.location.search);
    const partnerId = urlParams.get('partner_id');
    
    const params = tabKey === 'all' ? {} : { status: tabKey };
    
    if (partnerId) {
        params.partner_id = partnerId;
    }
    
    return route('partner.cases.index', params);
};

const debounceSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

const applyFilters = () => {
    // Получаем partner_id из текущего URL, если он есть
    const urlParams = new URLSearchParams(window.location.search);
    const partnerId = urlParams.get('partner_id');
    
    const params = {
        status: filters.value.status || undefined,
        search: filters.value.search || undefined,
        date_from: formatDateForServer(filters.value.date_from) || undefined,
        date_to: formatDateForServer(filters.value.date_to) || undefined
    };
    
    // Добавляем partner_id, если он был в URL
    if (partnerId) {
        params.partner_id = partnerId;
    }

    router.get(route('partner.cases.index'), params, {
        preserveState: true,
        replace: true
    });
};

const goToCase = (caseId) => {
    router.visit(route('partner.cases.show', { case: caseId }));
};

// Проверка наличия активных фильтров
const hasActiveFilters = computed(() => {
    return !!(filters.value.search || 
              filters.value.status || 
              filters.value.date_from || 
              filters.value.date_to);
});

// Сброс фильтров
const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        date_from: null,
        date_to: null
    };
    applyFilters();
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>