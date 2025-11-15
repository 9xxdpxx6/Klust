<template>
    <PartnerLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900">Создать кейс</h1>
        </template>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <form @submit.prevent="submitForm">
                <div class="space-y-6">
                    <!-- Название -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Название кейса *
                        </label>
                        <input
                            type="text"
                            id="title"
                            v-model="form.title"
                            required
                            :disabled="processing"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm disabled:opacity-50"
                            placeholder="Введите название кейса"
                        />
                        <div v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</div>
                    </div>

                    <!-- Описание -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Описание
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="6"
                            :disabled="processing"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm disabled:opacity-50"
                            placeholder="Подробное описание кейса..."
                        ></textarea>
                        <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
                    </div>

                    <!-- Требуемый размер команды -->
                    <div>
                        <label for="team_size" class="block text-sm font-medium text-gray-700 mb-1">
                            Требуемый размер команды *
                        </label>
                        <input
                            type="number"
                            id="team_size"
                            v-model.number="form.team_size"
                            min="1"
                            required
                            :disabled="processing"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm disabled:opacity-50"
                            placeholder="Например: 3"
                        />
                        <div v-if="errors.team_size" class="mt-1 text-sm text-red-600">{{ errors.team_size }}</div>
                    </div>

                    <!-- Требуемые навыки -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Требуемые навыки
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <label 
                                v-for="skill in skills" 
                                :key="skill.id"
                                class="flex items-center p-3 border rounded-md cursor-pointer hover:bg-gray-50"
                                :class="{
                                    'border-blue-500 bg-blue-50': form.required_skills.includes(skill.id),
                                    'border-gray-300': !form.required_skills.includes(skill.id)
                                }"
                            >
                                <input
                                    type="checkbox"
                                    :value="skill.id"
                                    v-model="form.required_skills"
                                    :disabled="processing"
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                                />
                                <span class="ml-3 text-sm font-medium text-gray-900">{{ skill.name }}</span>
                            </label>
                        </div>
                        <div v-if="errors.required_skills" class="mt-1 text-sm text-red-600">{{ errors.required_skills }}</div>
                    </div>

                    <!-- Дедлайн -->
                    <DatePicker
                        v-model="form.deadline"
                        label="Дедлайн"
                        :disabled="processing"
                        :error="errors.deadline"
                    />

                    <!-- Статус -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Статус
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer">
                                <input
                                    type="radio"
                                    value="draft"
                                    v-model="form.status"
                                    :disabled="processing"
                                    class="h-4 w-4 text-blue-600 border-gray-300"
                                />
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Черновик</span>
                                    <span class="block text-sm text-gray-500">Кейс не будет доступен студентам</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer">
                                <input
                                    type="radio"
                                    value="active"
                                    v-model="form.status"
                                    :disabled="processing"
                                    class="h-4 w-4 text-blue-600 border-gray-300"
                                />
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Активен</span>
                                    <span class="block text-sm text-gray-500">Кейс будет доступен для подачи заявок</span>
                                </div>
                            </label>
                        </div>
                        <div v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end space-x-4">
                    <Link 
                        :href="route('partner.cases.index')" 
                        class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                    >
                        Отмена
                    </Link>
                    <button
                        type="submit"
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
                        <span v-else-if="form.status === 'draft'">Сохранить как черновик</span>
                        <span v-else>Опубликовать</span>
                    </button>
                </div>
            </form>
        </div>
    </PartnerLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import DatePicker from '@/Components/UI/DatePicker.vue';

const props = defineProps({
    skills: {
        type: Array,
        required: true
    }
});

// Initialize form data
const form = reactive({
    title: '',
    description: '',
    team_size: 1,
    required_skills: [],
    deadline: '',
    status: 'draft' // Default to draft
});

// For handling form errors and processing state
const processing = ref(false);
const errors = ref({});

const formatDateForServer = (date) => {
    if (!date) return null;
    if (date instanceof Date) {
        return date.toISOString().split('T')[0];
    }
    return date;
};

const submitForm = () => {
    processing.value = true;
    errors.value = {};

    const formData = {
        ...form,
        deadline: formatDateForServer(form.deadline)
    };

    router.post(route('partner.cases.store'), formData, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: (err) => {
            processing.value = false;
            errors.value = err;
        }
    });
};
</script>