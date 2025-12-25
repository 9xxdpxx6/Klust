<template>
    <MobileContainer :padding="false">
        <div :class="[
            isMobile ? 'space-y-4' : 'space-y-6'
        ]">
            <Head :title="recommended ? 'Рекомендованные кейсы' : 'Каталог кейсов'" />
            <div>
                <h1 :class="[
                    'font-bold text-text-primary mb-2',
                    isMobile ? 'text-2xl' : 'text-3xl'
                ]">
                    {{ recommended ? 'Рекомендованные кейсы' : 'Каталог кейсов' }}
                </h1>
                <p :class="[
                    'text-text-secondary',
                    isMobile ? 'text-sm' : ''
                ]">
                    {{ recommended ? 'Подборка кейсов под ваши навыки' : 'Выберите кейс для подачи заявки' }}
                </p>
            </div>

            <!-- Фильтры -->
            <Card>
                <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3, large: 5 }" :class="isMobile ? 'gap-3' : 'gap-4'" class="filter-row">
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
                    <div class="filter-item" v-if="!recommended">
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
                </ResponsiveGrid>
            </Card>

            <!-- Кейсы -->
            <div v-if="cases.data && cases.data.length > 0">
                <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 2, large: 3 }" :class="isMobile ? 'gap-4' : 'gap-6'">
                <StudentCaseCard
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
                </ResponsiveGrid>
            </div>

            <Card v-else>
                <div :class="[
                    'text-center',
                    isMobile ? 'py-8' : 'py-12'
                ]">
                    <p :class="[
                        'text-text-muted',
                        isMobile ? 'text-sm' : ''
                    ]">Кейсы не найдены</p>
                </div>
            </Card>

            <!-- Пагинация -->
            <div v-if="cases.meta && cases.meta.last_page > 1" :class="[
                'flex justify-center',
                isMobile ? 'overflow-x-auto' : ''
            ]">
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
    </MobileContainer>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import Select from '@/Components/UI/Select.vue';
import StudentCaseCard from '@/Components/StudentCaseCard.vue';
import ApplyCaseModal from '@/Components/ApplyCaseModal.vue';
import MultiSelect from 'primevue/multiselect';
import Paginator from 'primevue/paginator';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue';
import { useResponsive } from '@/Composables/useResponsive';
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
    recommended: {
        type: Boolean,
        default: false,
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

const listRouteName = computed(() => (props.recommended ? 'student.cases.recommended' : 'student.cases.index'));

const { isMobile } = useResponsive();

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
    if (routeExists(listRouteName.value)) {
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
        
        if (!props.recommended && filters.value.status && filters.value.status !== '') {
            filterParams.status = filters.value.status;
        }
        
        if (filters.value.per_page) {
            filterParams.per_page = filters.value.per_page;
        }
        
        router.get(route(listRouteName.value), filterParams, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const handlePage = (event) => {
    if (routeExists(listRouteName.value)) {
        router.get(route(listRouteName.value), {
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

/* Адаптивность для пагинации на мобильных */
@media (max-width: 767px) {
    :deep(.p-paginator) {
        font-size: 0.875rem;
    }
    
    :deep(.p-paginator .p-paginator-pages .p-paginator-page) {
        min-width: 2rem;
        height: 2rem;
    }
    
    /* Ограничение ширины выпадающего списка MultiSelect на мобильных */
    :deep(.p-multiselect-panel),
    :deep(.p-multiselect-overlay) {
        max-width: calc(100vw - 2rem) !important;
        width: calc(100vw - 2rem) !important;
        min-width: auto !important;
        box-sizing: border-box !important;
    }
    
    /* Ограничение контейнера списка */
    :deep(.p-multiselect-list-container),
    :deep(div.p-multiselect-list-container) {
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }
    
    /* Ограничение самого списка ul */
    :deep(.p-multiselect-list),
    :deep(ul.p-multiselect-list),
    :deep(ul[id^="pv_id_"][id$="_list"]),
    :deep([role="listbox"].p-multiselect-list),
    :deep([data-pc-section="list"].p-multiselect-list) {
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
        overflow-x: hidden !important;
    }
    
    /* Переопределение inline стилей для списка */
    :deep(ul.p-multiselect-list[style*="width"]),
    :deep(ul[id^="pv_id_"][id$="_list"][style*="width"]) {
        width: 100% !important;
        max-width: 100% !important;
    }
    
    /* Позиционирование панели на мобильных - привязка к родителю */
    :deep(.p-multiselect) {
        position: relative;
    }
    
    /* Фиксированное позиционирование панели на мобильных - все состояния */
    :deep(.p-multiselect-panel.p-connected-overlay),
    :deep(.p-multiselect-panel.p-connected-overlay-enter),
    :deep(.p-multiselect-panel.p-connected-overlay-enter-active),
    :deep(.p-multiselect-panel.p-connected-overlay-enter-done),
    :deep(.p-multiselect-panel[data-pc-section="root"]),
    :deep(.p-multiselect-panel),
    :deep(.p-multiselect-overlay.p-connected-overlay),
    :deep(.p-multiselect-overlay.p-connected-overlay-enter),
    :deep(.p-multiselect-overlay.p-connected-overlay-enter-active),
    :deep(.p-multiselect-overlay.p-connected-overlay-enter-done) {
        position: fixed !important;
        left: 1rem !important;
        right: 1rem !important;
        width: calc(100vw - 2rem) !important;
        max-width: calc(100vw - 2rem) !important;
        min-width: auto !important;
        box-sizing: border-box !important;
    }
    
    /* Переопределение inline стилей PrimeVue для ширины */
    :deep(.p-multiselect-panel[style*="width"]),
    :deep(.p-multiselect-overlay[style*="width"]) {
        width: calc(100vw - 2rem) !important;
        max-width: calc(100vw - 2rem) !important;
    }
    
    /* Дополнительная защита - ограничение через родительский контейнер */
    .filter-item {
        max-width: 100%;
        overflow: visible;
    }
    
    /* Перенос длинных названий в опциях MultiSelect */
    :deep(.p-multiselect-panel .p-multiselect-item) {
        white-space: normal !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        line-height: 1.5 !important;
        padding: 0.75rem !important;
    }
    
    :deep(.p-multiselect-panel .p-multiselect-item-label) {
        white-space: normal !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        display: block !important;
        line-height: 1.5 !important;
    }
    
    /* Предотвращение горизонтальной прокрутки */
    :deep(.p-multiselect-panel .p-multiselect-items-wrapper) {
        overflow-x: hidden !important;
        overflow-y: auto !important;
        max-height: 50vh !important;
    }
    
    /* Ограничение ширины контейнера фильтров */
    .filter-row {
        overflow-x: hidden !important;
        max-width: 100% !important;
    }
    
    /* Ограничение ширины самого поля MultiSelect */
    :deep(.p-multiselect) {
        max-width: 100% !important;
        width: 100% !important;
        position: relative;
    }
    
    /* Дополнительное ограничение для всех возможных селекторов панели */
    :deep([data-pc-section="root"].p-multiselect-panel),
    :deep(.p-component.p-multiselect-panel),
    :deep(.p-multiselect-panel.p-component) {
        max-width: calc(100vw - 2rem) !important;
        width: calc(100vw - 2rem) !important;
        box-sizing: border-box !important;
    }
    
    /* Обрезка выбранных значений в поле (чтобы не растягивало) */
    :deep(.p-multiselect-label-container),
    :deep(.p-multiselect-token) {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}
</style>
