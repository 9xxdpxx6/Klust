<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import ProgressBar from '@/Components/UI/ProgressBar.vue'

const props = defineProps({
    session: {
        type: Object,
        required: true
    },
    simulator: {
        type: Object,
        required: true
    }
})

const timeSpent = ref(0)
const timer = ref(null)

const completeForm = useForm({
    score: 0,
    time_spent: 0,
    answers: {}
})

onMounted(() => {
    // Start timer
    timer.value = setInterval(() => {
        timeSpent.value++
    }, 1000)
})

onUnmounted(() => {
    if (timer.value) {
        clearInterval(timer.value)
    }
})

const formatTime = (seconds) => {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = seconds % 60

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`
}

const completeSession = () => {
    if (confirm('Вы уверены, что хотите завершить сессию?')) {
        completeForm.time_spent = timeSpent.value
        // Score calculation should be done based on actual simulator logic
        completeForm.score = calculateScore()

        completeForm.post(route('student.simulators.complete', props.session.id), {
            onSuccess: () => {
                router.visit(route('student.simulators.index'))
            }
        })
    }
}

const calculateScore = () => {
    // This is a placeholder. Real implementation depends on simulator type
    // For now, return a random score between 50-100
    return Math.floor(Math.random() * 51) + 50
}

const exitSession = () => {
    if (confirm('Вы уверены, что хотите выйти? Прогресс не будет сохранен.')) {
        router.visit(route('student.simulators.index'))
    }
}
</script>

<template>
    <Head :title="`Симулятор: ${session.simulator.title}`" />
    <div class="space-y-6">
        <div class="min-h-screen bg-gray-50">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
                <div class="max-w-7xl mx-auto px-4 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold">{{ simulator.title }}</h1>
                            <p class="text-sm text-gray-600">Сессия #{{ session.id }}</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <p class="text-2xl font-bold font-mono">{{ formatTime(timeSpent) }}</p>
                                <p class="text-xs text-gray-500">Время</p>
                            </div>
                            <Button variant="secondary" @click="exitSession">
                                Выйти
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 py-8">
                <Card class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold">Прогресс</h2>
                        <span class="text-sm text-gray-600">0%</span>
                    </div>
                    <ProgressBar :value="0" color="blue" />
                </Card>

                <!-- Simulator Content Area -->
                <Card class="min-h-[500px]">
                    <div class="text-center py-20">
                        <div class="text-6xl mb-6">🎮</div>
                        <h3 class="text-2xl font-bold mb-4">Симулятор загружается...</h3>
                        <p class="text-gray-600 mb-8">
                            Интерфейс симулятора будет здесь.<br/>
                            Реализация зависит от типа симулятора.
                        </p>

                        <!-- Placeholder Content -->
                        <div class="max-w-2xl mx-auto text-left space-y-6">
                            <div class="p-6 bg-gray-50 rounded-lg">
                                <h4 class="font-bold mb-3">Примеры интеграции:</h4>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li>• <strong>Iframe:</strong> Встраивание внешнего симулятора через iframe</li>
                                    <li>• <strong>Vue компоненты:</strong> Собственные интерактивные компоненты</li>
                                    <li>• <strong>WebGL/Canvas:</strong> Интеграция игровых движков (Three.js, Phaser)</li>
                                    <li>• <strong>Code Editor:</strong> Интеграция редакторов кода (Monaco, CodeMirror)</li>
                                    <li>• <strong>Квизы/Тесты:</strong> Интерактивные вопросы и задания</li>
                                </ul>
                            </div>

                            <div class="p-6 bg-blue-50 rounded-lg">
                                <h4 class="font-bold mb-3">💡 Для разработчиков:</h4>
                                <p class="text-sm text-gray-700 mb-3">
                                    Замените этот контент на реальный интерфейс симулятора.
                                    Используйте <code class="px-2 py-1 bg-white rounded">session.id</code> и
                                    <code class="px-2 py-1 bg-white rounded">simulator.id</code> для работы с API.
                                </p>
                                <p class="text-xs text-gray-600">
                                    Данные симулятора можно хранить в метаданных модели Simulator,
                                    а результаты отправлять через форму completeForm.
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-6">
                    <Button variant="secondary" @click="exitSession">
                        Выйти без сохранения
                    </Button>
                    <Button
                        variant="primary"
                        @click="completeSession"
                        :disabled="completeForm.processing"
                    >
                        Завершить сессию
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Add any simulator-specific styles here */
</style>
