<template>
    <div class="p-6">
        <Head title="Редактирование кейса" />

        <div class="max-w-4xl mx-auto">
            <!-- Заголовок -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Редактирование кейса</h1>
                <p class="text-gray-600 mt-2">Измените информацию о кейсе</p>
            </div>

            <!-- Уведомления -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                {{ $page.props.flash.success }}
            </div>

            <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                {{ $page.props.flash.error }}
            </div>

            <!-- Форма -->
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submitForm">
                    <!-- Основная информация -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Название -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Название кейса *
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-300': form.errors.title }"
                                placeholder="Введите название кейса"
                                required
                            />
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                                {{ form.errors.title }}
                            </div>
                        </div>

                        <!-- Партнер -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Партнер *
                            </label>
                            <select
                                v-model="form.partner_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-300': form.errors.partner_id }"
                                required
                            >
                                <option value="">Выберите партнера</option>
                                <option
                                    v-for="partner in partners"
                                    :key="partner.id"
                                    :value="partner.id"
                                >
                                    {{ partner.company_name }} ({{ partner.contact_person }})
                                </option>
                            </select>
                            <div v-if="form.errors.partner_id" class="text-red-500 text-sm mt-1">
                                {{ form.errors.partner_id }}
                            </div>
                        </div>

                        <!-- Размер команды -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Размер команды *
                            </label>
                            <select
                                v-model="form.required_team_size"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-300': form.errors.required_team_size }"
                                required
                            >
                                <option value="">Выберите размер</option>
                                <option value="1">1 человек</option>
                                <option value="2">2 человека</option>
                                <option value="3">3 человека</option>
                                <option value="4">4 человека</option>
                                <option value="5">5+ человек</option>
                            </select>
                            <div v-if="form.errors.required_team_size" class="text-red-500 text-sm mt-1">
                                {{ form.errors.required_team_size }}
                            </div>
                        </div>

                        <!-- Дедлайн -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Дедлайн *
                            </label>
                            <input
                                v-model="form.deadline"
                                type="date"
                                :min="minDate"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-300': form.errors.deadline }"
                                required
                            />
                            <div v-if="form.errors.deadline" class="text-red-500 text-sm mt-1">
                                {{ form.errors.deadline }}
                            </div>
                        </div>

                        <!-- Статус -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Статус *
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-300': form.errors.status }"
                                required
                            >
                                <option value="">Выберите статус</option>
                                <option
                                    v-for="status in statusOptions"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                                {{ form.errors.status }}
                            </div>
                        </div>

                        <!-- Награда -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Награда *
                            </label>
                            <input
                                v-model="form.reward"
                                type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-300': form.errors.reward }"
                                placeholder="Опишите награду за выполнение кейса"
                                required
                            />
                            <div v-if="form.errors.reward" class="text-red-500 text-sm mt-1">
                                {{ form.errors.reward }}
                            </div>
                        </div>
                    </div>

                    <!-- Описание -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Описание кейса *
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="6"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :class="{ 'border-red-300': form.errors.description }"
                            placeholder="Подробно опишите задачу, цели и ожидаемые результаты..."
                            required
                        ></textarea>
                        <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <!-- Навыки -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Требуемые навыки
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                            <div
                                v-for="skill in skills"
                                :key="skill.id"
                                class="flex items-center"
                            >
                                <input
                                    :id="`skill-${skill.id}`"
                                    v-model="form.required_skills"
                                    type="checkbox"
                                    :value="skill.id"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label
                                    :for="`skill-${skill.id}`"
                                    class="ml-2 text-sm text-gray-700"
                                >
                                    {{ skill.name }}
                                </label>
                            </div>
                        </div>
                        <div v-if="form.errors.required_skills" class="text-red-500 text-sm mt-1">
                            {{ form.errors.required_skills }}
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                        <Link
                            :href="route('admin.cases.index')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                        >
                            Отмена
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Обновление...</span>
                            <span v-else>Обновить кейс</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import { route } from "ziggy-js";

const props = defineProps({
    case: Object,
    partners: Array,
    skills: Array,
    statusOptions: Array,
})

// Используем встроенную обработку формы Inertia
const form = useForm({
    title: props.case?.title || '',
    description: props.case?.description || '',
    partner_id: props.case?.partner_id || '',
    deadline: props.case?.deadline ? formatDateForInput(props.case.deadline) : '',
    required_team_size: props.case?.required_team_size?.toString() || '',
    status: props.case?.status || '',
    reward: props.case?.reward || '',
    required_skills: props.case?.required_skills || [],
})

const minDate = computed(() => {
    const today = new Date()
    return today.toISOString().split('T')[0]
})

// Функция для форматирования даты в формат input[type="date"]
function formatDateForInput(dateString) {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toISOString().split('T')[0]
}

const submitForm = () => {
    // Используем transform для преобразования данных если нужно
    form.transform((data) => ({
        ...data,
        required_team_size: parseInt(data.required_team_size),
        required_skills: data.required_skills.map(id => parseInt(id)),
    })).put(route('admin.cases.update', props.case.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Успешное обновление
        },
        onError: (errors) => {
            console.log('Ошибки формы:', errors)
        },
    })
}
</script>
