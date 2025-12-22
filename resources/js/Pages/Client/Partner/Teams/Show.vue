<template>
    <div class="space-y-6">
        <Head :title="`Команда #${team.id}`" />
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Команда #{{ team.id }}</h1>
                <p class="text-lg font-medium text-gray-700 mt-1">{{ team.case.title }}</p>
            </div>
            <Link
                :href="route('partner.teams.index')"
                class="text-blue-600 hover:text-blue-900 text-sm font-medium"
            >
                ← Назад к командам
            </Link>
        </div>

        <!-- Case Info -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h2 class="text-lg font-medium text-gray-900 mb-2">{{ team.case.title }}</h2>
                    <p class="text-sm text-gray-500">{{ team.case.description }}</p>
                </div>
                <Link
                    :href="route('partner.cases.show', { case: team.case.id })"
                    class="ml-4 text-blue-600 hover:text-blue-900 text-sm font-medium whitespace-nowrap"
                >
                    Перейти к кейсу →
                </Link>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Статус команды</p>
                    <span
                        class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusClass(team.status?.name)"
                    >
                        {{ getStatusLabel(team.status?.name) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Дата подачи заявки</p>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(team.submitted_at || team.created_at) }}</p>
                </div>
                <div v-if="team.case.deadline">
                    <p class="text-sm font-medium text-gray-500">Дедлайн</p>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(team.case.deadline) }}</p>
                </div>
            </div>
        </div>

        <!-- Team Statistics -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Статистика команды</h3>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-500">Активность команды</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ progress.activity_score || 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">балл активности</p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-500">Дней до дедлайна</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ progress.days_remaining || 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">осталось</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                Участники команды ({{ (team.team_members?.length || 0) + 1 }} / {{ team.case.required_team_size || team.case.team_size }})
            </h3>

            <div class="space-y-4">
                <!-- Leader -->
                <Link
                    :href="route('partner.students.show', team.leader.id)"
                    class="flex items-center p-4 border border-gray-200 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors cursor-pointer"
                >
                    <div class="flex-shrink-0">
                        <img
                            v-if="team.leader.avatar_url"
                            class="h-12 w-12 rounded-full object-cover border-2 border-gray-200"
                            :src="team.leader.avatar_url"
                            :alt="team.leader.name"
                        />
                        <div
                            v-else
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200"
                        >
                            <span class="text-white text-sm font-bold">{{ getUserInitials(team.leader.name) }}</span>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex items-center">
                            <p class="text-sm font-medium text-gray-900">{{ team.leader.name }}</p>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Лидер
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">{{ team.leader.email }}</p>
                    </div>
                    <i class="pi pi-arrow-right text-gray-400 ml-4"></i>
                </Link>

                <!-- Team Members -->
                <Link
                    v-for="member in team.team_members"
                    :key="member.id"
                    :href="route('partner.students.show', member.user?.id)"
                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                >
                    <div class="flex-shrink-0">
                        <img
                            v-if="member.user?.avatar_url || member.user?.avatar"
                            class="h-12 w-12 rounded-full object-cover border-2 border-gray-200"
                            :src="member.user?.avatar_url || member.user?.avatar"
                            :alt="member.user?.name"
                        />
                        <div
                            v-else
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200"
                        >
                            <span class="text-white text-sm font-bold">{{ getUserInitials(member.user?.name) }}</span>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ member.user?.name }}</p>
                        <p class="text-sm text-gray-500">{{ member.user?.email }}</p>
                    </div>
                    <div class="ml-4 text-right">
                        <p class="text-xs text-gray-500">Присоединился</p>
                        <p class="text-sm text-gray-900">{{ formatDate(member.created_at || member.joined_at) }}</p>
                    </div>
                    <i class="pi pi-arrow-right text-gray-400 ml-4"></i>
                </Link>
            </div>
        </div>

        <!-- Activity History -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">История активности</h3>

            <div v-if="activityHistory.length === 0" class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-2 text-sm">Нет записей о активности</p>
            </div>

            <div v-else class="flow-root">
                <ul role="list" class="-mb-8">
                    <li v-for="(activity, idx) in activityHistory" :key="activity.id">
                        <div class="relative pb-8">
                            <span
                                v-if="idx !== activityHistory.length - 1"
                                class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                aria-hidden="true"
                            ></span>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span
                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                        :class="getActivityIconClass(activity.type)"
                                    >
                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div>
                                        <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500">{{ formatDateTime(activity.date || activity.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    team: {
        type: Object,
        required: true
    },
    progress: {
        type: Object,
        required: true
    },
    activityHistory: {
        type: Array,
        required: true
    }
});

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

const getActivityIconClass = (type) => {
    switch (type) {
        case 'task_completed':
            return 'bg-green-500';
        case 'member_joined':
            return 'bg-blue-500';
        case 'milestone_reached':
            return 'bg-purple-500';
        default:
            return 'bg-gray-500';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Не указан';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
};

const getUserInitials = (name) => {
    if (!name) return '??'
    return name
        .split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

const formatDateTime = (dateString) => {
    if (!dateString) return 'Не указан';
    const date = new Date(dateString);
    return date.toLocaleString('ru-RU');
};
</script>
