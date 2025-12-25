<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import ProgressBar from '@/Components/UI/ProgressBar.vue'

const props = defineProps({
    skills: {
        type: Array,
        required: true
    },
    averageLevel: {
        type: Number,
        default: 0
    },
    progressHistory: {
        type: Array,
        default: () => []
    },
    maxLevelInSystem: {
        type: Number,
        default: 10
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

// Универсальная функция для определения категории уровня на основе процента от максимума
const getLevelCategory = (level, maxLevel) => {
    if (!maxLevel || maxLevel === 0) {
        return { category: 'novice', color: 'gray' }
    }
    
    const percentage = (level / maxLevel) * 100
    
    // Используем процентные границы для универсальности
    if (percentage >= 80) {
        return { category: 'master', color: 'purple' }  // Мастер/Эксперт (80-100%)
    } else if (percentage >= 50) {
        return { category: 'expert', color: 'blue' }    // Опытный (50-80%)
    } else if (percentage >= 25) {
        return { category: 'practitioner', color: 'green' } // Практикующий (25-50%)
    } else {
        return { category: 'novice', color: 'gray' }    // Новичок (0-25%)
    }
}

const getLevelBadgeClass = (level, maxLevel = 10) => {
    const { color } = getLevelCategory(level, maxLevel)
    
    const colorClasses = {
        purple: 'bg-purple-100 text-purple-800 border-purple-200',
        blue: 'bg-blue-100 text-blue-800 border-blue-200',
        green: 'bg-green-100 text-green-800 border-green-200',
        gray: 'bg-gray-100 text-gray-800 border-gray-200'
    }
    
    return colorClasses[color] || colorClasses.gray
}

const getProgressColor = (level, maxLevel = 10) => {
    const { color } = getLevelCategory(level, maxLevel)
    
    // ProgressBar поддерживает только: primary, success, warning, danger
    const colorMap = {
        purple: 'primary',  // фиолетовый → primary (синий/фиолетовый)
        blue: 'primary',    // синий → primary
        green: 'success',   // зеленый → success
        gray: 'warning'     // серый → warning (желтый для новичков)
    }
    
    return colorMap[color] || 'success'
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

// Используем максимальный уровень из всей системы (приходит с бэкенда)
const maxLevelInSystem = computed(() => {
    return props.maxLevelInSystem || 10
})

// Функция для вычисления границ категорий на основе максимального уровня
const getLevelRanges = computed(() => {
    const max = maxLevelInSystem.value
    
    // Вычисляем границы на основе процентов
    const noviceMax = Math.max(1, Math.floor(max * 0.25)) // 0-25%
    const practitionerMin = noviceMax + 1
    const practitionerMax = Math.max(practitionerMin, Math.floor(max * 0.5)) // 25-50%
    const expertMin = practitionerMax + 1
    const expertMax = Math.max(expertMin, Math.floor(max * 0.8)) // 50-80%
    const masterMin = expertMax + 1
    
    return {
        novice: {
            min: 1,
            max: noviceMax,
            label: 'Новичок',
            color: 'gray',
            bgGradient: 'from-gray-50 to-gray-100',
            borderColor: 'border-gray-200',
            badgeBg: 'bg-gray-100',
            badgeText: 'text-gray-800',
            badgeBorder: 'border-gray-200',
            percentageRange: `0-${Math.round((noviceMax / max) * 100)}%`
        },
        practitioner: {
            min: practitionerMin > max ? max : practitionerMin,
            max: practitionerMax,
            label: 'Практикующий',
            color: 'green',
            bgGradient: 'from-green-50 to-green-100',
            borderColor: 'border-green-200',
            badgeBg: 'bg-green-100',
            badgeText: 'text-green-800',
            badgeBorder: 'border-green-200',
            percentageRange: `${Math.round((practitionerMin / max) * 100)}-${Math.round((practitionerMax / max) * 100)}%`
        },
        expert: {
            min: expertMin > max ? max : expertMin,
            max: expertMax,
            label: 'Опытный',
            color: 'blue',
            bgGradient: 'from-blue-50 to-blue-100',
            borderColor: 'border-blue-200',
            badgeBg: 'bg-blue-100',
            badgeText: 'text-blue-800',
            badgeBorder: 'border-blue-200',
            percentageRange: `${Math.round((expertMin / max) * 100)}-${Math.round((expertMax / max) * 100)}%`
        },
        master: {
            min: masterMin > max ? max : masterMin,
            max: max,
            label: max > 20 ? 'Эксперт' : 'Мастер',
            color: 'purple',
            bgGradient: 'from-purple-50 to-purple-100',
            borderColor: 'border-purple-200',
            badgeBg: 'bg-purple-100',
            badgeText: 'text-purple-800',
            badgeBorder: 'border-purple-200',
            percentageRange: `${Math.round((masterMin / max) * 100)}-100%`
        }
    }
})
</script>

<style scoped>
/* Hide scrollbar for sort buttons on mobile */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>

<template>
    <Head title="Мои навыки" />
    <div class="space-y-4 sm:space-y-6">
        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-4 py-6 sm:px-6 sm:py-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Мои навыки</h1>
                <p class="text-sm sm:text-base text-indigo-100">Ваш прогресс и достижения по навыкам</p>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 sm:p-6 shadow-md border border-blue-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-blue-600 mb-1">Всего навыков</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-900">{{ totalSkills }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-book text-white text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 sm:p-6 shadow-md border border-green-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-green-600 mb-1">Всего очков</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-900">{{ totalPoints }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-green-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-star text-white text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 sm:p-6 shadow-md border border-purple-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-purple-600 mb-1">Средний уровень</p>
                        <p class="text-2xl sm:text-3xl font-bold text-purple-900">{{ averageLevel }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-purple-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-chart-line text-white text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Skills List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <!-- Header with Sort Controls -->
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                                <i class="pi pi-list text-amber-600"></i>
                                Список навыков
                            </h2>
                            <div class="flex items-center gap-2 overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0 scrollbar-hide">
                                <div class="flex flex-nowrap gap-2 min-w-max sm:min-w-0">
                                <span class="text-sm font-medium text-gray-700">Сортировать:</span>
                                    <button
                                        @click="toggleSort('level')"
                                        :class="[
                                            'px-2.5 sm:px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all whitespace-nowrap',
                                            sortBy === 'level'
                                                ? 'bg-amber-500 text-white shadow-md'
                                                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                                        ]"
                                    >
                                        <i class="pi pi-sort-amount-down mr-1"></i>
                                        Уровень {{ sortBy === 'level' ? (sortOrder === 'desc' ? '↓' : '↑') : '' }}
                                    </button>
                                    <button
                                        @click="toggleSort('points')"
                                        :class="[
                                            'px-2.5 sm:px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all whitespace-nowrap',
                                            sortBy === 'points'
                                                ? 'bg-amber-500 text-white shadow-md'
                                                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                                        ]"
                                    >
                                        <i class="pi pi-star mr-1"></i>
                                        Очки {{ sortBy === 'points' ? (sortOrder === 'desc' ? '↓' : '↑') : '' }}
                                    </button>
                                    <button
                                        @click="toggleSort('name')"
                                        :class="[
                                            'px-2.5 sm:px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-all whitespace-nowrap',
                                            sortBy === 'name'
                                                ? 'bg-amber-500 text-white shadow-md'
                                                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                                        ]"
                                    >
                                        <i class="pi pi-sort-alpha-down mr-1"></i>
                                        Название {{ sortBy === 'name' ? (sortOrder === 'desc' ? '↓' : '↑') : '' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skills Content -->
                    <div class="p-4 sm:p-6">
                        <div v-if="sortedSkills.length === 0" class="text-center py-8 sm:py-12">
                            <i class="pi pi-book text-3xl sm:text-4xl text-gray-300 mb-4"></i>
                            <p class="text-sm sm:text-base text-gray-500 font-medium">У вас пока нет навыков</p>
                            <p class="text-xs sm:text-sm text-gray-400 mt-2">
                                Начните проходить симуляторы и работать над кейсами, чтобы получить навыки!
                            </p>
                        </div>
                        <div v-else class="space-y-3 sm:space-y-4">
                            <div
                                v-for="skill in sortedSkills"
                                :key="skill.id"
                                class="p-4 sm:p-5 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-lg hover:shadow-lg transition-all hover:border-amber-200"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 sm:gap-4 mb-3 sm:mb-4">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1">{{ skill.name }}</h3>
                                        <p v-if="skill.description" class="text-xs sm:text-sm text-gray-600">
                                            {{ skill.description }}
                                        </p>
                                    </div>
                                    <span
                                        :class="[
                                            'px-3 sm:px-4 py-1.5 rounded-lg text-xs sm:text-sm font-semibold border whitespace-nowrap',
                                            getLevelBadgeClass(skill.level, skill.max_level)
                                        ]"
                                    >
                                        Уровень {{ skill.level }}{{ skill.max_level ? ` / ${skill.max_level}` : '' }}
                                    </span>
                                </div>

                                <div class="space-y-2 sm:space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 text-xs sm:text-sm">
                                        <div class="flex items-center gap-2 text-gray-600">
                                            <i class="pi pi-star text-amber-500"></i>
                                            <span class="font-medium">{{ skill.points }} очков</span>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <span v-if="skill.progress_to_next_level.next_level" class="text-gray-600">
                                                До уровня {{ skill.progress_to_next_level.next_level }}:
                                                <span class="font-semibold text-gray-900">{{ skill.progress_to_next_level.points_needed }}</span> очков
                                            </span>
                                            <span v-else class="text-green-600 font-semibold flex items-center gap-1">
                                                <i class="pi pi-check-circle"></i>
                                                Максимальный уровень!
                                            </span>
                                        </div>
                                    </div>
                                    <ProgressBar
                                        :value="skill.progress_to_next_level.percentage"
                                        :color="getProgressColor(skill.level, skill.max_level)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress History -->
            <div>
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-history text-indigo-600"></i>
                            История прогресса
                        </h2>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div v-if="progressHistory.length === 0" class="text-center py-6 sm:py-8 text-gray-400">
                            <i class="pi pi-history text-3xl sm:text-4xl mb-2"></i>
                            <p class="text-xs sm:text-sm">Пока нет истории</p>
                        </div>
                        <div v-else class="space-y-2 sm:space-y-3 max-h-[400px] sm:max-h-[600px] overflow-y-auto">
                            <div
                                v-for="log in progressHistory"
                                :key="log.id"
                                class="p-3 sm:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all"
                            >
                                <div class="flex items-start justify-between mb-2 gap-2">
                                    <p class="text-xs sm:text-sm font-semibold text-gray-900 flex-1 min-w-0">{{ log.skill?.name || 'Неизвестный навык' }}</p>
                                    <span class="px-2 sm:px-2.5 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-lg border border-green-200 whitespace-nowrap flex-shrink-0">
                                        +{{ log.points_earned }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 mb-2">{{ log.description }}</p>
                                <p class="text-xs text-gray-400 flex items-center gap-1">
                                    <i class="pi pi-clock"></i>
                                    {{ formatDate(log.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Level Guide -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-info-circle text-purple-600"></i>
                    Уровни навыков
                </h3>
            </div>
            <div class="p-4 sm:p-6">
                <div class="mb-3 sm:mb-4 text-xs sm:text-sm text-gray-600">
                    <p>Максимальный уровень в системе: <span class="font-semibold text-gray-900">{{ maxLevelInSystem }}</span></p>
                    <p class="text-xs text-gray-500 mt-1">Категории рассчитываются относительно максимального уровня навыка</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div 
                        v-for="(range, key) in getLevelRanges" 
                        :key="key"
                        :class="[
                            'text-center p-3 sm:p-4 rounded-lg border',
                            `bg-gradient-to-br ${range.bgGradient}`,
                            range.borderColor
                        ]"
                    >
                        <span 
                            :class="[
                                'inline-block px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg border mb-2 sm:mb-3 text-sm sm:text-base font-semibold',
                                range.badgeBg,
                                range.badgeText,
                                range.badgeBorder
                            ]"
                        >
                            {{ range.min }}{{ range.min !== range.max ? `-${range.max}` : '' }}
                        </span>
                        <p class="text-xs sm:text-sm font-semibold text-gray-900 mb-1">{{ range.label }}</p>
                        <p class="text-xs text-gray-500">
                            {{ range.percentageRange }} от максимума
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
