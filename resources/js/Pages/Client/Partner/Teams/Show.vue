<template>
    <MobileContainer :padding="false">
        <div :class="[
            isMobile ? 'space-y-4' : 'space-y-6'
        ]">
            <Head :title="`Команда #${team.id}`" />
            
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <div :class="[
                        'flex items-start justify-between',
                        isMobile ? 'flex-col gap-4' : ''
                    ]">
                        <div class="flex-1">
                            <nav class="flex mb-4" aria-label="Breadcrumb">
                                <ol :class="[
                                    'flex items-center',
                                    isMobile ? 'space-x-1' : 'space-x-2'
                                ]">
                                    <li>
                                        <Link :href="route('partner.teams.index')" :class="[
                                            'text-indigo-200 hover:text-white transition-colors',
                                            isMobile ? 'text-xs' : 'text-sm'
                                        ]">
                                            <span class="font-medium">Команды</span>
                                        </Link>
                                    </li>
                                    <li>
                                        <i :class="[
                                            'pi pi-chevron-right text-indigo-300',
                                            isMobile ? 'text-xs' : ''
                                        ]"></i>
                                    </li>
                                    <li>
                                        <span :class="[
                                            'font-medium text-white',
                                            isMobile ? 'text-xs' : 'text-sm'
                                        ]">Команда #{{ team.id }}</span>
                                    </li>
                                </ol>
                            </nav>
                            <h1 :class="[
                                'font-bold text-white mb-2',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">Команда #{{ team.id }}</h1>
                            <p :class="[
                                'text-indigo-100',
                                isMobile ? 'text-sm' : 'text-lg'
                            ]">{{ team.case.title }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case Info -->
            <div :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <div :class="[
                        'flex items-start justify-between',
                        isMobile ? 'flex-col gap-3' : ''
                    ]">
                        <div class="flex-1">
                            <h2 :class="[
                                'font-medium text-gray-900 mb-2',
                                isMobile ? 'text-base' : 'text-lg'
                            ]">{{ team.case.title }}</h2>
                            <p :class="[
                                'text-gray-500',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">{{ team.case.description }}</p>
                        </div>
                        <Link
                            :href="route('partner.cases.show', { case: team.case.id })"
                            :class="[
                                'text-blue-600 hover:text-blue-900 font-medium whitespace-nowrap',
                                isMobile ? 'text-xs w-full text-center' : 'ml-4 text-sm'
                            ]"
                        >
                            Перейти к кейсу →
                        </Link>
                    </div>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 3 }">
                        <div>
                            <p :class="[
                                'font-medium text-gray-500',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Статус команды</p>
                            <span
                                :class="[
                                    'mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full font-medium',
                                    isMobile ? 'text-xs' : 'text-xs',
                                    getStatusClass(team.status?.name)
                                ]"
                            >
                                {{ getStatusLabel(team.status?.name) }}
                            </span>
                        </div>
                        <div>
                            <p :class="[
                                'font-medium text-gray-500',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Дата подачи заявки</p>
                            <p :class="[
                                'mt-1 text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">{{ formatDate(team.submitted_at || team.created_at) }}</p>
                        </div>
                        <div v-if="team.case.deadline">
                            <p :class="[
                                'font-medium text-gray-500',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Дедлайн</p>
                            <p :class="[
                                'mt-1 text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">{{ formatDate(team.case.deadline) }}</p>
                        </div>
                    </ResponsiveGrid>
                </div>
            </div>

            <!-- Team Statistics -->
            <div :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h3 :class="[
                        'font-medium text-gray-900',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">Статистика команды</h3>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 2 }">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p :class="[
                                'font-medium text-gray-500',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Активность команды</p>
                            <p :class="[
                                'mt-1 font-bold text-gray-900',
                                isMobile ? 'text-xl' : 'text-2xl'
                            ]">{{ progress.activity_score || 0 }}</p>
                            <p :class="[
                                'text-gray-500 mt-1',
                                isMobile ? 'text-xs' : 'text-xs'
                            ]">балл активности</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p :class="[
                                'font-medium text-gray-500',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Дней до дедлайна</p>
                            <p :class="[
                                'mt-1 font-bold text-gray-900',
                                isMobile ? 'text-xl' : 'text-2xl'
                            ]">{{ progress.days_remaining || 0 }}</p>
                            <p :class="[
                                'text-gray-500 mt-1',
                                isMobile ? 'text-xs' : 'text-xs'
                            ]">осталось</p>
                        </div>
                    </ResponsiveGrid>
                </div>
            </div>

            <!-- Team Members -->
            <div :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h3 :class="[
                        'font-medium text-gray-900',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        Участники команды ({{ (team.team_members?.length || 0) + 1 }} / {{ team.case.required_team_size || team.case.team_size }})
                    </h3>
                </div>
                <div :class="[
                    'space-y-4',
                    isMobile ? 'p-4' : 'p-6'
                ]">
                    <!-- Leader -->
                    <Link
                        :href="route('partner.students.show', team.leader.id)"
                        :class="[
                            'flex items-center border border-gray-200 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors cursor-pointer',
                            isMobile ? 'p-3' : 'p-4'
                        ]"
                    >
                        <div class="flex-shrink-0">
                            <img
                                v-if="team.leader.avatar_url"
                                :class="[
                                    'rounded-full object-cover border-2 border-gray-200',
                                    isMobile ? 'h-10 w-10' : 'h-12 w-12'
                                ]"
                                :src="team.leader.avatar_url"
                                :alt="team.leader.name"
                            />
                            <div
                                v-else
                                :class="[
                                    'rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200',
                                    isMobile ? 'h-10 w-10' : 'h-12 w-12'
                                ]"
                            >
                                <span :class="[
                                    'text-white font-bold',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">{{ getUserInitials(team.leader.name) }}</span>
                            </div>
                        </div>
                        <div :class="[
                            'flex-1 min-w-0',
                            isMobile ? 'ml-3' : 'ml-4'
                        ]">
                            <div class="flex items-center flex-wrap gap-2">
                                <p :class="[
                                    'font-medium text-gray-900 truncate',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">{{ team.leader.name }}</p>
                                <span :class="[
                                    'inline-flex items-center px-2 py-0.5 rounded font-medium bg-blue-100 text-blue-800 flex-shrink-0',
                                    isMobile ? 'text-xs' : 'text-xs'
                                ]">
                                    Лидер
                                </span>
                            </div>
                            <p :class="[
                                'text-gray-500 truncate',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">{{ team.leader.email }}</p>
                        </div>
                        <i :class="[
                            'pi pi-arrow-right text-gray-400 flex-shrink-0',
                            isMobile ? 'ml-2' : 'ml-4'
                        ]"></i>
                    </Link>

                    <!-- Team Members -->
                    <Link
                        v-for="member in team.team_members"
                        :key="member.id"
                        :href="route('partner.students.show', member.user?.id)"
                        :class="[
                            'flex items-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer',
                            isMobile ? 'p-3' : 'p-4'
                        ]"
                    >
                        <div class="flex-shrink-0">
                            <img
                                v-if="member.user?.avatar_url || member.user?.avatar"
                                :class="[
                                    'rounded-full object-cover border-2 border-gray-200',
                                    isMobile ? 'h-10 w-10' : 'h-12 w-12'
                                ]"
                                :src="member.user?.avatar_url || member.user?.avatar"
                                :alt="member.user?.name"
                            />
                            <div
                                v-else
                                :class="[
                                    'rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200',
                                    isMobile ? 'h-10 w-10' : 'h-12 w-12'
                                ]"
                            >
                                <span :class="[
                                    'text-white font-bold',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">{{ getUserInitials(member.user?.name) }}</span>
                            </div>
                        </div>
                        <div :class="[
                            'flex-1 min-w-0',
                            isMobile ? 'ml-3' : 'ml-4'
                        ]">
                            <p :class="[
                                'font-medium text-gray-900 truncate',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">{{ member.user?.name }}</p>
                            <p :class="[
                                'text-gray-500 truncate',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">{{ member.user?.email }}</p>
                            <p v-if="isMobile" :class="[
                                'text-gray-400 mt-1 text-xs'
                            ]">Присоединился: {{ formatDate(member.created_at || member.joined_at) }}</p>
                        </div>
                        <div v-if="!isMobile" class="text-right ml-4">
                            <p class="text-gray-500 text-xs">Присоединился</p>
                            <p class="text-gray-900 text-sm">{{ formatDate(member.created_at || member.joined_at) }}</p>
                        </div>
                        <i :class="[
                            'pi pi-arrow-right text-gray-400 flex-shrink-0',
                            isMobile ? 'ml-2' : 'ml-4'
                        ]"></i>
                    </Link>
                </div>
            </div>

            <!-- Activity History -->
            <div :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h3 :class="[
                        'font-medium text-gray-900',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">История активности</h3>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">

                    <div v-if="activityHistory.length === 0" :class="[
                        'text-center text-gray-500',
                        isMobile ? 'py-6' : 'py-8'
                    ]">
                        <div class="mx-auto w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-3">
                            <i class="pi pi-clock text-3xl text-gray-400"></i>
                        </div>
                        <p :class="[
                            'mt-2',
                            isMobile ? 'text-xs' : 'text-sm'
                        ]">Нет записей о активности</p>
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
                                        <p :class="[
                                            'text-gray-900',
                                            isMobile ? 'text-xs' : 'text-sm'
                                        ]">{{ activity.description }}</p>
                                        <p :class="[
                                            'mt-0.5 text-gray-500',
                                            isMobile ? 'text-xs' : 'text-xs'
                                        ]">{{ formatDateTime(activity.date || activity.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                    </div>
                </div>
            </div>
        </div>
    </MobileContainer>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue';
import { useResponsive } from '@/Composables/useResponsive';
import { route } from 'ziggy-js';

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

const { isMobile, padding } = useResponsive();

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
