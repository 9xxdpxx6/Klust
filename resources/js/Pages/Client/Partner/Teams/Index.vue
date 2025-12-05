<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900">Команды</h1>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
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
            </div>
        </div>

        <!-- Teams Grid -->
        <div v-if="teams.data.length === 0" class="bg-white shadow-sm rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Команд нет</h3>
            <p class="mt-1 text-sm text-gray-500">
                Пока нет одобренных команд.
            </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="team in teams.data"
                :key="team.id"
                class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-200"
            >
                <div class="p-6">
                    <!-- Team Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900 mb-1">
                                Команда: {{ team.leader.name }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ team.case.title }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="getStatusClass(team.status)"
                        >
                            {{ getStatusLabel(team.status) }}
                        </span>
                    </div>

                    <!-- Team Info -->
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                            Участников: {{ team.team_members.length + 1 }} / {{ team.case.team_size }}
                        </div>

                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                            </svg>
                            Создана: {{ formatDate(team.created_at) }}
                        </div>

                        <div v-if="team.case.deadline" class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            Дедлайн: {{ formatDate(team.case.deadline) }}
                        </div>
                    </div>

                    <!-- Action Button -->
                    <Link
                        :href="route('partner.teams.show', { application: team.id })"
                        class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
                    >
                        Подробнее
                    </Link>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <Pagination v-if="teams.links && teams.links.length > 2" :links="teams.links" class="mt-6" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Select from '@/Components/UI/Select.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    teams: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Extract available cases from teams for filtering
const availableCases = computed(() => {
    const casesMap = new Map();
    props.teams.data.forEach(team => {
        if (team.case && !casesMap.has(team.case.id)) {
            casesMap.set(team.case.id, team.case);
        }
    });
    return Array.from(casesMap.values());
});

const filters = ref({
    case_id: props.filters.case_id || '',
    status: props.filters.status || ''
});

const getStatusClass = (status) => {
    switch (status) {
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'accepted':
            return 'Активна';
        case 'completed':
            return 'Завершена';
        case 'rejected':
            return 'Отклонена';
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
    const params = {
        case_id: filters.value.case_id || undefined,
        status: filters.value.status || undefined
    };

    router.get(route('partner.teams.index'), params, {
        preserveState: true,
        replace: true
    });
};

const caseFilterOptions = computed(() => [
    { label: 'Все кейсы', value: '' },
    ...availableCases.value.map(caseItem => ({
        label: caseItem.title,
        value: caseItem.id
    }))
])

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Активные', value: 'accepted' },
    { label: 'Завершенные', value: 'completed' },
])
</script>
