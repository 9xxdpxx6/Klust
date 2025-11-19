<template>
    <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-text-primary mb-2">Панель студента</h1>
                <p class="text-text-secondary">Ваш прогресс и активность</p>
            </div>

            <!-- Личная статистика -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <StatsWidget
                    title="Уровень"
                    :value="stats?.level || 1"
                    icon="pi pi-star"
                    variant="primary"
                />
                <StatsWidget
                    title="Рейтинг"
                    :value="stats?.rating || 0"
                    icon="pi pi-chart-line"
                    variant="success"
                />
                <StatsWidget
                    title="Очки"
                    :value="stats?.totalPoints || 0"
                    icon="pi pi-trophy"
                    variant="warning"
                />
                <StatsWidget
                    title="Завершено кейсов"
                    :value="stats?.completedCases || 0"
                    icon="pi pi-check-circle"
                    variant="success"
                />
                <StatsWidget
                    title="Бейджи"
                    :value="stats?.badgesCount || 0"
                    icon="pi pi-award"
                    variant="primary"
                />
            </div>

            <!-- Активные кейсы и рекомендации -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card title="Активные кейсы" v-if="activeCases && activeCases.length > 0">
                    <div class="space-y-4">
                        <CaseCard
                            v-for="caseItem in activeCases"
                            :key="caseItem.id"
                            :case-data="caseItem"
                            :show-actions="false"
                        />
                    </div>
                </Card>
                <Card v-else title="Активные кейсы">
                    <p class="text-text-muted text-center py-8">У вас нет активных кейсов</p>
                </Card>

                <Card title="Рекомендуемые кейсы" v-if="recommendations && recommendations.length > 0">
                    <div class="space-y-4">
                        <CaseCard
                            v-for="caseItem in recommendations"
                            :key="caseItem.id"
                            :case-data="caseItem"
                            :can-apply="true"
                            @apply="handleApplyCase(caseItem.id)"
                        />
                    </div>
                </Card>
                <Card v-else title="Рекомендуемые кейсы">
                    <p class="text-text-muted text-center py-8">Нет рекомендаций</p>
                </Card>
            </div>

            <!-- Прогресс по навыкам -->
            <Card title="Прогресс по навыкам" v-if="skillsProgress && skillsProgress.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <SkillCard
                        v-for="skill in skillsProgress.slice(0, 6)"
                        :key="skill.id"
                        :skill="skill"
                        :level="skill.level"
                        :progress="skill.progress"
                        :points="skill.points"
                    />
                </div>
                <div class="mt-4 text-center">
                    <Button
                        variant="outline"
                        @click="safeVisit('student.skills.index')"
                    >
                        Посмотреть все навыки
                    </Button>
                </div>
            </Card>

            <!-- Последние достижения -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <Card title="Полученные бейджи" v-if="recentAchievements?.badges && recentAchievements.badges.length > 0">
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            v-for="badge in recentAchievements.badges.slice(0, 6)"
                            :key="badge.id"
                            class="flex flex-col items-center p-3 bg-surface rounded-lg"
                        >
                            <img
                                v-if="badge.image"
                                :src="badge.image"
                                :alt="badge.name"
                                class="w-16 h-16 object-contain mb-2"
                            />
                            <p class="text-xs text-center font-medium">{{ badge.name }}</p>
                        </div>
                    </div>
                </Card>

                <Card title="Новые навыки" v-if="recentAchievements?.skills && recentAchievements.skills.length > 0">
                    <div class="space-y-2">
                        <div
                            v-for="skill in recentAchievements.skills.slice(0, 5)"
                            :key="skill.id"
                            class="flex items-center justify-between p-2 bg-surface rounded"
                        >
                            <span class="font-medium">{{ skill.name }}</span>
                            <Badge variant="primary" size="sm">Уровень {{ skill.level }}</Badge>
                        </div>
                    </div>
                </Card>

                <Card title="Завершенные кейсы" v-if="recentAchievements?.completedCases && recentAchievements.completedCases.length > 0">
                    <div class="space-y-2">
                        <div
                            v-for="caseItem in recentAchievements.completedCases.slice(0, 5)"
                            :key="caseItem.id"
                            class="p-2 bg-surface rounded"
                        >
                            <p class="font-medium text-sm">{{ caseItem.title }}</p>
                            <p class="text-xs text-text-muted">{{ formatDate(caseItem.completed_at) }}</p>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3';
import StatsWidget from '@/Components/UI/StatsWidget.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import CaseCard from '@/Components/CaseCard.vue';
import SkillCard from '@/Components/SkillCard.vue';
import { routeExists } from '@/Utils/routes';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    activeCases: {
        type: Array,
        default: () => [],
    },
    recommendations: {
        type: Array,
        default: () => [],
    },
    recentAchievements: {
        type: Object,
        default: () => ({}),
    },
    skillsProgress: {
        type: Array,
        default: () => [],
    },
});

const handleApplyCase = (caseId) => {
    if (routeExists('student.cases.apply')) {
        router.post(route('student.cases.apply', caseId), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Flash message will be shown automatically
            },
        });
    }
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

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

