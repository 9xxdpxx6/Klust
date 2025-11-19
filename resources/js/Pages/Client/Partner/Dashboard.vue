<template>
    <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-text-primary mb-2">Панель партнера</h1>
                <p class="text-text-secondary">Управление кейсами и аналитика</p>
            </div>

            <!-- Статистика компании -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <StatsWidget
                    title="Всего кейсов"
                    :value="stats?.totalCases || 0"
                    icon="pi pi-briefcase"
                    variant="primary"
                />
                <StatsWidget
                    title="Активные кейсы"
                    :value="stats?.activeCases || 0"
                    icon="pi pi-check-circle"
                    variant="success"
                    :link="safeRoute('partner.cases.index', { status: 'active' })"
                />
                <StatsWidget
                    title="Завершенные"
                    :value="stats?.completedCases || 0"
                    icon="pi pi-check"
                    variant="success"
                />
                <StatsWidget
                    title="Команды"
                    :value="stats?.teamsCount || 0"
                    icon="pi pi-users"
                    variant="warning"
                    :link="safeRoute('partner.teams.index')"
                />
                <StatsWidget
                    v-if="stats?.averageRating"
                    title="Средняя оценка"
                    :value="stats.averageRating.toFixed(1)"
                    icon="pi pi-star"
                    variant="primary"
                />
            </div>

            <!-- Графики -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card title="Динамика создания кейсов" v-if="charts?.casesCreated">
                    <Chart
                        type="line"
                        :data="charts.casesCreated"
                        :options="chartOptions"
                        class="h-80"
                    />
                </Card>

                <Card title="Популярность кейсов" v-if="charts?.casesPopularity">
                    <Chart
                        type="bar"
                        :data="charts.casesPopularity"
                        :options="chartOptions"
                        class="h-80"
                    />
                </Card>
            </div>

            <!-- Активные кейсы -->
            <Card title="Активные кейсы" v-if="activeCases && activeCases.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <CaseCard
                        v-for="caseItem in activeCases"
                        :key="caseItem.id"
                        :case-data="caseItem"
                        :can-edit="true"
                        @view="safeVisit('partner.cases.show', caseItem.id)"
                        @edit="safeVisit('partner.cases.edit', caseItem.id)"
                    />
                </div>
            </Card>

            <!-- Последние активности -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <Card title="Новые заявки" v-if="recentActivities?.newApplications && recentActivities.newApplications.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(application, index) in recentActivities.newApplications.slice(0, 5)"
                            :key="application.id || index"
                            class="flex items-center justify-between p-2 hover:bg-surface rounded transition-colors cursor-pointer"
                            @click="safeVisit('partner.cases.show', application.case_id)"
                        >
                            <div>
                                <p class="text-sm font-medium">{{ application.case?.title || 'Кейс' }}</p>
                                <p class="text-xs text-text-muted">{{ formatDate(application.created_at) }}</p>
                            </div>
                            <Badge variant="warning" size="sm">Новая</Badge>
                        </div>
                    </div>
                </Card>

                <Card title="Завершенные кейсы" v-if="recentActivities?.completedCases && recentActivities.completedCases.length > 0">
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

                <Card title="Новые команды" v-if="recentActivities?.newTeams && recentActivities.newTeams.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="(team, index) in recentActivities.newTeams.slice(0, 5)"
                            :key="team.id || index"
                            class="p-2 hover:bg-surface rounded transition-colors"
                        >
                            <p class="text-sm font-medium">{{ team.case?.title || 'Команда' }}</p>
                            <p class="text-xs text-text-muted">{{ team.members_count || 0 }} участников</p>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import StatsWidget from '@/Components/UI/StatsWidget.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import CaseCard from '@/Components/CaseCard.vue';
import Chart from 'primevue/chart';
import { routeExists } from '@/Utils/routes';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    charts: {
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

