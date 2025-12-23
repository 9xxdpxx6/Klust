<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import UserAvatar from '@/Components/Shared/UserAvatar.vue'
import AvatarCropper from '@/Components/UI/AvatarCropper.vue'
import Button from '@/Components/UI/Button.vue'
import Modal from '@/Components/UI/Modal.vue'
import Card from '@/Components/UI/Card.vue'
import { useResponsive } from '@/Composables/useResponsive'

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    avatarRoutePrefix: {
        type: String,
        required: true,
        validator: (value) => ['admin', 'partner', 'student'].includes(value)
    },
    modelValue: {
        type: File,
        default: null
    },
    editable: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['update:modelValue'])

const avatarPreview = ref(null)
const avatarInput = ref(null)
const showCropper = ref(false)
const imageForCrop = ref(null)
const showDeleteModal = ref(false)
const formatError = ref(null)

// Допустимые форматы файлов
const ALLOWED_FORMATS = ['image/jpeg', 'image/jpg', 'image/png']
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png']

const isValidFormat = (file) => {
    const extension = file.name.split('.').pop()?.toLowerCase()
    return ALLOWED_FORMATS.includes(file.type) || ALLOWED_EXTENSIONS.includes(extension)
}

const handleAvatarChange = (event) => {
    const file = event.target.files[0]
    formatError.value = null
    
    if (!file) {
        return
    }

    // Проверка формата на фронте
    if (!isValidFormat(file)) {
        formatError.value = 'Недопустимый формат файла. Допустимые форматы: JPG, JPEG, PNG.'
        if (avatarInput.value) {
            avatarInput.value.value = ''
        }
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        imageForCrop.value = e.target.result
        showCropper.value = true
        formatError.value = null
    }
    reader.readAsDataURL(file)
}

const handleCrop = (file) => {
    emit('update:modelValue', file)
    const reader = new FileReader()
    reader.onload = (e) => {
        avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    showCropper.value = false
    imageForCrop.value = null
}

const handleCropCancel = () => {
    showCropper.value = false
    imageForCrop.value = null
    if (avatarInput.value) {
        avatarInput.value.value = ''
    }
}

const openDeleteModal = () => {
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
}

const deleteAvatar = () => {
    const routeName = `${props.avatarRoutePrefix}.profile.avatar.delete`
    router.delete(route(routeName), {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null
            emit('update:modelValue', null)
            closeDeleteModal()
        }
    })
}

const openFilePicker = () => {
    if (avatarInput.value) {
        avatarInput.value.click()
    }
}

// Вычисляем текущий аватар для отображения
const currentAvatar = computed(() => {
    return avatarPreview.value || props.user?.avatar
})

const { isMobile } = useResponsive()
</script>

<template>
    <Card>
        <h3 :class="[
            'font-bold mb-4 text-text-primary',
            isMobile ? 'text-base' : 'text-lg'
        ]">Фотография профиля</h3>
        <div :class="[
            'flex items-center',
            isMobile ? 'flex-col gap-4' : 'gap-6'
        ]">
            <UserAvatar
                :user="{ ...user, avatar: currentAvatar }"
                :size="isMobile ? 'lg' : 'xl'"
                class="flex-shrink-0"
            />
            <div v-if="editable" :class="[
                'flex flex-col w-full',
                isMobile ? 'gap-3' : 'gap-2'
            ]">
                <input
                    ref="avatarInput"
                    type="file"
                    accept="image/jpeg,image/jpg,image/png,.jpg,.jpeg,.png"
                    @change="handleAvatarChange"
                    class="hidden"
                />
                <div :class="[
                    'flex gap-2',
                    isMobile ? 'flex-col' : ''
                ]">
                    <Button
                        type="button"
                        variant="secondary"
                        :class="isMobile ? 'w-full' : ''"
                        @click="openFilePicker"
                    >
                        <i class="pi pi-upload mr-2"></i>
                        Загрузить фото
                    </Button>
                    <Button
                        v-if="user.avatar || avatarPreview"
                        type="button"
                        variant="outline"
                        :class="[
                            'text-red-600 hover:text-red-700',
                            isMobile ? 'w-full' : ''
                        ]"
                        @click="openDeleteModal"
                    >
                        <i class="pi pi-trash mr-2"></i>
                        Удалить
                    </Button>
                </div>
                <p :class="[
                    'text-text-muted',
                    isMobile ? 'text-xs' : 'text-sm'
                ]">JPG, JPEG, PNG. Макс. 2MB</p>
                <p v-if="formatError" :class="[
                    'text-red-600 mt-1',
                    isMobile ? 'text-xs' : 'text-sm'
                ]">
                    {{ formatError }}
                </p>
            </div>
        </div>
    </Card>

    <!-- Avatar Cropper Modal -->
    <AvatarCropper
        v-if="showCropper && imageForCrop"
        :image="imageForCrop"
        @crop="handleCrop"
        @cancel="handleCropCancel"
    />

    <!-- Modal подтверждения удаления -->
    <Modal
        :visible="showDeleteModal"
        title="Подтверждение удаления"
        @update:visible="showDeleteModal = $event"
        @close="closeDeleteModal"
        size="sm"
    >
        <p class="text-gray-700 mb-4">
            Вы уверены, что хотите удалить фотографию?
        </p>
        <p class="text-sm text-gray-600">
            Это действие необратимо. Фотография будет удалена.
        </p>

        <template #footer>
            <div :class="[
                'flex gap-2',
                isMobile ? 'flex-col-reverse' : 'justify-end'
            ]">
                <Button
                    variant="secondary"
                    type="button"
                    :class="isMobile ? 'w-full' : ''"
                    @click="closeDeleteModal"
                >
                    Отмена
                </Button>
                <Button
                    severity="danger"
                    type="button"
                    :class="isMobile ? 'w-full' : ''"
                    @click="deleteAvatar"
                >
                    Удалить
                </Button>
            </div>
        </template>
    </Modal>
</template>

