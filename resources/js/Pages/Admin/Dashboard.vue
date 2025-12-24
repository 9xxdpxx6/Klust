<template>
    <MobileContainer :padding="false">
        <div :class="[
            isMobile ? 'space-y-4' : 'space-y-6'
        ]">
            <Head title="Панель управления" />
            <!-- Заголовок с градиентом -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <h1 :class="[
                        'font-bold text-white mb-2',
                        isMobile ? 'text-2xl' : 'text-3xl'
                    ]">Панель управления</h1>
                    <p class="text-indigo-100" :class="isMobile ? 'text-sm' : ''">
                        Обзор системы и статистика
                    </p>
                </div>
            </div>

            <!-- Статистика -->
            <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 2, large: 4 }">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md border border-blue-200/50 hover:shadow-lg transition-all cursor-pointer group"
                     :class="isMobile ? 'p-4' : 'p-6'"
                     @click="safeVisit('admin.users.index', { role: 'student' })">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-blue-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Всего студентов</p>
                            <p :class="[
                                'font-bold text-blue-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ stats?.totalStudents || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-users text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md border border-green-200/50 hover:shadow-lg transition-all cursor-pointer group"
                     :class="isMobile ? 'p-4' : 'p-6'"
                     @click="safeVisit('admin.users.index', { role: 'partner' })">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-green-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Всего партнеров</p>
                            <p :class="[
                                'font-bold text-green-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ stats?.totalPartners || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-green-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-building text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-md border border-amber-200/50 hover:shadow-lg transition-all cursor-pointer group"
                     :class="isMobile ? 'p-4' : 'p-6'"
                     @click="safeVisit('admin.cases.index', { status: 'active' })">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-amber-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Активные кейсы</p>
                            <p :class="[
                                'font-bold text-amber-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ stats?.activeCases || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-briefcase text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-md border border-purple-200/50 hover:shadow-lg transition-all"
                     :class="isMobile ? 'p-4' : 'p-6'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-purple-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Завершено за месяц</p>
                            <p :class="[
                                'font-bold text-purple-900',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ stats?.completedCases?.month || 0 }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-purple-500 rounded-xl',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-check-circle text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>
            </ResponsiveGrid>

            <!-- Графики -->
            <ResponsiveGrid :cols="{ mobile: 1, desktop: 2 }" :class="isMobile ? 'gap-4' : 'gap-6'">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-chart-line text-indigo-600"></i>
                        Динамика регистраций
                    </h2>
                </div>
                <div :class="[
                    isMobile ? 'p-4' : 'p-6'
                ]" v-if="chartData?.registrations">
                    <Chart
                        type="line"
                        :data="chartData.registrations"
                        :options="chartOptions"
                        class="h-80"
                    />
                </div>
                <div v-else :class="[
                    'text-center text-gray-400',
                    isMobile ? 'p-6' : 'p-12'
                ]">
                    <i :class="[
                        'pi pi-chart-line mb-2',
                        isMobile ? 'text-2xl' : 'text-4xl'
                    ]"></i>
                    <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет данных для отображения</p>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-chart-bar text-indigo-600"></i>
                        Статистика по кейсам
                    </h2>
                </div>
                <div :class="[
                    isMobile ? 'p-4' : 'p-6'
                ]" v-if="chartData?.cases">
                    <Chart
                        type="bar"
                        :data="chartData.cases"
                        :options="barChartOptions"
                        class="h-80"
                    />
                </div>
                <div v-else :class="[
                    'text-center text-gray-400',
                    isMobile ? 'p-6' : 'p-12'
                ]">
                    <i :class="[
                        'pi pi-chart-bar mb-2',
                        isMobile ? 'text-2xl' : 'text-4xl'
                    ]"></i>
                    <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет данных для отображения</p>
                </div>
            </div>
            </ResponsiveGrid>

            <!-- Топ навыков и распределение по курсам -->
            <ResponsiveGrid :cols="{ mobile: 1, desktop: 2 }" :class="isMobile ? 'gap-4' : 'gap-6'">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-star text-amber-500"></i>
                        Топ-5 навыков
                    </h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]" v-if="chartData?.topSkills && chartData.topSkills.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(skill, index) in chartData.topSkills"
                            :key="skill.id || index"
                            :class="[
                                'bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all',
                                isMobile ? 'p-3 flex flex-col gap-2' : 'p-4 flex flex-wrap items-center gap-3'
                            ]"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="[
                                    'bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold flex-shrink-0',
                                    isMobile ? 'w-8 h-8 text-sm' : 'w-10 h-10'
                                ]">
                                    {{ index + 1 }}
                                </div>
                                <span :class="[
                                    'font-semibold text-gray-900 whitespace-normal break-words',
                                    isMobile ? 'text-sm' : ''
                                ]">{{ skill.name }}</span>
                            </div>
                            <span :class="[
                                'bg-indigo-100 text-indigo-800 font-semibold rounded-lg border border-indigo-200 flex-shrink-0',
                                isMobile ? 'px-2 py-1 text-xs w-fit' : 'px-3 py-1.5 text-sm'
                            ]">
                                {{ skill.count }} пользователей
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else :class="[
                    'text-center text-gray-400',
                    isMobile ? 'p-6' : 'p-12'
                ]">
                    <i :class="[
                        'pi pi-star mb-2',
                        isMobile ? 'text-2xl' : 'text-4xl'
                    ]"></i>
                    <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет данных для отображения</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-chart-pie text-indigo-600"></i>
                        Распределение студентов по курсам
                    </h2>
                </div>
                <div :class="[
                    isMobile ? 'p-4' : 'p-6'
                ]" v-if="chartData?.studentsByCourse">
                    <Chart
                        type="doughnut"
                        :data="chartData.studentsByCourse"
                        :options="doughnutChartOptions"
                        class="h-80"
                    />
                </div>
                <div v-else :class="[
                    'text-center text-gray-400',
                    isMobile ? 'p-6' : 'p-12'
                ]">
                    <i :class="[
                        'pi pi-chart-pie mb-2',
                        isMobile ? 'text-2xl' : 'text-4xl'
                    ]"></i>
                    <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет данных для отображения</p>
                </div>
            </div>
            </ResponsiveGrid>

            <!-- Последние активности -->
            <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3 }" :class="isMobile ? 'gap-4' : 'gap-6'">
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-user-plus text-blue-600"></i>
                        Новые регистрации
                    </h2>
                </div>
                <div :class="[
                    isMobile ? 'p-4' : 'p-6'
                ]" v-if="recentActivities?.registrations && recentActivities.registrations.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(user, index) in recentActivities.registrations.slice(0, 5)"
                            :key="user.id || index"
                            @click="navigateToUser(user)"
                            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition-all border border-gray-200 cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <UserAvatar :user="user" size="sm" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(user.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else :class="[
                    'text-center text-gray-400',
                    isMobile ? 'p-6' : 'p-12'
                ]">
                    <i :class="[
                        'pi pi-user-plus mb-2',
                        isMobile ? 'text-2xl' : 'text-4xl'
                    ]"></i>
                    <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет новых регистраций</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200',
                    isMobile ? 'px-4 py-3' : 'px-6 py-4'
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-briefcase text-amber-600"></i>
                        Последние кейсы
                    </h2>
                </div>
                <div :class="[
                    isMobile ? 'p-4' : 'p-6'
                ]" v-if="recentActivities?.createdCases && recentActivities.createdCases.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(caseItem, index) in recentActivities.createdCases.slice(0, 5)"
                            :key="caseItem.id || index"
                            @click="navigateToCase(caseItem)"
                            class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition-all border border-gray-200 cursor-pointer"
                        >
                            <p class="text-sm font-semibold text-gray-900 mb-1">{{ caseItem.title }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(caseItem.created_at) }}</p>
                        </div>
                    </div>
                </div>
                <div v-else :class="[
                    'text-center text-gray-400',
                    isMobile ? 'p-6' : 'p-12'
                ]">
                    <i :class="[
                        'pi pi-briefcase mb-2',
                        isMobile ? 'text-2xl' : 'text-4xl'
                    ]"></i>
                    <p :class="isMobile ? 'text-xs' : 'text-sm'">Нет созданных кейсов</p>
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
                </div>
                <div :class="[
                    isMobile ? 'p-4' : 'p-6'
                ]" v-if="recentActivities?.completedCases && recentActivities.completedCases.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(caseItem, index) in recentActivities.completedCases.slice(0, 5)"
                            :key="caseItem.id || index"
                            @click="navigateToCase(caseItem)"
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
                            @click="safeVisit('admin.cases.create')"
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
                            @click="safeVisit('admin.skills.index')"
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
                                    'pi pi-star text-white',
                                    isMobile ? 'text-lg' : 'text-xl'
                                ]"></i>
                            </div>
                            <span :class="[
                                'font-semibold text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Управление навыками</span>
                        </button>
                        <button
                            @click="safeVisit('admin.badges.index')"
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
                                    'pi pi-trophy text-white',
                                    isMobile ? 'text-lg' : 'text-xl'
                                ]"></i>
                            </div>
                            <span :class="[
                                'font-semibold text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Управление достижениями</span>
                        </button>
                        <button
                            @click="safeVisit('admin.users.index')"
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
                                    'pi pi-users text-white',
                                    isMobile ? 'text-lg' : 'text-xl'
                                ]"></i>
                            </div>
                            <span :class="[
                                'font-semibold text-gray-900',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Все пользователи</span>
                        </button>
                    </ResponsiveGrid>
                </div>
            </div>
        </div>
    </MobileContainer>
</template>

<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StatsWidget from '@/Components/UI/StatsWidget.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Chart from 'primevue/chart';
import UserAvatar from '@/Components/Shared/UserAvatar.vue';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue';
import { useResponsive } from '@/Composables/useResponsive';
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

const { isMobile, padding } = useResponsive();

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

const barChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
}));

const doughnutChartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
    },
    // Для круговых диаграмм отключаем оси и сетку
    scales: {
        x: {
            display: false,
        },
        y: {
            display: false,
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

const navigateToUser = (user) => {
    if (!user?.id) return;
    
    // Пытаемся перейти на show страницу пользователя
    if (routeExists('admin.users.show')) {
        try {
            router.visit(route('admin.users.show', user.id));
        } catch (e) {
            // Если show не работает, переходим на index с фильтром по ID
            navigateToUserIndex(user);
        }
    } else {
        // Если маршрута show нет, переходим на index с фильтром
        navigateToUserIndex(user);
    }
};

const navigateToUserIndex = (user) => {
    if (!user?.id) return;
    
    // Переходим на index с поиском по ID или имени
    const params = {};
    if (user.id) {
        params.search = user.id.toString();
    } else if (user.name) {
        params.search = user.name;
    }
    
    if (routeExists('admin.users.index')) {
        try {
            router.get(route('admin.users.index'), params, {
                preserveState: true,
                preserveScroll: true,
            });
        } catch (e) {
            console.warn('Failed to navigate to users index');
        }
    }
};

const navigateToCase = (caseItem) => {
    if (!caseItem?.id) {
        console.warn('Case item has no ID:', caseItem);
        return;
    }
    
    // Переходим на show страницу кейса
    try {
        // В Ziggy для route model binding нужно передать объект с ключом, соответствующим параметру маршрута
        router.visit(route('admin.cases.show', { case: caseItem.id }));
    } catch (e) {
        console.error('Failed to navigate to case show page:', e);
        console.error('Case item:', caseItem);
        // Fallback: попробуем напрямую через URL
        router.visit(`/admin/cases/${caseItem.id}`);
    }
};
</script>
