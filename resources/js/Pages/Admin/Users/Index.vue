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
                            {{ verifiedUsersCount }}
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
                            {{ studentsCount }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl">
                        <i class="pi pi-graduation-cap text-white text-xl"></i>
                    </div>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <!-- Поиск -->
                    <div class="lg:col-span-2">
                        <SearchInput
                            v-model="filters.search"
                            label="Поиск"
                            placeholder="Имя, email, ID или kubgtu_id"
                            @input="updateFilters"
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
                        :disabled="!hasActiveFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="pi pi-refresh"></i>
                        Сбросить фильтры
                    </button>
                </div>
            </div>
        </div>

        <!-- Список пользователей -->
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

            <div v-if="usersData && usersData.length > 0">
                <!-- Заголовки для планшетной/лаптопной версии (md до xl) -->
                <div class="hidden md:grid xl:hidden md:grid-cols-6 gap-4 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <div class="col-span-3 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Пользователь
                    </div>
                    <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Роли
                    </div>
                    <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Статус
                    </div>
                </div>

                <!-- Заголовки для десктопной версии (xl+) -->
                <div class="hidden xl:grid xl:grid-cols-12 gap-4 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <div class="col-span-3 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Пользователь
                    </div>
                    <div class="col-span-3 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Контакты
                    </div>
                    <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Курс
                    </div>
                    <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Роли
                    </div>
                    <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Статус
                    </div>
                    <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Дата регистрации
                    </div>
                </div>

                <!-- Список пользователей -->
                <div class="divide-y divide-gray-200">
                    <!-- Карточка пользователя (мобильная) / Строка пользователя (десктоп) -->
                    <div
                        v-for="user in usersData"
                        :key="user.id"
                        class="hover:bg-indigo-50/50 transition-all group"
                    >
                        <!-- Мобильная версия (карточка) - только для маленьких экранов -->
                        <div class="md:hidden p-5 sm:p-6">
                            <Link :href="route('admin.users.show', user.id)" class="block">
                                <div class="flex items-start gap-4 mb-5">
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
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ user.name }}</div>
                                        <div v-if="user.kubgtu_id" class="text-xs text-gray-500 mt-1">
                                            ID: {{ user.kubgtu_id }}
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3.5">
                                    <!-- Email -->
                                    <div class="flex items-center gap-3">
                                        <i class="pi pi-envelope text-gray-400 text-sm flex-shrink-0"></i>
                                        <div class="text-sm text-gray-900 truncate">{{ user.email }}</div>
                                    </div>

                                    <!-- Курс -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Курс:</span>
                                        <span
                                            v-if="user.student_profile?.course"
                                            :style="getCourseBadgeStyle(user.student_profile.course)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border"
                                        >
                                            <i class="pi pi-calendar text-xs"></i>
                                            {{ user.student_profile.course }} курс
                                        </span>
                                        <span v-else class="text-sm text-gray-400">—</span>
                                    </div>

                                    <!-- Роли -->
                                    <div class="flex items-start gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px] pt-1">Роли:</span>
                                        <div class="flex flex-wrap gap-1.5 flex-1">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role.id"
                                                :style="getRoleBadgeStyle(role.name)"
                                                class="px-2.5 py-1 text-xs font-semibold rounded-lg border"
                                            >
                                                {{ props.roleTranslations[role.name] || role.name }}
                                            </span>
                                            <span
                                                v-if="!user.roles || user.roles.length === 0"
                                                class="text-xs text-gray-400"
                                            >
                                                Нет ролей
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Статус -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Статус:</span>
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
                                    </div>

                                    <!-- Дата регистрации -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Дата:</span>
                                        <div class="text-sm text-gray-500">{{ formatDate(user.created_at) }}</div>
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <!-- Планшетная/лаптопная версия (md до xl) - упрощенная таблица -->
                        <div class="hidden md:grid xl:hidden md:grid-cols-6 gap-4 px-4 py-4 items-center">
                            <!-- Пользователь -->
                            <div class="col-span-3">
                                <Link :href="route('admin.users.show', user.id)" class="flex items-center gap-3 group-hover:text-indigo-600 transition-colors">
                                    <div class="flex-shrink-0">
                                        <img
                                            v-if="user.avatar"
                                            class="h-10 w-10 rounded-full border-2 border-gray-200 group-hover:border-indigo-300 transition-colors"
                                            :src="user.avatar"
                                            alt=""
                                        />
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200 group-hover:border-indigo-300 transition-colors"
                                        >
                                            <span class="text-white text-xs font-bold">{{ getUserInitials(user.name) }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</div>
                                        <div v-if="user.kubgtu_id" class="text-xs text-gray-500 truncate">
                                            ID: {{ user.kubgtu_id }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate mt-0.5">
                                            {{ user.email }}
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <!-- Роли -->
                            <div class="col-span-2">
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        :style="getRoleBadgeStyle(role.name)"
                                        class="px-2 py-1 text-xs font-semibold rounded-lg border"
                                    >
                                        {{ props.roleTranslations[role.name] || role.name }}
                                    </span>
                                    <span
                                        v-if="!user.roles || user.roles.length === 0"
                                        class="text-xs text-gray-400"
                                    >
                                        Нет ролей
                                    </span>
                                </div>
                            </div>

                            <!-- Статус -->
                            <div class="col-span-1">
                                <span
                                    v-if="user.email_verified_at"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-100 text-green-800 border border-green-200 whitespace-nowrap"
                                >
                                    <i class="pi pi-check-circle text-xs"></i>
                                    Вериф.
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-red-100 text-red-800 border border-red-200 whitespace-nowrap"
                                >
                                    <i class="pi pi-times-circle text-xs"></i>
                                    Не вериф.
                                </span>
                            </div>
                        </div>

                        <!-- Десктопная версия (xl+) - полная таблица -->
                        <div class="hidden xl:grid xl:grid-cols-12 gap-4 px-6 py-5 items-center">
                            <!-- Пользователь -->
                            <div class="col-span-3">
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
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</div>
                                        <div v-if="user.kubgtu_id" class="text-xs text-gray-500">
                                            ID: {{ user.kubgtu_id }}
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <!-- Контакты -->
                            <div class="col-span-3">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-envelope text-gray-400 text-sm flex-shrink-0"></i>
                                    <div class="text-sm text-gray-900 truncate">{{ user.email }}</div>
                                </div>
                            </div>

                            <!-- Курс -->
                            <div class="col-span-1">
                                <span
                                    v-if="user.student_profile?.course"
                                    :style="getCourseBadgeStyle(user.student_profile.course)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border whitespace-nowrap"
                                >
                                    <i class="pi pi-calendar text-xs"></i>
                                    {{ user.student_profile.course }} курс
                                </span>
                                <span v-else class="text-sm text-gray-400">—</span>
                            </div>

                            <!-- Роли -->
                            <div class="col-span-2">
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        :style="getRoleBadgeStyle(role.name)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg border"
                                    >
                                        {{ props.roleTranslations[role.name] || role.name }}
                                    </span>
                                    <span
                                        v-if="!user.roles || user.roles.length === 0"
                                        class="text-xs text-gray-400"
                                    >
                                        Нет ролей
                                    </span>
                                </div>
                            </div>

                            <!-- Статус -->
                            <div class="col-span-2">
                                <span
                                    v-if="user.email_verified_at"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-800 border border-green-200 whitespace-nowrap"
                                >
                                    <i class="pi pi-check-circle text-xs"></i>
                                    Верифицирован
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-100 text-red-800 border border-red-200 whitespace-nowrap"
                                >
                                    <i class="pi pi-times-circle text-xs"></i>
                                    Не верифицирован
                                </span>
                            </div>

                            <!-- Дата регистрации -->
                            <div class="col-span-1">
                                <div class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(user.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
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
                    <span v-if="hasActiveFilters">Попробуйте изменить параметры фильтрации</span>
                    <span v-else>Пользователи не найдены</span>
                </p>
                <button
                    v-if="hasActiveFilters"
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
import SearchInput from '@/Components/UI/SearchInput.vue'
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
    roleTranslations: {
        type: Object,
        default: () => ({})
    },
    roleColors: {
        type: Object,
        default: () => ({})
    },
    courseColors: {
        type: Object,
        default: () => ({})
    },
    courses: {
        type: [Array, Object],
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({})
    },
    filteredStatistics: {
        type: Object,
        default: () => ({})
    },
})

// Computed свойства для безопасного доступа
const usersData = computed(() => props.users?.data || [])
// Используем total из пагинации, который всегда учитывает фильтры
const usersTotal = computed(() => props.users?.total || 0)
const usersLinks = computed(() => props.users?.links || [])
const verifiedUsersCount = computed(() => {
    // Если есть отфильтрованная статистика, используем её (когда есть фильтры)
    return props.filteredStatistics?.verified_users ?? props.statistics?.verified_users ?? 0
})
const studentsCount = computed(() => {
    // Если есть отфильтрованная статистика, используем её (когда есть фильтры)
    return props.filteredStatistics?.students ?? props.statistics?.students ?? 0
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    role: props.filters?.role || '',
    status: props.filters?.status || '',
    course: props.filters?.course || '',
    perPage: props.filters?.perPage ? String(props.filters.perPage) : '30',
})

// Проверка активных фильтров
const hasActiveFilters = computed(() => {
    return filters.value.search !== '' ||
        filters.value.role !== '' ||
        filters.value.status !== '' ||
        filters.value.course !== '' ||
        filters.value.perPage !== '30'
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
        perPage: '30',
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
            label: props.roleTranslations[role] || role,
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
    { label: 'Отображать по 30', value: '30' },
    { label: 'Отображать по 60', value: '60' },
    { label: 'Отображать по 100', value: '100' },
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

const hexToRgba = (hex, alpha) => {
    const r = parseInt(hex.slice(1, 3), 16)
    const g = parseInt(hex.slice(3, 5), 16)
    const b = parseInt(hex.slice(5, 7), 16)
    return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

const getCourseBadgeStyle = (course) => {
    const color = props.courseColors[course] || '#6B7280'
    return {
        backgroundColor: hexToRgba(color, 0.1),
        color: color,
        borderColor: hexToRgba(color, 0.3),
    }
}

const getRoleBadgeStyle = (roleName) => {
    const color = props.roleColors[roleName] || '#6B7280'
    return {
        backgroundColor: hexToRgba(color, 0.1),
        color: color,
        borderColor: hexToRgba(color, 0.3),
    }
}
</script>
