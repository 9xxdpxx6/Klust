<template>
    <div class="space-y-6">
        <Head title="Каталог кейсов" />
            <div>
                <h1 class="text-3xl font-bold text-text-primary mb-2">Каталог кейсов</h1>
                <p class="text-text-secondary">Выберите кейс для подачи заявки</p>
            </div>

            <!-- Фильтры -->
            <Card>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <MultiSelect
                        v-model="filters.skills"
                        :options="availableSkills"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Навыки"
                        :filter="true"
                        class="w-full"
                        @update:modelValue="handleFilter"
                    />
                    <Select
                        v-model="filters.partner_id"
                        :options="partnerOptions"
                        optionLabel="label"
                        optionValue="value"
                        label="Партнер"
                        placeholder="Все партнеры"
                        @update:modelValue="handleFilter"
                    />
                    <Select
                        v-model="filters.status"
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        label="Статус"
                        placeholder="Все статусы"
                        @update:modelValue="handleFilter"
                    />
                    <SearchInput
                        v-model="filters.search"
                        label="Поиск"
                        placeholder="Название, описание..."
                        @input="handleSearch"
                    />
                </div>
            </Card>

            <!-- Кейсы -->
            <div v-if="cases.data && cases.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <CaseCard
                    v-for="caseItem in cases.data"
                    :key="caseItem.id"
                    :case-data="caseItem"
                    :can-apply="true"
                    :applying="applyingCases.includes(caseItem.id)"
                    @view="() => handleViewCase(caseItem.id)"
                    @apply="() => handleApply(caseItem.id)"
                />
            </div>

            <Card v-else>
                <div class="text-center py-12">
                    <p class="text-text-muted">Кейсы не найдены</p>
                </div>
            </Card>

            <!-- Пагинация -->
            <div v-if="cases.meta && cases.meta.last_page > 1" class="flex justify-center">
                <Paginator
                    :first="(cases.meta.current_page - 1) * cases.meta.per_page"
                    :rows="cases.meta.per_page"
                    :totalRecords="cases.meta.total"
                    @page="handlePage"
                />
            </div>
        </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import Select from '@/Components/UI/Select.vue';
import CaseCard from '@/Components/CaseCard.vue';
import MultiSelect from 'primevue/multiselect';
import Paginator from 'primevue/paginator';
import { routeExists } from '@/Utils/routes';
import { route } from 'ziggy-js';

const props = defineProps({
    cases: {
        type: Object,
        default: () => ({ data: [], meta: {}, links: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    availableSkills: {
        type: Array,
        default: () => [],
    },
    partners: {
        type: Array,
        default: () => [],
    },
});

const filters = ref({
    skills: props.filters.skills || [],
    partner_id: props.filters.partner_id || '',
    status: props.filters.status || '',
    search: props.filters.search || '',
});

const applyingCases = ref([]);

const partnerOptions = [
    { label: 'Все партнеры', value: '' },
    ...props.partners.map(p => ({ label: p.name, value: p.id })),
];

const statusOptions = [
    { label: 'Все статусы', value: '' },
    { label: 'Доступные', value: 'active' },
    { label: 'Закрытые', value: 'closed' },
];

let searchTimeout = null;
const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        handleFilter();
    }, 500);
};

const handleFilter = () => {
    if (routeExists('student.cases.index')) {
        router.get(route('student.cases.index'), filters.value, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const handlePage = (event) => {
    if (routeExists('student.cases.index')) {
        router.get(route('student.cases.index'), {
            ...filters.value,
            page: Math.floor(event.first / event.rows) + 1,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const handleViewCase = (caseId) => {
    try {
        router.visit(route('student.cases.show', caseId));
    } catch (e) {
        console.error('Error navigating to case:', e);
    }
};

const handleApply = (caseId) => {
    try {
        applyingCases.value.push(caseId);
        router.post(route('student.cases.apply', caseId), {}, {
            preserveScroll: true,
            onFinish: () => {
                applyingCases.value = applyingCases.value.filter(id => id !== caseId);
            },
        });
    } catch (e) {
        console.error('Error submitting application:', e);
        applyingCases.value = applyingCases.value.filter(id => id !== caseId);
    }
};
</script>
