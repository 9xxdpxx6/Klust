<template>
    <div class="p-6">
        <Head :title="`Редактирование пользователя - ${user.name}`" />

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
                            <Link :href="route('admin.users.show', user.id)" class="text-gray-400 hover:text-gray-500">
                                {{ user.name }}
                            </Link>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2 text-gray-400">/</span>
                            <span class="ml-2 text-sm font-medium text-gray-500">Редактирование</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Редактирование пользователя
                        </h3>
                        <div class="text-sm text-gray-500">
                            ID: {{ user.kubgtu_id }}
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Основная информация -->
                            <div class="sm:col-span-2">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Основная информация</h4>
                            </div>

                            <!-- KUBGTU ID (только для просмотра) -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">KUBGTU ID</label>
                                <div class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded border">
                                    {{ user.kubgtu_id }}
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Системный идентификатор, нельзя изменить</p>
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

                            <!-- Смена пароля -->
                            <div class="sm:col-span-2 border-t pt-4">
                                <h4 class="text-md font-medium text-gray-900 mb-4">Смена пароля</h4>
                                <p class="text-sm text-gray-500 mb-4">
                                    Оставьте эти поля пустыми, если не хотите менять пароль
                                </p>
                            </div>

                            <!-- Новый пароль -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Новый пароль</label>
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
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Подтверждение пароля</label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                        </div>

                        <!-- Кнопки -->
                        <div class="mt-8 flex justify-between">
                            <div>
                                <button
                                    type="button"
                                    @click="confirmDelete"
                                    class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    :disabled="form.processing"
                                >
                                    Удалить пользователя
                                </button>
                            </div>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('admin.users.show', user.id)"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Отмена
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    <span v-if="form.processing">Сохранение...</span>
                                    <span v-else>Сохранить изменения</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link, Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    roles: Array,
    currentRole: String,
    errors: Object,
})

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    course: props.user.course !== null && props.user.course !== undefined ? Number(props.user.course) : null,
    role: props.currentRole || '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    // Преобразуем курс перед отправкой
    const submitData = {
        ...form.data(),
        course: form.course === null || form.course === '' ? null : Number(form.course)
    }

    form.transform(() => submitData).put(route('admin.users.update', props.user.id))
}

const confirmDelete = () => {
    if (confirm(`Вы уверены, что хотите удалить пользователя "${props.user.name}"? Это действие нельзя отменить.`)) {
        router.delete(route('admin.users.destroy', props.user.id))
    }
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
