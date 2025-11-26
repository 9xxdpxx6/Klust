<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Textarea from '@/Components/UI/Textarea.vue'
import Select from '@/Components/UI/Select.vue'
import UserAvatar from '@/Components/Shared/UserAvatar.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    studentProfile: {
        type: Object,
        required: true
    },
    faculties: {
        type: Array,
        default: () => []
    }
})

const isEditing = ref(false)
const avatarPreview = ref(null)

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    avatar: null,
    faculty_id: props.studentProfile.faculty_id,
    course: props.studentProfile.course,
    group_number: props.studentProfile.group_number,
    bio: props.studentProfile.bio || '',
    telegram: props.studentProfile.telegram || '',
    vk: props.studentProfile.vk || ''
})

const courseOptions = [
    { value: 1, label: '1 курс' },
    { value: 2, label: '2 курс' },
    { value: 3, label: '3 курс' },
    { value: 4, label: '4 курс' },
    { value: 5, label: '5 курс' }
]

const facultyOptions = props.faculties.map(faculty => ({
    value: faculty.id,
    label: faculty.name
}))

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

const submitForm = () => {
    form.post(route('student.profile.update'), {
        forceFormData: true,
        onSuccess: () => {
            isEditing.value = false
            avatarPreview.value = null
        }
    })
}

const cancelEdit = () => {
    isEditing.value = false
    form.reset()
    avatarPreview.value = null
}
</script>

<template>
    <div class="space-y-6">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold">Мой профиль</h1>
                <Button
                    v-if="!isEditing"
                    variant="primary"
                    @click="isEditing = true"
                >
                    Редактировать
                </Button>
            </div>

            <!-- View Mode -->
            <div v-if="!isEditing" class="space-y-6">
                <!-- Basic Info -->
                <Card>
                    <div class="flex items-start gap-6">
                        <UserAvatar
                            :src="user.avatar"
                            :name="user.name"
                            size="xl"
                            class="flex-shrink-0"
                        />
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-2">{{ user.name }}</h2>
                            <div class="space-y-2 text-gray-600">
                                <p>
                                    <span class="font-medium">Email:</span> {{ user.email }}
                                </p>
                                <p>
                                    <span class="font-medium">ID КубГТУ:</span> {{ user.kubgtu_id || 'Не указан' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Academic Info -->
                <Card>
                    <h3 class="text-lg font-bold mb-4">Учебная информация</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Факультет</p>
                            <p class="font-medium">
                                {{ studentProfile.faculty?.name || 'Не указан' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Курс</p>
                            <p class="font-medium">
                                {{ studentProfile.course ? `${studentProfile.course} курс` : 'Не указан' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Группа</p>
                            <p class="font-medium">
                                {{ studentProfile.group_number || 'Не указана' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Всего очков</p>
                            <p class="font-medium text-blue-600 text-xl">
                                {{ studentProfile.total_points || 0 }}
                            </p>
                        </div>
                    </div>
                </Card>

                <!-- Bio -->
                <Card v-if="studentProfile.bio">
                    <h3 class="text-lg font-bold mb-4">О себе</h3>
                    <p class="text-gray-700">{{ studentProfile.bio }}</p>
                </Card>

                <!-- Contacts -->
                <Card>
                    <h3 class="text-lg font-bold mb-4">Контакты</h3>
                    <div class="space-y-3">
                        <div v-if="studentProfile.telegram">
                            <p class="text-sm text-gray-600">Telegram</p>
                            <a
                                :href="`https://t.me/${studentProfile.telegram}`"
                                target="_blank"
                                class="text-blue-600 hover:underline"
                            >
                                @{{ studentProfile.telegram }}
                            </a>
                        </div>
                        <div v-if="studentProfile.vk">
                            <p class="text-sm text-gray-600">VK</p>
                            <a
                                :href="studentProfile.vk"
                                target="_blank"
                                class="text-blue-600 hover:underline"
                            >
                                {{ studentProfile.vk }}
                            </a>
                        </div>
                        <p v-if="!studentProfile.telegram && !studentProfile.vk" class="text-gray-500">
                            Контакты не указаны
                        </p>
                    </div>
                </Card>
            </div>

            <!-- Edit Mode -->
            <form v-else @submit.prevent="submitForm" novalidate class="space-y-6">
                <!-- Avatar Upload -->
                <Card>
                    <h3 class="text-lg font-bold mb-4">Фотография профиля</h3>
                    <div class="flex items-center gap-6">
                        <UserAvatar
                            :src="avatarPreview || user.avatar"
                            :name="user.name"
                            size="xl"
                        />
                        <div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="handleAvatarChange"
                                class="hidden"
                                id="avatar-upload"
                            />
                            <label for="avatar-upload">
                                <Button
                                    type="button"
                                    variant="secondary"
                                    @click="$refs['avatar-upload'].click()"
                                >
                                    Загрузить фото
                                </Button>
                            </label>
                            <p class="text-sm text-gray-500 mt-2">JPG, PNG. Макс. 2MB</p>
                        </div>
                    </div>
                </Card>

                <!-- Basic Info -->
                <Card>
                    <h3 class="text-lg font-bold mb-4">Основная информация</h3>
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
                            type="text"
                            label="Email"
                            placeholder="Введите email"
                            :error="form.errors.email"
                            required
                        />
                        <Input
                            :model-value="user.kubgtu_id"
                            label="ID КубГТУ"
                            disabled
                            readonly
                        />
                    </div>
                </Card>

                <!-- Academic Info -->
                <Card>
                    <h3 class="text-lg font-bold mb-4">Учебная информация</h3>
                    <div class="space-y-4">
                        <Select
                            v-model="form.faculty_id"
                            label="Факультет"
                            :options="facultyOptions"
                            :error="form.errors.faculty_id"
                        />
                        <Select
                            v-model="form.course"
                            label="Курс"
                            :options="courseOptions"
                            :error="form.errors.course"
                        />
                        <Input
                            v-model="form.group_number"
                            label="Номер группы"
                            placeholder="Например: ИВТ-101"
                            :error="form.errors.group_number"
                        />
                    </div>
                </Card>

                <!-- Bio -->
                <Card>
                    <h3 class="text-lg font-bold mb-4">О себе</h3>
                    <Textarea
                        v-model="form.bio"
                        placeholder="Расскажите о себе, своих интересах и навыках..."
                        rows="4"
                        :error="form.errors.bio"
                    />
                </Card>

                <!-- Contacts -->
                <Card>
                    <h3 class="text-lg font-bold mb-4">Контакты</h3>
                    <div class="space-y-4">
                        <Input
                            v-model="form.telegram"
                            label="Telegram"
                            placeholder="Без @, например: username"
                            :error="form.errors.telegram"
                        />
                        <Input
                            v-model="form.vk"
                            label="VK"
                            placeholder="Ссылка на профиль VK"
                            :error="form.errors.vk"
                        />
                    </div>
                </Card>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="secondary"
                        @click="cancelEdit"
                    >
                        Отмена
                    </Button>
                    <Button
                        type="submit"
                        variant="primary"
                        :disabled="form.processing"
                    >
                        Сохранить изменения
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
