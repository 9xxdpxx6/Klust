<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue'
import ApplicationStatusTimeline from '@/Components/ApplicationStatusTimeline.vue'
import ApplyCaseModal from '@/Components/ApplyCaseModal.vue'

const props = defineProps({
    caseData: {
        type: Object,
        required: true
    },
    applicationStatus: {
        type: Object,
        default: null
    }
})

const showApplyModal = ref(false)
const showWithdrawConfirm = ref(false)

const withdrawApplication = () => {
    showWithdrawConfirm.value = true
}

const confirmWithdraw = () => {
    router.delete(route('student.applications.withdraw', props.applicationStatus.id))
    showWithdrawConfirm.value = false
}

const canApply = computed(() => {
    return !props.applicationStatus && props.caseData.status === 'active'
})

const statusColor = computed(() => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        accepted: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    }
    return colors[props.applicationStatus?.status?.name] || ''
})

const statusText = computed(() => {
    return props.applicationStatus?.status?.label || ''
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<template>
    <Head :title="`Кейс: ${caseData.title}`" />
    <div class="space-y-4 sm:space-y-6">
        <div class="max-w-5xl mx-auto px-4 py-6 sm:py-8">
            <!-- Breadcrumbs -->
            <nav class="mb-4 sm:mb-6 text-xs sm:text-sm">
                <a :href="route('student.cases.index')" class="text-blue-600 hover:underline">Каталог кейсов</a>
                <span class="mx-2">/</span>
                <span class="text-gray-600 truncate block sm:inline">{{ caseData.title }}</span>
            </nav>

            <!-- Заголовок с градиентом -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden mb-4 sm:mb-6">
                <div class="px-4 py-6 sm:px-6 sm:py-8">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-4">{{ caseData.title }}</h1>
                            <div class="flex items-center gap-3 sm:gap-4">
                                <img
                                    v-if="caseData.partner?.logo"
                                    :src="caseData.partner.logo"
                                    :alt="caseData.partner?.company_name"
                                    class="w-12 h-12 sm:w-16 sm:h-16 rounded-lg object-cover bg-white p-1 flex-shrink-0"
                                />
                                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0" v-else>
                                    <i class="pi pi-building text-white text-xl sm:text-2xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs sm:text-sm text-indigo-100">Партнер</p>
                                    <Link
                                        v-if="caseData.user_id && (caseData.partner?.company_name || caseData.partnerUser?.name) && (caseData.partner?.company_name || caseData.partnerUser?.name) !== 'Не указан'"
                                        :href="route('partners.show', caseData.user_id)"
                                        class="text-base sm:text-lg font-semibold text-white truncate hover:underline block"
                                    >
                                        {{ caseData.partner?.company_name || caseData.partnerUser?.name || 'Не указан' }}
                                    </Link>
                                    <p v-else class="text-base sm:text-lg font-semibold text-white truncate">
                                        {{ caseData.partner?.company_name || caseData.partnerUser?.name || 'Не указан' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-if="caseData.status === 'active'" class="px-3 py-2 sm:px-4 bg-green-500 rounded-lg self-start sm:self-auto">
                            <span class="text-white font-semibold text-sm sm:text-base">Активен</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Status -->
            <div v-if="applicationStatus" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-4 sm:mb-6">
                <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-amber-200">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-info-circle text-amber-600"></i>
                        Статус заявки: {{ statusText }}
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p v-if="applicationStatus.status?.name === 'pending'" class="text-sm text-gray-700 mb-2">
                                Ваша заявка ожидает рассмотрения партнером
                            </p>
                            <p v-if="applicationStatus.status?.name === 'accepted'" class="text-sm text-gray-700 mb-2">
                                Поздравляем! Ваша заявка одобрена
                            </p>
                            <p v-if="applicationStatus.status?.name === 'rejected'" class="text-sm text-gray-700 mb-2">
                                К сожалению, ваша заявка была отклонена
                            </p>
                            <p v-if="applicationStatus.rejection_reason" class="mt-2 text-sm text-gray-600">
                                <strong>Причина:</strong> {{ applicationStatus.rejection_reason }}
                            </p>
                            <p v-if="applicationStatus.partner_comment" class="mt-2 text-sm text-gray-600">
                                <strong>Комментарий:</strong> {{ applicationStatus.partner_comment }}
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 sm:ml-4 w-full sm:w-auto">
                            <Button
                                v-if="applicationStatus.status?.name === 'accepted'"
                                variant="primary"
                                class="w-full sm:w-auto"
                                @click="router.visit(route('student.team.show', applicationStatus.id))"
                            >
                                Перейти к команде
                            </Button>
                            <Button
                                v-if="applicationStatus.status?.name === 'pending'"
                                variant="danger"
                                class="w-full sm:w-auto"
                                @click="withdrawApplication"
                            >
                                Отозвать заявку
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Status History -->
            <div v-if="applicationStatus && applicationStatus.statusHistory" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-4 sm:mb-6">
                <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-clock text-blue-600"></i>
                        История статуса заявки
                    </h2>
                </div>
                <div class="p-4 sm:p-6">
                    <ApplicationStatusTimeline :history="applicationStatus.statusHistory" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                    <!-- Description -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                                <i class="pi pi-file-edit text-indigo-600"></i>
                                Описание кейса
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="prose prose-sm sm:prose max-w-none" v-html="caseData.description"></div>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                                <i class="pi pi-list text-green-600"></i>
                                Требования
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="space-y-3 sm:space-y-4">
                                <div class="p-3 sm:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Размер команды</p>
                                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ caseData.required_team_size }} человек</p>
                                </div>
                                <div v-if="caseData.deadline" class="p-3 sm:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Дедлайн</p>
                                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ formatDate(caseData.deadline) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Required Skills -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 sm:px-6 sm:py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                                <i class="pi pi-star text-purple-600"></i>
                                Требуемые навыки
                            </h3>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="skill in caseData.skills"
                                    :key="skill.id"
                                    class="px-2 sm:px-3 py-1 bg-purple-100 text-purple-800 text-xs sm:text-sm font-semibold rounded-lg border border-purple-200"
                                >
                                    {{ skill.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Apply Button -->
                    <div v-if="canApply" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="p-4 sm:p-6">
                            <Button
                                variant="primary"
                                class="w-full"
                                @click="showApplyModal = true"
                            >
                                <i class="pi pi-check mr-2"></i>
                                Подать заявку
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Apply Modal -->
        <ApplyCaseModal
            v-model="showApplyModal"
            :case-data="caseData"
            @success="() => {}"
        />

        <!-- Confirm Dialog для отзыва заявки -->
        <ConfirmDialog
            :visible="showWithdrawConfirm"
            @update:visible="showWithdrawConfirm = $event"
            @confirm="confirmWithdraw"
            title="Подтвердите действие"
            message="Вы уверены, что хотите отозвать заявку?"
            confirm-text="Отозвать"
            cancel-text="Отмена"
            type="warning"
            confirm-variant="danger"
        />
    </div>
</template>
