<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Textarea from '@/Components/UI/Textarea.vue'
import UserAvatar from '@/Components/Shared/UserAvatar.vue'
import InputMask from 'primevue/inputmask'

defineOptions({
    layout: AdminLayout
})

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
})

const isEditing = ref(false)
const avatarPreview = ref(null)
const showPasswordFields = ref(false)
const avatarInput = ref(null)

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    bio: props.user.bio || '',
    avatar: null,
    current_password: '',
    password: '',
    password_confirmation: ''
})

const handleAvatarChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.avatar = file
        const reader = new FileReader()
        reader.onload = (e) => {
            avatarPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const openFilePicker = () => {
    avatarInput.value.click()
}

const submitForm = () => {
    form.post(route('admin.profile.update'), {
        forceFormData: true,
        onSuccess: () => {
            isEditing.value = false
            avatarPreview.value = null
            showPasswordFields.value = false
            form.current_password = ''
            form.password = ''
            form.password_confirmation = ''
        }
    })
}

const cancelEdit = () => {
    isEditing.value = false
    form.reset()
    avatarPreview.value = null
    showPasswordFields.value = false
}
</script>

<template>
    <div class="space-y-6">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold text-text-primary">Мой профиль</h1>
                <Button
                    v-if="!isEditing"
                    variant="primary"
                    @click="isEditing = true"
                >
                    <i class="pi pi-pencil mr-2"></i>
                    Редактировать
                </Button>
            </div>

            <!-- View Mode -->
            <div v-if="!isEditing" class="space-y-6">
                <!-- Basic Info -->
                <Card>
                    <div class="flex items-start gap-6">
                        <UserAvatar
                            :user="user"
                            size="lg"
                            class="flex-shrink-0"
                        />
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-2 text-text-primary">{{ user.name }}</h2>
                            <div class="space-y-2 text-text-secondary">
                                <p class="flex items-center gap-2">
                                    <i class="pi pi-envelope text-sm"></i>
                                    <span class="font-medium">Email:</span> {{ user.email }}
                                </p>
                                <p v-if="user.phone" class="flex items-center gap-2">
                                    <i class="pi pi-phone text-sm"></i>
                                    <span class="font-medium">Телефон:</span> {{ user.phone }}
                                </p>
                                <p class="flex items-center gap-2">
                                    <i class="pi pi-shield text-sm"></i>
                                    <span class="font-medium">Роль:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ user.roles?.[0] === 'admin' ? 'Администратор' : 'Преподаватель' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Bio -->
                <Card v-if="user.bio">
                    <h3 class="text-lg font-bold mb-4 text-text-primary">О себе</h3>
                    <p class="text-text-secondary whitespace-pre-wrap">{{ user.bio }}</p>
                </Card>

                <Card v-else>
                    <div class="text-center py-8 text-text-muted">
                        <i class="pi pi-info-circle text-4xl mb-3"></i>
                        <p>Вы еще не заполнили информацию о себе</p>
                        <Button
                            variant="secondary"
                            class="mt-4"
                            @click="isEditing = true"
                        >
                            Заполнить профиль
                        </Button>
                    </div>
                </Card>
            </div>

            <!-- Edit Mode -->
            <form v-else @submit.prevent="submitForm" class="space-y-6">
                <!-- Avatar Upload -->
                <Card>
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Фотография профиля</h3>
                    <div class="flex items-center gap-6">
                        <UserAvatar
                            :user="{ ...user, avatar: avatarPreview || user.avatar }"
                            size="lg"
                        />
                        <div>
                            <input
                                ref="avatarInput"
                                type="file"
                                accept="image/*"
                                @change="handleAvatarChange"
                                class="hidden"
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                @click="openFilePicker"
                            >
                                <i class="pi pi-upload mr-2"></i>
                                Загрузить фото
                            </Button>
                            <p class="text-sm text-text-muted mt-2">JPG, PNG, GIF. Макс. 2MB</p>
                        </div>
                    </div>
                </Card>

                <!-- Basic Info -->
                <Card>
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Основная информация</h3>
                    <div class="space-y-4">
                        <Input
                            v-model="form.name"
                            label="ФИО"
                            placeholder="Введите ФИО"
                            :error="form.errors.name"
                            required
                        />
                        <Input
                            v-model="form.email"
                            type="email"
                            label="Email"
                            placeholder="Введите email"
                            :error="form.errors.email"
                            required
                        />
                        <div>
                            <label class="block text-sm font-medium mb-1 text-text-primary">
                                Телефон
                            </label>
                            <InputMask
                                v-model="form.phone"
                                mask="+7 (999) 999-99-99"
                                placeholder="+7 (___) ___-__-__"
                                :class="['w-full', { 'p-invalid': form.errors.phone }]"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>
                </Card>

                <!-- Bio -->
                <Card>
                    <h3 class="text-lg font-bold mb-4 text-text-primary">О себе</h3>
                    <Textarea
                        v-model="form.bio"
                        placeholder="Расскажите о себе, своем опыте и квалификации..."
                        :rows="4"
                        :error="form.errors.bio"
                    />
                </Card>

                <!-- Password Change -->
                <Card>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-text-primary">Смена пароля</h3>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="showPasswordFields = !showPasswordFields"
                        >
                            {{ showPasswordFields ? 'Скрыть' : 'Изменить пароль' }}
                        </Button>
                    </div>

                    <div v-if="showPasswordFields" class="space-y-4">
                        <Input
                            v-model="form.current_password"
                            type="password"
                            label="Текущий пароль"
                            placeholder="Введите текущий пароль"
                            :error="form.errors.current_password"
                            required
                        />
                        <Input
                            v-model="form.password"
                            type="password"
                            label="Новый пароль"
                            placeholder="Введите новый пароль (минимум 8 символов)"
                            :error="form.errors.password"
                        />
                        <Input
                            v-model="form.password_confirmation"
                            type="password"
                            label="Подтверждение пароля"
                            placeholder="Повторите новый пароль"
                            :error="form.errors.password_confirmation"
                        />
                        <p class="text-sm text-text-muted">
                            <i class="pi pi-info-circle mr-1"></i>
                            Для смены пароля необходимо ввести текущий пароль
                        </p>
                    </div>
                </Card>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="cancelEdit"
                        :disabled="form.processing"
                    >
                        <i class="pi pi-times mr-2"></i>
                        Отмена
                    </Button>
                    <Button
                        type="submit"
                        variant="primary"
                        :disabled="form.processing"
                    >
                        <i class="pi pi-check mr-2"></i>
                        {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
