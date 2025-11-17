<template>
    <div class="space-y-6">
        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <h1 class="text-3xl font-bold text-white mb-2">Панель управления</h1>
                <p class="text-indigo-100">Обзор системы и статистика</p>
            </div>
        </div>

        <FlashMessage />

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-md border border-blue-200/50 hover:shadow-lg transition-all cursor-pointer group"
                 @click="safeVisit('admin.users.index', { role: 'student' })">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Всего студентов</p>
                        <p class="text-3xl font-bold text-blue-900">{{ stats?.totalStudents || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-users text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-md border border-green-200/50 hover:shadow-lg transition-all cursor-pointer group"
                 @click="safeVisit('admin.users.index', { role: 'partner' })">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Всего партнеров</p>
                        <p class="text-3xl font-bold text-green-900">{{ stats?.totalPartners || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-building text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 shadow-md border border-amber-200/50 hover:shadow-lg transition-all cursor-pointer group"
                 @click="safeVisit('admin.cases.index', { status: 'active' })">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 mb-1">Активные кейсы</p>
                        <p class="text-3xl font-bold text-amber-900">{{ stats?.activeCases || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-briefcase text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-md border border-purple-200/50 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 mb-1">Завершено за месяц</p>
                        <p class="text-3xl font-bold text-purple-900">{{ stats?.completedCases?.month || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl">
                        <i class="pi pi-check-circle text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Графики -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-chart-line text-indigo-600"></i>
                        Динамика регистраций
                    </h2>
                </div>
                <div class="p-6" v-if="chartData?.registrations">
                    <Chart
                        type="line"
                        :data="chartData.registrations"
                        :options="chartOptions"
                        class="h-80"
                    />
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-chart-line text-4xl mb-2"></i>
                    <p class="text-sm">Нет данных для отображения</p>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-chart-bar text-indigo-600"></i>
                        Статистика по кейсам
                    </h2>
                </div>
                <div class="p-6" v-if="chartData?.cases">
                    <Chart
                        type="bar"
                        :data="chartData.cases"
                        :options="chartOptions"
                        class="h-80"
                    />
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-chart-bar text-4xl mb-2"></i>
                    <p class="text-sm">Нет данных для отображения</p>
                </div>
            </div>
        </div>

        <!-- Топ навыков и распределение по курсам -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-star text-amber-500"></i>
                        Топ-5 навыков
                    </h2>
                </div>
                <div class="p-6" v-if="chartData?.topSkills && chartData.topSkills.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(skill, index) in chartData.topSkills"
                            :key="skill.id || index"
                            class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold">
                                    {{ index + 1 }}
                                </div>
                                <span class="font-semibold text-gray-900">{{ skill.name }}</span>
                            </div>
                            <span class="px-3 py-1.5 bg-indigo-100 text-indigo-800 text-sm font-semibold rounded-lg border border-indigo-200">
                                {{ skill.count }} пользователей
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-star text-4xl mb-2"></i>
                    <p class="text-sm">Нет данных для отображения</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-chart-pie text-indigo-600"></i>
                        Распределение студентов по курсам
                    </h2>
                </div>
                <div class="p-6" v-if="chartData?.studentsByCourse">
                    <Chart
                        type="doughnut"
                        :data="chartData.studentsByCourse"
                        :options="chartOptions"
                        class="h-80"
                    />
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-chart-pie text-4xl mb-2"></i>
                    <p class="text-sm">Нет данных для отображения</p>
                </div>
            </div>
        </div>

        <!-- Последние активности -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-user-plus text-blue-600"></i>
                        Новые регистрации
                    </h2>
                </div>
                <div class="p-6" v-if="recentActivities?.registrations && recentActivities.registrations.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(user, index) in recentActivities.registrations.slice(0, 5)"
                            :key="user.id || index"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200"
                        >
                            <div class="flex items-center gap-3">
                                <UserAvatar :user="user" size="sm" />
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(user.created_at) }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg border border-blue-200">
                                {{ user.roles?.[0] || 'student' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-user-plus text-4xl mb-2"></i>
                    <p class="text-sm">Нет новых регистраций</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-briefcase text-amber-600"></i>
                        Последние кейсы
                    </h2>
                </div>
                <div class="p-6" v-if="recentActivities?.createdCases && recentActivities.createdCases.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(caseItem, index) in recentActivities.createdCases.slice(0, 5)"
                            :key="caseItem.id || index"
                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200"
                        >
                            <p class="text-sm font-semibold text-gray-900 mb-1">{{ caseItem.title }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(caseItem.created_at) }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-briefcase text-4xl mb-2"></i>
                    <p class="text-sm">Нет созданных кейсов</p>
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
                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200"
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
        </div>

        <!-- Быстрые действия -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-indigo-200">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-bolt text-indigo-600"></i>
                    Быстрые действия
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <button
                        @click="safeVisit('admin.cases.create')"
                        class="flex flex-col items-center justify-center gap-2 p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl border border-indigo-200 hover:shadow-lg transition-all group"
                    >
                        <div class="w-12 h-12 flex items-center justify-center bg-indigo-500 rounded-xl group-hover:scale-110 transition-transform">
                            <i class="pi pi-plus text-white text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">Создать кейс</span>
                    </button>
                    <button
                        @click="safeVisit('admin.skills.index')"
                        class="flex flex-col items-center justify-center gap-2 p-6 bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl border border-amber-200 hover:shadow-lg transition-all group"
                    >
                        <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform">
                            <i class="pi pi-star text-white text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">Управление навыками</span>
                    </button>
                    <button
                        @click="safeVisit('admin.badges.index')"
                        class="flex flex-col items-center justify-center gap-2 p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 hover:shadow-lg transition-all group"
                    >
                        <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl group-hover:scale-110 transition-transform">
                            <i class="pi pi-award text-white text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">Управление бейджами</span>
                    </button>
                    <button
                        @click="safeVisit('admin.users.index')"
                        class="flex flex-col items-center justify-center gap-2 p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:shadow-lg transition-all group"
                    >
                        <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform">
                            <i class="pi pi-users text-white text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">Все пользователи</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import Chart from 'primevue/chart';
import UserAvatar from '@/Components/Shared/UserAvatar.vue';
import { routeExists } from '@/Utils/routes';
import { route } from 'ziggy-js';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    recentActivities: {
        type: Object,
        default: () => ({}),
    },
    chartData: {
        type: Object,
        default: () => ({}),
    },
});

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
}));

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
