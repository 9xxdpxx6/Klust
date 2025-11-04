<template>
  <Dialog
    :visible="visible"
    :modal="true"
    :closable="closable"
    :draggable="false"
    :style="{ width: modalWidth }"
    :header="title"
    @update:visible="$emit('update:visible', $event)"
    @hide="$emit('close')"
  >
    <slot />
    <template #footer>
      <slot name="footer">
        <Button variant="secondary" @click="$emit('update:visible', false)">
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

const props = defineProps({
  visible: {
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
});

defineEmits(['update:visible', 'close']);

const modalWidth = computed(() => {
  const sizes = {
    sm: '400px',
    md: '600px',
    lg: '800px',
    xl: '1000px',
  };
  return sizes[props.size] || sizes.md;
});
</script>


