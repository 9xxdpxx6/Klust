<script setup>
import { ref, computed } from 'vue'
import { router, Link, Head, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import Button from '@/Components/UI/Button.vue'
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue'
import ApplicationStatusTimeline from '@/Components/ApplicationStatusTimeline.vue'
import ApplyCaseModal from '@/Components/ApplyCaseModal.vue'
import { useResponsive } from '@/Composables/useResponsive'

const page = usePage()
const { isMobile } = useResponsive()

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

// Проверка авторизации
const isAuthenticated = computed(() => {
    return page.props.auth?.user !== null && page.props.auth?.user !== undefined
})

// Проверка роли студента
const isStudent = computed(() => {
    if (!isAuthenticated.value) {
        return false
    }
    const roles = page.props.auth.user.roles || []
    return roles.includes('student')
})

const withdrawApplication = () => {
    showWithdrawConfirm.value = true
}

const confirmWithdraw = () => {
    router.delete(route('student.applications.withdraw', props.applicationStatus.id))
    showWithdrawConfirm.value = false
}

const canApply = computed(() => {
    return isStudent.value && !props.applicationStatus && props.caseData.status === 'active'
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
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Кейс: ${caseData.title}`" />
        <!-- Hero Section -->
        <section :class="[
            'bg-gradient-to-br from-primary to-primary-dark text-white',
            isMobile ? 'py-8' : 'py-16'
        ]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumbs -->
                <nav :class="[
                    'text-sm mb-4',
                    isMobile ? 'mb-3' : 'mb-6'
                ]">
                    <Link :href="route('guest.cases')" class="text-white/80 hover:text-white transition-colors">Каталог кейсов</Link>
                    <span class="mx-2 text-white/60">/</span>
                    <span class="text-white/90" :class="isMobile ? 'text-xs' : ''">{{ caseData.title }}</span>
                </nav>

                <div :class="[
                    'flex items-start gap-6',
                    isMobile ? 'flex-col gap-4' : ''
                ]">
                    <div class="flex-1">
                        <h1 :class="[
                            'font-extrabold mb-4',
                            isMobile ? 'text-2xl mb-3' : 'text-4xl md:text-5xl'
                        ]">{{ caseData.title }}</h1>
                        <div :class="[
                            'flex items-center gap-4',
                            isMobile ? 'gap-3' : ''
                        ]">
                            <img
                                v-if="caseData.partner?.logo"
                                :src="caseData.partner.logo"
                                :alt="caseData.partner?.company_name"
                                :class="[
                                    'rounded-lg object-cover bg-white p-1',
                                    isMobile ? 'w-12 h-12' : 'w-16 h-16'
                                ]"
                            />
                            <div :class="[
                                'bg-white/20 rounded-lg flex items-center justify-center',
                                isMobile ? 'w-12 h-12' : 'w-16 h-16'
                            ]" v-else>
                                <svg :class="[
                                    'text-white',
                                    isMobile ? 'w-6 h-6' : 'w-8 h-8'
                                ]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <p :class="[
                                    'text-white/80',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">Партнер</p>
                                <template v-if="caseData.user_id">
                                    <Link
                                        :href="route('partners.show', caseData.user_id)"
                                        :class="[
                                            'font-semibold text-white hover:underline block truncate',
                                            isMobile ? 'text-sm' : 'text-lg'
                                        ]"
                                    >
                                        {{ caseData.partner?.company_name || caseData.partner?.user?.name || 'Не указан' }}
                                    </Link>
                                </template>
                                <p v-else :class="[
                                    'font-semibold text-white truncate',
                                    isMobile ? 'text-sm' : 'text-lg'
                                ]">
                                    {{ caseData.partner?.company_name || caseData.partner?.user?.name || 'Не указан' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-if="caseData.status === 'active'" :class="[
                        'bg-green-500 rounded-lg flex-shrink-0',
                        isMobile ? 'px-3 py-1.5 text-sm' : 'px-4 py-2'
                    ]">
                        <span class="text-white font-semibold">Активен</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section :class="[
            'bg-surface',
            isMobile ? 'py-6' : 'py-12'
        ]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div :class="[
                    'space-y-6',
                    isMobile ? 'space-y-4' : ''
                ]">

                    <!-- Application Status - только для авторизованных студентов -->
                    <div v-if="isStudent && applicationStatus" class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                        <div class="px-6 py-4 border-b border-border-light bg-amber-50">
                            <h2 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Статус заявки: {{ statusText }}
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p v-if="applicationStatus.status?.name === 'pending'" class="text-sm text-text-secondary mb-2">
                                        Ваша заявка ожидает рассмотрения партнером
                                    </p>
                                    <p v-if="applicationStatus.status?.name === 'accepted'" class="text-sm text-text-secondary mb-2">
                                        Поздравляем! Ваша заявка одобрена
                                    </p>
                                    <p v-if="applicationStatus.status?.name === 'rejected'" class="text-sm text-text-secondary mb-2">
                                        К сожалению, ваша заявка была отклонена
                                    </p>
                                    <p v-if="applicationStatus.rejection_reason" class="mt-2 text-sm text-text-secondary">
                                        <strong>Причина:</strong> {{ applicationStatus.rejection_reason }}
                                    </p>
                                    <p v-if="applicationStatus.partner_comment" class="mt-2 text-sm text-text-secondary">
                                        <strong>Комментарий:</strong> {{ applicationStatus.partner_comment }}
                                    </p>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <Button
                                        v-if="applicationStatus.status?.name === 'accepted'"
                                        variant="primary"
                                        @click="router.visit(route('student.team.show', applicationStatus.id))"
                                    >
                                        Перейти к команде
                                    </Button>
                                    <Button
                                        v-if="applicationStatus.status?.name === 'pending'"
                                        variant="danger"
                                        @click="withdrawApplication"
                                    >
                                        Отозвать заявку
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Application Status History - только для авторизованных студентов -->
                    <div v-if="isStudent && applicationStatus && applicationStatus.statusHistory" class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                        <div class="px-6 py-4 border-b border-border-light bg-blue-50">
                            <h2 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                История статуса заявки
                            </h2>
                        </div>
                        <div class="p-6">
                            <ApplicationStatusTimeline :history="applicationStatus.statusHistory" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Content -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Description -->
                            <div class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                                <div class="px-6 py-4 border-b border-border-light">
                                    <h2 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Описание кейса
                                    </h2>
                                </div>
                                <div class="p-6">
                                    <div class="prose max-w-none text-text-secondary" v-html="caseData.description"></div>
                                </div>
                            </div>

                            <!-- Requirements -->
                            <div class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                                <div class="px-6 py-4 border-b border-border-light">
                                    <h2 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Требования
                                    </h2>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-4">
                                        <div class="p-4 bg-surface rounded-lg border border-border-light">
                                            <p class="text-sm text-text-secondary mb-1">Размер команды</p>
                                            <p class="text-lg font-semibold text-text-primary">{{ caseData.required_team_size }} человек</p>
                                        </div>
                                        <div v-if="caseData.deadline" class="p-4 bg-surface rounded-lg border border-border-light">
                                            <p class="text-sm text-text-secondary mb-1">Дедлайн</p>
                                            <p class="text-lg font-semibold text-text-primary">{{ formatDate(caseData.deadline) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Required Skills -->
                            <div class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                                <div class="px-6 py-4 border-b border-border-light">
                                    <h3 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                        Требуемые навыки
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="skill in caseData.skills"
                                            :key="skill.id"
                                            class="px-3 py-1 bg-primary/10 text-primary text-sm font-medium rounded"
                                        >
                                            {{ skill.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Apply Button - только для авторизованных студентов -->
                            <div v-if="canApply" class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                                <div class="p-6">
                                    <Button
                                        variant="primary"
                                        class="w-full"
                                        @click="showApplyModal = true"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Подать заявку
                                    </Button>
                                </div>
                            </div>

                            <!-- Призыв к регистрации для неавторизованных -->
                            <div v-if="!isAuthenticated && caseData.status === 'active'" class="bg-blue-50 border border-blue-200 rounded-xl overflow-hidden">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-blue-900 mb-2">
                                        Хотите подать заявку?
                                    </h3>
                                    <p class="text-sm text-blue-800 mb-4">
                                        Зарегистрируйтесь на платформе, чтобы подавать заявки на кейсы и работать над реальными проектами.
                                    </p>
                                    <Link
                                        :href="route('register')"
                                        class="block w-full px-4 py-2 text-center text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-dark transition-colors"
                                    >
                                        Зарегистрироваться
                                    </Link>
                                </div>
                            </div>

                            <!-- Призыв к регистрации для авторизованных, но не студентов -->
                            <div v-if="isAuthenticated && !isStudent && caseData.status === 'active'" class="bg-amber-50 border border-amber-200 rounded-xl overflow-hidden">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-amber-900 mb-2">
                                        Только для студентов
                                    </h3>
                                    <p class="text-sm text-amber-800">
                                        Подавать заявки на кейсы могут только студенты. Если вы студент, убедитесь, что ваш профиль настроен правильно.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Apply Modal - только для авторизованных студентов -->
        <ApplyCaseModal
            v-if="isStudent"
            v-model="showApplyModal"
            :case-data="caseData"
            @success="() => {}"
        />

        <!-- Confirm Dialog для отзыва заявки -->
        <ConfirmDialog
            v-if="isStudent"
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
    </PublicLayout>
</template>

