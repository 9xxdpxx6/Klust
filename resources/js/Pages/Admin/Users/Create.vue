<template>
    <div class="p-6">
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-300': errors.email }"
                                />
                                <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</div>
                            </div>

                            <!-- Курс -->
                            <div>
                                <label for="course" class="block text-sm font-medium text-gray-700">Курс</label>
                                <select
                                    id="course"
                                    v-model="form.course"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null">Не указан</option>
                                    <option v-for="i in 6" :key="i" :value="i">{{ i }} курс</option>
                                </select>
                                <div v-if="errors.course" class="text-red-500 text-sm mt-1">{{ errors.course }}</div>
                            </div>

                            <!-- Пароль -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Пароль *</label>
                                <input
                                    type="password"
                                    id="password"
                                    v-model="form.password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <!-- Роль -->
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700">Роль *</label>
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-300': errors.role }"
                                >
                                    <option value="">Выберите роль</option>
                                    <option v-for="role in roles" :key="role" :value="role">
                                        {{ getRoleDisplayName(role) }}
                                    </option>
                                </select>
                                <div v-if="errors.role" class="text-red-500 text-sm mt-1">{{ errors.role }}</div>
                            </div>
                        </div>

                        <!-- Кнопки -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <Link
                                :href="route('admin.users.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Отмена
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
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

const props = defineProps({
    roles: Array,
    errors: Object,
})

const form = useForm({
    kubgtu_id: '',
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
</script>
