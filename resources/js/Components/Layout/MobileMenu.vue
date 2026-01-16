<template>
  <Drawer
    v-model:visible="isVisible"
    position="left"
    :modal="true"
    :dismissable="true"
    class="mobile-menu-drawer"
    :style="{ 
      '--p-drawer-background': 'var(--color-primary)',
      '--p-drawer-color': 'var(--color-kubgtu-white)',
      '--p-drawer-border-color': 'transparent'
    }"
  >
    <template #header>
      <div class="mobile-menu-header">
        <div class="flex items-center justify-between w-full">
          <div class="flex items-center gap-3">
            <img 
              src="/images/assets/kubstu-icon-white.png" 
              alt="КубГТУ" 
              class="mobile-menu-header-icon"
            />
            <h2 class="text-lg font-bold text-white">{{ title }}</h2>
          </div>
          <button
            @click="close"
            class="p-1 rounded hover:bg-white/10 transition-colors text-white flex items-center justify-center"
            aria-label="Закрыть меню"
          >
            <i class="pi pi-times"></i>
          </button>
        </div>
      </div>
    </template>
    
    <div class="mobile-menu-content">
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
      
      <div class="mobile-menu-footer">
        <div class="flex justify-center items-center">
          <img 
            src="/images/assets/kubgtu-square-logo-white.png" 
            alt="КубГТУ" 
            class="mobile-menu-footer-logo"
          />
        </div>
      </div>
    </div>
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
/* ПЕРЕОПРЕДЕЛЯЕМ CSS ПЕРЕМЕННЫЕ PRIMEVUE ДЛЯ DRAWER */
:deep(.mobile-menu-drawer),
:deep(.p-drawer.mobile-menu-drawer) {
  --p-drawer-background: var(--color-primary) !important;
  --p-drawer-color: var(--color-kubgtu-white) !important;
  --p-drawer-border-color: transparent !important;
}

/* КОРНЕВОЙ ЭЛЕМЕНТ - САМОЕ ВАЖНОЕ! */
:deep(.mobile-menu-drawer),
:deep(.mobile-menu-drawer.p-drawer),
:deep(.mobile-menu-drawer.p-component),
:deep(.p-drawer.mobile-menu-drawer) {
  width: 16rem !important;
  max-width: 90vw !important;
  background: var(--color-primary) !important;
  background-color: var(--color-primary) !important;
  padding: 0 !important;
  margin: 0 !important;
  border: none !important;
  border-radius: 0 !important;
  display: flex !important;
  flex-direction: column !important;
  height: 100vh !important;
  min-height: 100vh !important;
  overflow: hidden !important;
}

/* ПЕРЕОПРЕДЕЛЯЕМ CSS ПЕРЕМЕННЫЕ ДЛЯ ВСЕХ ЭЛЕМЕНТОВ DRAWER */
:deep(.p-drawer),
:deep(.p-drawer-content-wrapper),
:deep(.p-drawer-header),
:deep(.p-drawer-content) {
  --p-drawer-background: var(--color-primary) !important;
  --p-drawer-color: var(--color-kubgtu-white) !important;
  --p-drawer-border-color: transparent !important;
}

/* Убираем все отступы у Drawer контейнера */
:deep(.p-drawer) {
  background: var(--color-primary) !important;
  background-color: var(--color-primary) !important;
  padding: 0 !important;
  margin: 0 !important;
  border: none !important;
  border-radius: 0 !important;
  display: flex !important;
  flex-direction: column !important;
  height: 100vh !important;
  min-height: 100vh !important;
  width: 100% !important;
  overflow: hidden !important;
}

/* Убираем отступы у content wrapper */
:deep(.p-drawer-content-wrapper) {
  background: var(--color-primary) !important;
  padding: 0 !important;
  margin: 0 !important;
  border: none !important;
  border-radius: 0 !important;
  width: 100% !important;
  height: 100vh !important;
  min-height: 100vh !important;
  display: flex !important;
  flex-direction: column !important;
  gap: 0 !important;
  overflow: hidden !important;
}

/* Убираем отступы у header */
:deep(.p-drawer-header) {
  background: var(--color-primary) !important;
  padding: 0 !important;
  margin: 0 !important;
  border: none !important;
  border-bottom: none !important;
  border-radius: 0 !important;
  width: 100% !important;
  flex-shrink: 0 !important;
}

/* Убираем отступы у content */
:deep(.p-drawer-content) {
  padding: 0 !important;
  margin: 0 !important;
  background: var(--color-primary) !important;
  border: none !important;
  border-radius: 0 !important;
  min-height: calc(100vh - var(--header-height, 0px)) !important;
  height: 100% !important;
  width: 100% !important;
  display: flex !important;
  flex-direction: column !important;
  flex: 1 1 auto !important;
  overflow: hidden !important;
}

/* Убираем отступы у всех дочерних элементов header и content */
:deep(.p-drawer-header > *) {
  margin: 0 !important;
}

:deep(.p-drawer-content > *) {
  margin: 0 !important;
}

:deep(.p-drawer .p-component) {
  background: var(--color-primary) !important;
}

/* ПРИМЕНЯЕМ ФОН КО ВСЕМ ЭЛЕМЕНТАМ ВНУТРИ DRAWER - НАЧИНАЯ С КОРНЯ */
:deep(.mobile-menu-drawer),
:deep(.mobile-menu-drawer *),
:deep(.p-drawer),
:deep(.p-drawer *),
:deep(.p-drawer-content-wrapper),
:deep(.p-drawer-content-wrapper *),
:deep(.p-drawer-header),
:deep(.p-drawer-header *),
:deep(.p-drawer-content),
:deep(.p-drawer-content *) {
  background-color: var(--color-primary) !important;
  background: var(--color-primary) !important;
}

/* Убираем отступы у overlay/mask контейнера */
:deep(.p-drawer-mask),
:deep(.p-overlay-mask) {
  padding: 0 !important;
  margin: 0 !important;
}

/* Убираем все возможные зазоры */
:deep(.p-drawer-content-wrapper),
:deep(.p-drawer) {
  gap: 0 !important;
  row-gap: 0 !important;
  column-gap: 0 !important;
}

.mobile-menu-nav {
  padding: 1rem 0;
  background: var(--color-primary) !important;
  flex: 1 1 auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  width: 100% !important;
  box-sizing: border-box;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.mobile-menu-item {
  margin-bottom: 0.25rem;
  background: var(--color-primary);
}

.mobile-menu-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  color: var(--color-kubgtu-white);
  text-decoration: none;
  transition: all 0.2s;
  border-left: 3px solid transparent;
  opacity: 0.9;
}

.mobile-menu-link:hover {
  background: rgba(255, 255, 255, 0.1) !important;
  color: var(--color-kubgtu-white);
  opacity: 1;
}

.mobile-menu-link.active {
  background: rgba(255, 255, 255, 0.15) !important;
  color: var(--color-kubgtu-white);
  border-left-color: var(--color-kubgtu-white);
  font-weight: 600;
  opacity: 1;
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
  background: var(--color-primary);
}

.mobile-menu-group-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  color: var(--color-kubgtu-white);
  cursor: default;
  opacity: 0.9;
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

.mobile-menu-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: var(--color-primary) !important;
  margin: 0 !important;
  width: 100% !important;
  box-sizing: border-box;
  flex-shrink: 0;
}

.mobile-menu-header-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}

.mobile-menu-content {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  height: 100%;
  background: var(--color-primary) !important;
}

.mobile-menu-footer {
  padding: 0.5rem 0.5rem;
  margin-top: auto;
  flex-shrink: 0;
  background: var(--color-primary) !important;
}

.mobile-menu-footer-logo {
  max-width: 100%;
  height: auto;
  opacity: 0.3;
  transition: opacity 0.2s;
}

.mobile-menu-footer-logo:hover {
  opacity: 1;
}
</style>

<!-- ГЛОБАЛЬНЫЕ СТИЛИ БЕЗ SCOPED ДЛЯ ПЕРЕОПРЕДЕЛЕНИЯ PRIMEVUE -->
<style>
/* МАКСИМАЛЬНАЯ СПЕЦИФИЧНОСТЬ ДЛЯ ПЕРЕОПРЕДЕЛЕНИЯ PRIMEVUE DRAWER */
.p-drawer.mobile-menu-drawer,
.p-drawer.mobile-menu-drawer.p-component,
.p-drawer.mobile-menu-drawer[role="dialog"],
div.p-drawer.mobile-menu-drawer {
  --p-drawer-background: #3157DD !important;
  --p-drawer-color: #FFFFFF !important;
  --p-drawer-border-color: transparent !important;
  background: #3157DD !important;
  background-color: #3157DD !important;
}

.p-drawer.mobile-menu-drawer .p-drawer-content-wrapper,
.p-drawer.mobile-menu-drawer .p-drawer-header,
.p-drawer.mobile-menu-drawer .p-drawer-content {
  --p-drawer-background: var(--color-primary) !important;
  background: var(--color-primary) !important;
  background-color: var(--color-primary) !important;
}

.p-drawer.mobile-menu-drawer * {
  --p-drawer-background: var(--color-primary) !important;
}
</style>

