<template>
    <div class="space-y-6">
        <Head title="Пользователи" />

        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Управление пользователями</h1>
                        <p class="text-indigo-100">Список всех пользователей системы</p>
                    </div>
                    <Link
                        :href="route('admin.users.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium"
                    >
                        <i class="pi pi-plus"></i>
                        Добавить пользователя
                    </Link>
                </div>
            </div>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-filter text-indigo-600"></i>
                    Фильтры поиска
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Поиск -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                        <div class="relative">
                            <i class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Имя, email или ID"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                                @input="updateFilters"
                            />
                        </div>
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
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        <i class="pi pi-refresh"></i>
                        Сбросить фильтры
                    </button>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 shadow-md border border-blue-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Всего пользователей</p>
                        <p class="text-2xl font-bold text-blue-900">{{ usersTotal }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl">
                        <i class="pi pi-users text-white text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 shadow-md border border-green-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Верифицированных</p>
                        <p class="text-2xl font-bold text-green-900">
                            {{ usersData.filter(u => u.email_verified_at).length }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl">
                        <i class="pi pi-check-circle text-white text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-5 shadow-md border border-amber-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 mb-1">Студентов</p>
                        <p class="text-2xl font-bold text-amber-900">
                            {{ usersData.filter(u => u.roles?.some(r => r.name === 'student')).length }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl">
                        <i class="pi pi-graduation-cap text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Таблица пользователей -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-list text-indigo-600"></i>
                        Список пользователей
                    </h2>
                    <span class="text-sm text-gray-600 bg-white px-3 py-1 rounded-lg border border-gray-200">
                        Всего: {{ usersTotal }}
                    </span>
                </div>
            </div>

            <div v-if="usersData && usersData.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Пользователь
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Контакты
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Курс
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Роли
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Статус
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Дата регистрации
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    <tr
                        v-for="user in usersData"
                        :key="user.id"
                        class="hover:bg-indigo-50/50 transition-all group"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <Link :href="route('admin.users.show', user.id)" class="flex items-center gap-3 group-hover:text-indigo-600 transition-colors">
                                <div class="flex-shrink-0">
                                    <img
                                        v-if="user.avatar"
                                        class="h-12 w-12 rounded-full border-2 border-gray-200 group-hover:border-indigo-300 transition-colors"
                                        :src="user.avatar"
                                        alt=""
                                    />
                                    <div
                                        v-else
                                        class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200 group-hover:border-indigo-300 transition-colors"
                                    >
                                        <span class="text-white text-sm font-bold">{{ getUserInitials(user.name) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ user.name }}</div>
                                    <div v-if="user.kubgtu_id" class="text-xs text-gray-500">
                                        ID: {{ user.kubgtu_id }}
                                    </div>
                                </div>
                            </Link>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-envelope text-gray-400 text-sm"></i>
                                <div class="text-sm text-gray-900">{{ user.email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                v-if="user.course"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 border border-blue-200"
                            >
                                <i class="pi pi-calendar text-xs"></i>
                                {{ user.course }} курс
                            </span>
                            <span v-else class="text-sm text-gray-400">—</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-100 text-green-800 border border-green-200"
                                >
                                    {{ role.name }}
                                </span>
                                <span
                                    v-if="!user.roles || user.roles.length === 0"
                                    class="text-xs text-gray-400"
                                >
                                    Нет ролей
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                v-if="user.email_verified_at"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-800 border border-green-200"
                            >
                                <i class="pi pi-check-circle text-xs"></i>
                                Верифицирован
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-100 text-red-800 border border-red-200"
                            >
                                <i class="pi pi-times-circle text-xs"></i>
                                Не верифицирован
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ formatDate(user.created_at) }}</div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div v-if="usersLinks && usersLinks.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <Pagination :links="usersLinks" />
            </div>
        </div>

        <!-- Сообщение если нет пользователей -->
        <div v-if="!usersData || usersData.length === 0" class="bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                    <i class="pi pi-users text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Пользователи не найдены</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Попробуйте изменить параметры фильтрации
                </p>
                <button
                    @click="resetFilters"
                    class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                >
                    Сбросить фильтры
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import Select from '@/Components/UI/Select.vue'
import { route } from 'ziggy-js'

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
        type: [Array, Object],
        default: () => []
    },
    courses: {
        type: [Array, Object],
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
    perPage: props.filters?.perPage ? String(props.filters.perPage) : '25',
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
        perPage: '25',
    }
    updateFilters()
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
}

const roleFilterOptions = computed(() => {
    const rolesArray = Array.isArray(props.roles) 
        ? props.roles 
        : (props.roles ? Object.values(props.roles) : []);
    
    return [
        { label: 'Все роли', value: '' },
        ...rolesArray.map(role => ({
            label: role,
            value: role
        }))
    ];
})

const statusFilterOptions = computed(() => [
    { label: 'Все', value: '' },
    { label: 'Верифицирован', value: 'verified' },
    { label: 'Не верифицирован', value: 'unverified' },
])

const courseFilterOptions = computed(() => {
    const coursesArray = Array.isArray(props.courses) 
        ? props.courses 
        : (props.courses ? Object.values(props.courses) : []);
    
    return [
        { label: 'Все курсы', value: '' },
        ...coursesArray.map(course => ({
            label: `${course} курс`,
            value: course
        }))
    ];
})

const perPageOptions = computed(() => [
    { label: 'Отображать по 10', value: '10' },
    { label: 'Отображать по 15', value: '15' },
    { label: 'Отображать по 25', value: '25' },
    { label: 'Отображать по 50', value: '50' },
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
