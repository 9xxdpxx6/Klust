<template>
    <div 
        v-if="visible" 
        class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 !mt-0"
        @click.self="$emit('update:visible', false)"
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
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-2 text-center">
                        {{ message }}
                    </p>
                    <p v-if="itemName" class="text-sm font-semibold text-gray-900 mb-4 text-center">
                        "{{ itemName }}"?
                    </p>

                    <!-- Слот для дополнительного контента (blocking data, предупреждения) -->
                    <slot />

                    <!-- Сообщение по умолчанию, если нет слота и нет itemName -->
                    <p v-if="!itemName && !$slots.default" :class="[
                        'text-xs rounded-lg p-3',
                        defaultMessageClass
                    ]">
                        {{ defaultMessage }}
                    </p>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-center gap-3 mt-6">
                    <button
                        @click="$emit('update:visible', false)"
                        :disabled="loading"
                        class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                    >
                        {{ cancelText }}
                    </button>
                    <button
                        @click="$emit('confirm')"
                        :disabled="loading || disabled"
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
import { computed } from 'vue'

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'delete',
        validator: (value) => ['delete', 'archive', 'warning'].includes(value),
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
    itemName: {
        type: String,
        default: null,
    },
    confirmText: {
        type: String,
        default: 'Удалить',
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
        default: 'Удаление...',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    defaultMessage: {
        type: String,
        default: 'Это действие нельзя отменить. Все данные будут удалены безвозвратно.',
    },
})

defineEmits(['update:visible', 'confirm'])

const iconClass = computed(() => {
    const icons = {
        delete: 'pi pi-exclamation-triangle text-3xl',
        archive: 'pi pi-archive text-3xl',
        warning: 'pi pi-exclamation-triangle text-3xl',
    }
    return icons[props.type] || icons.delete
})

const iconBgClass = computed(() => {
    const classes = {
        delete: 'bg-red-100',
        archive: 'bg-amber-100',
        warning: 'bg-yellow-100',
    }
    return classes[props.type] || classes.delete
})

const iconTextClass = computed(() => {
    const classes = {
        delete: 'text-red-600',
        archive: 'text-amber-600',
        warning: 'text-yellow-600',
    }
    return classes[props.type] || classes.delete
})

const confirmButtonClass = computed(() => {
    const classes = {
        delete: 'bg-red-600 text-white hover:bg-red-700',
        archive: 'bg-gray-600 text-white hover:bg-gray-700',
        warning: 'bg-yellow-600 text-white hover:bg-yellow-700',
    }
    return classes[props.type] || classes.delete
})

const defaultMessageClass = computed(() => {
    const classes = {
        delete: 'text-red-600 bg-red-50',
        archive: 'text-amber-600 bg-amber-50',
        warning: 'text-yellow-600 bg-yellow-50',
    }
    return classes[props.type] || classes.delete
})
</script>

