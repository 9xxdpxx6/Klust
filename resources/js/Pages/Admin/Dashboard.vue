<template>
    <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-text-primary mb-2">Панель управления</h1>
                <p class="text-text-secondary">Обзор системы и статистика</p>
            </div>

            <FlashMessage />

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatsWidget
                    title="Всего студентов"
                    :value="stats?.totalStudents || 0"
                    icon="pi pi-users"
                    variant="primary"
                    :link="safeRoute('admin.users.index', { role: 'student' })"
                />
                <StatsWidget
                    title="Всего партнеров"
                    :value="stats?.totalPartners || 0"
                    icon="pi pi-building"
                    variant="success"
                    :link="safeRoute('admin.users.index', { role: 'partner' })"
                />
                <StatsWidget
                    title="Активные кейсы"
                    :value="stats?.activeCases || 0"
                    icon="pi pi-briefcase"
                    variant="warning"
                    :link="safeRoute('admin.cases.index', { status: 'active' })"
                />
                <StatsWidget
                    title="Завершено за месяц"
                    :value="stats?.completedCases?.month || 0"
                    icon="pi pi-check-circle"
                    variant="success"
                />
            </div>

            <!-- Графики -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card title="Динамика регистраций" v-if="chartData?.registrations">
                    <Chart
                        type="line"
                        :data="chartData.registrations"
                        :options="chartOptions"
                        class="h-80"
                    />
                </Card>
                
                <Card title="Статистика по кейсам" v-if="chartData?.cases">
                    <Chart
                        type="bar"
                        :data="chartData.cases"
                        :options="chartOptions"
                        class="h-80"
                    />
                </Card>
            </div>

            <!-- Топ навыков и распределение по курсам -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card title="Топ-5 навыков" v-if="chartData?.topSkills && chartData.topSkills.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(skill, index) in chartData.topSkills"
                            :key="skill.id || index"
                            class="flex items-center justify-between p-3 bg-surface rounded-lg"
                        >
                            <div class="flex items-center">
                                <span class="text-lg font-bold text-primary mr-3">#{{ index + 1 }}</span>
                                <span class="font-medium">{{ skill.name }}</span>
                            </div>
                            <Badge variant="primary">{{ skill.count }} пользователей</Badge>
                        </div>
                    </div>
                </Card>

                <Card title="Распределение студентов по курсам" v-if="chartData?.studentsByCourse">
                    <Chart
                        type="doughnut"
                        :data="chartData.studentsByCourse"
                        :options="chartOptions"
                        class="h-80"
                    />
                </Card>
            </div>

            <!-- Последние активности -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <Card title="Новые регистрации" v-if="recentActivities?.registrations">
                    <div class="space-y-3">
                        <div
                            v-for="(user, index) in recentActivities.registrations.slice(0, 5)"
                            :key="user.id || index"
                            class="flex items-center justify-between p-2 hover:bg-surface rounded transition-colors"
                        >
                            <div class="flex items-center">
                                <UserAvatar :user="user" size="sm" class="mr-2" />
                                <div>
                                    <p class="text-sm font-medium">{{ user.name }}</p>
                                    <p class="text-xs text-text-muted">{{ formatDate(user.created_at) }}</p>
                                </div>
                            </div>
                            <Badge variant="secondary" size="sm">{{ user.roles?.[0] || 'student' }}</Badge>
                        </div>
                    </div>
                </Card>

                <Card title="Последние кейсы" v-if="recentActivities?.createdCases">
                    <div class="space-y-3">
                        <div
                            v-for="(caseItem, index) in recentActivities.createdCases.slice(0, 5)"
                            :key="caseItem.id || index"
                            class="p-2 hover:bg-surface rounded transition-colors"
                        >
                            <p class="text-sm font-medium">{{ caseItem.title }}</p>
                            <p class="text-xs text-text-muted">{{ formatDate(caseItem.created_at) }}</p>
                        </div>
                    </div>
                </Card>

                <Card title="Завершенные кейсы" v-if="recentActivities?.completedCases">
                    <div class="space-y-3">
                        <div
                            v-for="(caseItem, index) in recentActivities.completedCases.slice(0, 5)"
                            :key="caseItem.id || index"
                            class="p-2 hover:bg-surface rounded transition-colors"
                        >
                            <p class="text-sm font-medium">{{ caseItem.title }}</p>
                            <p class="text-xs text-text-muted">{{ formatDate(caseItem.completed_at || caseItem.updated_at) }}</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Быстрые действия -->
            <Card title="Быстрые действия">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Button
                        variant="primary"
                        icon="pi pi-plus"
                        @click="safeVisit('admin.cases.create')"
                    >
                        Создать кейс
                    </Button>
                    <Button
                        variant="secondary"
                        icon="pi pi-star"
                        @click="safeVisit('admin.skills.index')"
                    >
                        Управление навыками
                    </Button>
                    <Button
                        variant="secondary"
                        icon="pi pi-award"
                        @click="safeVisit('admin.badges.index')"
                    >
                        Управление бейджами
                    </Button>
                    <Button
                        variant="secondary"
                        icon="pi pi-users"
                        @click="safeVisit('admin.users.index')"
                    >
                        Все пользователи
                    </Button>
                </div>
            </Card>
        </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import StatsWidget from '@/Components/UI/StatsWidget.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Chart from 'primevue/chart';
import UserAvatar from '@/Components/Shared/UserAvatar.vue';
import { routeExists, getRouteUrl } from '@/Utils/routes';

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

// Безопасные функции для получения роутов
const safeRoute = (routeName, params = {}) => {
    if (routeExists(routeName)) {
        try {
            return route(routeName, params);
        } catch (e) {
            return null;
        }
    }
    return null;
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

