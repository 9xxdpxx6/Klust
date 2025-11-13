<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import Button from '@/Components/UI/Button.vue'
import Badge from '@/Components/UI/Badge.vue'
import Card from '@/Components/UI/Card.vue'
import Modal from '@/Components/UI/Modal.vue'
import Textarea from '@/Components/UI/Textarea.vue'
import Input from '@/Components/UI/Input.vue'
import ApplicationStatusTimeline from '@/Components/ApplicationStatusTimeline.vue'

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

const applyForm = useForm({
    message: '',
    team_members: []
})

const newMemberEmail = ref('')

const addTeamMember = () => {
    if (newMemberEmail.value && applyForm.team_members.length < props.caseData.required_team_size - 1) {
        applyForm.team_members.push(newMemberEmail.value)
        newMemberEmail.value = ''
    }
}

const removeMember = (index) => {
    applyForm.team_members.splice(index, 1)
}

const submitApplication = () => {
    applyForm.post(route('student.cases.apply', props.caseData.id), {
        onSuccess: () => {
            showApplyModal.value = false
        }
    })
}

const withdrawApplication = () => {
    if (confirm('Вы уверены, что хотите отозвать заявку?')) {
        router.delete(route('student.applications.withdraw', props.applicationStatus.id))
    }
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
    <StudentLayout>
        <div class="max-w-5xl mx-auto px-4 py-8">
            <!-- Breadcrumbs -->
            <nav class="mb-6 text-sm">
                <a :href="route('student.cases.index')" class="text-blue-600 hover:underline">Каталог кейсов</a>
                <span class="mx-2">/</span>
                <span class="text-gray-600">{{ caseData.title }}</span>
            </nav>

            <!-- Case Header -->
            <Card class="mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold mb-4">{{ caseData.title }}</h1>
                        <div class="flex items-center gap-4">
                            <img
                                v-if="caseData.partner.logo"
                                :src="caseData.partner.logo"
                                :alt="caseData.partner.company_name"
                                class="w-16 h-16 rounded-lg object-cover"
                            />
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center" v-else>
                                <span class="text-2xl text-gray-400">🏢</span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Партнер</p>
                                <p class="text-lg font-semibold">{{ caseData.partner.company_name }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-if="caseData.status === 'active'" class="text-green-600 font-semibold">
                        Активен
                    </div>
                </div>
            </Card>

            <!-- Application Status -->
            <Card v-if="applicationStatus" class="mb-6" :class="statusColor">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-lg mb-2">Статус заявки: {{ statusText }}</h3>
                        <p v-if="applicationStatus.status?.name === 'pending'" class="text-sm">
                            Ваша заявка ожидает рассмотрения партнером
                        </p>
                        <p v-if="applicationStatus.status?.name === 'accepted'" class="text-sm">
                            Поздравляем! Ваша заявка одобрена
                        </p>
                        <p v-if="applicationStatus.status?.name === 'rejected'" class="text-sm">
                            К сожалению, ваша заявка была отклонена
                        </p>
                        <p v-if="applicationStatus.rejection_reason" class="mt-2 text-sm">
                            <strong>Причина:</strong> {{ applicationStatus.rejection_reason }}
                        </p>
                        <p v-if="applicationStatus.partner_comment" class="mt-2 text-sm">
                            <strong>Комментарий:</strong> {{ applicationStatus.partner_comment }}
                        </p>
                    </div>
                    <div class="flex gap-2">
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
            </Card>

            <!-- Application Status History -->
            <Card v-if="applicationStatus && applicationStatus.statusHistory" class="mb-6">
                <h3 class="text-xl font-bold mb-4">История статуса заявки</h3>
                <ApplicationStatusTimeline :history="applicationStatus.statusHistory" />
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Description -->
                    <Card class="mb-6">
                        <h2 class="text-xl font-bold mb-4">Описание кейса</h2>
                        <div class="prose max-w-none" v-html="caseData.description"></div>
                    </Card>

                    <!-- Requirements -->
                    <Card>
                        <h2 class="text-xl font-bold mb-4">Требования</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600">Размер команды</p>
                                <p class="text-lg font-semibold">{{ caseData.required_team_size }} человек</p>
                            </div>
                            <div v-if="caseData.deadline">
                                <p class="text-sm text-gray-600">Дедлайн</p>
                                <p class="text-lg font-semibold">{{ formatDate(caseData.deadline) }}</p>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div>
                    <!-- Required Skills -->
                    <Card class="mb-6">
                        <h3 class="text-lg font-bold mb-3">Требуемые навыки</h3>
                        <div class="flex flex-wrap gap-2">
                            <Badge
                                v-for="skill in caseData.skills"
                                :key="skill.id"
                                variant="primary"
                            >
                                {{ skill.name }}
                            </Badge>
                        </div>
                    </Card>

                    <!-- Apply Button -->
                    <Card v-if="canApply">
                        <Button
                            variant="primary"
                            class="w-full"
                            @click="showApplyModal = true"
                        >
                            Подать заявку
                        </Button>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Apply Modal -->
        <Modal v-model="showApplyModal" title="Подать заявку на кейс">
            <form @submit.prevent="submitApplication">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">
                            Сообщение для партнера
                        </label>
                        <Textarea
                            v-model="applyForm.message"
                            rows="4"
                            placeholder="Расскажите, почему вы хотите работать над этим кейсом..."
                            :error="applyForm.errors.message"
                        />
                    </div>

                    <div v-if="caseData.required_team_size > 1">
                        <label class="block text-sm font-medium mb-2">
                            Члены команды ({{ applyForm.team_members.length }}/{{ caseData.required_team_size - 1 }})
                        </label>
                        <div class="space-y-2 mb-3">
                            <div
                                v-for="(member, index) in applyForm.team_members"
                                :key="index"
                                class="flex items-center gap-2"
                            >
                                <Input
                                    :model-value="member"
                                    disabled
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="danger"
                                    size="sm"
                                    @click="removeMember(index)"
                                >
                                    Удалить
                                </Button>
                            </div>
                        </div>
                        <div class="flex gap-2" v-if="applyForm.team_members.length < caseData.required_team_size - 1">
                            <Input
                                v-model="newMemberEmail"
                                type="email"
                                placeholder="Email участника"
                                class="flex-1"
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                @click="addTeamMember"
                            >
                                Добавить
                            </Button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <Button
                            type="button"
                            variant="secondary"
                            @click="showApplyModal = false"
                        >
                            Отмена
                        </Button>
                        <Button
                            type="submit"
                            variant="primary"
                            :disabled="applyForm.processing"
                        >
                            Подать заявку
                        </Button>
                    </div>
                </div>
            </form>
        </Modal>
    </StudentLayout>
</template>
