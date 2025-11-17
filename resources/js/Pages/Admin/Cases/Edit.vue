<template>
    <div>
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
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                                :class="{ 'border-red-300': form.errors.title }"
                                placeholder="Введите название кейса"
                                required
                            />
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                                {{ form.errors.title }}
                            </div>
                        </div>

                        <!-- Партнер -->
                        <Select
                            v-model="form.partner_id"
                            label="Партнер"
                            :options="partnerOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Выберите партнера"
                            :error="form.errors.partner_id"
                            required
                        />

                        <!-- Размер команды -->
                        <Select
                            v-model="form.required_team_size"
                            label="Размер команды"
                            :options="teamSizeOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Выберите размер"
                            :error="form.errors.required_team_size"
                            required
                        />

                        <!-- Дедлайн -->
                        <DatePicker
                            v-model="form.deadline"
                            label="Дедлайн"
                            :minDate="minDate"
                            :error="form.errors.deadline"
                            required
                        />

                        <!-- Статус -->
                        <Select
                            v-model="form.status"
                            label="Статус"
                            :options="statusOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Выберите статус"
                            :error="form.errors.status"
                            required
                        />

                        <!-- Награда -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Награда *
                            </label>
                            <input
                                v-model="form.reward"
                                type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
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
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
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
                                    class="rounded border-gray-300 text-indigo-600"
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

                    <!-- Симулятор -->
                    <div class="mb-6">
                        <Select
                            v-model="form.simulator_id"
                            label="Симулятор"
                            :options="simulatorOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Без симулятора"
                        />
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
import DatePicker from '@/Components/UI/DatePicker.vue';
import Select from '@/Components/UI/Select.vue';

const props = defineProps({
    case: Object,
    partners: Array,
    skills: Array,
    simulators: Array,
    statusOptions: Array,
})

// Используем встроенную обработку формы Inertia
const form = useForm({
    title: props.case?.title || '',
    description: props.case?.description || '',
    partner_id: props.case?.partner_id || '',
    deadline: props.case?.deadline ? new Date(props.case.deadline) : null,
    required_team_size: props.case?.required_team_size || '',
    status: props.case?.status || '',
    reward: props.case?.reward || '',
    simulator_id: props.case?.simulator_id || null,
    required_skills: props.case?.required_skills || [],
})

const minDate = computed(() => {
    return new Date()
})

const partnerOptions = computed(() => [
    { label: 'Выберите партнера', value: '' },
    ...props.partners.map(partner => ({
        label: `${partner.name} (${partner.contact_person})`,
        value: partner.id
    }))
])

const teamSizeOptions = computed(() => [
    { label: '1 человек', value: 1 },
    { label: '2 человека', value: 2 },
    { label: '3 человека', value: 3 },
    { label: '4 человека', value: 4 },
    { label: '5 человек', value: 5 },
    { label: '6 человек', value: 6 },
    { label: '7 человек', value: 7 },
    { label: '8 человек', value: 8 },
    { label: '9 человек', value: 9 },
    { label: '10 человек', value: 10 },
])

const simulatorOptions = computed(() => [
    { label: 'Без симулятора', value: null },
    ...props.simulators.map(simulator => ({
        label: simulator.name,
        value: simulator.id
    }))
])

const submitForm = () => {
    // Используем transform для преобразования данных если нужно
    form.transform((data) => ({
        ...data,
        deadline: data.deadline ? formatDateForServer(data.deadline) : null,
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

const formatDateForServer = (date) => {
    if (!date) return null
    if (date instanceof Date) {
        return date.toISOString().split('T')[0]
    }
    return date
}
</script>
