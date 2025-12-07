<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import ProgressBar from '@/Components/UI/ProgressBar.vue'

const props = defineProps({
    skills: {
        type: Array,
        required: true
    },
    progressHistory: {
        type: Array,
        default: () => []
    }
})

const sortBy = ref('level')
const sortOrder = ref('desc')

const sortedSkills = computed(() => {
    const sorted = [...props.skills].sort((a, b) => {
        let compareA, compareB

        if (sortBy.value === 'level') {
            compareA = a.level
            compareB = b.level
        } else if (sortBy.value === 'points') {
            compareA = a.points
            compareB = b.points
        } else {
            compareA = a.name
            compareB = b.name
        }

        if (sortOrder.value === 'asc') {
            return compareA > compareB ? 1 : -1
        } else {
            return compareA < compareB ? 1 : -1
        }
    })

    return sorted
})

const toggleSort = (field) => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = field
        sortOrder.value = 'desc'
    }
}

const getLevelColor = (level) => {
    if (level >= 8) return 'text-purple-600 bg-purple-100'
    if (level >= 5) return 'text-blue-600 bg-blue-100'
    if (level >= 3) return 'text-green-600 bg-green-100'
    return 'text-gray-600 bg-gray-100'
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

const totalSkills = computed(() => props.skills.length)
const totalPoints = computed(() => props.skills.reduce((sum, skill) => sum + skill.points, 0))
const averageLevel = computed(() => {
    if (props.skills.length === 0) return 0
    return (props.skills.reduce((sum, skill) => sum + skill.level, 0) / props.skills.length).toFixed(1)
})
</script>

<template>
    <Head title="Мои навыки" />
    <div class="space-y-6">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Мои навыки</h1>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <Card class="text-center">
                    <p class="text-4xl font-bold text-blue-600">{{ totalSkills }}</p>
                    <p class="text-gray-600 mt-2">Всего навыков</p>
                </Card>
                <Card class="text-center">
                    <p class="text-4xl font-bold text-green-600">{{ totalPoints }}</p>
                    <p class="text-gray-600 mt-2">Всего очков</p>
                </Card>
                <Card class="text-center">
                    <p class="text-4xl font-bold text-purple-600">{{ averageLevel }}</p>
                    <p class="text-gray-600 mt-2">Средний уровень</p>
                </Card>
            </div>

            <!-- Sort Controls -->
            <Card class="mb-6">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700">Сортировать по:</span>
                    <button
                        @click="toggleSort('level')"
                        :class="[
                            'px-3 py-1 rounded text-sm font-medium transition-colors',
                            sortBy === 'level'
                                ? 'bg-blue-100 text-blue-700'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                    >
                        Уровень {{ sortBy === 'level' ? (sortOrder === 'desc' ? '↓' : '↑') : '' }}
                    </button>
                    <button
                        @click="toggleSort('points')"
                        :class="[
                            'px-3 py-1 rounded text-sm font-medium transition-colors',
                            sortBy === 'points'
                                ? 'bg-blue-100 text-blue-700'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                    >
                        Очки {{ sortBy === 'points' ? (sortOrder === 'desc' ? '↓' : '↑') : '' }}
                    </button>
                    <button
                        @click="toggleSort('name')"
                        :class="[
                            'px-3 py-1 rounded text-sm font-medium transition-colors',
                            sortBy === 'name'
                                ? 'bg-blue-100 text-blue-700'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]"
                    >
                        Название {{ sortBy === 'name' ? (sortOrder === 'desc' ? '↓' : '↑') : '' }}
                    </button>
                </div>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Skills List -->
                <div class="lg:col-span-2">
                    <Card>
                        <div v-if="sortedSkills.length === 0" class="text-center py-12">
                            <p class="text-gray-500">У вас пока нет навыков</p>
                            <p class="text-sm text-gray-400 mt-2">
                                Начните проходить симуляторы и работать над кейсами, чтобы получить навыки!
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="skill in sortedSkills"
                                :key="skill.id"
                                class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold">{{ skill.name }}</h3>
                                        <p v-if="skill.description" class="text-sm text-gray-600 mt-1">
                                            {{ skill.description }}
                                        </p>
                                    </div>
                                    <Badge :class="getLevelColor(skill.level)" class="text-lg">
                                        Уровень {{ skill.level }}
                                    </Badge>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">
                                            {{ skill.points }} очков
                                        </span>
                                        <span v-if="skill.progress_to_next_level.next_level" class="text-gray-600">
                                            До уровня {{ skill.progress_to_next_level.next_level }}:
                                            {{ skill.progress_to_next_level.points_needed }} очков
                                        </span>
                                        <span v-else class="text-green-600 font-medium">
                                            Максимальный уровень!
                                        </span>
                                    </div>
                                    <ProgressBar
                                        :value="skill.progress_to_next_level.percentage"
                                        :color="skill.level >= 7 ? 'purple' : skill.level >= 4 ? 'blue' : 'green'"
                                    />
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Progress History -->
                <div>
                    <Card>
                        <h2 class="text-lg font-bold mb-4">История прогресса</h2>
                        <div v-if="progressHistory.length === 0" class="text-center py-8 text-gray-500">
                            Пока нет истории
                        </div>
                        <div v-else class="space-y-3 max-h-[600px] overflow-y-auto">
                            <div
                                v-for="log in progressHistory"
                                :key="log.id"
                                class="p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-start justify-between mb-1">
                                    <p class="text-sm font-medium">{{ log.skill.name }}</p>
                                    <Badge variant="success" size="sm">
                                        +{{ log.points_earned }}
                                    </Badge>
                                </div>
                                <p class="text-xs text-gray-600">{{ log.description }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ formatDate(log.created_at) }}</p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Level Guide -->
            <Card class="mt-6">
                <h3 class="text-lg font-bold mb-4">Уровни навыков</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center">
                        <Badge class="text-gray-600 bg-gray-100 text-lg mb-2">1-2</Badge>
                        <p class="text-sm text-gray-600">Новичок</p>
                        <p class="text-xs text-gray-500">0-250 очков</p>
                    </div>
                    <div class="text-center">
                        <Badge class="text-green-600 bg-green-100 text-lg mb-2">3-4</Badge>
                        <p class="text-sm text-gray-600">Практикующий</p>
                        <p class="text-xs text-gray-500">250-1000 очков</p>
                    </div>
                    <div class="text-center">
                        <Badge class="text-blue-600 bg-blue-100 text-lg mb-2">5-7</Badge>
                        <p class="text-sm text-gray-600">Опытный</p>
                        <p class="text-xs text-gray-500">1000-8000 очков</p>
                    </div>
                    <div class="text-center">
                        <Badge class="text-purple-600 bg-purple-100 text-lg mb-2">8-9</Badge>
                        <p class="text-sm text-gray-600">Эксперт</p>
                        <p class="text-xs text-gray-500">8000-32000 очков</p>
                    </div>
                    <div class="text-center">
                        <Badge class="text-yellow-600 bg-yellow-100 text-lg mb-2">10</Badge>
                        <p class="text-sm text-gray-600">Мастер</p>
                        <p class="text-xs text-gray-500">32000+ очков</p>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>
