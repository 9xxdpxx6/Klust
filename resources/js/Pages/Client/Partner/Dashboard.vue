<template>
    <MobileContainer :padding="false">
        <div :class="[
            isMobile ? 'space-y-4' : 'space-y-6'
        ]">
            <Head title="Панель партнера" />
            <!-- Заголовок с градиентом -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <h1 :class="[
                        'font-bold text-white mb-2',
                        isMobile ? 'text-2xl' : 'text-3xl'
                    ]">Панель партнера</h1>
                    <p class="text-indigo-100" :class="isMobile ? 'text-sm' : ''">
                        Управление кейсами и аналитика
                    </p>
                </div>
            </div>

            <!-- Статистика -->
            <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 2, large: 4 }">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md border border-blue-200/50 hover:shadow-lg transition-all cursor-pointer group"
                     :class="isMobile ? 'p-4' : 'p-6'"
                     @click="safeVisit('partner.cases.index')">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-blue-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Всего кейсов</p>
                            <p :class="[
                                'font-bold text-blue-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ statistics?.totalCases || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-briefcase text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md border border-green-200/50 hover:shadow-lg transition-all cursor-pointer group"
                     :class="isMobile ? 'p-4' : 'p-6'"
                     @click="safeVisit('partner.cases.index', { status: 'active' })">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-green-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Активные кейсы</p>
                            <p :class="[
                                'font-bold text-green-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ statistics?.activeCases || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-green-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-check-circle text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-md border border-amber-200/50 hover:shadow-lg transition-all cursor-pointer group"
                     :class="isMobile ? 'p-4' : 'p-6'"
                     @click="safeVisit('partner.cases.index', { status: 'completed' })">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-amber-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Завершенные</p>
                            <p :class="[
                                'font-bold text-amber-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ statistics?.completedCases || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-check text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-md border border-purple-200/50 hover:shadow-lg transition-all cursor-pointer group"
                     :class="isMobile ? 'p-4' : 'p-6'"
                     @click="safeVisit('partner.teams.index')">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-purple-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Команды</p>
                            <p :class="[
                                'font-bold text-purple-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ statistics?.teamsCount || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-purple-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-users text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>
            </ResponsiveGrid>

            <!-- Активные кейсы -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-briefcase text-indigo-600"></i>
                        Активные кейсы
                    </h2>
                    <p class="text-xs text-gray-600 mt-1">Все активные кейсы</p>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]" v-if="activeCases && activeCases.length > 0">
                    <ResponsiveGrid :cols="{ mobile: 1, desktop: 2 }">
                    <CaseCard
                        v-for="caseItem in activeCases"
                        :key="caseItem.id"
                        :case-data="caseItem"
                        :can-edit="true"
                        @view="() => router.visit(route('partner.cases.show', caseItem.id))"
                        @edit="() => router.visit(route('partner.cases.edit', caseItem.id))"
                    />
                    </ResponsiveGrid>
                </div>
                <div v-else :class="[
                    'text-center text-gray-400',
                    isMobile ? 'p-6' : 'p-12'
                ]">
                    <i :class="[
                        'pi pi-briefcase mb-2',
                        isMobile ? 'text-2xl' : 'text-4xl'
                    ]"></i>
                    <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет активных кейсов</p>
                </div>
            </div>

            <!-- Последние активности -->
            <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3 }" :class="isMobile ? 'gap-4' : 'gap-6'">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div :class="[
                        'py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200',
                        isMobile ? 'px-4 py-3' : 'px-6 py-4'
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-lg'
                        ]">
                            <i class="pi pi-inbox text-amber-600"></i>
                            Новые заявки
                        </h2>
                        <p class="text-xs text-gray-600 mt-1">{{ RECENT_PERIOD_LABEL }}</p>
                    </div>
                    <div :class="[
                        isMobile ? 'p-4' : 'p-6'
                    ]" v-if="recentActivities?.newApplications && recentActivities.newApplications.length > 0">
                    <div class="space-y-3">
                        <Link
                            v-for="(application, index) in recentActivities.newApplications.slice(0, 5)"
                            :key="application.id || index"
                            :href="route('partner.cases.show', { case: application.case_id })"
                            class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition-all border border-gray-200 cursor-pointer"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                        Команда #{{ application.id }}
                                    </h4>
                                    <p class="text-xs font-medium text-gray-700 mb-2">
                                        {{ application.case?.title || 'Кейс' }}
                                    </p>
                            </div>
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg border border-amber-200">
                                Новая
                            </span>
                        </div>
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-gray-900">
                                    • {{ application.leader?.name || 'Неизвестно' }}
                                </p>
                                <p
                                    v-for="member in application.team_members || []"
                                    :key="member.id"
                                    class="text-xs text-gray-700"
                                >
                                    • {{ member.user?.name || 'Неизвестно' }}
                                </p>
                            </div>
                        </Link>
                    </div>
                    </div>
                    <div v-else :class="[
                        'text-center text-gray-400',
                        isMobile ? 'p-6' : 'p-12'
                    ]">
                        <i :class="[
                            'pi pi-inbox mb-2',
                            isMobile ? 'text-2xl' : 'text-4xl'
                        ]"></i>
                        <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет новых заявок</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div :class="[
                        'py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200',
                        isMobile ? 'px-4 py-3' : 'px-6 py-4'
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-lg'
                        ]">
                            <i class="pi pi-check-circle text-green-600"></i>
                            Завершенные кейсы
                        </h2>
                        <p class="text-xs text-gray-600 mt-1 invisible">Все завершенные кейсы</p>
                    </div>
                    <div :class="[
                        isMobile ? 'p-4' : 'p-6'
                    ]" v-if="recentActivities?.completedCases && recentActivities.completedCases.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(caseItem, index) in recentActivities.completedCases.slice(0, 5)"
                            :key="caseItem.id || index"
                            @click="safeVisit('partner.cases.show', caseItem.id)"
                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition-all border border-gray-200 cursor-pointer"
                        >
                            <p class="text-sm font-semibold text-gray-900 mb-1">{{ caseItem.title }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(caseItem.completed_at || caseItem.updated_at) }}</p>
                        </div>
                    </div>
                    </div>
                    <div v-else :class="[
                        'text-center text-gray-400',
                        isMobile ? 'p-6' : 'p-12'
                    ]">
                        <i :class="[
                            'pi pi-check-circle mb-2',
                            isMobile ? 'text-2xl' : 'text-4xl'
                        ]"></i>
                        <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет завершенных кейсов</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div :class="[
                        'py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200',
                        isMobile ? 'px-4 py-3' : 'px-6 py-4'
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-lg'
                        ]">
                            <i class="pi pi-users text-blue-600"></i>
                            Новые команды
                        </h2>
                        <p class="text-xs text-gray-600 mt-1">{{ RECENT_PERIOD_LABEL }}</p>
                    </div>
                    <div :class="[
                        isMobile ? 'p-4' : 'p-6'
                    ]" v-if="recentActivities?.newTeams && recentActivities.newTeams.length > 0">
                    <div class="space-y-3">
                        <Link
                            v-for="(team, index) in recentActivities.newTeams.slice(0, 5)"
                            :key="team.id || index"
                            :href="route('partner.teams.show', { application: team.id })"
                            class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition-all border border-gray-200 cursor-pointer"
                        >
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                Команда #{{ team.id }}
                            </h4>
                            <p class="text-xs font-medium text-gray-700 mb-2">
                                {{ team.case?.title || 'Кейс' }}
                            </p>
                            <div class="space-y-0.5">
                                <p class="text-xs font-semibold text-gray-900">
                                    • {{ team.leader?.name || 'Неизвестно' }}
                                </p>
                                <p
                                    v-for="member in team.team_members || []"
                                    :key="member.id"
                                    class="text-xs text-gray-700"
                                >
                                    • {{ member.user?.name || 'Неизвестно' }}
                                </p>
                        </div>
                        </Link>
                    </div>
                    </div>
                    <div v-else :class="[
                        'text-center text-gray-400',
                        isMobile ? 'p-6' : 'p-12'
                    ]">
                        <i :class="[
                            'pi pi-users mb-2',
                            isMobile ? 'text-2xl' : 'text-4xl'
                        ]"></i>
                        <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет новых команд</p>
                    </div>
                </div>
            </ResponsiveGrid>

            <!-- Быстрые действия -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-bolt text-indigo-600"></i>
                        Быстрые действия
                    </h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 2, tablet: 2, desktop: 4 }">
                        <button
                            @click="safeVisit('partner.cases.create')"
                            :class="[
                                'flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl border border-indigo-200 hover:shadow-lg transition-all group',
                                isMobile ? 'p-4' : 'p-6'
                            ]"
                        >
                            <div :class="[
                                'flex items-center justify-center bg-indigo-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                                isMobile ? 'w-10 h-10' : 'w-12 h-12'
                            ]">
                                <i :class="[
                                    'pi pi-plus text-white',
                                    isMobile ? 'text-lg' : 'text-xl'
                                ]"></i>
                            </div>
                            <span :class="[
                                'font-semibold text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Создать кейс</span>
                        </button>
                        <button
                            @click="safeVisit('partner.cases.index')"
                            :class="[
                                'flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:shadow-lg transition-all group',
                                isMobile ? 'p-4' : 'p-6'
                            ]"
                        >
                            <div :class="[
                                'flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                                isMobile ? 'w-10 h-10' : 'w-12 h-12'
                            ]">
                                <i :class="[
                                    'pi pi-briefcase text-white',
                                    isMobile ? 'text-lg' : 'text-xl'
                                ]"></i>
                            </div>
                            <span :class="[
                                'font-semibold text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Все кейсы</span>
                        </button>
                        <button
                            @click="safeVisit('partner.teams.index')"
                            :class="[
                                'flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 hover:shadow-lg transition-all group',
                                isMobile ? 'p-4' : 'p-6'
                            ]"
                        >
                            <div :class="[
                                'flex items-center justify-center bg-purple-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                                isMobile ? 'w-10 h-10' : 'w-12 h-12'
                            ]">
                                <i :class="[
                                    'pi pi-users text-white',
                                    isMobile ? 'text-lg' : 'text-xl'
                                ]"></i>
                            </div>
                            <span :class="[
                                'font-semibold text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Команды</span>
                        </button>
                        <button
                            @click="safeVisit('partner.analytics.index')"
                            :class="[
                                'flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl border border-amber-200 hover:shadow-lg transition-all group',
                                isMobile ? 'p-4' : 'p-6'
                            ]"
                        >
                            <div :class="[
                                'flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                                isMobile ? 'w-10 h-10' : 'w-12 h-12'
                            ]">
                                <i :class="[
                                    'pi pi-chart-bar text-white',
                                    isMobile ? 'text-lg' : 'text-xl'
                                ]"></i>
                            </div>
                            <span :class="[
                                'font-semibold text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Аналитика</span>
                        </button>
                    </ResponsiveGrid>
                </div>
            </div>
        </div>
    </MobileContainer>
</template>

<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import CaseCard from '@/Components/CaseCard.vue';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue';
import { useResponsive } from '@/Composables/useResponsive';
import { routeExists } from '@/Utils/routes';
import { route } from 'ziggy-js';

const props = defineProps({
    statistics: {
        type: Object,
        default: () => ({}),
    },
    activeCases: {
        type: Array,
        default: () => [],
    },
    recentActivities: {
        type: Object,
        default: () => ({}),
    },
});

const { isMobile, padding } = useResponsive();

// Константа для периода отображения
const RECENT_PERIOD_LABEL = 'За последние 7 дней';

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' });
};

const safeVisit = (routeName, params = {}) => {
    if (routeExists(routeName)) {
        try {
            router.visit(route(routeName, params));
        } catch (e) {
            console.warn(`Route "${routeName}" not found`);
        }
    }
};
</script>

