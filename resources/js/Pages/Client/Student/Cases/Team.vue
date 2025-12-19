<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import UserAvatar from '@/Components/Shared/UserAvatar.vue'

const props = defineProps({
    application: {
        type: Object,
        required: true
    },
    teamProgress: {
        type: Object,
        required: true
    },
    teamActivity: {
        type: Array,
        default: () => []
    },
    isLeader: {
        type: Boolean,
        default: false
    }
})

const partnerName = computed(() => {
    return (
        props.application?.case?.partner?.company_name ||
        props.application?.case?.partner_user?.partner_profile?.company_name ||
        props.application?.case?.partner_user?.name ||
        'Не указан'
    )
})

const partnerLogo = computed(() => {
    return (
        props.application?.case?.partner?.logo ||
        props.application?.case?.partner_user?.partner_profile?.logo ||
        null
    )
})

const formatDateTime = (dateString) => {
    if (!dateString) return 'Не указан'
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).replace(/ г\./g, '').replace(/,$/, '')
}

const coveredSkillIds = computed(() => {
    const ids = props.teamProgress?.covered_skill_ids || []
    return new Set(ids.map(id => Number(id)))
})

const isSkillCovered = (skillId) => {
    return coveredSkillIds.value.has(Number(skillId))
}

const activityUi = (activity) => {
    const type = activity?.type || 'progress'

    if (type === 'simulator_completed') {
        return { icon: 'pi pi-desktop', bg: 'bg-purple-500' }
    }

    if (type === 'joined_team') {
        return { icon: 'pi pi-users', bg: 'bg-blue-500' }
    }

    if (type === 'applied_to_case') {
        return { icon: 'pi pi-send', bg: 'bg-indigo-500' }
    }

    return { icon: 'pi pi-chart-line', bg: 'bg-green-500' }
}
</script>

<template>
    <div class="space-y-6">
        <Head :title="`Команда: ${application.case.title}`" />

        <!-- Breadcrumbs -->
        <nav class="text-sm flex items-center gap-2 text-text-secondary">
            <Link :href="route('student.cases.my')" class="text-primary hover:underline">
                Мои кейсы
            </Link>
            <i class="pi pi-angle-right text-xs text-text-muted"></i>
            <span class="text-text-secondary">Команда</span>
        </nav>

        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-3xl font-bold text-white mb-4 break-words">
                            {{ application.case.title }}
                        </h1>

                        <div class="flex items-center gap-4">
                            <img
                                v-if="partnerLogo"
                                :src="partnerLogo"
                                :alt="partnerName"
                                class="w-16 h-16 rounded-lg object-cover bg-white p-1"
                            />
                            <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center" v-else>
                                <i class="pi pi-building text-white text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-indigo-100">Партнёр</p>
                                <Link
                                    v-if="application.case?.user_id && partnerName !== 'Не указан'"
                                    :href="route('student.partners.show', application.case.user_id)"
                                    class="text-lg font-semibold text-white truncate hover:underline"
                                >
                                    {{ partnerName }}
                                </Link>
                                <p v-else class="text-lg font-semibold text-white truncate">
                                    {{ partnerName }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-start md:items-end gap-3">
                        <div class="px-4 py-2 bg-green-500 rounded-lg">
                            <span class="text-white font-semibold">Активная команда</span>
                        </div>

                        <Button
                            variant="white-outline"
                            @click="router.visit(route('student.cases.show', application.case.id))"
                        >
                            <i class="pi pi-external-link mr-2"></i>
                            Просмотреть кейс
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Progress -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-chart-line text-indigo-600"></i>
                            Прогресс команды
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 shadow-sm border border-blue-200/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-blue-600 mb-1">Участников</p>
                                        <p class="text-3xl font-bold text-blue-900">{{ teamProgress.team_size }}</p>
                                    </div>
                                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl flex-shrink-0">
                                        <i class="pi pi-users text-white text-xl"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 shadow-sm border border-green-200/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-green-600 mb-1">Всего очков</p>
                                        <p class="text-3xl font-bold text-green-900">{{ teamProgress.total_skill_points }}</p>
                                    </div>
                                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl flex-shrink-0">
                                        <i class="pi pi-bolt text-white text-xl"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 shadow-sm border border-purple-200/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-purple-600 mb-1">Средние очки</p>
                                        <p class="text-3xl font-bold text-purple-900">{{ teamProgress.average_skill_points }}</p>
                                    </div>
                                    <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl flex-shrink-0">
                                        <i class="pi pi-chart-bar text-white text-xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="pi pi-star text-purple-600"></i>
                                    Навыки по требованиям кейса
                                </span>
                                <span v-if="teamProgress.case_required_skills" class="text-xs text-gray-500">
                                    Покрыто: {{ teamProgress.matching_skills_count || 0 }} / {{ teamProgress.case_required_skills }}
                                </span>
                            </div>

                            <div v-if="!application.case?.skills || application.case.skills.length === 0" class="text-sm text-gray-500">
                                Для этого кейса не указаны требуемые навыки
                            </div>

                            <div v-else class="flex flex-wrap gap-2">
                                <span
                                    v-for="skill in application.case.skills"
                                    :key="skill.id"
                                    class="inline-flex items-center gap-1 px-3 py-1 text-sm font-semibold rounded-lg border"
                                    :class="isSkillCovered(skill.id)
                                        ? 'bg-green-100 text-green-800 border-green-200'
                                        : 'bg-gray-100 text-gray-700 border-gray-200'"
                                >
                                    <i
                                        v-if="isSkillCovered(skill.id)"
                                        class="pi pi-check text-xs"
                                    ></i>
                                    {{ skill.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-users text-blue-600"></i>
                            Участники команды
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="member in teamProgress.members"
                                :key="member.id"
                                class="flex items-center gap-4 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all"
                            >
                                <UserAvatar
                                    :src="member.avatar"
                                    :name="member.name"
                                    size="lg"
                                />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-semibold text-gray-900 truncate">{{ member.name }}</h3>
                                        <Badge
                                            v-if="member.id === application.leader_id"
                                            variant="primary"
                                            size="sm"
                                        >
                                            Лидер
                                        </Badge>
                                    </div>

                                    <div class="flex flex-wrap gap-x-6 gap-y-2 mt-2 text-sm text-gray-700">
                                        <span class="inline-flex items-center gap-2">
                                            <i class="pi pi-bolt text-amber-600"></i>
                                            {{ member.total_points }} очков
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <i class="pi pi-star text-blue-600"></i>
                                            {{ member.skills_count }} навыков
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <i class="pi pi-trophy text-purple-600"></i>
                                            {{ member.badges_count }} достижений
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-history text-amber-600"></i>
                            История активности
                        </h2>
                    </div>
                    <div class="p-6">
                        <div v-if="teamActivity.length === 0" class="text-center py-10 text-gray-400">
                            <i class="pi pi-history text-4xl mb-2"></i>
                            <p class="text-sm">Пока нет активности</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="activity in teamActivity"
                                :key="activity.id || `${activity.type}-${activity.date}`"
                                class="flex items-start gap-3 p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200"
                            >
                                <div
                                    class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                                    :class="activityUi(activity).bg"
                                >
                                    <i :class="[activityUi(activity).icon, 'text-white text-sm']"></i>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">
                                        {{ activity.description }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ formatDateTime(activity.date || activity.created_at) }}
                                    </p>
                                </div>

                                <span
                                    v-if="Number(activity?.data?.points || 0) > 0"
                                    class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-800 border border-green-200"
                                >
                                    +{{ activity.data.points }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-info-circle text-purple-600"></i>
                            Детали кейса
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600 mb-1">Статус</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ application.status?.name === 'accepted' ? 'Активен' : (application.status?.label || application.status?.name) }}
                            </p>
                        </div>

                        <div v-if="application.case.deadline" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600 mb-1">Дедлайн</p>
                            <p class="text-lg font-semibold text-gray-900">{{ formatDateTime(application.case.deadline) }}</p>
                        </div>

                        <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600 mb-1">Дата начала</p>
                            <p class="text-lg font-semibold text-gray-900">{{ formatDateTime(application.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-3">
                            <Button
                                variant="secondary"
                                class="w-full"
                                @click="router.visit(route('student.cases.show', application.case.id))"
                            >
                                <i class="pi pi-eye mr-2"></i>
                                Просмотреть кейс
                            </Button>
                            <Button
                                variant="outline"
                                class="w-full"
                                @click="router.visit(route('student.cases.my'))"
                            >
                                <i class="pi pi-arrow-left mr-2"></i>
                                Назад к моим кейсам
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
