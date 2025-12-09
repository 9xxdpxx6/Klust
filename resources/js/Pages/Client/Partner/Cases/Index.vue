<template>
    <div class="space-y-6">
        <Head title="Кейсы" />
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Кейсы</h1>
            <Button
                @click="router.visit(route('partner.cases.create'))"
                label="Создать кейс"
                icon="pi pi-plus"
                unstyled
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out inline-flex items-center justify-center gap-2"
            />
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <Link 
                        v-for="tab in tabs" 
                        :key="tab.key" 
                        :href="route('partner.cases.index', { status: tab.key })"
                        :class="[
                            currentTab === tab.key
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ tab.label }}
                        <span 
                            v-if="tab.count !== undefined" 
                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="currentTab === tab.key ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                        >
                            {{ tab.count }}
                        </span>
                    </Link>
                </nav>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <SearchInput
                        v-model="filters.search"
                        label="Поиск по названию"
                        placeholder="Поиск кейсов..."
                        :debounce="0"
                        @input="debounceSearch"
                    />
                </div>

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

                <div>
                    <DatePicker
                        v-model="filters.date_from"
                        label="Дата создания с"
                        placeholder="Дата начала"
                        @date-select="applyFilters"
                    />
                </div>

                <div>
                    <DatePicker
                        v-model="filters.date_to"
                        label="Дата создания по"
                        placeholder="Дата окончания"
                        @date-select="applyFilters"
                    />
                </div>
            </div>
        </div>

        <!-- Cases List -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div v-if="cases.data.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Кейсы не найдены</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{
                        currentTab === 'all' 
                            ? 'Пока нет кейсов.' 
                            : `Нет кейсов со статусом "${currentTab}".`
                    }}
                </p>
                <div class="mt-6">
                    <Button
                        @click="router.visit(route('partner.cases.create'))"
                        label="Создать кейс"
                        icon="pi pi-plus"
                        unstyled
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    />
                </div>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Название
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Статус
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Заявки
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Команды
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Дедлайн
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Создан
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="caseItem in cases.data"
                            :key="caseItem.id"
                            @click="goToCase(caseItem.id)"
                            class="hover:bg-blue-50 cursor-pointer transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ caseItem.title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getStatusClass(caseItem.status)"
                                >
                                    {{ getStatusLabel(caseItem.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ caseItem.applications_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ caseItem.teams_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(caseItem.deadline) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(caseItem.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination v-if="cases.links.length > 2" :links="cases.links" class="mt-6" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import DatePicker from '@/Components/UI/DatePicker.vue';
import Select from '@/Components/UI/Select.vue';
import Button from 'primevue/button';
import { route } from 'ziggy-js';

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
    }
});

const page = usePage();

// Функция для правильного парсинга даты без смещения часового пояса
const parseDateString = (dateString) => {
    if (!dateString) return null;
    
    // Если это уже Date объект, возвращаем как есть
    if (dateString instanceof Date) {
        return dateString;
    }
    
    // Парсим дату в формате YYYY-MM-DD как локальную дату
    // Это предотвращает смещение из-за часового пояса
    const parts = dateString.split('-');
    if (parts.length === 3) {
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1; // Месяцы в JS начинаются с 0
        const day = parseInt(parts[2], 10);
        return new Date(year, month, day);
    }
    
    // Если формат не распознан, пробуем стандартный парсинг
    return new Date(dateString);
};

// Initialize filters with props
const filters = ref({
    status: props.filters.status || '',
    search: props.filters.search || '',
    date_from: parseDateString(props.filters.date_from),
    date_to: parseDateString(props.filters.date_to)
});

const currentTab = computed(() => {
    return props.filters.status || 'all';
});

const tabs = computed(() => {
    const allTabs = [
        { key: 'all', label: 'Все кейсы' },
        { key: 'draft', label: 'Черновики', count: props.cases.meta?.draft_count },
        { key: 'active', label: 'Активные', count: props.cases.meta?.active_count },
        { key: 'completed', label: 'Завершенные', count: props.cases.meta?.completed_count },
        { key: 'archived', label: 'Архив', count: props.cases.meta?.archived_count }
    ];

    return allTabs.map(tab => ({
        ...tab,
        count: tab.count !== undefined ? tab.count : undefined
    }));
});

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Черновик', value: 'draft' },
    { label: 'Активен', value: 'active' },
    { label: 'Завершен', value: 'completed' },
    { label: 'Архив', value: 'archived' },
]);

const getStatusClass = (status) => {
    switch (status) {
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'archived':
            return 'bg-purple-100 text-purple-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
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
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
};

const debounceSearch = useDebounceFn(() => {
    applyFilters();
}, 300);

const formatDateForServer = (date) => {
    if (!date) return null;
    if (date instanceof Date) {
        return date.toISOString().split('T')[0];
    }
    return date;
};

const applyFilters = () => {
    const params = {
        status: filters.value.status || undefined,
        search: filters.value.search || undefined,
        date_from: formatDateForServer(filters.value.date_from) || undefined,
        date_to: formatDateForServer(filters.value.date_to) || undefined
    };

    router.get(route('partner.cases.index'), params, {
        preserveState: true,
        replace: true
    });
};

const goToCase = (caseId) => {
    router.visit(route('partner.cases.show', { case: caseId }));
};
</script>