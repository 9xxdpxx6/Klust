<template>
    <div class="space-y-6">
        <Head :title="`Профиль: ${student.name}`" />
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <Link
                        :href="route('partner.teams.index')"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium mb-2 inline-flex items-center"
                    >
                        <i class="pi pi-arrow-left mr-1"></i>
                        Назад к командам
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">{{ student.name }}</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ student.email }}</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Всего баллов</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ statistics.total_points || 0 }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Навыков</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ statistics.skills_count || 0 }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Бейджей</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ statistics.badges_count || 0 }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">Завершенных кейсов</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ statistics.completed_cases || 0 }}</p>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Информация о студенте</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="flex items-center space-x-4">
                            <img
                                :src="student.avatar_url || student.avatar || '/images/default-avatar.png'"
                                :alt="student.name"
                                class="h-20 w-20 rounded-full object-cover"
                            />
                            <div>
                                <p class="text-lg font-semibold text-gray-900">{{ student.name }}</p>
                                <p class="text-sm text-gray-500">{{ student.email }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div v-if="studentProfile?.faculty">
                            <p class="text-sm font-medium text-gray-500">Факультет</p>
                            <p class="text-sm text-gray-900">
                                {{ studentProfile.faculty?.name || studentProfile.faculty }}
                            </p>
                        </div>
                        <div v-if="studentProfile?.course">
                            <p class="text-sm font-medium text-gray-500">Курс</p>
                            <p class="text-sm text-gray-900">{{ studentProfile.course }} курс</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Skills -->
            <div class="bg-white shadow-sm rounded-lg p-6" v-if="skills && skills.length > 0">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Навыки</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="skill in skills"
                        :key="skill.id"
                        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-base font-semibold text-gray-900">{{ skill.name }}</h3>
                            <span
                                class="px-2 py-1 rounded text-xs font-medium"
                                :class="getLevelColor(skill.pivot?.level || 0)"
                            >
                                Уровень {{ skill.pivot?.level || 0 }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">
                            {{ skill.pivot?.points_earned || 0 }} очков
                        </p>
                    </div>
                </div>
            </div>

            <!-- Badges -->
            <div class="bg-white shadow-sm rounded-lg p-6" v-if="badges && badges.length > 0">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Бейджи и достижения</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div
                        v-for="badge in badges"
                        :key="badge.id"
                        class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                        :title="badge.name"
                    >
                        <!-- Icon as image file -->
                        <img
                            v-if="badge.icon_path"
                            :src="badge.icon_path"
                            :alt="badge.name"
                            class="w-16 h-16 mb-2 object-contain"
                        />
                        <!-- Icon as CSS class (PrimeVue icon) -->
                        <div
                            v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                            class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-2"
                        >
                            <i :class="['text-4xl text-amber-600', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                        </div>
                        <!-- Fallback icon -->
                        <div v-else class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-2">
                            <span class="text-4xl">🏆</span>
                        </div>
                        <p class="text-xs font-medium text-gray-900 text-center">{{ badge.name }}</p>
                        <p
                            v-if="badge.pivot?.earned_at"
                            class="text-xs text-gray-500 mt-1"
                        >
                            {{ formatDate(badge.pivot.earned_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Completed Cases -->
            <div class="bg-white shadow-sm rounded-lg p-6" v-if="completedCases && completedCases.length > 0">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Завершенные кейсы</h2>
                <div class="space-y-3">
                    <div
                        v-for="application in completedCases"
                        :key="application.id"
                        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-900">
                                    {{ application.case?.title }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Партнер: {{ application.case?.partner?.company_name || application.case?.partnerUser?.name || 'Не указан' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Завершен: {{ formatDate(application.updated_at) }}
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                Завершен
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty States -->
            <div
                v-if="(!skills || skills.length === 0) && (!badges || badges.length === 0) && (!completedCases || completedCases.length === 0)"
                class="bg-white shadow-sm rounded-lg p-12 text-center"
            >
                <i class="pi pi-info-circle text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Профиль студента пока пуст</p>
            </div>
        </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    studentProfile: {
        type: Object,
        default: null,
    },
    statistics: {
        type: Object,
        required: true,
    },
    skills: {
        type: Array,
        default: () => [],
    },
    badges: {
        type: Array,
        default: () => [],
    },
    completedCases: {
        type: Array,
        default: () => [],
    },
})

const getLevelColor = (level) => {
    if (level >= 8) return 'bg-purple-100 text-purple-800'
    if (level >= 5) return 'bg-blue-100 text-blue-800'
    if (level >= 3) return 'bg-green-100 text-green-800'
    return 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

