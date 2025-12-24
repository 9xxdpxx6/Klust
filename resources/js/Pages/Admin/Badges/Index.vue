<template>
    <div class="space-y-6">
        <Head title="Управление достижениями"/>

        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-4 sm:px-6 py-6 sm:py-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Управление достижениями</h1>
                        <p class="text-sm sm:text-base text-indigo-100">Список всех достижений в системе</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium text-sm sm:text-base w-full sm:w-auto"
                    >
                        <i class="pi pi-plus"></i>
                        Создать достижение
                    </button>
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Поиск -->
                    <div>
                        <SearchInput
                            v-model="filters.search"
                            label="Поиск"
                            placeholder="Название достижения"
                            @input="handleSearch"
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
            </div>

            <!-- Кнопка сброса фильтров -->
            <div class="px-6 pb-6">
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

        <!-- Статистика -->
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-5 shadow-md border border-amber-200/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-amber-600 mb-1">Всего достижений</p>
                    <p class="text-2xl font-bold text-amber-900">{{ badgesTotal }}</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl">
                    <i class="pi pi-trophy text-white text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Таблица достижений -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-4 md:px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div :class="['flex items-center', 'flex-col gap-2 items-start md:flex-row md:justify-between md:items-center']">
                    <h2 class="text-base md:text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-list text-indigo-600"></i>
                        Список достижений
                    </h2>
                    <span class="text-xs md:text-sm text-gray-600 bg-white px-2 md:px-3 py-1 rounded-lg border border-gray-200 whitespace-nowrap">
                        Всего: {{ badgesTotal }}
                    </span>
                </div>
            </div>

            <div v-if="badgesData && badgesData.length > 0">
                <!-- Заголовки для планшетной/лаптопной версии (md до xl) -->
                <div class="hidden md:grid xl:hidden md:grid-cols-4 gap-4 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Достижение
                    </div>
                    <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Требуемые баллы
                    </div>
                    <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Действия
                    </div>
                </div>

                <!-- Заголовки для десктопной версии (xl+) -->
                <div class="hidden xl:grid xl:grid-cols-6 gap-4 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Иконка
                    </div>
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Название
                    </div>
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Описание
                    </div>
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Требуемые баллы
                    </div>
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Создан
                    </div>
                    <div class="text-xs font-bold text-gray-700 uppercase tracking-wider text-right">
                        Действия
                    </div>
                </div>

                <!-- Список достижений -->
                <div class="divide-y divide-gray-200">
                    <!-- Карточка достижения (мобильная) / Строка достижения (планшет/десктоп) -->
                    <div
                        v-for="badge in badgesData"
                        :key="badge.id"
                        class="hover:bg-indigo-50/50 transition-all group"
                    >
                        <!-- Мобильная версия (карточка) -->
                        <div class="md:hidden p-5 sm:p-6">
                            <div class="flex items-start gap-4 mb-5">
                                <div class="w-14 h-14 flex items-center justify-center bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors flex-shrink-0">
                                    <div v-if="badge.icon_path" class="w-10 h-10 flex items-center justify-center">
                                        <img :src="badge.icon_path" :alt="badge.name" class="max-w-full max-h-full object-contain"/>
                                    </div>
                                    <div v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))" class="w-10 h-10 flex items-center justify-center">
                                        <i :class="['text-amber-600 text-xl', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                                    </div>
                                    <div v-else class="w-10 h-10 flex items-center justify-center">
                                        <i class="pi pi-trophy text-amber-600 text-xl"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 mb-1">
                                        {{ badge.name }}
                                    </div>
                                    <div class="text-xs text-gray-500 line-clamp-2">
                                        {{ badge.description || '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                <!-- Требуемые баллы -->
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-500 font-medium min-w-[70px]">Баллы:</span>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 flex items-center justify-center bg-indigo-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-star text-indigo-600 text-xs"></i>
                                        </div>
                                        <span class="text-sm font-semibold text-indigo-600">
                                            {{ badge.required_points }} {{ pluralize(badge.required_points, 'балл', 'балла', 'баллов') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Дата создания -->
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-500 font-medium min-w-[70px]">Создан:</span>
                                    <div class="text-sm text-gray-500">{{ formatDate(badge.created_at) }}</div>
                                </div>

                                <!-- Действия -->
                                <div class="flex items-center gap-3 pt-2 border-t border-gray-200">
                                    <span class="text-xs text-gray-500 font-medium min-w-[70px]">Действия:</span>
                                    <div class="flex gap-2">
                                        <button
                                            @click.stop="openEditModal(badge)"
                                            class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-indigo-200"
                                            title="Редактировать"
                                        >
                                            <i class="pi pi-pencil text-sm"></i>
                                        </button>
                                        <button
                                            @click.stop="confirmDelete(badge)"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-red-200"
                                            title="Удалить"
                                        >
                                            <i class="pi pi-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Планшетная/лаптопная версия (md до xl) - упрощенная таблица -->
                        <div class="hidden md:grid xl:hidden md:grid-cols-4 gap-4 px-4 py-4 items-center">
                            <!-- Достижение -->
                            <div class="col-span-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 flex items-center justify-center bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors flex-shrink-0">
                                        <div v-if="badge.icon_path" class="w-8 h-8 flex items-center justify-center">
                                            <img :src="badge.icon_path" :alt="badge.name" class="max-w-full max-h-full object-contain"/>
                                        </div>
                                        <div v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))" class="w-8 h-8 flex items-center justify-center">
                                            <i :class="['text-amber-600 text-lg', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                                        </div>
                                        <div v-else class="w-8 h-8 flex items-center justify-center">
                                            <i class="pi pi-trophy text-amber-600 text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 truncate">{{ badge.name }}</div>
                                        <div class="text-xs text-gray-500 line-clamp-1">{{ badge.description || '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Требуемые баллы -->
                            <div class="col-span-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 flex items-center justify-center bg-indigo-100 rounded-lg flex-shrink-0">
                                        <i class="pi pi-star text-indigo-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-indigo-600 whitespace-nowrap">
                                        {{ badge.required_points }}
                                    </span>
                                </div>
                            </div>

                            <!-- Действия -->
                            <div class="col-span-1">
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click.stop="openEditModal(badge)"
                                        class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-indigo-200"
                                        title="Редактировать"
                                    >
                                        <i class="pi pi-pencil text-sm"></i>
                                    </button>
                                    <button
                                        @click.stop="confirmDelete(badge)"
                                        class="p-2 text-gray-400 group-hover:text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-red-200 opacity-50 group-hover:opacity-100"
                                        title="Удалить"
                                    >
                                        <i class="pi pi-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Десктопная версия (xl+) - полная таблица -->
                        <div class="hidden xl:grid xl:grid-cols-6 gap-4 px-6 py-5 items-center">
                            <!-- Иконка -->
                            <div>
                                <div class="w-14 h-14 flex items-center justify-center bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors">
                                    <div v-if="badge.icon_path" class="w-10 h-10 flex items-center justify-center">
                                        <img :src="badge.icon_path" :alt="badge.name" class="max-w-full max-h-full object-contain"/>
                                    </div>
                                    <div v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))" class="w-10 h-10 flex items-center justify-center">
                                        <i :class="['text-amber-600 text-xl', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"></i>
                                    </div>
                                    <div v-else class="w-10 h-10 flex items-center justify-center">
                                        <i class="pi pi-trophy text-amber-600 text-xl"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Название -->
                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ badge.name }}
                                </div>
                            </div>

                            <!-- Описание -->
                            <div>
                                <div class="text-sm text-gray-600 max-w-md line-clamp-2">
                                    {{ badge.description || '—' }}
                                </div>
                            </div>

                            <!-- Требуемые баллы -->
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 flex items-center justify-center bg-indigo-100 rounded-lg flex-shrink-0">
                                        <i class="pi pi-star text-indigo-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-indigo-600 whitespace-nowrap">
                                        {{ badge.required_points }} {{ pluralize(badge.required_points, 'балл', 'балла', 'баллов') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Создан -->
                            <div>
                                <div class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(badge.created_at) }}</div>
                            </div>

                            <!-- Действия -->
                            <div class="text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        @click.stop="openEditModal(badge)"
                                        class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-indigo-200"
                                        title="Редактировать"
                                    >
                                        <i class="pi pi-pencil text-sm"></i>
                                    </button>
                                    <button
                                        @click.stop="confirmDelete(badge)"
                                        class="p-2 text-gray-400 group-hover:text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-red-200 opacity-50 group-hover:opacity-100"
                                        title="Удалить"
                                    >
                                        <i class="pi pi-trash text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div v-if="badgesLinks && badgesLinks.length > 0" :class="['border-t border-gray-200 bg-gray-50', 'px-4 md:px-6 py-4']">
                <Pagination :links="badgesLinks" />
            </div>
        </div>

        <!-- Сообщение если нет достижений -->
        <div v-if="!badgesData || badgesData.length === 0" class="bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                    <i class="pi pi-trophy text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Достижения не найдены</h3>
                <p class="text-sm text-gray-500 mb-6">
                    <span v-if="hasActiveFilters">Попробуйте изменить параметры фильтрации</span>
                    <span v-else>Создайте первое достижение, чтобы начать работу</span>
                </p>
                <div class="flex gap-3 justify-center">
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Сбросить фильтры
                    </button>
                    <button
                        v-else
                        @click="openCreateModal"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                    >
                        Создать достижение
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания/редактирования -->
        <Modal
            :visible="modalVisible"
            :title="editingBadge ? 'Редактировать достижение' : 'Создать достижение'"
            @update:visible="modalVisible = $event"
            @close="closeModal"
            size="md"
        >
            <form @submit.prevent="submitForm" novalidate>
                <div class="space-y-4">
                    <Input
                        v-model="form.name"
                        label="Название"
                        placeholder="Введите название достижения"
                        :error="form.errors.name"
                        required
                    />

                    <Textarea
                        v-model="form.description"
                        label="Описание"
                        placeholder="Введите описание достижения"
                        :error="form.errors.description"
                        :rows="3"
                        required
                    />

                    <Input
                        v-model.number="form.required_points"
                        type="text"
                        label="Требуемые баллы"
                        placeholder="100"
                        :error="form.errors.required_points"
                        :hint="`Количество баллов, необходимое для получения достижения (минимум 1)`"
                        required
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Иконка достижения
                        </label>
                        <div v-if="form.icon || editingBadge?.icon" class="mb-3 flex items-center gap-4">
                            <div
                                class="w-20 h-20 flex items-center justify-center border border-gray-300 rounded p-2 bg-gray-50"
                            >
                                <i :class="['text-[48px] text-gray-600', `pi ${form.icon || editingBadge.icon}`]"></i>
                            </div>
                        </div>
                        <IconPicker
                            v-model="form.icon"
                            :error="form.errors.icon"
                        />
                        <div v-if="form.errors.icon" class="mt-1 text-sm text-red-600">
                            {{ form.errors.icon }}
                        </div>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        variant="secondary"
                        type="button"
                        @click="closeModal"
                        :disabled="form.processing"
                    >
                        Отмена
                    </Button>
                    <button
                        type="button"
                        @click="submitForm"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    >
                        {{ editingBadge ? 'Сохранить' : 'Создать' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Модальное окно подтверждения удаления -->
        <Modal
            :visible="deleteModalVisible"
            title="Подтверждение удаления"
            @update:visible="deleteModalVisible = $event"
            @close="closeDeleteModal"
            size="sm"
        >
            <div v-if="badgeToDelete">
                <p class="text-gray-700 mb-4">
                    Вы уверены, что хотите удалить достижение <strong>{{ badgeToDelete.name }}</strong>?
                </p>
                <p v-if="badgeToDelete.users_count" class="text-sm text-red-600 mb-4">
                    Внимание! Это достижение используется 
                    {{ badgeToDelete.users_count }} 
                    {{ pluralize(badgeToDelete.users_count, 'пользователем', 'пользователями', 'пользователями') }}.
                    Удаление невозможно.
                </p>
                <p v-else class="text-sm text-gray-600">
                    Это действие необратимо. Достижение будет удалено.
                </p>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        variant="secondary"
                        type="button"
                        @click="closeDeleteModal"
                        :disabled="deleteForm.processing"
                    >
                        Отмена
                    </Button>
                    <Button
                        severity="danger"
                        type="button"
                        @click="deleteBadge"
                        :disabled="deleteForm.processing || (badgeToDelete && badgeToDelete.users_count)"
                    >
                        Удалить
                    </Button>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import {ref, computed} from 'vue'
import {router, useForm} from '@inertiajs/vue3'
import {Head} from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import Select from '@/Components/UI/Select.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import Input from '@/Components/UI/Input.vue'
import Textarea from '@/Components/UI/Textarea.vue'
import IconPicker from '@/Components/UI/IconPicker.vue'
import Modal from '@/Components/UI/Modal.vue'
import Button from 'primevue/button'
import {route} from "ziggy-js"

const props = defineProps({
    badges: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
})

// Computed свойства для безопасного доступа
const badgesData = computed(() => props.badges?.data || [])
const badgesTotal = computed(() => props.badges?.total || 0)
const badgesLinks = computed(() => props.badges?.links || [])

// Проверка активных фильтров
const hasActiveFilters = computed(() => {
    return filters.value.search !== '' || filters.value.perPage !== '30'
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    perPage: props.filters?.perPage ? String(props.filters.perPage) : '30',
})

// Модальные окна
const modalVisible = ref(false)
const deleteModalVisible = ref(false)
const editingBadge = ref(null)
const badgeToDelete = ref(null)

// Форма создания/редактирования
const form = useForm({
    name: '',
    description: '',
    required_points: 1,
    icon: null,
})

// Форма удаления
const deleteForm = useForm({})

const perPageOptions = computed(() => [
    { label: 'Отображать по 30', value: '30' },
    { label: 'Отображать по 60', value: '60' },
    { label: 'Отображать по 100', value: '100' },
])

// Таймер для дебаунса
let searchTimeout = null

// Обработчик поиска с дебаунсом
const handleSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        updateFilters()
    }, 500)
}

const updateFilters = () => {
    router.get(route('admin.badges.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        perPage: '30',
    }
    updateFilters()
}


// Функции модального окна
const openCreateModal = () => {
    editingBadge.value = null
    form.reset()
    form.clearErrors()
    modalVisible.value = true
}

const openEditModal = (badge) => {
    editingBadge.value = badge
    form.name = badge.name
    form.description = badge.description || ''
    form.required_points = badge.required_points
    form.icon = badge.icon || null
    form.clearErrors()
    modalVisible.value = true
}

const closeModal = () => {
    modalVisible.value = false
    editingBadge.value = null
    form.reset()
    form.clearErrors()
}

const submitForm = () => {
    if (editingBadge.value) {
        form.put(route('admin.badges.update', editingBadge.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            },
        })
    } else {
        form.post(route('admin.badges.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            },
        })
    }
}

// Функции удаления
const confirmDelete = (badge) => {
    badgeToDelete.value = badge
    deleteModalVisible.value = true
}

const closeDeleteModal = () => {
    deleteModalVisible.value = false
    badgeToDelete.value = null
    deleteForm.reset()
    deleteForm.clearErrors()
}

const deleteBadge = () => {
    if (!badgeToDelete.value) return

    deleteForm.delete(route('admin.badges.destroy', badgeToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal()
        },
    })
}

// Вспомогательные функции
const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
}

const pluralize = (count, one, two, five) => {
    const mod10 = count % 10
    const mod100 = count % 100

    if (mod100 >= 11 && mod100 <= 19) {
        return five
    }
    if (mod10 === 1) {
        return one
    }
    if (mod10 >= 2 && mod10 <= 4) {
        return two
    }
    return five
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
