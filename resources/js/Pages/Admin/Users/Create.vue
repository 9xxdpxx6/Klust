<template>
    <div>
        <Head title="Создание пользователя" />

        <!-- Хлебные крошки -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <Link :href="route('admin.users.index')" class="text-gray-400 hover:text-gray-500">
                                Пользователи
                            </Link>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2 text-gray-400">/</span>
                            <span class="ml-2 text-sm font-medium text-gray-500">Создание пользователя</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">Создание нового пользователя</h3>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Основная информация -->
                            <div class="sm:col-span-2">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Основная информация</h4>
                            </div>

                            <!-- Имя -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Имя *</label>
                                <input
                                    type="text"
                                    id="name"
                                    v-model="form.name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                                    :class="{ 'border-red-300': errors.name }"
                                />
                                <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                <input
                                    type="email"
                                    id="email"
                                    v-model="form.email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                                    :class="{ 'border-red-300': errors.email }"
                                />
                                <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</div>
                            </div>

                            <!-- Курс -->
                            <Select
                                v-model="form.course"
                                label="Курс"
                                :options="courseOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Не указан"
                                :error="errors.course"
                            />

                            <!-- Пароль -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Пароль *</label>
                                <input
                                    type="password"
                                    id="password"
                                    v-model="form.password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                                    :class="{ 'border-red-300': errors.password }"
                                />
                                <div v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</div>
                            </div>

                            <!-- Подтверждение пароля -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Подтверждение пароля *</label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                                />
                            </div>

                            <!-- Роль -->
                            <Select
                                v-model="form.role"
                                label="Роль"
                                :options="roleOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Выберите роль"
                                :error="errors.role"
                                required
                            />
                        </div>

                        <!-- Кнопки -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <Link
                                :href="route('admin.users.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                            >
                                Отмена
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none disabled:opacity-50"
                            >
                                <span v-if="form.processing">Создание...</span>
                                <span v-else>Создать пользователя</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import Select from '@/Components/UI/Select.vue'
import { route } from 'ziggy-js'

const props = defineProps({
    roles: Array,
    errors: Object,
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    course: null,
    role: '',
    email_verified: false,
})

const submit = () => {
    form.post(route('admin.users.store'))
}

const getRoleDisplayName = (role) => {
    const roleNames = {
        'admin': 'Администратор',
        'teacher': 'Преподаватель',
        'student': 'Студент',
        'partner': 'Партнер',
    }
    return roleNames[role] || role
}

const courseOptions = computed(() => [
    { label: 'Не указан', value: null },
    ...Array.from({ length: 6 }, (_, i) => ({
        label: `${i + 1} курс`,
        value: i + 1
    }))
])

const roleOptions = computed(() => [
    { label: 'Выберите роль', value: '' },
    ...props.roles.map(role => ({
        label: getRoleDisplayName(role),
        value: role
    }))
])
</script>
