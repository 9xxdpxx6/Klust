<template>
    <div 
        v-if="visible" 
        class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 !mt-0"
        @click.self="handleClose"
    >
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full border border-gray-200">
            <div class="p-6">
                <!-- Иконка в круге -->
                <div :class="[
                    'mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4',
                    iconBgClass
                ]">
                    <i :class="[iconClass, iconTextClass]"></i>
                </div>

                <!-- Заголовок -->
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">{{ title }}</h3>

                <!-- Сообщение -->
                <p class="text-sm text-gray-600 mb-4 text-center">
                    {{ message }}
                </p>

                <!-- Поле для причины отказа (только для типа reject) -->
                <div v-if="type === 'reject'" class="mb-4">
                    <label for="rejection-reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Причина отклонения <span class="text-red-500">*</span>
                    </label>
                    <Textarea
                        id="rejection-reason"
                        v-model="rejectionReason"
                        :rows="4"
                        placeholder="Укажите причину отклонения заявки (минимум 10 символов)..."
                        :error="error"
                        :disabled="loading"
                        class="w-full"
                    />
                    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
                    <p v-else class="mt-1 text-xs text-gray-500">
                        Минимум 10 символов. Осталось символов: {{ remainingChars }}
                    </p>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-center gap-3 mt-6">
                    <button
                        @click="handleClose"
                        :disabled="loading"
                        class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                    >
                        {{ cancelText }}
                    </button>
                    <button
                        @click="handleConfirm"
                        :disabled="loading || !canConfirm"
                        :class="[
                            'px-6 py-2.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium',
                            confirmButtonClass
                        ]"
                    >
                        <span v-if="loading">{{ loadingText }}</span>
                        <span v-else>{{ confirmText }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Textarea from './Textarea.vue'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'approve',
        validator: (value) => ['approve', 'reject'].includes(value),
    },
    title: {
        type: String,
        default: null,
    },
    message: {
        type: String,
        default: null,
    },
    confirmText: {
        type: String,
        default: null,
    },
    cancelText: {
        type: String,
        default: 'Отмена',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    loadingText: {
        type: String,
        default: null,
    },
})

const emit = defineEmits(['update:visible', 'confirm'])

const rejectionReason = ref('')
const error = ref('')

const remainingChars = computed(() => {
    const length = rejectionReason.value.length
    return Math.max(0, 1000 - length)
})

const canConfirm = computed(() => {
    if (props.type === 'reject') {
        const trimmed = rejectionReason.value.trim()
        return trimmed.length >= 10 && trimmed.length <= 1000
    }
    return true
})

const validate = () => {
    if (props.type !== 'reject') {
        return true
    }
    
    const trimmed = rejectionReason.value.trim()
    
    if (!trimmed) {
        error.value = 'Укажите причину отклонения заявки.'
        return false
    }
    
    if (trimmed.length < 10) {
        error.value = 'Причина отклонения должна содержать минимум 10 символов.'
        return false
    }
    
    if (trimmed.length > 1000) {
        error.value = 'Причина отклонения не должна превышать 1000 символов.'
        return false
    }
    
    error.value = ''
    return true
}

const handleConfirm = () => {
    if (props.type === 'reject' && !validate()) {
        return
    }
    
    if (props.type === 'reject') {
        emit('confirm', {
            rejection_reason: rejectionReason.value.trim()
        })
    } else {
        emit('confirm')
    }
}

const handleClose = () => {
    emit('update:visible', false)
    // Сброс формы при закрытии
    setTimeout(() => {
        rejectionReason.value = ''
        error.value = ''
    }, 300)
}

// Валидация при вводе
watch(rejectionReason, () => {
    if (error.value) {
        validate()
    }
})

// Сброс при открытии
watch(() => props.visible, (newVal) => {
    if (newVal) {
        rejectionReason.value = ''
        error.value = ''
    }
})

// Вычисляемые значения на основе типа
const iconClass = computed(() => {
    return props.type === 'approve' 
        ? 'pi pi-check-circle text-3xl'
        : 'pi pi-exclamation-triangle text-3xl'
})

const iconBgClass = computed(() => {
    return props.type === 'approve'
        ? 'bg-green-100'
        : 'bg-red-100'
})

const iconTextClass = computed(() => {
    return props.type === 'approve'
        ? 'text-green-600'
        : 'text-red-600'
})

const confirmButtonClass = computed(() => {
    return props.type === 'approve'
        ? 'bg-green-600 text-white hover:bg-green-700'
        : 'bg-red-600 text-white hover:bg-red-700'
})

const defaultTitle = computed(() => {
    return props.type === 'approve' ? 'Одобрить заявку' : 'Отклонить заявку'
})

const defaultMessage = computed(() => {
    return props.type === 'approve'
        ? 'Вы уверены, что хотите одобрить эту заявку?'
        : 'Вы уверены, что хотите отклонить эту заявку?'
})

const defaultConfirmText = computed(() => {
    return props.type === 'approve' ? 'Одобрить' : 'Отклонить'
})

const defaultLoadingText = computed(() => {
    return props.type === 'approve' ? 'Одобрение...' : 'Отклонение...'
})

// Используем пропсы или значения по умолчанию
const title = computed(() => props.title || defaultTitle.value)
const message = computed(() => props.message || defaultMessage.value)
const confirmText = computed(() => props.confirmText || defaultConfirmText.value)
const loadingText = computed(() => props.loadingText || defaultLoadingText.value)
</script>

