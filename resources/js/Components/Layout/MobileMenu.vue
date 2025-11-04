<template>
  <Drawer
    v-model:visible="isVisible"
    position="left"
    :modal="true"
    :dismissable="true"
    class="mobile-menu-drawer"
  >
    <template #header>
      <div class="flex items-center justify-between p-4 border-b border-border-light">
        <h2 class="text-lg font-bold text-primary">{{ title }}</h2>
        <button
          @click="close"
          class="p-2 rounded-lg hover:bg-surface-hover transition-colors"
          aria-label="Закрыть меню"
        >
          <i class="pi pi-times text-xl"></i>
        </button>
      </div>
    </template>
    
    <BaseSidebar
      :items="items"
      :title="title"
      :show-toggle="false"
      class="mobile-sidebar-content"
    />
  </Drawer>
</template>

<script setup>
import { computed } from 'vue';
import Drawer from 'primevue/drawer';
import BaseSidebar from '@/Components/Layout/BaseSidebar.vue';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Меню',
  },
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const isVisible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const close = () => {
  isVisible.value = false;
};
</script>

<style scoped>
:deep(.mobile-menu-drawer) {
  width: 16rem;
  max-width: 90vw;
}

:deep(.mobile-sidebar-content) {
  position: static;
  transform: none;
  width: 100%;
  height: auto;
  border: none;
}

:deep(.p-drawer-content) {
  padding: 0;
}
</style>

