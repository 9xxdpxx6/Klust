<template>
    <div class="space-y-6">
        <Head title="Управление навыками"/>

        <FlashMessage/>

        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Управление навыками</h1>
                        <p class="text-indigo-100">Список всех навыков в системе</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium"
                    >
                        <i class="pi pi-plus"></i>
                        Создать навык
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Поиск -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                        <div class="relative">
                            <i class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Название навыка"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all"
                                @input="handleSearch"
                            />
                        </div>
                    </div>

                <!-- Фильтр по категории -->
                <div>
                    <Select
                        v-model="filters.category"
                        label="Категория"
                        :options="categoryFilterOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Все категории"
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

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 shadow-md border border-blue-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Всего навыков</p>
                        <p class="text-2xl font-bold text-blue-900">{{ skillsTotal }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl">
                        <i class="pi pi-star text-white text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 shadow-md border border-green-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Hard Skills</p>
                        <p class="text-2xl font-bold text-green-900">
                            {{ skillsData.filter(s => s.category === 'hard').length }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl">
                        <i class="pi pi-code text-white text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 shadow-md border border-purple-200/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 mb-1">Soft Skills</p>
                        <p class="text-2xl font-bold text-purple-900">
                            {{ skillsData.filter(s => s.category === 'soft').length }}
                        </p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl">
                        <i class="pi pi-users text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Таблица навыков -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-list text-indigo-600"></i>
                        Список навыков
                    </h2>
                    <span class="text-sm text-gray-600 bg-white px-3 py-1 rounded-lg border border-gray-200">
                        Всего: {{ skillsTotal }}
                    </span>
                </div>
            </div>

            <div v-if="skillsData && skillsData.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Навык
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Категория
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Макс. уровень
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Используется
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Создан
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                    <tr
                        v-for="skill in skillsData"
                        :key="skill.id"
                        class="hover:bg-indigo-50/50 transition-all group"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors">
                                    <i class="pi pi-star text-indigo-600"></i>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ skill.name }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border',
                                    getCategoryBadgeClass(skill.category)
                                ]"
                            >
                                {{ getCategoryLabel(skill.category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-blue-100 rounded-lg">
                                    <i class="pi pi-chart-line text-blue-600 text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ skill.max_level }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <div v-if="skill.users_count || skill.cases_count" class="flex items-center gap-2 flex-wrap">
                                    <span v-if="skill.users_count" class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg border border-blue-200">
                                        <i class="pi pi-users text-xs"></i>
                                        {{ skill.users_count }} {{ pluralize(skill.users_count, 'пользователь', 'пользователя', 'пользователей') }}
                                    </span>
                                    <span v-if="skill.cases_count" class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-lg border border-indigo-200">
                                        <i class="pi pi-briefcase text-xs"></i>
                                        {{ skill.cases_count }} {{ pluralize(skill.cases_count, 'кейс', 'кейса', 'кейсов') }}
                                    </span>
                                </div>
                                <span v-else class="text-xs text-gray-400">Не используется</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ formatDate(skill.created_at) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    @click.stop="openEditModal(skill)"
                                    class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-indigo-200"
                                    title="Редактировать"
                                >
                                    <i class="pi pi-pencil text-sm"></i>
                                </button>
                                <button
                                    @click.stop="confirmDelete(skill)"
                                    class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-red-200"
                                    title="Удалить"
                                >
                                    <i class="pi pi-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div v-if="skillsLinks && skillsLinks.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <Pagination :links="skillsLinks" />
            </div>
        </div>

        <!-- Сообщение если нет навыков -->
        <div v-if="!skillsData || skillsData.length === 0" class="bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                    <i class="pi pi-star text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Навыки не найдены</h3>
                <p class="text-sm text-gray-500 mb-6">
                    <span v-if="hasActiveFilters">Попробуйте изменить параметры фильтрации</span>
                    <span v-else>Создайте первый навык, чтобы начать работу</span>
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
                        Создать навык
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания/редактирования -->
        <Modal
            :visible="modalVisible"
            :title="editingSkill ? 'Редактировать навык' : 'Создать навык'"
            @update:visible="modalVisible = $event"
            @close="closeModal"
            size="md"
        >
            <form @submit.prevent="submitForm">
                <div class="space-y-4">
                    <Input
                        v-model="form.name"
                        label="Название"
                        placeholder="Введите название навыка"
                        :error="form.errors.name"
                        required
                    />

                    <Select
                        v-model="form.category"
                        label="Категория"
                        :options="categoryOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Выберите категорию"
                        :error="form.errors.category"
                        required
                    />

                    <Input
                        v-model.number="form.max_level"
                        type="number"
                        label="Максимальный уровень"
                        placeholder="100"
                        :error="form.errors.max_level"
                        :hint="`Максимальный уровень, который можно достичь по этому навыку (от 1 до 1000)`"
                        required
                    />
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
                        {{ editingSkill ? 'Сохранить' : 'Создать' }}
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
            <div v-if="skillToDelete">
                <p class="text-gray-700 mb-4">
                    Вы уверены, что хотите удалить навык <strong>{{ skillToDelete.name }}</strong>?
                </p>
                <p v-if="skillToDelete.users_count || skillToDelete.cases_count" class="text-sm text-red-600 mb-4">
                    Внимание! Этот навык используется:
                    <span v-if="skillToDelete.users_count">
                        {{ skillToDelete.users_count }} {{ pluralize(skillToDelete.users_count, 'пользователем', 'пользователями', 'пользователями') }}
                    </span>
                    <span v-if="skillToDelete.users_count && skillToDelete.cases_count"> и </span>
                    <span v-if="skillToDelete.cases_count">
                        {{ skillToDelete.cases_count }} {{ pluralize(skillToDelete.cases_count, 'кейсом', 'кейсами', 'кейсами') }}
                    </span>
                    . Удаление невозможно.
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
                        variant="danger"
                        type="button"
                        @click="deleteSkill"
                        :disabled="deleteForm.processing || (skillToDelete && (skillToDelete.users_count || skillToDelete.cases_count))"
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
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import Button from 'primevue/button'
import {route} from "ziggy-js";

const props = defineProps({
    skills: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
})

// Computed свойства для безопасного доступа
const skillsData = computed(() => props.skills?.data || [])
const skillsTotal = computed(() => props.skills?.total || 0)
const skillsLinks = computed(() => props.skills?.links || [])

// Проверка активных фильтров
const hasActiveFilters = computed(() => {
    return filters.value.search !== '' ||
        filters.value.category !== '' ||
        filters.value.perPage !== '25'
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    perPage: props.filters?.perPage ? String(props.filters.perPage) : '25',
})

// Модальные окна
const modalVisible = ref(false)
const deleteModalVisible = ref(false)
const editingSkill = ref(null)
const skillToDelete = ref(null)

// Форма создания/редактирования
const form = useForm({
    name: '',
    category: 'hard',
    max_level: 100,
})

// Форма удаления
const deleteForm = useForm({})

// Опции категорий
const categoryOptions = [
    { label: 'Hard Skills (Технические)', value: 'hard' },
    { label: 'Soft Skills (Мягкие)', value: 'soft' },
    { label: 'Language (Языки)', value: 'language' },
    { label: 'Other (Прочее)', value: 'other' },
]

const categoryFilterOptions = computed(() => [
    { label: 'Все категории', value: '' },
    ...categoryOptions
])

const perPageOptions = computed(() => [
    { label: 'Отображать по 10', value: '10' },
    { label: 'Отображать по 15', value: '15' },
    { label: 'Отображать по 25', value: '25' },
    { label: 'Отображать по 50', value: '50' },
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
    router.get(route('admin.skills.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        category: '',
        perPage: '25',
    }
    updateFilters()
}

// Функции модального окна
const openCreateModal = () => {
    editingSkill.value = null
    form.reset()
    form.clearErrors()
    modalVisible.value = true
}

const openEditModal = (skill) => {
    editingSkill.value = skill
    form.name = skill.name
    form.category = skill.category
    form.max_level = skill.max_level
    form.clearErrors()
    modalVisible.value = true
}

const closeModal = () => {
    modalVisible.value = false
    editingSkill.value = null
    form.reset()
    form.clearErrors()
}

const submitForm = () => {
    if (editingSkill.value) {
        form.put(route('admin.skills.update', editingSkill.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            },
        })
    } else {
        form.post(route('admin.skills.store'), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
            },
        })
    }
}

// Функции удаления
const confirmDelete = (skill) => {
    skillToDelete.value = skill
    deleteModalVisible.value = true
}

const closeDeleteModal = () => {
    deleteModalVisible.value = false
    skillToDelete.value = null
    deleteForm.reset()
    deleteForm.clearErrors()
}

const deleteSkill = () => {
    if (!skillToDelete.value) return

    deleteForm.delete(route('admin.skills.destroy', skillToDelete.value.id), {
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

const getCategoryLabel = (category) => {
    const categoryMap = {
        hard: 'Hard Skills',
        soft: 'Soft Skills',
        language: 'Language',
        other: 'Other',
    }
    return categoryMap[category] || category
}

const getCategoryBadgeClass = (category) => {
    const classMap = {
        hard: 'bg-blue-100 text-blue-800 border-blue-200',
        soft: 'bg-green-100 text-green-800 border-green-200',
        language: 'bg-purple-100 text-purple-800 border-purple-200',
        other: 'bg-gray-100 text-gray-800 border-gray-200',
    }
    return classMap[category] || 'bg-gray-100 text-gray-800 border-gray-200'
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
