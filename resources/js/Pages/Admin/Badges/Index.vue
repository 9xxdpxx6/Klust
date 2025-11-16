<template>
    <div>
        <Head title="Управление бейджами"/>

        <FlashMessage/>

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Управление бейджами</h1>
                <p class="text-gray-600 mt-2">Список всех бейджей в системе</p>
            </div>
            <button
                @click="openCreateModal"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
            >
                + Создать бейдж
            </button>
        </div>

        <!-- Фильтры -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Поиск -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Название бейджа"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500"
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

        <!-- Таблица бейджей -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="text-sm text-gray-600">
                    Всего бейджей: {{ badgesTotal }}
                </div>
            </div>

            <table v-if="badgesData && badgesData.length > 0" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Иконка
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Название
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Описание
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Требуемые баллы
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
                    v-for="badge in badgesData"
                    :key="badge.id"
                    class="hover:bg-gray-50 transition-colors"
                >
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div v-if="badge.icon_path" class="w-12 h-12 flex items-center justify-center">
                            <img :src="badge.icon_path" :alt="badge.name" class="max-w-full max-h-full object-contain"/>
                        </div>
                        <div v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))" class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded">
                            <i :class="['text-gray-600 text-2xl', badge.icon.startsWith('fa-') ? badge.icon.replace('fa-', 'pi-') : badge.icon]"></i>
                        </div>
                        <div v-else class="w-12 h-12 flex items-center justify-center bg-gray-200 rounded">
                            <i class="pi pi-star text-gray-400 text-xl"></i>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ badge.name }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600 max-w-md line-clamp-2">
                            {{ badge.description || '—' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-semibold text-indigo-600">
                            {{ badge.required_points }} {{ pluralize(badge.required_points, 'балл', 'балла', 'баллов') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ formatDate(badge.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2">
                            <button
                                @click.stop="openEditModal(badge)"
                                class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-md transition-colors focus:outline-none"
                                title="Редактировать"
                            >
                                <i class="pi pi-pencil text-sm"></i>
                            </button>
                            <button
                                @click.stop="confirmDelete(badge)"
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
            <Pagination v-if="badgesLinks && badgesLinks.length > 0" :links="badgesLinks" class="mt-4"/>
        </div>

        <!-- Сообщение если нет бейджей -->
        <div v-if="!badgesData || badgesData.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">Бейджи не найдены</p>
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
            :title="editingBadge ? 'Редактировать бейдж' : 'Создать бейдж'"
            @update:visible="modalVisible = $event"
            @close="closeModal"
            size="md"
        >
            <form @submit.prevent="submitForm">
                <div class="space-y-4">
                    <Input
                        v-model="form.name"
                        label="Название"
                        placeholder="Введите название бейджа"
                        :error="form.errors.name"
                        required
                    />

                    <Textarea
                        v-model="form.description"
                        label="Описание"
                        placeholder="Введите описание бейджа"
                        :error="form.errors.description"
                        rows="3"
                        required
                    />

                    <Input
                        v-model.number="form.required_points"
                        type="number"
                        label="Требуемые баллы"
                        placeholder="100"
                        :error="form.errors.required_points"
                        :hint="`Количество баллов, необходимое для получения бейджа (минимум 1)`"
                        required
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Иконка бейджа
                        </label>
                        <div v-if="iconPreview || editingBadge?.icon" class="mb-3 flex items-center gap-4">
                            <img
                                v-if="iconPreview || editingBadge?.icon_path"
                                :src="iconPreview || editingBadge.icon_path"
                                alt="Preview"
                                class="w-20 h-20 object-contain border border-gray-300 rounded p-2"
                            />
                            <div
                                v-else-if="editingBadge?.icon && (editingBadge.icon.startsWith('pi-') || editingBadge.icon.startsWith('fa-'))"
                                class="w-20 h-20 flex items-center justify-center border border-gray-300 rounded p-2 bg-gray-50"
                            >
                                <i :class="['text-4xl text-gray-600', editingBadge.icon.startsWith('fa-') ? editingBadge.icon.replace('fa-', 'pi-') : editingBadge.icon]"></i>
                            </div>
                            <button
                                v-if="iconPreview"
                                type="button"
                                @click="clearIcon"
                                class="text-sm text-red-600 hover:text-red-800"
                            >
                                Удалить
                            </button>
                        </div>
                        <input
                            ref="iconInput"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                            @change="handleIconChange"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Форматы: JPEG, PNG, JPG, GIF, SVG. Максимальный размер: 2 МБ.
                        </p>
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
                    Вы уверены, что хотите удалить бейдж <strong>{{ badgeToDelete.name }}</strong>?
                </p>
                <p class="text-sm text-gray-600">
                    Это действие необратимо. Бейдж будет удален у всех пользователей, которые его получили.
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
                        @click="deleteBadge"
                        :disabled="deleteForm.processing"
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
import Textarea from '@/Components/UI/Textarea.vue'
import Modal from '@/Components/UI/Modal.vue'
import Button from '@/Components/UI/Button.vue'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'
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
    return filters.value.search !== '' || filters.value.perPage !== 15
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    perPage: props.filters?.perPage || 15,
})

// Модальные окна
const modalVisible = ref(false)
const deleteModalVisible = ref(false)
const editingBadge = ref(null)
const badgeToDelete = ref(null)
const iconPreview = ref(null)
const iconInput = ref(null)

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
    router.get(route('admin.badges.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        perPage: 15,
    }
    updateFilters()
}

// Обработка загрузки иконки
const handleIconChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.icon = file
        const reader = new FileReader()
        reader.onload = (e) => {
            iconPreview.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const clearIcon = () => {
    form.icon = null
    iconPreview.value = null
    if (iconInput.value) {
        iconInput.value.value = ''
    }
}

// Функции модального окна
const openCreateModal = () => {
    editingBadge.value = null
    form.reset()
    form.clearErrors()
    iconPreview.value = null
    modalVisible.value = true
}

const openEditModal = (badge) => {
    editingBadge.value = badge
    form.name = badge.name
    form.description = badge.description || ''
    form.required_points = badge.required_points
    form.icon = null
    form.clearErrors()
    iconPreview.value = null
    modalVisible.value = true
}

const closeModal = () => {
    modalVisible.value = false
    editingBadge.value = null
    form.reset()
    form.clearErrors()
    iconPreview.value = null
}

const submitForm = () => {
    if (editingBadge.value) {
        form.post(route('admin.badges.update', editingBadge.value.id), {
            _method: 'put',
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
