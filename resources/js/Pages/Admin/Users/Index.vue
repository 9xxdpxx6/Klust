<template>
    <div class="p-6">
        <Head title="Пользователи" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold">Управление пользователями</h1>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Поиск -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Имя, email или ID"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        @change="updateFilters"
                    />
                </div>

                <!-- Фильтр по ролям -->
                <div>
                    <Select
                        v-model="filters.role"
                        label="Роль"
                        :options="roleFilterOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Все роли"
                        @update:modelValue="updateFilters"
                    />
                </div>

                <!-- Фильтр по статусу -->
                <div>
                    <Select
                        v-model="filters.status"
                        label="Статус"
                        :options="statusFilterOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Все"
                        @update:modelValue="updateFilters"
                    />
                </div>

                <!-- Фильтр по курсу -->
                <div>
                    <Select
                        v-model="filters.course"
                        label="Курс"
                        :options="courseFilterOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Все курсы"
                        @update:modelValue="updateFilters"
                    />
                </div>

                <!-- Количество на странице -->
                <div>
                    <Select
                        v-model="filters.perPage"
                        label="На странице"
                        :options="perPageOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Выберите количество"
                        @update:modelValue="updateFilters"
                    />
                </div>
            </div>

            <!-- Кнопка сброса фильтров -->
            <div class="mt-4 flex justify-end">
                <button
                    @click="resetFilters"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                >
                    Сбросить фильтры
                </button>
            </div>
        </div>

        <!-- Таблица пользователей -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Всего пользователей: {{ usersTotal }}
                </div>
                <Link
                    :href="route('admin.users.create')"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
                >
                    Добавить пользователя
                </Link>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Пользователь
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Контакты
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Курс
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Роли
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Статус
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Дата регистрации
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in usersData" :key="user.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img
                                    v-if="user.avatar"
                                    class="h-10 w-10 rounded-full"
                                    :src="user.avatar"
                                    alt=""
                                />
                                <div
                                    v-else
                                    class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center"
                                >
                                    <span class="text-gray-600 text-sm font-medium">{{ getUserInitials(user.name) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <Link :href="route('admin.users.show', user.id)" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                    {{ user.name }}
                                </Link>
                                <div v-if="user.kubgtu_id" class="text-sm text-gray-500">
                                    ID: {{ user.kubgtu_id }}
                                </div>
                            </div>

                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ user.email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
              <span
                  v-if="user.course"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
              >
                {{ user.course }} курс
              </span>
                        <span v-else class="text-sm text-gray-500">—</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-wrap gap-1">
                <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800"
                >
                  {{ role.name }}
                </span>
                            <span
                                v-if="!user.roles || user.roles.length === 0"
                                class="text-sm text-gray-500"
                            >
                  Нет ролей
                </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
              <span
                  v-if="user.email_verified_at"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
              >
                Верифицирован
              </span>
                        <span
                            v-else
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                        >
                Не верифицирован
              </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatDate(user.created_at) }}
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Пагинация -->
            <Pagination v-if="usersLinks" :links="usersLinks" class="mt-4" />
        </div>

        <!-- Сообщение если нет пользователей -->
        <div v-if="!usersData || usersData.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">Пользователи не найдены</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import Select from '@/Components/UI/Select.vue'

const props = defineProps({
    users: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    roles: {
        type: Array,
        default: () => []
    },
    courses: {
        type: Array,
        default: () => []
    },
})

// Computed свойства для безопасного доступа
const usersData = computed(() => props.users?.data || [])
const usersTotal = computed(() => props.users?.total || 0)
const usersLinks = computed(() => props.users?.links || [])

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    role: props.filters?.role || '',
    status: props.filters?.status || '',
    course: props.filters?.course || '',
    perPage: props.filters?.perPage || 15,
})

// Обновление фильтров с debounce для поиска
let searchTimeout = null
const updateFilters = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), filters.value, {
            preserveState: true,
            replace: true,
        })
    }, 500)
}

const resetFilters = () => {
    filters.value = {
        search: '',
        role: '',
        status: '',
        course: '',
        perPage: 15,
    }
    updateFilters()
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
}

const roleFilterOptions = computed(() => [
    { label: 'Все роли', value: '' },
    ...props.roles.map(role => ({
        label: role,
        value: role
    }))
])

const statusFilterOptions = computed(() => [
    { label: 'Все', value: '' },
    { label: 'Верифицирован', value: 'verified' },
    { label: 'Не верифицирован', value: 'unverified' },
])

const courseFilterOptions = computed(() => [
    { label: 'Все курсы', value: '' },
    ...props.courses.map(course => ({
        label: `${course} курс`,
        value: course
    }))
])

const perPageOptions = computed(() => [
    { label: '10', value: '10' },
    { label: '15', value: '15' },
    { label: '25', value: '25' },
    { label: '50', value: '50' },
])

const getUserInitials = (name) => {
    if (!name) return '??'
    return name
        .split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}
</script>
