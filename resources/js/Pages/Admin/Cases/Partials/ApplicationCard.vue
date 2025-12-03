<template>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h4 class="text-lg font-medium text-gray-900">
                    {{ application.leader?.name || 'Не указан' }}
                </h4>
                <p class="text-sm text-gray-500">
                    {{ application.leader?.email || '' }}
                </p>
                <p v-if="application.leader?.student_profile" class="text-sm text-gray-500">
                    <span v-if="application.leader.student_profile.faculty">
                        {{ application.leader.student_profile.faculty.name || application.leader.student_profile.faculty }}, 
                    </span>
                    <span v-if="application.leader.student_profile.course">
                        {{ application.leader.student_profile.course }} курс, 
                    </span>
                    {{ application.leader.student_profile.group }} группа
                </p>
            </div>
            <span :class="[
                'px-3 py-1 text-sm font-medium rounded-full',
                getApplicationStatusBadgeClass(application.status?.name)
            ]">
                {{ getApplicationStatusLabel(application.status?.name) }}
            </span>
        </div>

        <!-- Мотивационное письмо -->
        <div class="mb-4">
            <h5 class="text-sm font-medium text-gray-700 mb-2">Мотивационное письмо:</h5>
            <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">{{ application.motivation }}</p>
        </div>

        <!-- Команда -->
        <div>
            <h5 class="text-sm font-medium text-gray-700 mb-2">Состав команды ({{ (application.team_members?.length || 0) + 1 }} чел.):</h5>
            <div class="space-y-2">
                <!-- Лидер команды -->
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-900">{{ application.leader?.name || 'Не указан' }}</span>
                    <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Лидер</span>
                </div>

                <!-- Участники команды -->
                <div
                    v-for="member in application.team_members || []"
                    :key="member.id"
                    class="flex items-center text-sm text-gray-600"
                >
                    {{ member.user?.name || 'Не указан' }}
                    <span v-if="member.user?.student_profile" class="text-gray-400 ml-2">
                        ({{ member.user.student_profile.group }})
                    </span>
                </div>

                <div v-if="!application.team_members || application.team_members.length === 0" class="text-sm text-gray-500">
                    Участники не добавлены
                </div>
            </div>
        </div>

        <!-- Дата подачи -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-500">
                Подана: {{ formatDate(application.created_at) }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    application: {
        type: Object,
        default: () => ({})
    }
})

const application = computed(() => props.application || {})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
}

const getApplicationStatusLabel = (status) => {
    const statusMap = {
        pending: 'На рассмотрении',
        accepted: 'Принята',
        approved: 'Одобрена', // Альтернативное название
        rejected: 'Отклонена',
    }
    return statusMap[status] || status
}

const getApplicationStatusBadgeClass = (status) => {
    const classMap = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800'
}
</script>
