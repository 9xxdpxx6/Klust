<template>
    <div class="space-y-6">
        <Head title="Панель студента" />
        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <h1 class="text-3xl font-bold text-white mb-2">Панель студента</h1>
                <p class="text-indigo-100">Ваш прогресс и активность</p>
            </div>
        </div>

        <!-- Личная статистика -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-md border border-blue-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Уровень</p>
                        <p class="text-3xl font-bold text-blue-900">{{ statistics?.level || 1 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-star text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-md border border-green-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Рейтинг</p>
                        <p class="text-3xl font-bold text-green-900">{{ statistics?.rating || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-chart-line text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 shadow-md border border-amber-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 mb-1">Очки</p>
                        <p class="text-3xl font-bold text-amber-900">{{ statistics?.total_points || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-trophy text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-md border border-purple-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 mb-1">Завершено кейсов</p>
                        <p class="text-3xl font-bold text-purple-900">{{ statistics?.completed_cases || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-check-circle text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-6 shadow-md border border-pink-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-pink-600 mb-1">Достижения</p>
                        <p class="text-3xl font-bold text-pink-900">{{ statistics?.badges_count || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-pink-500 rounded-xl group-hover:scale-110 transition-transform">
                        <i class="pi pi-trophy text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Активные кейсы и рекомендации -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Активные кейсы -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-briefcase text-indigo-600"></i>
                            Активные кейсы
                        </h2>
                        <Button
                            variant="outline"
                            size="sm"
                            class="whitespace-nowrap"
                            @click="safeVisit('student.cases.my')"
                        >
                            Смотреть все
                        </Button>
                    </div>
                </div>
                <div class="p-6" v-if="activeCases && activeCases.length > 0">
                    <div class="space-y-4">
                        <CaseCard
                            v-for="caseItem in activeCases.slice(0, 5)"
                            :key="caseItem.id"
                            :case-data="caseItem"
                            :show-actions="true"
                            @view="handleViewCase(caseItem.id)"
                        />
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-briefcase text-4xl mb-2"></i>
                    <p class="text-sm">У вас нет активных кейсов</p>
                </div>
            </div>

            <!-- Рекомендуемые кейсы -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-star text-green-600"></i>
                            Рекомендуемые кейсы
                        </h2>
                        <Button
                            variant="outline"
                            size="sm"
                            class="whitespace-nowrap"
                            @click="safeVisit('student.cases.recommended')"
                        >
                            Смотреть все
                        </Button>
                    </div>
                </div>
                <div class="p-6" v-if="recommendations && recommendations.length > 0">
                    <div class="space-y-4">
                        <GuestCaseCard
                            v-for="caseItem in recommendations.slice(0, 5)"
                            :key="caseItem.id"
                            :case-data="caseItem"
                            :show-link="false"
                            :show-team-size="false"
                            :show-student-actions="true"
                            :can-apply="!caseItem.user_application"
                            :has-application="!!caseItem.user_application"
                            :application-status="caseItem.user_application"
                            @view="() => handleViewCase(caseItem.id)"
                            @apply="() => handleApply(caseItem.id)"
                        />
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-star text-4xl mb-2"></i>
                    <p class="text-sm">Нет рекомендаций</p>
                </div>
            </div>
        </div>

        <!-- Прогресс по навыкам -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden" v-if="skillsProgress && skillsProgress.length > 0">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-chart-line text-amber-600"></i>
                    Прогресс по навыкам
                </h2>
            </div>
            <div class="p-6">
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
                <div class="mt-6 text-center">
                    <Button
                        variant="outline"
                        @click="safeVisit('student.skills.index')"
                    >
                        Посмотреть все навыки
                    </Button>
                </div>
            </div>
        </div>

        <!-- Последние достижения -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Полученные достижения -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-trophy text-purple-600"></i>
                        Полученные достижения
                    </h2>
                </div>
                <div class="p-6" v-if="recentAchievements?.badges && recentAchievements.badges.length > 0">
                    <div class="grid grid-cols-3 gap-3">
                        <div
                            v-for="badge in recentAchievements.badges.slice(0, 6)"
                            :key="badge.id"
                            class="text-center p-3 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all"
                        >
                            <div class="flex justify-center mb-2">
                                <div class="w-12 h-12 flex items-center justify-center bg-amber-100 rounded-lg">
                                    <img
                                        v-if="badge.icon_path || badge.image"
                                        :src="badge.icon_path || badge.image"
                                        :alt="badge.name"
                                        class="w-8 h-8 object-contain"
                                    />
                                    <i
                                        v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                                        :class="['text-amber-600 text-lg', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"
                                    ></i>
                                    <i v-else class="pi pi-trophy text-amber-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="font-semibold text-gray-900 text-xs mb-1">{{ badge.name }}</div>
                            <div v-if="badge.earned_at" class="text-xs text-gray-500">{{ formatDate(badge.earned_at) }}</div>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-trophy text-4xl mb-2"></i>
                    <p class="text-sm">Нет полученных достижений</p>
                </div>
            </div>

            <!-- Новые навыки -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-star text-blue-600"></i>
                        Новые навыки
                    </h2>
                </div>
                <div class="p-6" v-if="recentAchievements?.skills && recentAchievements.skills.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="skill in recentAchievements.skills.slice(0, 5)"
                            :key="skill.id"
                            class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all"
                        >
                            <span class="text-sm font-semibold text-gray-900">{{ skill.name }}</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg border border-blue-200">
                                Уровень {{ skill.level }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-star text-4xl mb-2"></i>
                    <p class="text-sm">Нет новых навыков</p>
                </div>
            </div>

            <!-- Завершенные кейсы -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-check-circle text-green-600"></i>
                        Завершенные кейсы
                    </h2>
                </div>
                <div class="p-6" v-if="recentAchievements?.completedCases && recentAchievements.completedCases.length > 0">
                    <div class="space-y-3">
                        <div
                            v-for="caseItem in recentAchievements.completedCases.slice(0, 5)"
                            :key="caseItem.id"
                            class="p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all"
                        >
                            <p class="text-sm font-semibold text-gray-900 mb-1">{{ caseItem.title }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(caseItem.completed_at) }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="p-12 text-center text-gray-400">
                    <i class="pi pi-check-circle text-4xl mb-2"></i>
                    <p class="text-sm">Нет завершенных кейсов</p>
                </div>
            </div>
        </div>

        <!-- Apply Modal -->
        <ApplyCaseModal
            v-if="selectedCase"
            v-model="showApplyModal"
            :case-data="selectedCase"
            @success="handleApplySuccess"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import CaseCard from '@/Components/CaseCard.vue';
import GuestCaseCard from '@/Components/GuestCaseCard.vue';
import ApplyCaseModal from '@/Components/ApplyCaseModal.vue';
import SkillCard from '@/Components/SkillCard.vue';
import { routeExists } from '@/Utils/routes';

const props = defineProps({
    statistics: {
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

const handleViewCase = (caseId) => {
    try {
        router.visit(route('student.cases.show', caseId));
    } catch (e) {
        console.error('Error navigating to case:', e);
    }
};

const showApplyModal = ref(false);
const selectedCase = ref(null);

const handleApply = (caseId) => {
    try {
        const caseItem = props.recommendations.find(c => c.id === caseId);
        if (caseItem) {
            selectedCase.value = caseItem;
            showApplyModal.value = true;
        }
    } catch (e) {
        console.error('Error opening apply modal:', e);
    }
};

const handleApplySuccess = () => {
    // Обновляем данные после успешной подачи заявки
    router.reload({ only: ['recommendations'] });
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

