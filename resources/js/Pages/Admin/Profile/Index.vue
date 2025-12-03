<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Card from '@/Components/UI/Card.vue'
import Button from '@/Components/UI/Button.vue'
import Input from '@/Components/UI/Input.vue'
import Textarea from '@/Components/UI/Textarea.vue'
import UserAvatar from '@/Components/Shared/UserAvatar.vue'
import InputMask from 'primevue/inputmask'
import Select from '@/Components/UI/Select.vue'
import AvatarEditor from '@/Components/Shared/AvatarEditor.vue'
import { router } from '@inertiajs/vue3'

defineOptions({
    layout: AdminLayout
})

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    faculties: {
        type: Array,
        default: () => []
    }
})

const isEditing = ref(false)
const avatarFile = ref(null)
const showPasswordFields = ref(false)

// Определяем роль пользователя
const userRole = computed(() => {
    if (props.user.roles && props.user.roles.length > 0) {
        return props.user.roles[0].name || props.user.roles[0]
    }
    return null
})

const isTeacher = computed(() => userRole.value === 'teacher')
const isStudent = computed(() => userRole.value === 'student')
const isPartner = computed(() => userRole.value === 'partner')
const isAdmin = computed(() => userRole.value === 'admin')

// Инициализируем форму в зависимости от роли
const initialFormData = () => {
    const baseData = {
        name: props.user.name,
        email: props.user.email,
        current_password: '',
        password: '',
        password_confirmation: ''
    }
    
    if (isTeacher.value && props.user.teacher_profile) {
        return {
            ...baseData,
            department: props.user.teacher_profile.department || '',
            position: props.user.teacher_profile.position || '',
            bio: props.user.teacher_profile.bio || ''
        }
    }
    
    if (isStudent.value && props.user.student_profile) {
        return {
            ...baseData,
            faculty_id: props.user.student_profile.faculty_id || null,
            group: props.user.student_profile.group || '',
            specialization: props.user.student_profile.specialization || '',
            phone: props.user.student_profile.phone || '',
            bio: props.user.student_profile.bio || '',
            course: props.user.course || null
        }
    }
    
    if (isPartner.value && props.user.partner_profile) {
        return {
            ...baseData,
            company_name: props.user.partner_profile.company_name || '',
            inn: props.user.partner_profile.inn || '',
            address: props.user.partner_profile.address || '',
            website: props.user.partner_profile.website || '',
            description: props.user.partner_profile.description || '',
            contact_person: props.user.partner_profile.contact_person || '',
            contact_phone: props.user.partner_profile.contact_phone || ''
        }
    }
    
    // Для админа - только базовые поля из users (name, email, avatar)
    return {
        ...baseData
    }
}

const form = useForm(initialFormData())

const courseOptions = computed(() => [
    { label: 'Не указан', value: null },
    ...Array.from({ length: 6 }, (_, i) => ({
        label: `${i + 1} курс`,
        value: i + 1
    }))
])

const facultyOptions = computed(() => 
    props.faculties.map(faculty => ({
        value: faculty.id,
        label: faculty.name
    }))
)


const submitForm = () => {
    const hasAvatar = avatarFile.value !== null && avatarFile.value instanceof File
    
    const submitData = { ...form.data() }
    if (hasAvatar) {
        submitData.avatar = avatarFile.value
    }
    
    form.transform((data) => {
        if (hasAvatar) {
            const formData = new FormData()
            Object.keys(submitData).forEach(key => {
                if (submitData[key] !== null && submitData[key] !== undefined) {
                    if (submitData[key] instanceof File) {
                        formData.append(key, submitData[key])
                    } else {
                        formData.append(key, submitData[key])
                    }
                }
            })
            formData.append('_method', 'PUT')
            return formData
        }
        return { ...submitData, _method: 'PUT' }
    }).post(route('admin.profile.update'), {
        forceFormData: hasAvatar,
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false
            avatarFile.value = null
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
    avatarFile.value = null
    showPasswordFields.value = false
}

const getRoleLabel = (role) => {
    const labels = {
        'admin': 'Администратор',
        'teacher': 'Преподаватель',
        'student': 'Студент',
        'partner': 'Партнер'
    }
    return labels[role] || role
}

// Проверка, заполнен ли профиль
const isProfileFilled = computed(() => {
    // У админа всегда "заполнен", так как name и email обязательны
    if (isAdmin.value) {
        return true
    }
    if (isTeacher.value && props.user.teacher_profile) {
        return props.user.teacher_profile.department || 
               props.user.teacher_profile.position || 
               props.user.teacher_profile.bio
    }
    if (isStudent.value && props.user.student_profile) {
        return props.user.student_profile.faculty_id ||
               props.user.student_profile.group ||
               props.user.student_profile.specialization ||
               props.user.student_profile.phone ||
               props.user.student_profile.bio
    }
    if (isPartner.value && props.user.partner_profile) {
        return props.user.partner_profile.company_name ||
               props.user.partner_profile.inn ||
               props.user.partner_profile.address ||
               props.user.partner_profile.description ||
               props.user.partner_profile.contact_person ||
               props.user.partner_profile.contact_phone
    }
    return false
})
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
                                <p v-if="(isStudent && user.student_profile?.phone)" class="flex items-center gap-2">
                                    <i class="pi pi-phone text-sm"></i>
                                    <span class="font-medium">Телефон:</span> 
                                    {{ user.student_profile.phone }}
                                </p>
                                <p v-if="isStudent && user.course" class="flex items-center gap-2">
                                    <i class="pi pi-calendar text-sm"></i>
                                    <span class="font-medium">Курс:</span> {{ user.course }} курс
                                </p>
                                <p class="flex items-center gap-2">
                                    <i class="pi pi-shield text-sm"></i>
                                    <span class="font-medium">Роль:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ getRoleLabel(userRole) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Teacher Profile Info -->
                <Card v-if="isTeacher && user.teacher_profile && isProfileFilled">
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Профиль преподавателя</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="user.teacher_profile.department">
                            <p class="text-sm text-text-muted mb-1">Кафедра</p>
                            <p class="font-medium text-text-primary">
                                {{ user.teacher_profile.department }}
                            </p>
                        </div>
                        <div v-if="user.teacher_profile.position">
                            <p class="text-sm text-text-muted mb-1">Должность</p>
                            <p class="font-medium text-text-primary">
                                {{ user.teacher_profile.position }}
                            </p>
                        </div>
                        <div v-if="user.teacher_profile.bio" class="md:col-span-2">
                            <p class="text-sm text-text-muted mb-1">О себе</p>
                            <p class="text-text-secondary whitespace-pre-wrap">{{ user.teacher_profile.bio }}</p>
                        </div>
                    </div>
                </Card>

                <!-- Student Profile Info -->
                <Card v-if="isStudent && user.student_profile && isProfileFilled">
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Учебная информация</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="user.student_profile.faculty">
                            <p class="text-sm text-text-muted mb-1">Факультет</p>
                            <p class="font-medium text-text-primary">
                                {{ user.student_profile.faculty.name || user.student_profile.faculty }}
                            </p>
                        </div>
                        <div v-if="user.student_profile.group">
                            <p class="text-sm text-text-muted mb-1">Группа</p>
                            <p class="font-medium text-text-primary">
                                {{ user.student_profile.group }}
                            </p>
                        </div>
                        <div v-if="user.student_profile.specialization">
                            <p class="text-sm text-text-muted mb-1">Специальность</p>
                            <p class="font-medium text-text-primary">
                                {{ user.student_profile.specialization }}
                            </p>
                        </div>
                        <div v-if="user.student_profile.bio" class="md:col-span-2">
                            <p class="text-sm text-text-muted mb-1">О себе</p>
                            <p class="text-text-secondary whitespace-pre-wrap">{{ user.student_profile.bio }}</p>
                        </div>
                    </div>
                </Card>

                <!-- Partner Profile Info -->
                <Card v-if="isPartner && user.partner_profile && isProfileFilled">
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Информация о компании</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="user.partner_profile.company_name">
                            <p class="text-sm text-text-muted mb-1">Название компании</p>
                            <p class="font-medium text-text-primary">
                                {{ user.partner_profile.company_name }}
                            </p>
                        </div>
                        <div v-if="user.partner_profile.inn">
                            <p class="text-sm text-text-muted mb-1">ИНН</p>
                            <p class="font-medium text-text-primary">
                                {{ user.partner_profile.inn }}
                            </p>
                        </div>
                        <div v-if="user.partner_profile.address" class="md:col-span-2">
                            <p class="text-sm text-text-muted mb-1">Адрес</p>
                            <p class="font-medium text-text-primary">
                                {{ user.partner_profile.address }}
                            </p>
                        </div>
                        <div v-if="user.partner_profile.website">
                            <p class="text-sm text-text-muted mb-1">Сайт</p>
                            <p class="font-medium text-text-primary">
                                <a :href="user.partner_profile.website" target="_blank" class="text-blue-600 hover:underline">
                                    {{ user.partner_profile.website }}
                                </a>
                            </p>
                        </div>
                        <div v-if="user.partner_profile.contact_person">
                            <p class="text-sm text-text-muted mb-1">Контактное лицо</p>
                            <p class="font-medium text-text-primary">
                                {{ user.partner_profile.contact_person }}
                            </p>
                        </div>
                        <div v-if="user.partner_profile.contact_phone">
                            <p class="text-sm text-text-muted mb-1">Контактный телефон</p>
                            <p class="font-medium text-text-primary">
                                {{ user.partner_profile.contact_phone }}
                            </p>
                        </div>
                        <div v-if="user.partner_profile.description" class="md:col-span-2">
                            <p class="text-sm text-text-muted mb-1">Описание</p>
                            <p class="text-text-secondary whitespace-pre-wrap">{{ user.partner_profile.description }}</p>
                        </div>
                    </div>
                </Card>

                <!-- Пустой профиль -->
                <Card v-if="!isProfileFilled">
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
                <AvatarEditor
                    :user="user"
                    avatar-route-prefix="admin"
                    v-model="avatarFile"
                />

                <!-- Basic Info (для всех ролей) -->
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
                        
                        <!-- Курс для студента -->
                        <Select
                            v-if="isStudent"
                            v-model="form.course"
                            label="Курс"
                            :options="courseOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Не указан"
                            :error="form.errors.course"
                        />
                    </div>
                </Card>

                <!-- Teacher Profile Fields -->
                <Card v-if="isTeacher">
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Профиль преподавателя</h3>
                    <div class="space-y-4">
                        <Input
                            v-model="form.department"
                            label="Кафедра"
                            placeholder="Введите название кафедры"
                            :error="form.errors.department"
                        />
                        <Input
                            v-model="form.position"
                            label="Должность"
                            placeholder="Введите должность"
                            :error="form.errors.position"
                        />
                        <Textarea
                            v-model="form.bio"
                            label="О себе"
                            placeholder="Расскажите о себе, своем опыте и квалификации..."
                            :rows="4"
                            :error="form.errors.bio"
                        />
                    </div>
                </Card>

                <!-- Student Profile Fields -->
                <Card v-if="isStudent">
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Учебная информация</h3>
                    <div class="space-y-4">
                        <Select
                            v-model="form.faculty_id"
                            label="Факультет"
                            :options="facultyOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Выберите факультет"
                            :error="form.errors.faculty_id"
                        />
                        <Input
                            v-model="form.group"
                            label="Группа"
                            placeholder="Например: ИВТ-101"
                            :error="form.errors.group"
                        />
                        <Input
                            v-model="form.specialization"
                            label="Специальность"
                            placeholder="Введите специальность"
                            :error="form.errors.specialization"
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
                        <Textarea
                            v-model="form.bio"
                            label="О себе"
                            placeholder="Расскажите о себе, своих интересах и навыках..."
                            :rows="4"
                            :error="form.errors.bio"
                        />
                    </div>
                </Card>

                <!-- Partner Profile Fields -->
                <Card v-if="isPartner">
                    <h3 class="text-lg font-bold mb-4 text-text-primary">Информация о компании</h3>
                    <div class="space-y-4">
                        <Input
                            v-model="form.company_name"
                            label="Название компании"
                            placeholder="Введите название компании"
                            :error="form.errors.company_name"
                        />
                        <Input
                            v-model="form.inn"
                            label="ИНН"
                            placeholder="Введите ИНН"
                            :error="form.errors.inn"
                        />
                        <Textarea
                            v-model="form.address"
                            label="Адрес"
                            placeholder="Введите адрес компании"
                            :rows="2"
                            :error="form.errors.address"
                        />
                        <Input
                            v-model="form.website"
                            type="url"
                            label="Сайт"
                            placeholder="https://example.com"
                            :error="form.errors.website"
                        />
                        <Input
                            v-model="form.contact_person"
                            label="Контактное лицо"
                            placeholder="Введите ФИО контактного лица"
                            :error="form.errors.contact_person"
                        />
                        <div>
                            <label class="block text-sm font-medium mb-1 text-text-primary">
                                Контактный телефон
                            </label>
                            <InputMask
                                v-model="form.contact_phone"
                                mask="+7 (999) 999-99-99"
                                placeholder="+7 (___) ___-__-__"
                                :class="['w-full', { 'p-invalid': form.errors.contact_phone }]"
                            />
                            <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-600">
                                {{ form.errors.contact_phone }}
                            </p>
                        </div>
                        <Textarea
                            v-model="form.description"
                            label="Описание компании"
                            placeholder="Расскажите о деятельности компании..."
                            :rows="4"
                            :error="form.errors.description"
                        />
                    </div>
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
