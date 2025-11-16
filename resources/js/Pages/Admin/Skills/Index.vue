<template>
    <div>
        <Head title="Управление навыками"/>

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Управление навыками</h1>
                <p class="text-gray-600 mt-2">Список всех навыков в системе</p>
            </div>
            <button
                @click="openCreateModal"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
            >
                + Создать навык
            </button>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Поиск -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Название навыка"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
                        @input="handleSearch"
                    />
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
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                >
                    Сбросить фильтры
                </button>
            </div>
        </div>

        <!-- Таблица навыков -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="text-sm text-gray-600">
                    Всего навыков: {{ skillsTotal }}
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Навык
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Категория
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Макс. уровень
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Используется
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Создан
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Действия
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <tr
                    v-for="skill in skillsData"
                    :key="skill.id"
                    class="hover:bg-gray-50 transition-colors"
                >
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ skill.name }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            :class="[
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                getCategoryBadgeClass(skill.category)
                            ]"
                        >
                            {{ getCategoryLabel(skill.category) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">
                            {{ skill.max_level }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            <div v-if="skill.users_count || skill.cases_count">
                                <span v-if="skill.users_count" class="text-blue-600">
                                    {{ skill.users_count }} {{ pluralize(skill.users_count, 'пользователь', 'пользователя', 'пользователей') }}
                                </span>
                                <span v-if="skill.users_count && skill.cases_count" class="mx-1">•</span>
                                <span v-if="skill.cases_count" class="text-indigo-600">
                                    {{ skill.cases_count }} {{ pluralize(skill.cases_count, 'кейс', 'кейса', 'кейсов') }}
                                </span>
                            </div>
                            <span v-else class="text-gray-400">Не используется</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatDate(skill.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <button
                                @click.stop="openEditModal(skill)"
                                class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-colors focus:outline-none"
                                title="Редактировать"
                            >
                                <i class="pi pi-pencil text-sm"></i>
                            </button>
                            <button
                                @click.stop="confirmDelete(skill)"
                                class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-md transition-colors focus:outline-none"
                                title="Удалить"
                            >
                                <i class="pi pi-trash text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>

            <!-- Пагинация -->
            <Pagination v-if="skillsLinks && skillsLinks.length > 0" :links="skillsLinks" class="mt-4"/>
        </div>

        <!-- Сообщение если нет навыков -->
        <div v-if="!skillsData || skillsData.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">Навыки не найдены</p>
            <button
                v-if="hasActiveFilters"
                @click="resetFilters"
                class="mt-4 px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-md transition-colors"
            >
                Сбросить фильтры
            </button>
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
import Button from '@/Components/UI/Button.vue'
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
        filters.value.perPage !== 15
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    category: props.filters?.category || '',
    perPage: props.filters?.perPage || 15,
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
    { label: '10', value: '10' },
    { label: '15', value: '15' },
    { label: '25', value: '25' },
    { label: '50', value: '50' },
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
        perPage: 15,
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
        hard: 'bg-blue-100 text-blue-800',
        soft: 'bg-green-100 text-green-800',
        language: 'bg-purple-100 text-purple-800',
        other: 'bg-gray-100 text-gray-800',
    }
    return classMap[category] || 'bg-gray-100 text-gray-800'
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
