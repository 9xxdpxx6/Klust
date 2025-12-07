<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import Input from '@/Components/UI/Input.vue'
import UserAvatar from '@/Components/Shared/UserAvatar.vue'
import ProgressBar from '@/Components/UI/ProgressBar.vue'

const props = defineProps({
    application: {
        type: Object,
        required: true
    },
    teamProgress: {
        type: Object,
        required: true
    },
    teamActivity: {
        type: Array,
        default: () => []
    },
    isLeader: {
        type: Boolean,
        default: false
    }
})

const showAddMemberModal = ref(false)

const addMemberForm = useForm({
    user_id: ''
})

const submitAddMember = () => {
    addMemberForm.post(route('student.team.addMember', props.application.id), {
        onSuccess: () => {
            showAddMemberModal.value = false
            addMemberForm.reset()
        }
    })
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

const skillsMatchPercentage = () => {
    if (teamProgress.case_required_skills === 0) return 100
    return Math.round((teamProgress.matching_skills_count / (teamProgress.case_required_skills * teamProgress.team_size)) * 100)
}
</script>

<template>
    <div class="space-y-6">
        <Head :title="`Команда: ${application.case.title}`" />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Breadcrumbs -->
            <nav class="mb-6 text-sm">
                <a :href="route('student.cases.my')" class="text-blue-600 hover:underline">Мои кейсы</a>
                <span class="mx-2">/</span>
                <span class="text-gray-600">Команда</span>
            </nav>

            <!-- Case Info Header -->
            <Card class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">{{ application.case.title }}</h1>
                        <p class="text-gray-600">{{ application.case.partner.company_name }}</p>
                    </div>
                    <Badge variant="success" class="text-lg">
                        Активная команда
                    </Badge>
                </div>
            </Card>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Team Progress -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Progress Stats -->
                    <Card>
                        <h2 class="text-xl font-bold mb-4">Прогресс команды</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-3xl font-bold text-blue-600">{{ teamProgress.team_size }}</p>
                                <p class="text-sm text-gray-600 mt-1">Участников</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-3xl font-bold text-green-600">{{ teamProgress.total_skill_points }}</p>
                                <p class="text-sm text-gray-600 mt-1">Всего очков</p>
                            </div>
                            <div class="text-center p-4 bg-purple-50 rounded-lg">
                                <p class="text-3xl font-bold text-purple-600">{{ teamProgress.average_skill_points }}</p>
                                <p class="text-sm text-gray-600 mt-1">Средние очки</p>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                <p class="text-3xl font-bold text-yellow-600">{{ skillsMatchPercentage() }}%</p>
                                <p class="text-sm text-gray-600 mt-1">Соответствие</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium">Навыки команды</span>
                                <span class="text-sm text-gray-600">
                                    {{ teamProgress.matching_skills_count }} / {{ teamProgress.case_required_skills * teamProgress.team_size }}
                                </span>
                            </div>
                            <ProgressBar
                                :value="skillsMatchPercentage()"
                                :color="skillsMatchPercentage() >= 70 ? 'green' : skillsMatchPercentage() >= 40 ? 'yellow' : 'red'"
                            />
                        </div>
                    </Card>

                    <!-- Team Members -->
                    <Card>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold">Участники команды</h2>
                            <Button
                                v-if="isLeader"
                                variant="primary"
                                size="sm"
                                @click="showAddMemberModal = true"
                            >
                                Добавить участника
                            </Button>
                        </div>
                        <div class="space-y-4">
                            <div
                                v-for="member in teamProgress.members"
                                :key="member.id"
                                class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg"
                            >
                                <UserAvatar
                                    :src="member.avatar"
                                    :name="member.name"
                                    size="lg"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold">{{ member.name }}</h3>
                                        <Badge
                                            v-if="member.id === application.leader_id"
                                            variant="primary"
                                            size="sm"
                                        >
                                            Лидер
                                        </Badge>
                                    </div>
                                    <div class="flex gap-4 mt-1 text-sm text-gray-600">
                                        <span>💎 {{ member.total_points }} очков</span>
                                        <span>⭐ {{ member.skills_count }} навыков</span>
                                        <span>🏆 {{ member.badges_count }} бейджей</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Activity History -->
                    <Card>
                        <h2 class="text-xl font-bold mb-4">История активности</h2>
                        <div v-if="teamActivity.length === 0" class="text-center py-8 text-gray-500">
                            Пока нет активности
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="(activity, index) in teamActivity"
                                :key="index"
                                class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg"
                            >
                                <UserAvatar
                                    :src="activity.user.avatar"
                                    :name="activity.user.name"
                                    size="sm"
                                />
                                <div class="flex-1">
                                    <p class="text-sm">
                                        <span class="font-semibold">{{ activity.user.name }}</span>
                                        {{ activity.description }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">{{ formatDate(activity.date) }}</p>
                                </div>
                                <Badge
                                    v-if="activity.type === 'skill_progress'"
                                    variant="success"
                                    size="sm"
                                >
                                    +{{ activity.data.points }}
                                </Badge>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Case Details -->
                    <Card>
                        <h3 class="text-lg font-bold mb-3">Детали кейса</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Статус</p>
                                <p class="font-semibold">{{ application.status?.name === 'accepted' ? 'Активен' : (application.status?.label || application.status?.name) }}</p>
                            </div>
                            <div v-if="application.case.deadline">
                                <p class="text-sm text-gray-600">Дедлайн</p>
                                <p class="font-semibold">{{ formatDate(application.case.deadline) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Дата начала</p>
                                <p class="font-semibold">{{ formatDate(application.created_at) }}</p>
                            </div>
                        </div>
                    </Card>

                    <!-- Required Skills -->
                    <Card>
                        <h3 class="text-lg font-bold mb-3">Требуемые навыки</h3>
                        <div class="flex flex-wrap gap-2">
                            <Badge
                                v-for="skill in application.case.skills"
                                :key="skill.id"
                                variant="primary"
                            >
                                {{ skill.name }}
                            </Badge>
                        </div>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <div class="space-y-2">
                            <Button
                                variant="secondary"
                                class="w-full"
                                @click="$inertia.visit(route('student.cases.show', application.case.id))"
                            >
                                Просмотреть кейс
                            </Button>
                        </div>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Add Member Modal -->
        <Modal v-model="showAddMemberModal" title="Добавить участника">
            <form @submit.prevent="submitAddMember" novalidate>
                <div class="space-y-4">
                    <Input
                        v-model="addMemberForm.user_id"
                        label="ID пользователя или Email"
                        placeholder="Введите ID или email студента"
                        :error="addMemberForm.errors.user_id"
                        required
                    />

                    <div class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="secondary"
                            @click="showAddMemberModal = false"
                        >
                            Отмена
                        </Button>
                        <Button
                            type="submit"
                            variant="primary"
                            :disabled="addMemberForm.processing"
                        >
                            Добавить
                        </Button>
                    </div>
                </div>
            </form>
        </Modal>
    </div>
</template>
