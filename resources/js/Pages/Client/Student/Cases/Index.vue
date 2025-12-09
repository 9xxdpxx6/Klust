<template>
    <div class="space-y-6">
        <Head title="Каталог кейсов" />
            <div>
                <h1 class="text-3xl font-bold text-text-primary mb-2">Каталог кейсов</h1>
                <p class="text-text-secondary">Выберите кейс для подачи заявки</p>
            </div>

            <!-- Фильтры -->
            <Card>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 filter-row">
                    <div class="filter-item">
                        <SearchInput
                            v-model="filters.search"
                            label="Поиск"
                            placeholder="Название, описание..."
                            class="w-full"
                            @input="handleSearch"
                        />
                    </div>
                    <div class="filter-item">
                        <label class="block text-sm font-medium mb-1">Навыки</label>
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
                    </div>
                    <div class="filter-item">
                        <Select
                            v-model="filters.partner_id"
                            :options="partnerOptions"
                            optionLabel="label"
                            optionValue="value"
                            label="Партнер"
                            placeholder="Все партнеры"
                            class="w-full"
                            @update:modelValue="handleFilter"
                        />
                    </div>
                    <div class="filter-item">
                        <Select
                            v-model="filters.status"
                            :options="statusOptions"
                            optionLabel="label"
                            optionValue="value"
                            label="Статус"
                            placeholder="Все статусы"
                            class="w-full"
                            @update:modelValue="handleFilter"
                        />
                    </div>
                    <div class="filter-item">
                        <Select
                            v-model="filters.per_page"
                            :options="perPageOptions"
                            optionLabel="label"
                            optionValue="value"
                            label="На странице"
                            placeholder="Выберите количество"
                            class="w-full"
                            @update:modelValue="handleFilter"
                        />
                    </div>
                </div>
            </Card>

            <!-- Кейсы -->
            <div v-if="cases.data && cases.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <GuestCaseCard
                    v-for="caseItem in cases.data"
                    :key="caseItem.id"
                    :case-data="caseItem"
                    :show-link="false"
                    :show-team-size="false"
                    :show-student-actions="true"
                    :can-apply="!caseItem.user_application"
                    :has-application="!!caseItem.user_application"
                    :application-status="caseItem.user_application"
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

        <!-- Apply Modal -->
        <ApplyCaseModal
            v-if="selectedCase"
            v-model="showApplyModal"
            :case-data="selectedCase"
            @success="handleApplySuccess"
        />
        </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import Select from '@/Components/UI/Select.vue';
import GuestCaseCard from '@/Components/GuestCaseCard.vue';
import ApplyCaseModal from '@/Components/ApplyCaseModal.vue';
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

// Нормализация skills: преобразуем в массив чисел
const normalizeSkills = (skills) => {
    if (!skills) return [];
    if (!Array.isArray(skills)) {
        return [Number(skills)].filter(n => !isNaN(n));
    }
    return skills.map(s => Number(s)).filter(n => !isNaN(n));
};

// Нормализация partner_id: преобразуем в число или пустую строку
const normalizePartnerId = (partnerId) => {
    if (!partnerId || partnerId === '' || partnerId === '0') return '';
    const num = Number(partnerId);
    return isNaN(num) ? '' : num;
};

// Нормализация status: строка или пустая строка
const normalizeStatus = (status) => {
    return status && status !== '' ? String(status) : '';
};

const filters = ref({
    search: props.filters?.search || '',
    skills: normalizeSkills(props.filters?.skills),
    partner_id: normalizePartnerId(props.filters?.partner_id),
    status: normalizeStatus(props.filters?.status),
    per_page: props.filters?.per_page ? String(props.filters.per_page) : '30',
});

const showApplyModal = ref(false);
const selectedCase = ref(null);

// Используем computed для опций партнеров, чтобы они обновлялись при изменении props
const partnerOptions = computed(() => [
    { label: 'Все партнеры', value: '' },
    ...props.partners.map(p => ({ label: p.name, value: Number(p.id) })),
]);

const statusOptions = [
    { label: 'Все статусы', value: '' },
    { label: 'Доступные', value: 'active' },
    { label: 'Завершенные', value: 'completed' },
];

const perPageOptions = [
    { label: 'Отображать по 30', value: '30' },
    { label: 'Отображать по 60', value: '60' },
    { label: 'Отображать по 100', value: '100' },
];

// Синхронизация filters с props при обновлении страницы
watch(() => props.filters, (newFilters) => {
    if (newFilters) {
        filters.value = {
            search: newFilters.search || '',
            skills: normalizeSkills(newFilters.skills),
            partner_id: normalizePartnerId(newFilters.partner_id),
            status: normalizeStatus(newFilters.status),
            per_page: newFilters.per_page ? String(newFilters.per_page) : '30',
        };
    }
}, { immediate: true, deep: true });

let searchTimeout = null;
const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        handleFilter();
    }, 500);
};

const handleFilter = () => {
    if (routeExists('student.cases.index')) {
        const filterParams = {};
        
        // Добавляем только непустые значения
        if (filters.value.search) {
            filterParams.search = filters.value.search;
        }
        
        if (filters.value.skills && filters.value.skills.length > 0) {
            filterParams.skills = filters.value.skills;
        }
        
        if (filters.value.partner_id && filters.value.partner_id !== '') {
            filterParams.partner_id = filters.value.partner_id;
        }
        
        if (filters.value.status && filters.value.status !== '') {
            filterParams.status = filters.value.status;
        }
        
        if (filters.value.per_page) {
            filterParams.per_page = filters.value.per_page;
        }
        
        router.get(route('student.cases.index'), filterParams, {
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
        const caseItem = props.cases.data.find(c => c.id === caseId);
        if (caseItem) {
            selectedCase.value = caseItem;
            showApplyModal.value = true;
        }
    } catch (e) {
        console.error('Error opening apply modal:', e);
    }
};

const handleApplySuccess = () => {
    // Обновляем данные после успешной подачи заявки
    router.reload({ only: ['cases'] });
};
</script>

<style scoped>
.filter-row {
    align-items: flex-start;
}

.filter-item {
    display: flex;
    flex-direction: column;
    min-height: 100%;
}

/* Ensure all filter inputs have consistent height */
:deep(.p-multiselect),
:deep(.p-select),
:deep(.p-inputtext) {
    min-height: 2.5rem;
}

/* Ensure labels are consistent */
.filter-item label {
    min-height: 1.25rem;
    margin-bottom: 0.25rem;
}
</style>
