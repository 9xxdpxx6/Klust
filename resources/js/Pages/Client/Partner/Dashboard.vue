<template>
    <div class="space-y-6">
        <Head title="Панель партнера" />
        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <h1 class="text-3xl font-bold text-white mb-2">Панель партнера</h1>
                <p class="text-indigo-100">Управление кейсами и аналитика</p>
            </div>
        </div>

        <!-- Статистика компании -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-md border border-blue-200/50 hover:shadow-lg transition-all cursor-pointer group"
                 @click="safeVisit('partner.cases.index')">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Всего кейсов</p>
                        <p class="text-3xl font-bold text-blue-900">{{ statistics?.totalCases || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-briefcase text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-md border border-green-200/50 hover:shadow-lg transition-all cursor-pointer group"
                 @click="safeVisit('partner.cases.index', { status: 'active' })">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Активные кейсы</p>
                        <p class="text-3xl font-bold text-green-900">{{ statistics?.activeCases || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-check-circle text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 shadow-md border border-amber-200/50 hover:shadow-lg transition-all cursor-pointer group"
                 @click="safeVisit('partner.cases.index', { status: 'completed' })">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 mb-1">Завершенные</p>
                        <p class="text-3xl font-bold text-amber-900">{{ statistics?.completedCases || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-check text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-md border border-purple-200/50 hover:shadow-lg transition-all cursor-pointer group"
                 @click="safeVisit('partner.teams.index')">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 mb-1">Команды</p>
                        <p class="text-3xl font-bold text-purple-900">{{ statistics?.teamsCount || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-users text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Активные кейсы -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-briefcase text-indigo-600"></i>
                    Активные кейсы
                </h2>
            </div>
            <div class="p-6" v-if="activeCases && activeCases.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <CaseCard
                        v-for="caseItem in activeCases"
                        :key="caseItem.id"
                        :case-data="caseItem"
                        :can-edit="true"
                        @view="() => router.visit(route('partner.cases.show', caseItem.id))"
                        @edit="() => router.visit(route('partner.cases.edit', caseItem.id))"
                    />
                </div>
            </div>
            <div v-else class="p-12 text-center text-gray-400">
                <i class="pi pi-briefcase text-4xl mb-2"></i>
                <p class="text-sm">Нет активных кейсов</p>
            </div>
        </div>

        <!-- Последние активности -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-inbox text-amber-600"></i>
                        Новые заявки
                    </h2>
                </div>
                <div class="p-6" v-if="recentActivities?.newApplications && recentActivities.newApplications.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(application, index) in recentActivities.newApplications.slice(0, 5)"
                            :key="application.id || index"
                            @click="safeVisit('partner.cases.show', application.case_id)"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition-all border border-gray-200 cursor-pointer"
                        >
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ application.case?.title || 'Кейс' }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(application.created_at) }}</p>
                            </div>
                            <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg border border-amber-200">
                                Новая
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-inbox text-4xl mb-2"></i>
                    <p class="text-sm">Нет новых заявок</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-check-circle text-green-600"></i>
                        Завершенные кейсы
                    </h2>
                </div>
                <div class="p-6" v-if="recentActivities?.completedCases && recentActivities.completedCases.length > 0">
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
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-check-circle text-4xl mb-2"></i>
                    <p class="text-sm">Нет завершенных кейсов</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-users text-blue-600"></i>
                        Новые команды
                    </h2>
                </div>
                <div class="p-6" v-if="recentActivities?.newTeams && recentActivities.newTeams.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(team, index) in recentActivities.newTeams.slice(0, 5)"
                            :key="team.id || index"
                            @click="safeVisit('partner.teams.index')"
                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition-all border border-gray-200 cursor-pointer"
                        >
                            <p class="text-sm font-semibold text-gray-900 mb-1">{{ team.case?.title || 'Команда' }}</p>
                            <p class="text-xs text-gray-500">{{ team.members_count || 0 }} участников</p>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-users text-4xl mb-2"></i>
                    <p class="text-sm">Нет новых команд</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import CaseCard from '@/Components/CaseCard.vue';
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

