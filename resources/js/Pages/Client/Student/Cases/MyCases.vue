<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    applications: {
        type: Object,
        required: true
    }
})

const activeTab = ref('current')

const tabs = [
    { key: 'current', label: 'Текущие', count: props.applications.current.length },
    { key: 'pending', label: 'Заявки', count: props.applications.pending.length },
    { key: 'completed', label: 'Завершенные', count: props.applications.completed.length },
    { key: 'rejected', label: 'Отклоненные', count: props.applications.rejected.length }
]

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800',
    accepted: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    rejected: 'bg-red-100 text-red-800'
}

const roleText = (isLeader) => {
    return isLeader ? 'Лидер команды' : 'Участник'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const viewCase = (application) => {
    router.visit(route('student.cases.show', application.case.id))
}

const viewTeam = (application) => {
    router.visit(route('student.team.show', application.id))
}
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold">Мои кейсы</h1>
                <Button variant="primary" @click="router.visit(route('student.cases.index'))">
                    Найти кейсы
                </Button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            activeTab === tab.key
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        {{ tab.label }}
                        <span
                            v-if="tab.count > 0"
                            :class="[
                                'ml-2 py-0.5 px-2 rounded-full text-xs',
                                activeTab === tab.key
                                    ? 'bg-blue-100 text-blue-600'
                                    : 'bg-gray-100 text-gray-600'
                            ]"
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Current Cases -->
            <div v-if="activeTab === 'current'">
                <div v-if="applications.current.length === 0" class="text-center py-12">
                    <p class="text-gray-500 mb-4">У вас пока нет активных кейсов</p>
                    <Button variant="primary" @click="router.visit(route('student.cases.index'))">
                        Найти кейс
                    </Button>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card
                        v-for="application in applications.current"
                        :key="application.id"
                        class="hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold mb-2">{{ application.case.title }}</h3>
                                <p class="text-sm text-gray-600">{{ application.case.partner.company_name }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge :class="statusColors.accepted">
                                    Активен
                                </Badge>
                                <Badge variant="secondary">
                                    {{ roleText(application.is_leader) }}
                                </Badge>
                            </div>

                            <div class="text-sm text-gray-600">
                                <p>Подано: {{ formatDate(application.created_at) }}</p>
                            </div>

                            <div class="flex gap-2">
                                <Button
                                    variant="primary"
                                    size="sm"
                                    class="flex-1"
                                    @click="viewTeam(application)"
                                >
                                    Команда
                                </Button>
                                <Button
                                    variant="secondary"
                                    size="sm"
                                    class="flex-1"
                                    @click="viewCase(application)"
                                >
                                    Кейс
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Pending Applications -->
            <div v-if="activeTab === 'pending'">
                <div v-if="applications.pending.length === 0" class="text-center py-12">
                    <p class="text-gray-500">У вас нет ожидающих рассмотрения заявок</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card
                        v-for="application in applications.pending"
                        :key="application.id"
                        class="hover:shadow-lg transition-shadow"
                    >
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold mb-2">{{ application.case.title }}</h3>
                                <p class="text-sm text-gray-600">{{ application.case.partner.company_name }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge :class="statusColors.pending">
                                    Ожидает рассмотрения
                                </Badge>
                                <Badge variant="secondary">
                                    {{ roleText(application.is_leader) }}
                                </Badge>
                            </div>

                            <div class="text-sm text-gray-600">
                                <p>Подано: {{ formatDate(application.created_at) }}</p>
                            </div>

                            <Button
                                variant="secondary"
                                size="sm"
                                class="w-full"
                                @click="viewCase(application)"
                            >
                                Просмотреть кейс
                            </Button>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Completed Cases -->
            <div v-if="activeTab === 'completed'">
                <div v-if="applications.completed.length === 0" class="text-center py-12">
                    <p class="text-gray-500">У вас пока нет завершенных кейсов</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card
                        v-for="application in applications.completed"
                        :key="application.id"
                        class="hover:shadow-lg transition-shadow"
                    >
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold mb-2">{{ application.case.title }}</h3>
                                <p class="text-sm text-gray-600">{{ application.case.partner.company_name }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge :class="statusColors.completed">
                                    Завершен
                                </Badge>
                                <Badge variant="secondary">
                                    {{ roleText(application.is_leader) }}
                                </Badge>
                            </div>

                            <div class="text-sm text-gray-600">
                                <p>Завершено: {{ formatDate(application.updated_at) }}</p>
                            </div>

                            <Button
                                variant="secondary"
                                size="sm"
                                class="w-full"
                                @click="viewCase(application)"
                            >
                                Просмотреть
                            </Button>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Rejected Applications -->
            <div v-if="activeTab === 'rejected'">
                <div v-if="applications.rejected.length === 0" class="text-center py-12">
                    <p class="text-gray-500">У вас нет отклоненных заявок</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card
                        v-for="application in applications.rejected"
                        :key="application.id"
                        class="hover:shadow-lg transition-shadow"
                    >
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xl font-bold mb-2">{{ application.case.title }}</h3>
                                <p class="text-sm text-gray-600">{{ application.case.partner.company_name }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge :class="statusColors.rejected">
                                    Отклонена
                                </Badge>
                                <Badge variant="secondary">
                                    {{ roleText(application.is_leader) }}
                                </Badge>
                            </div>

                            <div v-if="application.rejection_reason" class="text-sm text-gray-600">
                                <p class="font-medium">Причина:</p>
                                <p>{{ application.rejection_reason }}</p>
                            </div>

                            <div class="text-sm text-gray-600">
                                <p>Отклонено: {{ formatDate(application.updated_at) }}</p>
                            </div>

                            <Button
                                variant="secondary"
                                size="sm"
                                class="w-full"
                                @click="viewCase(application)"
                            >
                                Просмотреть кейс
                            </Button>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
</template>
