<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Команда: {{ team.leader.name }}</h1>
                <Link
                    :href="route('partner.teams.index')"
                    class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                >
                    ← Назад к командам
                </Link>
            </div>
        </template>

        <!-- Case Info -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-medium text-gray-900">{{ team.case.title }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ team.case.description }}</p>
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
                        :class="getStatusClass(team.status)"
                    >
                        {{ getStatusLabel(team.status) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Дата создания</p>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(team.created_at) }}</p>
                </div>
                <div v-if="team.case.deadline">
                    <p class="text-sm font-medium text-gray-500">Дедлайн</p>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(team.case.deadline) }}</p>
                </div>
            </div>
        </div>

        <!-- Team Progress -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Прогресс команды</h3>

            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Общий прогресс</span>
                        <span class="text-sm font-medium text-gray-900">{{ progress.overall || 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div
                            class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                            :style="{ width: (progress.overall || 0) + '%' }"
                        ></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-500">Задач выполнено</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ progress.tasks_completed || 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">из {{ progress.tasks_total || 0 }}</p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-500">Активность</p>
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
                Участники команды ({{ team.team_members.length + 1 }} / {{ team.case.team_size }})
            </h3>

            <div class="space-y-4">
                <!-- Leader -->
                <div class="flex items-center p-4 border border-gray-200 rounded-lg bg-blue-50">
                    <div class="flex-shrink-0 h-12 w-12">
                        <img
                            class="h-12 w-12 rounded-full object-cover"
                            :src="team.leader.avatar_url || '/images/default-avatar.png'"
                            :alt="team.leader.name"
                        />
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
                </div>

                <!-- Team Members -->
                <div
                    v-for="member in team.team_members"
                    :key="member.id"
                    class="flex items-center p-4 border border-gray-200 rounded-lg"
                >
                    <div class="flex-shrink-0 h-12 w-12">
                        <img
                            class="h-12 w-12 rounded-full object-cover"
                            :src="member.user.avatar_url || '/images/default-avatar.png'"
                            :alt="member.user.name"
                        />
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ member.user.name }}</p>
                        <p class="text-sm text-gray-500">{{ member.user.email }}</p>
                    </div>
                    <div class="ml-4 text-right">
                        <p class="text-xs text-gray-500">Присоединился</p>
                        <p class="text-sm text-gray-900">{{ formatDate(member.joined_at) }}</p>
                    </div>
                </div>
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
                                        <p class="mt-0.5 text-xs text-gray-500">{{ formatDateTime(activity.created_at) }}</p>
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
import { Link } from '@inertiajs/vue3';

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

const formatDateTime = (dateString) => {
    if (!dateString) return 'Не указан';
    const date = new Date(dateString);
    return date.toLocaleString('ru-RU');
};
</script>
