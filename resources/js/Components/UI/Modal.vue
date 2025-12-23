<template>
  <Dialog
    :visible="modelValue"
    :modal="true"
    :closable="closable"
    :draggable="false"
    :dismissableMask="true"
    :style="{ width: modalWidth, maxWidth: isMobile ? '95vw' : '90vw' }"
    :header="title"
    :breakpoints="{ '960px': '95vw', '640px': '95vw' }"
    @update:visible="$emit('update:modelValue', $event)"
    @hide="$emit('close')"
  >
    <slot />
    <template #footer>
      <slot name="footer">
        <Button v-if="showDefaultFooter" variant="secondary" @click="$emit('update:modelValue', false)">
          Закрыть
        </Button>
      </slot>
    </template>
  </Dialog>
</template>

<script setup>
import { computed } from 'vue';
import Dialog from 'primevue/dialog';
import Button from './Button.vue';
import { useResponsive } from '@/Composables/useResponsive';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: null,
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
  },
  closable: {
    type: Boolean,
    default: true,
  },
  showDefaultFooter: {
    type: Boolean,
    default: true,
  },
});

defineEmits(['update:modelValue', 'close']);

const { isMobile } = useResponsive();

const modalWidth = computed(() => {
  if (isMobile.value) {
    return '95vw';
  }
  const sizes = {
    sm: '400px',
    md: '600px',
    lg: '800px',
    xl: '1000px',
  };
  return sizes[props.size] || sizes.md;
});
</script>

<style scoped>
:deep(.p-dialog-footer:empty),
:deep(.p-dialog-footer:has(> :empty)) {
  display: none !important;
}
</style>


