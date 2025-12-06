<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900">Профиль студента</h1>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex justify-end mb-6">
                <button
                    v-if="!isEditing"
                    @click="startEditing"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
                >
                    Редактировать
                </button>
                <div v-else class="flex space-x-3">
                    <button
                        @click="cancelEdit"
                        class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Отмена
                    </button>
                    <button
                        @click="submitForm"
                        :disabled="processing"
                        class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none disabled:opacity-50"
                    >
                        <span v-if="processing">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Сохранение...
                        </span>
                        <span v-else>Сохранить</span>
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Информация о студенте -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Информация о студенте</h3>
                            <p class="mt-1 text-sm text-gray-500">Основная информация о вас</p>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <!-- Фотография профиля -->
                            <AvatarEditor
                                :user="user"
                                avatar-route-prefix="student"
                                :editable="isEditing"
                                v-model="avatarFile"
                            />

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    ФИО *
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    v-model="form.name"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="ФИО"
                                />
                                <div v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email *
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    v-model="form.email"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="email@example.com"
                                />
                                <div v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</div>
                            </div>

                            <div>
                                <label for="kubgtu_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    ID КубГТУ
                                </label>
                                <input
                                    type="text"
                                    id="kubgtu_id"
                                    :value="user.kubgtu_id || 'Не указан'"
                                    readonly
                                    disabled
                                    class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Учебная информация -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Учебная информация</h3>
                            <p class="mt-1 text-sm text-gray-500">Данные об обучении в университете</p>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label for="faculty_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Факультет
                                </label>
                                <select
                                    id="faculty_id"
                                    v-model="form.faculty_id"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm disabled:opacity-50 disabled:bg-gray-50"
                                >
                                    <option :value="null">Не выбран</option>
                                    <option
                                        v-for="faculty in faculties"
                                        :key="faculty.id"
                                        :value="faculty.id"
                                    >
                                        {{ faculty.name }}
                                    </option>
                                </select>
                                <div v-if="errors.faculty_id" class="mt-1 text-sm text-red-600">{{ errors.faculty_id }}</div>
                            </div>

                            <div>
                                <label for="course" class="block text-sm font-medium text-gray-700 mb-1">
                                    Курс
                                </label>
                                <div v-if="!isEditing" class="py-2">
                                    <span class="text-gray-900">
                                        {{ form.course ? `${form.course} курс` : 'Не выбран' }}
                                    </span>
                                </div>
                                <div v-else class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">
                                            {{ form.course ? `${form.course} курс` : 'Не выбран' }}
                                        </span>
                                        <button
                                            v-if="form.course"
                                            type="button"
                                            @click="form.course = null; courseSliderTemp = 1;"
                                            class="text-xs text-blue-600 hover:text-blue-700"
                                        >
                                            Сбросить
                                        </button>
                                    </div>
                                    <Slider
                                        v-model="courseSliderTemp"
                                        :min="1"
                                        :max="6"
                                        :step="0.01"
                                        :disabled="processing"
                                        class="w-full"
                                        @slideend="onCourseSliderEnd"
                                    />
                                </div>
                                <div v-if="errors.course" class="mt-1 text-sm text-red-600">{{ errors.course }}</div>
                            </div>

                            <div>
                                <label for="group" class="block text-sm font-medium text-gray-700 mb-1">
                                    Группа
                                </label>
                                <input
                                    type="text"
                                    id="group"
                                    v-model="form.group"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="Например: ИВТ-101"
                                />
                                <div v-if="errors.group" class="mt-1 text-sm text-red-600">{{ errors.group }}</div>
                            </div>

                            <div>
                                <label for="specialization" class="block text-sm font-medium text-gray-700 mb-1">
                                    Специализация
                                </label>
                                <input
                                    type="text"
                                    id="specialization"
                                    v-model="form.specialization"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="Например: Информационные системы и программирование"
                                />
                                <div v-if="errors.specialization" class="mt-1 text-sm text-red-600">{{ errors.specialization }}</div>
                            </div>

                            <div v-if="!isEditing">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Всего очков
                                </label>
                                <p class="text-lg font-semibold text-blue-600">
                                    {{ studentProfile?.total_points || 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- О себе -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">О себе</h3>
                            <p class="mt-1 text-sm text-gray-500">Расскажите о своих интересах и навыках</p>
                        </div>
                        <div class="md:col-span-2">
                            <textarea
                                id="bio"
                                v-model="form.bio"
                                rows="4"
                                :readonly="!isEditing"
                                :disabled="!isEditing || processing"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                placeholder="Расскажите о себе, своих интересах и навыках..."
                            ></textarea>
                            <div v-if="errors.bio" class="mt-1 text-sm text-red-600">{{ errors.bio }}</div>
                        </div>
                    </div>
                </div>

                <!-- Контактная информация -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Контактная информация</h3>
                            <p class="mt-1 text-sm text-gray-500">Способы связи</p>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Телефон
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    v-model="form.phone"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="+7 (XXX) XXX-XX-XX"
                                />
                                <div v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AvatarEditor from '@/Components/Shared/AvatarEditor.vue';
import Slider from 'primevue/slider';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    studentProfile: {
        type: Object,
        default: null
    },
    faculties: {
        type: Array,
        default: () => []
    }
});

// Форма по моделям:
// User: name, email, avatar, kubgtu_id (readonly)
// StudentProfile: faculty_id, course, group, specialization, bio, phone, total_points (readonly)
const initialForm = {
    // User fields
    name: props.user?.name || '',
    email: props.user?.email || '',
    
    // StudentProfile fields
    faculty_id: props.studentProfile?.faculty_id || null,
    course: props.studentProfile?.course || null,
    group: props.studentProfile?.group || '',
    specialization: props.studentProfile?.specialization || '',
    bio: props.studentProfile?.bio || '',
    phone: props.studentProfile?.phone || ''
};

const form = reactive({ ...initialForm });
const isEditing = ref(false);
const processing = ref(false);
const errors = ref({});
const avatarFile = ref(null);

// Временное значение для плавного движения слайдера
const courseSliderTemp = ref(form.course || 1);

// Обновляем form.course в реальном времени при движении слайдера (с округлением для отображения)
watch(courseSliderTemp, (newValue) => {
    const rounded = Math.round(newValue);
    const clamped = Math.max(1, Math.min(6, rounded)); // Ограничиваем диапазон 1-6
    form.course = clamped;
});

// Округление до ближайшего целого при отпускании слайдера (финальная "магнитная" фиксация)
const onCourseSliderEnd = () => {
    const rounded = Math.round(courseSliderTemp.value);
    const clamped = Math.max(1, Math.min(6, rounded)); // Ограничиваем диапазон 1-6
    courseSliderTemp.value = clamped;
    form.course = clamped;
};

// Синхронизация временного значения при изменении form.course извне (например, при сбросе)
watch(() => form.course, (newValue) => {
    if (newValue === null) {
        courseSliderTemp.value = 1;
    } else if (newValue !== Math.round(courseSliderTemp.value)) {
        // Обновляем только если значение действительно изменилось извне
        courseSliderTemp.value = newValue;
    }
});

const startEditing = () => {
    // Синхронизируем временное значение слайдера с текущим значением курса
    courseSliderTemp.value = form.course || 1;
    isEditing.value = true;
};

const cancelEdit = () => {
    // Reset form to initial values
    Object.assign(form, initialForm);
    courseSliderTemp.value = form.course || 1;
    avatarFile.value = null;
    isEditing.value = false;
    errors.value = {};
};

const submitForm = () => {
    processing.value = true;
    errors.value = {};

    const hasAvatar = avatarFile.value !== null && avatarFile.value instanceof File;

    // Подготавливаем данные
    const submitData = {
        name: form.name,
        email: form.email,
        faculty_id: form.faculty_id || null,
        course: form.course || null,
        group: form.group || '',
        specialization: form.specialization || '',
        bio: form.bio || '',
        phone: form.phone || ''
    };

    if (hasAvatar) {
        submitData.avatar = avatarFile.value;
    }

    // Для PUT запросов с файлами нужно использовать POST с _method: PUT
    if (hasAvatar) {
        const formData = new FormData();
        Object.keys(submitData).forEach(key => {
            if (submitData[key] !== null && submitData[key] !== undefined) {
                if (submitData[key] instanceof File) {
                    formData.append(key, submitData[key]);
                } else {
                    formData.append(key, submitData[key]);
                }
            }
        });
        formData.append('_method', 'PUT');

        router.post(route('student.profile.update'), formData, {
            preserveState: true,
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                isEditing.value = false;
                processing.value = false;
                avatarFile.value = null;
                // Update the initial form data with new values
                Object.assign(initialForm, {
                    name: form.name,
                    email: form.email,
                    faculty_id: form.faculty_id,
                    course: form.course,
                    group: form.group,
                    specialization: form.specialization,
                    bio: form.bio,
                    phone: form.phone
                });
            },
            onError: (err) => {
                processing.value = false;
                errors.value = err;
            }
        });
    } else {
        // Без файлов можно использовать обычный PUT
        router.put(route('student.profile.update'), submitData, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
                processing.value = false;
                // Update the initial form data with new values
                Object.assign(initialForm, {
                    name: form.name,
                    email: form.email,
                    faculty_id: form.faculty_id,
                    course: form.course,
                    group: form.group,
                    specialization: form.specialization,
                    bio: form.bio,
                    phone: form.phone
                });
            },
            onError: (err) => {
                processing.value = false;
                errors.value = err;
            }
        });
    }
};
</script>
