<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
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
    <Head title="Мои достижения" />
    <div class="space-y-6">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-6">Мои достижения</h1>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <Card class="text-center">
                    <p class="text-4xl font-bold text-blue-600">{{ totalBadges }}</p>
                    <p class="text-gray-600 mt-2">Получено достижений</p>
                </Card>
                <Card class="text-center">
                    <p class="text-4xl font-bold text-green-600">{{ currentPoints }}</p>
                    <p class="text-gray-600 mt-2">Всего очков</p>
                </Card>
                <Card class="text-center" v-if="recentBadge">
                    <div class="flex justify-center mb-2">
                        <img
                            v-if="recentBadge.icon_path"
                            :src="recentBadge.icon_path"
                            :alt="recentBadge.name"
                            class="w-12 h-12 object-contain"
                        />
                        <i
                            v-else-if="recentBadge.icon && (recentBadge.icon.startsWith('pi-') || recentBadge.icon.startsWith('fa-'))"
                            :class="['text-[48px] text-yellow-600', recentBadge.icon.startsWith('fa-') ? `pi pi-${recentBadge.icon.replace('fa-', '')}` : `pi ${recentBadge.icon}`]"
                        ></i>
                        <div v-else class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">🏆</span>
                        </div>
                    </div>
                    <p class="text-sm font-medium text-gray-700">{{ recentBadge.name }}</p>
                    <p class="text-xs text-gray-500 mt-1">Последнее достижение</p>
                </Card>
                <Card class="text-center" v-else>
                    <p class="text-2xl text-gray-400 mb-2">🎯</p>
                    <p class="text-gray-600">Получите первое достижение!</p>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Earned Badges -->
                <div class="lg:col-span-2">
                    <Card>
                        <h2 class="text-xl font-bold mb-6">Полученные достижения</h2>
                        <div v-if="earnedBadges.length === 0" class="text-center py-12">
                            <div class="text-6xl mb-4">🏆</div>
                            <p class="text-gray-500 mb-2">У вас пока нет достижений</p>
                            <p class="text-sm text-gray-400">
                                Проходите симуляторы и работайте над кейсами, чтобы получить свое первое достижение!
                            </p>
                        </div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            <div
                                v-for="badge in earnedBadges"
                                :key="badge.id"
                                class="group relative"
                            >
                                <div class="text-center p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl hover:shadow-lg transition-all hover:scale-105">
                                    <div class="relative inline-block mb-4">
                                        <img
                                            v-if="badge.icon_path"
                                            :src="badge.icon_path"
                                            :alt="badge.name"
                                            class="w-24 h-24 object-contain mx-auto"
                                        />
                                        <div
                                            v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                                            class="w-24 h-24 bg-yellow-200 rounded-full flex items-center justify-center mx-auto"
                                        >
                                            <i :class="['text-[60px] text-yellow-700', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                                        </div>
                                        <div v-else class="w-24 h-24 bg-yellow-200 rounded-full flex items-center justify-center mx-auto">
                                            <span class="text-4xl">🏆</span>
                                        </div>
                                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 class="font-bold text-lg mb-2">{{ badge.name }}</h3>
                                    <p class="text-sm text-gray-600 mb-3">{{ badge.description }}</p>
                                    <p class="text-xs text-gray-500">
                                        Получен {{ formatDate(badge.earned_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Upcoming Badges -->
                <div>
                    <Card>
                        <h2 class="text-lg font-bold mb-4">Следующие цели</h2>
                        <div v-if="upcomingBadges.length === 0" class="text-center py-8 text-gray-500">
                            <p>🎉</p>
                            <p class="text-sm mt-2">Вы получили все доступные достижения!</p>
                        </div>
                        <div v-else class="space-y-6 max-h-[700px] overflow-y-auto">
                            <div
                                v-for="badge in upcomingBadges"
                                :key="badge.id"
                                class="p-4 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <img
                                        v-if="badge.icon_path"
                                        :src="badge.icon_path"
                                        :alt="badge.name"
                                        class="w-12 h-12 object-contain opacity-50"
                                    />
                                    <div
                                        v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                                        class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center opacity-50"
                                    >
                                        <i :class="['text-3xl text-gray-600', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                                    </div>
                                    <div v-else class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center opacity-50">
                                        <span class="text-2xl">🏆</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-sm">{{ badge.name }}</h4>
                                        <p class="text-xs text-gray-600 mt-1">{{ badge.description }}</p>
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
                    </Card>

                    <!-- Tips -->
                    <Card class="mt-6">
                        <h3 class="text-sm font-bold mb-3">💡 Как получить больше достижений?</h3>
                        <ul class="text-xs text-gray-600 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>Проходите симуляторы и получайте очки</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>Участвуйте в командных кейсах</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>Развивайте навыки до высоких уровней</span>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">•</span>
                                <span>Завершайте кейсы успешно</span>
                            </li>
                        </ul>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
