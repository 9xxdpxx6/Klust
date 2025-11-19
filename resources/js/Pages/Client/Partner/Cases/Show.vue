<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">{{ caseData.title }}</h1>
                <div class="flex space-x-3">
                    <Link
                        :href="route('partner.cases.edit', { case: caseData.id })"
                        class="inline-flex items-center justify-center p-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium transition duration-150 ease-in-out focus:outline-none"
                        title="Редактировать"
                    >
                        <i class="pi pi-pencil text-sm"></i>
                    </Link>
                    <button
                        v-if="caseData.status !== 'archived'"
                        @click="archiveCase"
                        class="inline-flex items-center justify-center p-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm font-medium transition duration-150 ease-in-out focus:outline-none"
                        title="Архивировать"
                    >
                        <i class="pi pi-archive text-sm"></i>
                    </button>
                    <button
                        v-if="caseData.status !== 'archived'"
                        @click="deleteCase"
                        class="inline-flex items-center justify-center p-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium transition duration-150 ease-in-out focus:outline-none"
                        title="Удалить"
                    >
                        <i class="pi pi-trash text-sm"></i>
                    </button>
                </div>
            </div>

        <!-- Case Info -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Информация о кейсе</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Описание</p>
                            <p class="mt-1 text-gray-900 whitespace-pre-line">{{ caseData.description || 'Не указано' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Требуемый размер команды</p>
                            <p class="mt-1 text-gray-900">{{ caseData.team_size }} человек</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Дедлайн</p>
                            <p class="mt-1 text-gray-900">{{ formatDate(caseData.deadline) || 'Не указан' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Статус</p>
                            <p class="mt-1">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getStatusClass(caseData.status)"
                                >
                                    {{ getStatusLabel(caseData.status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Требуемые навыки</h2>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="skill in caseData.required_skills"
                            :key="skill.id"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800"
                        >
                            {{ skill.name }}
                        </span>
                        <span v-if="caseData.required_skills.length === 0" class="text-gray-500 italic">Навыки не указаны</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="route('partner.cases.show', { case: caseData.id, tab: tab.key })"
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

            <div class="p-6">
                <!-- Заявки Tab -->
                <div v-if="currentTab === 'applications'">
                    <div class="mb-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Заявки на кейс</h3>
                            <div class="flex space-x-2">
                                <Select
                                    v-model="applicationFilters.status"
                                    label="Статус"
                                    :options="statusFilterOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Все статусы"
                                    @update:modelValue="loadApplications"
                                />
                                <a
                                    :href="route('partner.cases.applications.export', { case: caseData.id })"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    <svg class="h-5 w-5 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Экспорт (Excel)
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-if="applications.data.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Заявок нет</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{
                                applicationFilters.status 
                                    ? `Нет заявок со статусом "${getStatusLabel(applicationFilters.status)}".` 
                                    : 'Пока нет заявок на этот кейс.'
                            }}
                        </p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Лидер
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Размер команды
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Дата подачи
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Статус
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="application in applications.data" :key="application.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img 
                                                    class="h-10 w-10 rounded-full object-cover" 
                                                    :src="application.leader.avatar_url || '/images/default-avatar.png'" 
                                                    :alt="application.leader.name"
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ application.leader.name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ application.leader.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ application.team_size }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(application.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusClass(application.status?.name)"
                                        >
                                            {{ application.status?.label || application.status?.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <Link
                                                :href="route('partner.cases.applications.show', { application: application.id })"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Просмотреть
                                            </Link>
                                            <button
                                                v-if="application.status?.name === 'pending'"
                                                @click="approveApplication(application.id)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Одобрить
                                            </button>
                                            <button
                                                v-if="application.status?.name === 'pending'"
                                                @click="rejectApplication(application.id)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Отклонить
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination for Applications -->
                    <Pagination v-if="applications.links.length > 2" :links="applications.links" class="mt-6" />
                </div>

                <!-- Команды Tab -->
                <div v-else-if="currentTab === 'teams'">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Одобренные команды</h3>
                    
                    <div v-if="teams.length === 0" class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Команд пока нет</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Нет одобренных команд для этого кейса.
                        </p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div 
                            v-for="team in teams" 
                            :key="team.id"
                            class="border border-gray-200 rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-md font-medium text-gray-900">Команда: {{ team.leader.name }}</h4>
                                    <p class="text-sm text-gray-500">Количество участников: {{ team.members.length }}</p>
                                </div>
                                <Link 
                                    :href="route('partner.teams.show', { application: team.id })" 
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                >
                                    Подробнее
                                </Link>
                            </div>
                            <div class="mt-3">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    Прогресс: {{ team.progress || 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Статистика Tab -->
                <div v-else-if="currentTab === 'stats'">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Статистика кейса</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white p-4 rounded-lg shadow border">
                            <p class="text-sm font-medium text-gray-500">Всего заявок</p>
                            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_applications }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border">
                            <p class="text-sm font-medium text-gray-500">Ожидают</p>
                            <p class="text-2xl font-bold text-gray-900">{{ statistics.pending_applications }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border">
                            <p class="text-sm font-medium text-gray-500">Одобренные</p>
                            <p class="text-2xl font-bold text-gray-900">{{ statistics.accepted_applications }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border">
                            <p class="text-sm font-medium text-gray-500">Конверсия</p>
                            <p class="text-2xl font-bold text-gray-900">{{ statistics.conversion_rate }}%</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Детали конверсии</h4>
                        <div class="bg-white p-4 rounded-lg shadow border">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-blue-600 rounded-full" 
                                            :style="{ width: statistics.conversion_rate + '%' }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="ml-4 text-sm text-gray-500">
                                    {{ statistics.accepted_applications }} из {{ statistics.total_applications }} заявок принято
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import Select from '@/Components/UI/Select.vue';

const props = defineProps({
    caseData: {
        type: Object,
        required: true
    },
    applications: {
        type: Object,
        required: true
    },
    teams: {
        type: Array,
        required: true
    },
    statistics: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ tab: 'applications' })
    }
});

// Initialize filters
const applicationFilters = ref({
    status: null
});

const currentTab = computed(() => {
    return props.filters.tab || 'applications';
});

const tabs = [
    { key: 'applications', label: 'Заявки', count: props.applications.meta?.total },
    { key: 'teams', label: 'Команды', count: props.teams.length },
    { key: 'stats', label: 'Статистика', count: undefined }
];

const getStatusClass = (status) => {
    switch (status) {
        case 'draft':
        case 'pending':
            return 'bg-gray-100 text-gray-800';
        case 'active':
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'archived':
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Черновик';
        case 'pending':
            return 'Ожидает';
        case 'active':
            return 'Активен';
        case 'completed':
            return 'Завершен';
        case 'archived':
            return 'Архив';
        case 'accepted':
            return 'Принято';
        case 'rejected':
            return 'Отклонено';
        default:
            return status;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Не указан';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
};

const archiveCase = () => {
    if (confirm('Вы уверены, что хотите архивировать этот кейс?')) {
        router.post(route('partner.cases.archive', { case: props.caseData.id }), {}, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const deleteCase = () => {
    if (props.caseData.applications_count > 0) {
        alert('Нельзя удалить кейс, если на него есть заявки или команды.');
        return;
    }

    if (confirm('Вы уверены, что хотите удалить этот кейс? Это действие нельзя отменить.')) {
        router.delete(route('partner.cases.destroy', { case: props.caseData.id }), {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const approveApplication = (applicationId) => {
    if (confirm('Вы уверены, что хотите одобрить эту заявку?')) {
        router.post(route('partner.cases.applications.approve', { case: props.caseData.id, application: applicationId }), {}, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const rejectApplication = (applicationId) => {
    if (confirm('Вы уверены, что хотите отклонить эту заявку?')) {
        router.post(route('partner.cases.applications.reject', { case: props.caseData.id, application: applicationId }), {}, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Ожидает', value: 'pending' },
    { label: 'Принято', value: 'accepted' },
    { label: 'Отклонено', value: 'rejected' },
])

const loadApplications = () => {
    router.get(
        route('partner.cases.show', {
            case: props.caseData.id,
            tab: 'applications',
            status: applicationFilters.value.status || undefined
        }),
        {},
        {
            preserveState: true,
            preserveScroll: true
        }
    );
};
</script>