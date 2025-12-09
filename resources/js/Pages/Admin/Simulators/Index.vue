<template>
    <div class="space-y-6">
        <Head title="Управление симуляторами"/>

        <!-- Заголовок с градиентом -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Управление симуляторами</h1>
                        <p class="text-indigo-100">Список всех симуляторов в системе</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium"
                    >
                        <i class="pi pi-plus"></i>
                        Создать симулятор
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
                    <div class="lg:col-span-1">
                        <SearchInput
                            v-model="filters.search"
                            label="Поиск"
                            placeholder="Название, slug или описание"
                            @input="handleSearch"
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
                        placeholder="Все статусы"
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
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-5 shadow-md border border-purple-200/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600 mb-1">Всего симуляторов</p>
                    <p class="text-2xl font-bold text-purple-900">{{ simulatorsTotal }}</p>
                </div>
                <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl">
                    <i class="pi pi-desktop text-white text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Таблица симуляторов -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="pi pi-list text-indigo-600"></i>
                        Список симуляторов
                    </h2>
                    <span class="text-sm text-gray-600 bg-white px-3 py-1 rounded-lg border border-gray-200">
                        Всего: {{ simulatorsTotal }}
                    </span>
                </div>
            </div>

            <!-- Десктопная таблица -->
            <div v-if="simulatorsData && simulatorsData.length > 0" class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Симулятор
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Партнер
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Статус
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
                        v-for="simulator in simulatorsData"
                        :key="simulator.id"
                        class="hover:bg-indigo-50/50 transition-all group"
                    >
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors flex-shrink-0">
                                    <img
                                        v-if="simulator.preview_image_url"
                                        :src="simulator.preview_image_url"
                                        :alt="simulator.title"
                                        class="w-10 h-10 object-cover rounded"
                                    />
                                    <div v-else class="w-10 h-10 flex items-center justify-center">
                                        <i class="pi pi-desktop text-purple-600"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-semibold text-gray-900 break-words">
                                        {{ simulator.title }}
                                    </div>
                                    <div v-if="simulator.description" class="text-xs text-gray-500 break-words mt-1 line-clamp-2">
                                        {{ simulator.description }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 bg-blue-100 rounded-lg">
                                    <i class="pi pi-building text-blue-600 text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ simulator.partner?.name || 'Не указан' }}
                                    </div>
                                    <div v-if="simulator.partner?.contact_person" class="text-xs text-gray-500">
                                        {{ simulator.partner.contact_person }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border',
                                    simulator.is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-800 border-gray-200'
                                ]"
                            >
                                {{ simulator.is_active ? 'Активен' : 'Неактивен' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ formatDate(simulator.created_at) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-2">
                                <button
                                    @click.stop="openEditModal(simulator)"
                                    class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors focus:outline-none border border-transparent hover:border-indigo-200"
                                    title="Редактировать"
                                >
                                    <i class="pi pi-pencil text-sm"></i>
                                </button>
                                <button
                                    @click.stop="confirmDelete(simulator)"
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

            <!-- Мобильные карточки -->
            <div v-if="simulatorsData && simulatorsData.length > 0" class="md:hidden space-y-4 p-6">
                <div
                    v-for="simulator in simulatorsData"
                    :key="simulator.id"
                    class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start gap-3 mb-3">
                        <div class="p-2 bg-purple-100 rounded-lg flex-shrink-0">
                            <img
                                v-if="simulator.preview_image_url"
                                :src="simulator.preview_image_url"
                                :alt="simulator.title"
                                class="w-12 h-12 object-cover rounded"
                            />
                            <div v-else class="w-12 h-12 flex items-center justify-center">
                                <i class="pi pi-desktop text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-900 mb-1">
                                {{ simulator.title }}
                            </h3>
                            <div v-if="simulator.description" class="text-xs text-gray-500 line-clamp-2 mb-2">
                                {{ simulator.description }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Партнер:</span>
                            <span class="text-gray-900 font-medium">
                                {{ simulator.partner?.name || 'Не указан' }}
                            </span>
                        </div>
                        <div v-if="simulator.partner?.contact_person" class="flex items-center justify-between">
                            <span class="text-gray-500">Контакт:</span>
                            <span class="text-gray-600">{{ simulator.partner.contact_person }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Статус:</span>
                            <span
                                :class="[
                                    'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border',
                                    simulator.is_active ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-800 border-gray-200'
                                ]"
                            >
                                {{ simulator.is_active ? 'Активен' : 'Неактивен' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Создан:</span>
                            <span class="text-gray-600">{{ formatDate(simulator.created_at) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2 pt-3 border-t border-gray-200">
                        <button
                            @click="openEditModal(simulator)"
                            class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-200"
                            title="Редактировать"
                        >
                            <i class="pi pi-pencil"></i>
                        </button>
                        <button
                            @click="confirmDelete(simulator)"
                            class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-200"
                            title="Удалить"
                        >
                            <i class="pi pi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div v-if="simulatorsLinks && simulatorsLinks.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <Pagination :links="simulatorsLinks" />
            </div>
        </div>

        <!-- Сообщение если нет симуляторов -->
        <div v-if="!simulatorsData || simulatorsData.length === 0" class="bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                    <i class="pi pi-desktop text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Симуляторы не найдены</h3>
                <p class="text-sm text-gray-500 mb-6">
                    <span v-if="hasActiveFilters">Попробуйте изменить параметры фильтрации</span>
                    <span v-else>Создайте первый симулятор, чтобы начать работу</span>
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
                        Создать симулятор
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания/редактирования -->
        <Modal
            :visible="modalVisible"
            :title="editingSimulator ? 'Редактировать симулятор' : 'Создать симулятор'"
            @update:visible="modalVisible = $event"
            @close="closeModal"
            size="lg"
        >
            <form @submit.prevent="submitForm" novalidate>
                <div class="space-y-4">
                    <Select
                        v-model="form.partner_id"
                        label="Партнер"
                        :options="partnerOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Выберите партнера"
                        :error="form.errors.partner_id"
                        required
                    />

                    <Input
                        v-model="form.title"
                        label="Название"
                        placeholder="Введите название симулятора"
                        :error="form.errors.title"
                        required
                    />

                    <Input
                        v-model="form.slug"
                        label="URL-идентификатор (slug)"
                        placeholder="url-friendly-identifier"
                        :error="form.errors.slug"
                        :hint="`Только строчные буквы, цифры, дефис и подчеркивание`"
                        required
                    />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Описание
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Введите описание симулятора"
                            :class="[
                                'w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500',
                                form.errors.description ? 'border-red-500' : ''
                            ]"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Загрузка превью изображения -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Превью изображение
                        </label>
                        <div class="flex items-center gap-4">
                            <div v-if="previewImageUrl || (editingSimulator && editingSimulator.preview_image_url)" class="w-32 h-32 border rounded overflow-hidden">
                                <img
                                    :src="previewImageUrl || editingSimulator?.preview_image_url"
                                    alt="Preview"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="flex-1">
                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg,image/gif"
                                    @change="handleImageChange"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    JPG, PNG, GIF. Макс. 5 МБ
                                </p>
                                <p v-if="form.errors.preview_image" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.preview_image }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            id="is_active"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                        />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Активен
                        </label>
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
                        {{ editingSimulator ? 'Сохранить' : 'Создать' }}
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
            <div v-if="simulatorToDelete">
                <p class="text-gray-700 mb-4">
                    Вы уверены, что хотите удалить симулятор <strong>{{ simulatorToDelete.title }}</strong>?
                </p>
                <p class="text-sm text-yellow-600 mb-4">
                    Внимание! Если у симулятора есть активные сессии, удаление будет невозможно.
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
                        @click="deleteSimulator"
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
import SearchInput from '@/Components/UI/SearchInput.vue'
import Input from '@/Components/UI/Input.vue'
import Modal from '@/Components/UI/Modal.vue'
import Button from 'primevue/button'
import {route} from "ziggy-js";

const props = defineProps({
    simulators: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    partners: {
        type: Array,
        default: () => []
    }
})

// Computed свойства для безопасного доступа
const simulatorsData = computed(() => props.simulators?.data || [])
const simulatorsTotal = computed(() => props.simulators?.total || 0)
const simulatorsLinks = computed(() => props.simulators?.links || [])

// Проверка активных фильтров
const hasActiveFilters = computed(() => {
    return filters.value.search !== '' ||
        filters.value.status !== '' ||
        filters.value.perPage !== '30'
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    perPage: props.filters?.perPage ? String(props.filters.perPage) : '30',
})

// Модальные окна
const modalVisible = ref(false)
const deleteModalVisible = ref(false)
const editingSimulator = ref(null)
const simulatorToDelete = ref(null)
const previewImageUrl = ref(null)

// Форма создания/редактирования
const form = useForm({
    partner_id: null,
    title: '',
    slug: '',
    description: '',
    preview_image: null,
    is_active: true,
})

// Форма удаления
const deleteForm = useForm({})

// Опции партнеров
const partnerOptions = computed(() => {
    return props.partners.map(partner => ({
        label: `${partner.name}${partner.contact_person ? ` (${partner.contact_person})` : ''}`,
        value: partner.id
    }))
})

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Активные', value: 'active' },
    { label: 'Неактивные', value: 'inactive' },
])

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
    router.get(route('admin.simulators.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        perPage: '30',
    }
    updateFilters()
}

// Обработчик изменения изображения
const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        form.preview_image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            previewImageUrl.value = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

// Функции модального окна
const openCreateModal = () => {
    editingSimulator.value = null
    previewImageUrl.value = null
    form.reset()
    form.clearErrors()
    form.is_active = true
    modalVisible.value = true
}

const openEditModal = (simulator) => {
    editingSimulator.value = simulator
    previewImageUrl.value = null
    form.partner_id = simulator.partner_id
    form.title = simulator.title
    form.slug = simulator.slug
    form.description = simulator.description
    form.is_active = simulator.is_active
    form.preview_image = null
    form.clearErrors()
    modalVisible.value = true
}

const closeModal = () => {
    modalVisible.value = false
    editingSimulator.value = null
    previewImageUrl.value = null
    form.reset()
    form.clearErrors()
}

const submitForm = () => {
    if (editingSimulator.value) {
        form.put(route('admin.simulators.update', editingSimulator.value.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                closeModal()
            },
        })
    } else {
        form.post(route('admin.simulators.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                closeModal()
            },
        })
    }
}

// Функции удаления
const confirmDelete = (simulator) => {
    simulatorToDelete.value = simulator
    deleteModalVisible.value = true
}

const closeDeleteModal = () => {
    deleteModalVisible.value = false
    simulatorToDelete.value = null
    deleteForm.reset()
    deleteForm.clearErrors()
}

const deleteSimulator = () => {
    if (!simulatorToDelete.value) return

    deleteForm.delete(route('admin.simulators.destroy', simulatorToDelete.value.id), {
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
</script>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
