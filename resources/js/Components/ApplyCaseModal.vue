<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Modal from '@/Components/UI/Modal.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Textarea from '@/Components/UI/Textarea.vue'

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },
    caseData: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['update:modelValue', 'success'])

const applyForm = useForm({
    motivation: '',
    team_member_emails: []
})

const newMemberEmail = ref('')
const teamMemberEmails = ref([])
const emailError = ref('')

const validateEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
}

const addTeamMember = () => {
    emailError.value = ''
    
    if (!newMemberEmail.value) {
        emailError.value = 'Введите email'
        return
    }
    
    const email = newMemberEmail.value.trim().toLowerCase()
    
    // Проверка формата email
    if (!validateEmail(email)) {
        emailError.value = 'Некорректный формат email'
        return
    }
    
    // Проверка на дубликаты
    if (teamMemberEmails.value.includes(email)) {
        emailError.value = 'Этот email уже добавлен'
        return
    }
    
    // Проверка лимита участников
    if (teamMemberEmails.value.length >= props.caseData.required_team_size - 1) {
        emailError.value = `Максимум ${props.caseData.required_team_size - 1} участников`
        return
    }
    
    teamMemberEmails.value.push(email)
    newMemberEmail.value = ''
    emailError.value = ''
}

const removeMember = (index) => {
    teamMemberEmails.value.splice(index, 1)
    // Очищаем ошибку для этого email
    if (applyForm.errors[`team_member_emails.${index}`]) {
        delete applyForm.errors[`team_member_emails.${index}`]
    }
}

const getEmailError = (index) => {
    return applyForm.errors[`team_member_emails.${index}`] || null
}

const submitApplication = (event) => {
    if (event) {
        event.preventDefault()
        event.stopPropagation()
    }
    
    // Валидация на фронте
    if (!applyForm.motivation || !applyForm.motivation.trim()) {
        applyForm.setError('motivation', 'Мотивационное письмо обязательно для заполнения.')
        return
    }
    
    // Обновляем данные формы перед отправкой
    applyForm.team_member_emails = teamMemberEmails.value
    
    // Отправляем POST запрос
    applyForm.post(route('student.cases.apply', props.caseData.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:modelValue', false)
            applyForm.reset()
            teamMemberEmails.value = []
            newMemberEmail.value = ''
            emailError.value = ''
            emit('success')
        },
        onError: (errors) => {
            console.error('Application errors:', errors)
            // Показываем общую ошибку, если есть
            if (errors.team_member_emails && typeof errors.team_member_emails === 'string') {
                emailError.value = errors.team_member_emails
            }
        }
    })
}

const closeModal = () => {
    emit('update:modelValue', false)
    // Сбрасываем форму при закрытии
    if (!applyForm.processing) {
        applyForm.reset()
        teamMemberEmails.value = []
        newMemberEmail.value = ''
        emailError.value = ''
    }
}
</script>

<template>
    <Modal :model-value="modelValue" @update:model-value="closeModal" title="Подать заявку на кейс" :show-default-footer="false">
        <form @submit.prevent.stop="submitApplication" novalidate>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Мотивационное письмо <span class="text-red-500">*</span>
                    </label>
                    <Textarea
                        v-model="applyForm.motivation"
                        :rows="4"
                        placeholder="Расскажите, почему вы хотите работать над этим кейсом..."
                        :error="applyForm.errors.motivation"
                        required
                    />
                </div>

                <div v-if="caseData.required_team_size > 1">
                    <label class="block text-sm font-medium mb-2">
                        Члены команды ({{ teamMemberEmails.length }}/{{ caseData.required_team_size - 1 }})
                    </label>
                    <div class="space-y-2 mb-3">
                        <div
                            v-for="(email, index) in teamMemberEmails"
                            :key="index"
                        >
                            <div class="flex items-center gap-2">
                                <Input
                                    :model-value="email"
                                    disabled
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="danger-outline"
                                    @click="removeMember(index)"
                                >
                                    <i class="pi pi-trash"></i>
                                </Button>
                            </div>
                            <p v-if="getEmailError(index)" class="text-sm text-red-600 mt-1">
                                {{ getEmailError(index) }}
                            </p>
                        </div>
                    </div>
                    <div v-if="teamMemberEmails.length < caseData.required_team_size - 1">
                        <div class="flex gap-2 mb-1">
                            <Input
                                v-model="newMemberEmail"
                                type="text"
                                placeholder="Email участника"
                                class="flex-1"
                                @keyup.enter="addTeamMember"
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                @click="addTeamMember"
                            >
                                Добавить
                            </Button>
                        </div>
                        <p v-if="emailError" class="text-sm text-red-600 mt-1">
                            {{ emailError }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-between gap-2 mt-6">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="closeModal"
                    >
                        Закрыть
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
</template>

