<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import ProgressBar from '@/Components/UI/ProgressBar.vue'

const props = defineProps({
    earnedBadges: {
        type: Array,
        default: () => []
    },
    upcomingBadges: {
        type: Array,
        default: () => []
    },
    currentPoints: {
        type: Number,
        default: 0
    }
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const getProgressPercentage = (badge) => {
    if (badge.required_points === 0) return 100
    return Math.min(100, Math.round((props.currentPoints / badge.required_points) * 100))
}

const totalBadges = computed(() => props.earnedBadges.length)
const recentBadge = computed(() => {
    if (props.earnedBadges.length === 0) return null
    return [...props.earnedBadges].sort((a, b) =>
        new Date(b.earned_at) - new Date(a.earned_at)
    )[0]
})
</script>

<template>
    <div class="space-y-4 sm:space-y-6">
        <Head title="Мои достижения" />
        
        <!-- Header with gradient -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-4 py-6 sm:px-6 sm:py-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Мои достижения</h1>
                <p class="text-sm sm:text-base text-indigo-100">Ваш прогресс и полученные награды</p>
            </div>
        </div>

            <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 sm:p-6 shadow-md border border-blue-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-blue-600 mb-1">Получено достижений</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-900">{{ totalBadges }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-blue-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-trophy text-white text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 sm:p-6 shadow-md border border-green-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-green-600 mb-1">Всего очков</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-900">{{ currentPoints }}</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-green-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <i class="pi pi-bolt text-white text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 sm:p-6 shadow-md border border-amber-200/50 hover:shadow-lg transition-all group">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-amber-600 mb-1">Последнее достижение</p>
                        <p v-if="recentBadge" class="text-base sm:text-lg font-bold text-amber-900 truncate">{{ recentBadge.name }}</p>
                        <p v-else class="text-base sm:text-lg font-bold text-amber-900">Получите первое!</p>
                    </div>
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-amber-500 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0 ml-2">
                        <img
                            v-if="recentBadge?.icon_path"
                            :src="recentBadge.icon_path"
                            :alt="recentBadge.name"
                            class="w-6 h-6 sm:w-8 sm:h-8 object-contain"
                        />
                        <i
                            v-else-if="recentBadge?.icon && (recentBadge.icon.startsWith('pi-') || recentBadge.icon.startsWith('fa-'))"
                            :class="['text-white text-lg sm:text-xl', recentBadge.icon.startsWith('fa-') ? `pi pi-${recentBadge.icon.replace('fa-', '')}` : `pi ${recentBadge.icon}`]"
                        ></i>
                        <i v-else class="pi pi-star text-white text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Earned Badges -->
                <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-yellow-50 to-yellow-100 border-b border-yellow-200">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-trophy text-yellow-600"></i>
                            Полученные достижения
                        </h2>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div v-if="earnedBadges.length === 0" class="text-center py-8 sm:py-12">
                            <i class="pi pi-trophy text-3xl sm:text-4xl text-gray-400 mb-4"></i>
                            <p class="text-sm sm:text-base text-gray-500 mb-2">У вас пока нет достижений</p>
                            <p class="text-xs sm:text-sm text-gray-400">
                                Проходите симуляторы и работайте над кейсами, чтобы получить свое первое достижение!
                            </p>
                        </div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                            <div
                                v-for="badge in earnedBadges"
                                :key="badge.id"
                                class="group relative bg-white rounded-xl p-4 sm:p-6 border border-gray-200 hover:shadow-lg transition-all hover:scale-[1.02]"
                            >
                                <div class="relative inline-block mb-3 sm:mb-4 w-full flex justify-center">
                                    <div class="relative">
                                        <img
                                            v-if="badge.icon_path"
                                            :src="badge.icon_path"
                                            :alt="badge.name"
                                            class="w-16 h-16 sm:w-20 sm:h-20 object-contain mx-auto"
                                        />
                                        <div
                                            v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                                            class="w-16 h-16 sm:w-20 sm:h-20 bg-yellow-200 rounded-full flex items-center justify-center mx-auto"
                                        >
                                            <i :class="['text-[48px] sm:text-[64px] text-yellow-700', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                                        </div>
                                        <div v-else class="w-16 h-16 sm:w-20 sm:h-20 bg-yellow-200 rounded-full flex items-center justify-center mx-auto">
                                            <span class="text-3xl sm:text-4xl">🏆</span>
                                        </div>
                                        <div 
                                            v-if="badge.level && badge.level > 1"
                                            class="absolute top-0 right-0 min-w-[1.25rem] sm:min-w-[1.5rem] h-5 sm:h-6 px-1 sm:px-1.5 bg-blue-50 border border-blue-300 rounded-full flex items-center justify-center shadow-sm"
                                        >
                                            <span class="text-blue-600 font-semibold text-[9px] sm:text-[10px] leading-none">{{ badge.level }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h3 class="font-bold text-base sm:text-lg mb-1 sm:mb-2 text-gray-900">{{ badge.name }}</h3>
                                    <p class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-3 line-clamp-2">{{ badge.description }}</p>
                                    <p class="text-xs text-gray-500">
                                        Получен {{ formatDate(badge.earned_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Upcoming Badges -->
            <div class="space-y-4 sm:space-y-6">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-star text-purple-600"></i>
                            Следующие цели
                        </h2>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div v-if="upcomingBadges.length === 0" class="text-center py-6 sm:py-8 text-gray-500">
                            <i class="pi pi-check-circle text-3xl sm:text-4xl mb-2"></i>
                            <p class="text-xs sm:text-sm mt-2">Вы получили все доступные достижения!</p>
                        </div>
                        <div v-else class="space-y-3 sm:space-y-4 max-h-[400px] sm:max-h-[600px] overflow-y-auto">
                            <div
                                v-for="badge in upcomingBadges"
                                :key="badge.id"
                                class="p-3 sm:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-md transition-all"
                            >
                                <div class="flex items-center gap-2 sm:gap-3 mb-2 sm:mb-3">
                                    <img
                                        v-if="badge.icon_path"
                                        :src="badge.icon_path"
                                        :alt="badge.name"
                                        class="w-10 h-10 sm:w-12 sm:h-12 object-contain opacity-50 flex-shrink-0"
                                    />
                                    <div
                                        v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                                        class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-200 rounded-full flex items-center justify-center opacity-50 flex-shrink-0"
                                    >
                                        <i :class="['text-2xl sm:text-3xl text-gray-600', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                                    </div>
                                    <div v-else class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-200 rounded-full flex items-center justify-center opacity-50 flex-shrink-0">
                                        <span class="text-xl sm:text-2xl">🏆</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-xs sm:text-sm text-gray-900">{{ badge.name }}</h4>
                                        <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ badge.description }}</p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs text-gray-600">
                                        <span>{{ currentPoints }} / {{ badge.required_points }} очков</span>
                                        <span class="font-medium">{{ getProgressPercentage(badge) }}%</span>
                                    </div>
                                    <ProgressBar
                                        :value="getProgressPercentage(badge)"
                                        color="primary"
                                    />
                                    <p class="text-xs text-gray-500">
                                        Осталось: {{ badge.points_needed }} очков
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Tips -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-lightbulb text-indigo-600"></i>
                            Как получить больше достижений?
                        </h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        <ul class="text-xs text-gray-600 space-y-1.5 sm:space-y-2">
                            <li class="flex items-start">
                                <i class="pi pi-check-circle text-green-600 mr-2 mt-0.5"></i>
                                <span>Проходите симуляторы и получайте очки</span>
                            </li>
                            <li class="flex items-start">
                                <i class="pi pi-check-circle text-green-600 mr-2 mt-0.5"></i>
                                <span>Участвуйте в командных кейсах</span>
                            </li>
                            <li class="flex items-start">
                                <i class="pi pi-check-circle text-green-600 mr-2 mt-0.5"></i>
                                <span>Развивайте навыки до высоких уровней</span>
                            </li>
                            <li class="flex items-start">
                                <i class="pi pi-check-circle text-green-600 mr-2 mt-0.5"></i>
                                <span>Завершайте кейсы успешно</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
