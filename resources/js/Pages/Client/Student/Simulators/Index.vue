<script setup>
import { Head, router } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Table from '@/Components/UI/Table.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    simulators: {
        type: Array,
        required: true
    },
    activeSessions: {
        type: Array,
        default: () => []
    },
    completedSessions: {
        type: Array,
        default: () => []
    }
})

const continueSession = (session) => {
    router.visit(route('student.simulators.session', session.id))
}

const startSimulator = (simulator) => {
    router.post(route('student.simulators.start', simulator.id))
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatDuration = (seconds) => {
    if (!seconds) return 'N/A'
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    if (hours > 0) {
        return `${hours}ч ${minutes}м`
    }
    return `${minutes}м`
}

const getScoreColor = (score) => {
    if (!score) return 'bg-gray-100 text-gray-800'
    // Ограничиваем score до 100 для корректного отображения процента
    const scorePercent = Math.min(score, 100)
    if (scorePercent >= 90) return 'bg-green-100 text-green-800'
    if (scorePercent >= 75) return 'bg-blue-100 text-blue-800'
    if (scorePercent >= 50) return 'bg-yellow-100 text-yellow-800'
    return 'bg-red-100 text-red-800'
}

const formatScore = (score) => {
    if (score === null || score === undefined) return null
    // Ограничиваем score до 100 для корректного отображения процента
    return Math.min(score, 100)
}

const sessionColumns = [
    { key: 'simulator', label: 'Симулятор' },
    { key: 'completed_at', label: 'Дата' },
    { key: 'score', label: 'Результат' },
    { key: 'time_spent', label: 'Время' },
    { key: 'points_earned', label: 'Очки' }
]
</script>

<template>
    <Head title="Симуляторы" />
    <div class="space-y-4 sm:space-y-6">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 py-6 sm:py-8">
            <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Симуляторы</h1>

            <!-- Available Simulators -->
            <Card class="mb-4 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-bold mb-4 sm:mb-6">Доступные симуляторы</h2>
                <div v-if="simulators.length === 0" class="text-center py-8 sm:py-12">
                    <p class="text-sm sm:text-base text-gray-500">Симуляторы пока не добавлены</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div
                        v-for="simulator in simulators"
                        :key="simulator.id"
                        class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                    >
                        <div class="aspect-video bg-gray-100 flex items-center justify-center">
                            <img
                                v-if="simulator.preview_path"
                                :src="simulator.preview_path"
                                :alt="simulator.title"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="text-4xl sm:text-6xl">🎮</div>
                        </div>
                        <div class="p-3 sm:p-4">
                            <h3 class="font-bold text-base sm:text-lg mb-2">{{ simulator.title }}</h3>
                            <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4 line-clamp-2">
                                {{ simulator.description }}
                            </p>
                            <Button
                                variant="primary"
                                class="w-full text-xs sm:text-sm"
                                @click="startSimulator(simulator)"
                            >
                                Начать
                            </Button>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Active Sessions -->
            <Card v-if="activeSessions.length > 0" class="mb-4 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-bold mb-4 sm:mb-6">Активные сессии</h2>
                <div class="space-y-3">
                    <div
                        v-for="session in activeSessions"
                        :key="session.id"
                        class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                    >
                        <div class="flex-1">
                            <h3 class="font-semibold text-sm sm:text-base text-gray-900 mb-1">
                                {{ session.simulator.title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-600">
                                Начато: {{ formatDate(session.started_at || session.created_at) }}
                            </p>
                        </div>
                        <Button
                            variant="primary"
                            size="sm"
                            @click="continueSession(session)"
                            class="ml-4"
                        >
                            Продолжить
                        </Button>
                    </div>
                </div>
            </Card>

            <!-- Completed Sessions -->
            <Card>
                <h2 class="text-lg sm:text-xl font-bold mb-4 sm:mb-6">История прохождений</h2>
                <div v-if="completedSessions.length === 0" class="text-center py-8 sm:py-12">
                    <p class="text-sm sm:text-base text-gray-500">У вас пока нет завершенных сессий</p>
                    <p class="text-xs sm:text-sm text-gray-400 mt-2">
                        Начните проходить симуляторы, чтобы увидеть результаты здесь
                    </p>
                </div>
                <div v-else class="overflow-x-auto -mx-2 sm:mx-0">
                    <div class="inline-block min-w-full align-middle px-2 sm:px-0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        v-for="column in sessionColumns"
                                        :key="column.key"
                                        class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ column.label }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="session in completedSessions"
                                    :key="session.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">
                                            {{ session.simulator.title }}
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <div class="text-xs sm:text-sm text-gray-500">
                                            {{ formatDate(session.completed_at) }}
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <Badge
                                            v-if="session.score !== null"
                                            :class="getScoreColor(session.score)"
                                            class="text-xs"
                                        >
                                            {{ formatScore(session.score) }}%
                                        </Badge>
                                        <span v-else class="text-xs sm:text-sm text-gray-400">N/A</span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-500">
                                        {{ formatDuration(session.time_spent) }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                                        <Badge variant="success" class="text-xs">
                                            +{{ session.points_earned || 0 }}
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Card>

            <!-- Info Card -->
            <Card class="mt-4 sm:mt-6 bg-blue-50">
                <div class="flex items-start gap-2 sm:gap-3">
                    <div class="text-2xl sm:text-3xl flex-shrink-0">💡</div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-sm sm:text-base mb-2">Как работают симуляторы?</h3>
                        <ul class="text-xs sm:text-sm text-gray-700 space-y-1">
                            <li>• Симуляторы помогают развивать навыки на практике</li>
                            <li>• За прохождение вы получаете очки, которые идут в ваши навыки</li>
                            <li>• Результаты зависят от качества выполнения заданий</li>
                            <li>• Вы можете проходить симуляторы несколько раз для улучшения результата</li>
                        </ul>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>
