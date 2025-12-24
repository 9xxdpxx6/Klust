<template>
    <MobileContainer :padding="false">
        <div :class="[
            isMobile ? 'space-y-4' : 'space-y-6'
        ]">
            <Head title="Команды" />
            
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <h1 :class="[
                        'font-bold text-white',
                        isMobile ? 'text-2xl' : 'text-3xl'
                    ]">Команды</h1>
                    <p class="text-indigo-100 mt-2" :class="isMobile ? 'text-sm' : ''">
                        Управление командами студентов
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
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
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3 }">
                        <!-- Поиск -->
                        <div class="select-wrapper w-full">
                            <label
                                for="search-input"
                                class="block text-sm font-medium mb-1"
                            >
                                Поиск
                            </label>
                            <div class="relative">
                                <input
                                    id="search-input"
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="По имени участника..."
                                    class="w-full min-h-[2.5rem] h-[2.6rem] pl-10 pr-3 py-2 border border-border rounded-md shadow-sm text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 box-border leading-[1.5]"
                                    style="padding-left: 2.75rem;"
                                    @input="debouncedSearch"
                                />
                                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                        
                        <div class="select-wrapper w-full">
                            <Select
                                v-model="filters.case_id"
                                label="Кейс"
                                :options="caseFilterOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Все кейсы"
                                @update:modelValue="applyFilters"
                            />
                        </div>

                        <div class="select-wrapper w-full">
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

            <!-- Teams Grid -->
            <div v-if="teams.data.length === 0" :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center',
                isMobile ? 'p-8' : ''
            ]">
                <div class="max-w-md mx-auto">
                    <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                        <i class="pi pi-users text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Команд нет</h3>
                    <p class="text-sm text-gray-500">
                        Пока нет одобренных команд.
                    </p>
                </div>
            </div>

            <ResponsiveGrid v-else :cols="{ mobile: 1, tablet: 2, desktop: 3 }" :class="isMobile ? 'gap-4' : 'gap-6'">
                <div
                    v-for="team in teams.data"
                    :key="team.id"
                    class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-200 flex flex-col"
                >
                    <div :class="[
                        'p-6 flex-1 flex flex-col',
                        isMobile ? 'p-4' : ''
                    ]">
                        <!-- Team Header -->
                        <div :class="[
                            'flex items-start mb-4',
                            isMobile ? 'flex-col gap-3' : 'justify-between'
                        ]">
                            <div class="flex-1">
                                <h3 :class="[
                                    'font-semibold text-gray-900 mb-2',
                                    isMobile ? 'text-base' : 'text-lg'
                                ]">
                                    Команда #{{ team.id }}
                                </h3>
                                <p :class="[
                                    'font-medium text-gray-700 mb-3',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">
                                    {{ team.case.title }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full font-medium',
                                    isMobile ? 'text-xs w-fit' : 'text-xs',
                                    getStatusClass(team.status?.name)
                                ]"
                            >
                                {{ getStatusLabel(team.status?.name) }}
                            </span>
                        </div>

                    <!-- Team Members -->
                    <div class="mb-4">
                        <p class="text-xs font-medium text-gray-500 mb-2">Состав команды:</p>
                        <div class="space-y-1">
                            <!-- Leader -->
                            <div class="text-sm font-semibold text-gray-900">
                                • {{ team.leader.name }}
                            </div>
                            <!-- Other members -->
                            <div
                                v-for="member in team.team_members || []"
                                :key="member.id"
                                class="text-sm text-gray-700"
                            >
                                • {{ member.user?.name }}
                            </div>
                        </div>
                    </div>

                    <!-- Team Info -->
                    <div class="space-y-2 mb-4 pt-3 border-t border-gray-200 flex-1">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                            Участников: {{ (team.team_members?.length || 0) + 1 }} / {{ team.case.required_team_size || team.case.team_size }}
                        </div>

                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            Создана: {{ formatDate(team.created_at) }}
                        </div>

                        <div v-if="team.case.deadline" class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            Дедлайн: {{ formatDate(team.case.deadline) }}
                        </div>
                    </div>

                        <!-- Action Button -->
                        <Link
                            :href="route('partner.teams.show', { application: team.id })"
                            :class="[
                                'block w-full text-center border border-transparent rounded-lg shadow-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition-colors',
                                isMobile ? 'px-3 py-2 text-xs' : 'px-4 py-2 text-sm'
                            ]"
                        >
                            Подробнее
                        </Link>
                    </div>
                </div>
            </ResponsiveGrid>

            <!-- Pagination -->
            <Pagination v-if="teams.links && teams.links.length > 2" :links="teams.links" :class="['mt-6', isMobile ? '' : '']" />
        </div>
    </MobileContainer>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Select from '@/Components/UI/Select.vue';
import Pagination from '@/Components/Pagination.vue';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue';
import { useResponsive } from '@/Composables/useResponsive';
import { route } from 'ziggy-js';

const props = defineProps({
    teams: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    partnerCases: {
        type: Array,
        default: () => []
    }
});

const { isMobile, padding } = useResponsive();

const filters = ref({
    search: props.filters?.search || '',
    case_id: props.filters?.case_id || '',
    status: props.filters?.status || ''
});

// Debounce для поиска
let searchTimeout = null;
const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const getStatusClass = (status) => {
    if (!status) return 'bg-gray-100 text-gray-800';
    
    switch (status) {
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    if (!status) return 'Неизвестно';
    
    switch (status) {
        case 'accepted':
            return 'Активна';
        case 'completed':
            return 'Завершена';
        case 'rejected':
            return 'Отклонена';
        case 'pending':
            return 'Ожидает';
        default:
            return status;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Не указан';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
};

const applyFilters = () => {
    const params = {};
    
    if (filters.value.search) {
        params.search = filters.value.search;
    }
    
    if (filters.value.case_id) {
        params.case_id = filters.value.case_id;
    }
    
    if (filters.value.status) {
        params.status = filters.value.status;
    }

    // Сохраняем фокус на инпуте, если он есть
    const searchInput = document.getElementById('search-input');
    const hadFocus = document.activeElement === searchInput;

    router.get(route('partner.teams.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            // Восстанавливаем фокус после обновления
            if (hadFocus && searchInput) {
                searchInput.focus();
            }
        }
    });
};

const caseFilterOptions = computed(() => [
    { label: 'Все кейсы', value: '' },
    ...props.partnerCases.map(caseItem => ({
        label: caseItem.title,
        value: caseItem.id
    }))
])

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Активные', value: 'accepted' },
    { label: 'Завершенные', value: 'completed' },
])

// Проверка наличия активных фильтров
const hasActiveFilters = computed(() => {
    return !!(filters.value.search || 
              filters.value.case_id || 
              filters.value.status);
});

// Сброс фильтров
const resetFilters = () => {
    filters.value = {
        search: '',
        case_id: '',
        status: ''
    };
    applyFilters();
};
</script>
