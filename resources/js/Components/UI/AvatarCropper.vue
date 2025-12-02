<script setup>
import { ref, watch } from 'vue'
import { Cropper } from 'vue-advanced-cropper'
import { CircleStencil } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    image: {
        type: String,
        required: true
    }
})

const emit = defineEmits(['crop', 'cancel'])

const cropperRef = ref(null)
const imageSrc = ref(props.image)

watch(() => props.image, (newImage) => {
    imageSrc.value = newImage
})

// Функция для максимального размера (квадрат, ограниченный меньшей стороной изображения)
const defaultSize = ({ imageSize, visibleArea }) => {
    const availableWidth = (visibleArea || imageSize).width
    const availableHeight = (visibleArea || imageSize).height
    const size = Math.min(availableWidth, availableHeight)
    return {
        width: size,
        height: size,
    }
}

// Функция для центрирования
const defaultPosition = ({ coordinates, imageSize, visibleArea }) => {
    const area = visibleArea || imageSize
    // Используем размер из coordinates, если он есть, иначе вычисляем максимальный размер
    const size = coordinates?.width || Math.min(area.width, area.height)
    return {
        left: (area.width - size) / 2,
        top: (area.height - size) / 2,
    }
}

const getResult = () => {
    const { canvas } = cropperRef.value.getResult()
    if (canvas) {
        // Конвертируем canvas в blob
        canvas.toBlob((blob) => {
            // Создаем File из blob
            const file = new File([blob], 'avatar.png', { type: 'image/png' })
            emit('crop', file)
        }, 'image/png', 1.0)
    }
}

const cancel = () => {
    emit('cancel')
}
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-text-primary">Обрезка фотографии</h3>
                    <button
                        @click="cancel"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <i class="pi pi-times text-xl"></i>
                    </button>
                </div>
                
                <div class="mb-4" style="height: 400px; width: 100%;">
                    <Cropper
                        ref="cropperRef"
                        class="cropper"
                        :src="imageSrc"
                        :stencil-props="{
                            aspectRatio: 1,
                            resizable: true,
                            movable: true,
                            scalable: true,
                        }"
                        :default-size="defaultSize"
                        :default-position="defaultPosition"
                        :stencil-component="CircleStencil"
                    />
                </div>
                
                <div class="flex justify-end gap-3">
                    <Button
                        variant="secondary"
                        @click="cancel"
                    >
                        Отмена
                    </Button>
                    <Button
                        variant="primary"
                        @click="getResult"
                    >
                        Применить
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cropper {
    background: #e5e7eb;
    height: 100%;
}
</style>

