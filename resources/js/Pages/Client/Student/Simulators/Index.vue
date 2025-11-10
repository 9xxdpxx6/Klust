<script setup>
import { router } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Table from '@/Components/UI/Table.vue'
import Badge from '@/Components/UI/Badge.vue'

const props = defineProps({
    simulators: {
        type: Array,
        required: true
    },
    recentSessions: {
        type: Array,
        default: () => []
    }
})

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
    if (score >= 90) return 'bg-green-100 text-green-800'
    if (score >= 75) return 'bg-blue-100 text-blue-800'
    if (score >= 50) return 'bg-yellow-100 text-yellow-800'
    return 'bg-red-100 text-red-800'
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
    <StudentLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Симуляторы</h1>

            <!-- Available Simulators -->
            <Card class="mb-8">
                <h2 class="text-xl font-bold mb-6">Доступные симуляторы</h2>
                <div v-if="simulators.length === 0" class="text-center py-12">
                    <p class="text-gray-500">Симуляторы пока не добавлены</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                            <div v-else class="text-6xl">🎮</div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2">{{ simulator.title }}</h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ simulator.description }}
                            </p>
                            <Button
                                variant="primary"
                                class="w-full"
                                @click="startSimulator(simulator)"
                            >
                                Начать
                            </Button>
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Recent Sessions -->
            <Card>
                <h2 class="text-xl font-bold mb-6">История прохождений</h2>
                <div v-if="recentSessions.length === 0" class="text-center py-12">
                    <p class="text-gray-500">У вас пока нет завершенных сессий</p>
                    <p class="text-sm text-gray-400 mt-2">
                        Начните проходить симуляторы, чтобы увидеть результаты здесь
                    </p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    v-for="column in sessionColumns"
                                    :key="column.key"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    {{ column.label }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="session in recentSessions"
                                :key="session.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ session.simulator.title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        {{ formatDate(session.completed_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge
                                        v-if="session.score !== null"
                                        :class="getScoreColor(session.score)"
                                    >
                                        {{ session.score }}%
                                    </Badge>
                                    <span v-else class="text-sm text-gray-400">N/A</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDuration(session.time_spent) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge variant="success">
                                        +{{ session.points_earned }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <!-- Info Card -->
            <Card class="mt-6 bg-blue-50">
                <div class="flex items-start gap-3">
                    <div class="text-3xl">💡</div>
                    <div>
                        <h3 class="font-bold mb-2">Как работают симуляторы?</h3>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Симуляторы помогают развивать навыки на практике</li>
                            <li>• За прохождение вы получаете очки, которые идут в ваши навыки</li>
                            <li>• Результаты зависят от качества выполнения заданий</li>
                            <li>• Вы можете проходить симуляторы несколько раз для улучшения результата</li>
                        </ul>
                    </div>
                </div>
            </Card>
        </div>
    </StudentLayout>
</template>
