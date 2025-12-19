<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    cases: {
        type: Object,
        default: () => ({
            current: [],
            pending: [],
            completed: [],
            rejected: []
        })
    }
})

const activeTab = ref('current')

const tabs = computed(() => [
    { key: 'current', label: 'Текущие', count: props.cases?.current?.length || 0, icon: 'pi-briefcase', activeClass: 'bg-indigo-500 text-white' },
    { key: 'pending', label: 'Заявки', count: props.cases?.pending?.length || 0, icon: 'pi-clock', activeClass: 'bg-amber-500 text-white' },
    { key: 'completed', label: 'Завершенные', count: props.cases?.completed?.length || 0, icon: 'pi-check-circle', activeClass: 'bg-green-500 text-white' },
    { key: 'rejected', label: 'Отклоненные', count: props.cases?.rejected?.length || 0, icon: 'pi-times-circle', activeClass: 'bg-red-500 text-white' }
])

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    accepted: 'bg-green-100 text-green-800 border-green-200',
    completed: 'bg-blue-100 text-blue-800 border-blue-200',
    rejected: 'bg-red-100 text-red-800 border-red-200'
}

const roleText = (isLeader) => {
    return isLeader ? 'Лидер команды' : 'Участник'
}

const formatDate = (dateString) => {
    if (!dateString) return 'Не указано'
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).replace(/ г\./g, '').replace(/,$/, '')
}

const viewCase = (application) => {
    router.visit(route('student.cases.show', application.case.id))
}

const viewTeam = (application) => {
    router.visit(route('student.team.show', application.id))
}
</script>

<template>
    <div class="space-y-6">
        <Head title="Мои кейсы" />
        
        <!-- Header with gradient -->
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Мои кейсы</h1>
                        <p class="text-indigo-100">Управление вашими заявками и активными проектами</p>
                    </div>
                    <Button 
                        variant="white-outline" 
                        @click="router.visit(route('student.cases.index'))"
                    >
                        <i class="pi pi-search mr-2"></i>
                        Найти кейсы
                    </Button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <nav class="flex space-x-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all',
                            activeTab === tab.key
                                ? tab.activeClass + ' shadow-sm'
                                : 'text-gray-600 hover:bg-gray-200'
                        ]"
                    >
                        <i :class="['pi', tab.icon]"></i>
                        <span>{{ tab.label }}</span>
                        <span
                            v-if="tab.count > 0"
                            :class="[
                                'ml-1 py-0.5 px-2 rounded-full text-xs font-semibold',
                                activeTab === tab.key
                                    ? 'bg-white/20 text-white'
                                    : 'bg-gray-300 text-gray-700'
                            ]"
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Current Cases -->
            <div v-if="activeTab === 'current'" class="p-6">
                <div v-if="!cases?.current || cases.current.length === 0" class="text-center py-12">
                    <i class="pi pi-briefcase text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500 mb-4">У вас пока нет активных кейсов</p>
                    <Button variant="primary" @click="router.visit(route('student.cases.index'))">
                        <i class="pi pi-search mr-2"></i>
                        Найти кейс
                    </Button>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="application in cases.current"
                        :key="application.id"
                        class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all flex flex-col h-full"
                    >
                        <div class="p-6 flex flex-col h-full">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ application.case.title }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="pi pi-building"></i>
                                        <span class="truncate">{{ application.case.partner?.company_name || application.case.partnerUser?.name || 'Не указан' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <span :class="[statusColors.accepted, 'border', 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold']">
                                        <i class="pi pi-check-circle mr-1"></i>
                                        Активен
                                    </span>
                                    <Badge variant="secondary" class="border border-gray-200">
                                        <i :class="[application.is_leader ? 'pi pi-star' : 'pi pi-users', 'mr-1']"></i>
                                        {{ roleText(application.is_leader) }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="mt-4 flex-grow flex flex-col justify-end">
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <i class="pi pi-calendar"></i>
                                    <span>Подано: {{ formatDate(application.created_at) }}</span>
                                </div>
                                <div class="border-t border-gray-200 pt-2">
                                    <div class="flex gap-2">
                                        <Button
                                            variant="primary"
                                            size="sm"
                                            class="flex-1"
                                            @click="viewTeam(application)"
                                        >
                                            <i class="pi pi-users mr-2"></i>
                                            Команда
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="flex-1"
                                            @click="viewCase(application)"
                                        >
                                            <i class="pi pi-eye mr-2"></i>
                                            Кейс
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Applications -->
            <div v-if="activeTab === 'pending'" class="p-6">
                <div v-if="!cases?.pending || cases.pending.length === 0" class="text-center py-12">
                    <i class="pi pi-clock text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">У вас нет ожидающих рассмотрения заявок</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="application in cases.pending"
                        :key="application.id"
                        class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all flex flex-col h-full"
                    >
                        <div class="p-6 flex flex-col h-full">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ application.case.title }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="pi pi-building"></i>
                                        <span class="truncate">{{ application.case.partner?.company_name || application.case.partnerUser?.name || 'Не указан' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <span :class="[statusColors.pending, 'border', 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold']">
                                        <i class="pi pi-clock mr-1"></i>
                                        Ожидает рассмотрения
                                    </span>
                                    <Badge variant="secondary" class="border border-gray-200">
                                        <i :class="[application.is_leader ? 'pi pi-star' : 'pi pi-users', 'mr-1']"></i>
                                        {{ roleText(application.is_leader) }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="mt-4 flex-grow flex flex-col justify-end">
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <i class="pi pi-calendar"></i>
                                    <span>Подано: {{ formatDate(application.created_at) }}</span>
                                </div>
                                <div class="border-t border-gray-200 pt-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="w-full"
                                        @click="viewCase(application)"
                                    >
                                        <i class="pi pi-eye mr-2"></i>
                                        Просмотреть кейс
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Cases -->
            <div v-if="activeTab === 'completed'" class="p-6">
                <div v-if="!cases?.completed || cases.completed.length === 0" class="text-center py-12">
                    <i class="pi pi-check-circle text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">У вас пока нет завершенных кейсов</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="application in cases.completed"
                        :key="application.id"
                        class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all flex flex-col h-full"
                    >
                        <div class="p-6 flex flex-col h-full">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ application.case.title }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="pi pi-building"></i>
                                        <span class="truncate">{{ application.case.partner?.company_name || application.case.partnerUser?.name || 'Не указан' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <span :class="[statusColors.completed, 'border', 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold']">
                                        <i class="pi pi-check-circle mr-1"></i>
                                        Завершен
                                    </span>
                                    <Badge variant="secondary" class="border border-gray-200">
                                        <i :class="[application.is_leader ? 'pi pi-star' : 'pi pi-users', 'mr-1']"></i>
                                        {{ roleText(application.is_leader) }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="mt-4 flex-grow flex flex-col justify-end">
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <i class="pi pi-calendar"></i>
                                    <span>Завершено: {{ formatDate(application.updated_at) }}</span>
                                </div>
                                <div class="border-t border-gray-200 pt-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="w-full"
                                        @click="viewCase(application)"
                                    >
                                        <i class="pi pi-eye mr-2"></i>
                                        Просмотреть
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejected Applications -->
            <div v-if="activeTab === 'rejected'" class="p-6">
                <div v-if="!cases?.rejected || cases.rejected.length === 0" class="text-center py-12">
                    <i class="pi pi-times-circle text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">У вас нет отклоненных заявок</p>
                </div>
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="application in cases.rejected"
                        :key="application.id"
                        class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all flex flex-col h-full"
                    >
                        <div class="p-6 flex flex-col h-full">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ application.case.title }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <i class="pi pi-building"></i>
                                        <span class="truncate">{{ application.case.partner?.company_name || application.case.partnerUser?.name || 'Не указан' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap">
                                    <span :class="[statusColors.rejected, 'border', 'inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold']">
                                        <i class="pi pi-times-circle mr-1"></i>
                                        Отклонена
                                    </span>
                                    <Badge variant="secondary" class="border border-gray-200">
                                        <i :class="[application.is_leader ? 'pi pi-star' : 'pi pi-users', 'mr-1']"></i>
                                        {{ roleText(application.is_leader) }}
                                    </Badge>
                                </div>

                                <div v-if="application.rejection_reason" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm font-medium text-red-800 mb-1 flex items-center gap-2">
                                        <i class="pi pi-info-circle"></i>
                                        Причина отклонения:
                                    </p>
                                    <p class="text-sm text-red-700">{{ application.rejection_reason }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex-grow flex flex-col justify-end">
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <i class="pi pi-calendar"></i>
                                    <span>Отклонено: {{ formatDate(application.updated_at) }}</span>
                                </div>
                                <div class="border-t border-gray-200 pt-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="w-full"
                                        @click="viewCase(application)"
                                    >
                                        <i class="pi pi-eye mr-2"></i>
                                        Просмотреть кейс
                                    </Button>
                                </div>
                            </div>
                        </div>
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
