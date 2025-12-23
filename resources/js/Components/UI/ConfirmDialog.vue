<template>
  <Dialog
    :visible="visible"
    :modal="true"
    :closable="true"
    :draggable="false"
    :style="{ width: isMobile ? '95vw' : '500px', maxWidth: isMobile ? '95vw' : '500px' }"
    :header="title"
    :breakpoints="{ '960px': '95vw', '640px': '95vw' }"
    @update:visible="$emit('update:visible', $event)"
    @hide="$emit('update:visible', false)"
  >
    <div :class="isMobile ? 'py-3' : 'py-4'">
      <div :class="[
        'flex items-start',
        isMobile ? 'gap-2' : 'gap-4'
      ]">
        <div class="flex-shrink-0">
          <i :class="[
            iconClass,
            isMobile ? 'text-2xl' : 'text-3xl'
          ]"></i>
        </div>
        <div class="flex-1">
          <p :class="[
            'text-gray-700',
            isMobile ? 'text-sm' : ''
          ]">{{ message }}</p>
        </div>
      </div>
    </div>
    <template #footer>
      <div :class="[
        'flex justify-end',
        isMobile ? 'flex-col gap-2' : 'gap-3'
      ]">
        <Button 
          variant="secondary" 
          @click="$emit('update:visible', false)"
          :disabled="loading"
        >
          {{ cancelText }}
        </Button>
        <Button 
          :variant="confirmVariant" 
          @click="$emit('confirm')"
          :disabled="loading"
        >
          <span v-if="loading">{{ loadingText }}</span>
          <span v-else>{{ confirmText }}</span>
        </Button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { computed } from 'vue'
import Dialog from 'primevue/dialog'
import Button from './Button.vue'
import { useResponsive } from '@/Composables/useResponsive'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Подтвердите действие',
  },
  message: {
    type: String,
    required: true,
  },
  confirmText: {
    type: String,
    default: 'OK',
  },
  cancelText: {
    type: String,
    default: 'Отмена',
  },
  confirmVariant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'danger', 'success', 'outline'].includes(value),
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['warning', 'danger', 'info', 'success'].includes(value),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  loadingText: {
    type: String,
    default: 'Выполнение...',
  },
})

defineEmits(['update:visible', 'confirm'])

const { isMobile } = useResponsive()

const iconClass = computed(() => {
  const icons = {
    warning: 'pi pi-exclamation-triangle text-yellow-500',
    danger: 'pi pi-exclamation-circle text-red-500',
    info: 'pi pi-info-circle text-blue-500',
    success: 'pi pi-check-circle text-green-500',
  }
  return icons[props.type] || icons.warning
})
</script>

