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
    
    <nav class="mobile-menu-nav">
      <div v-for="(item, index) in items" :key="index" class="mobile-menu-item">
        <Link
          v-if="!item.children && routeExists(item.route)"
          :href="getRouteUrl(item.route)"
          :class="[
            'mobile-menu-link',
            { 'active': isActive(item.route) }
          ]"
          @click="close"
        >
          <i :class="['mobile-menu-icon', item.icon]"></i>
          <span class="mobile-menu-label">{{ item.label }}</span>
        </Link>
        
        <div v-else class="mobile-menu-group">
          <div class="mobile-menu-group-header">
            <i :class="['mobile-menu-icon', item.icon]"></i>
            <span class="mobile-menu-label">{{ item.label }}</span>
          </div>
          <div class="mobile-menu-subitems">
            <Link
              v-for="(child, childIndex) in item.children.filter(c => routeExists(c.route))"
              :key="childIndex"
              :href="getRouteUrl(child.route)"
              :class="[
                'mobile-menu-link mobile-menu-link--child',
                { 'active': isActive(child.route) }
              ]"
              @click="close"
            >
              <i :class="['mobile-menu-icon', child.icon || 'pi pi-circle-fill']"></i>
              <span class="mobile-menu-label">{{ child.label }}</span>
            </Link>
          </div>
        </div>
      </div>
    </nav>
  </Drawer>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Drawer from 'primevue/drawer';
import { useNavigation } from '@/Composables/useNavigation';
import { routeExists, getRouteUrl } from '@/Utils/routes';

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

const { isActive } = useNavigation();
</script>

<style scoped>
:deep(.mobile-menu-drawer) {
  width: 16rem;
  max-width: 90vw;
}

:deep(.p-drawer-content) {
  padding: 0;
}

.mobile-menu-nav {
  padding: 1rem 0;
}

.mobile-menu-item {
  margin-bottom: 0.25rem;
}

.mobile-menu-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  color: var(--color-text-secondary);
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.mobile-menu-link:hover {
  background: var(--color-surface);
  color: var(--color-primary);
}

.mobile-menu-link.active {
  background: var(--color-surface-hover);
  color: var(--color-primary);
  border-left-color: var(--color-primary);
  font-weight: 600;
}

.mobile-menu-icon {
  font-size: 1.25rem;
  width: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mobile-menu-label {
  flex: 1;
}

.mobile-menu-group {
  margin-bottom: 0.5rem;
}

.mobile-menu-group-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  color: var(--color-text-primary);
  cursor: default;
}

.mobile-menu-subitems {
  padding-left: 1rem;
  margin-top: 0.25rem;
}

.mobile-menu-link--child {
  padding-left: 3rem;
  font-size: 0.875rem;
}

.mobile-menu-link--child .mobile-menu-icon {
  font-size: 0.5rem;
}
</style>

