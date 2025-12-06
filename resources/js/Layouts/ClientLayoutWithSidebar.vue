<template>
  <div
    :class="[
      'layout-with-sidebar',
      { 'collapsed': isCollapsed }
    ]"
  >
    <BaseSidebar
      :items="menuItems"
      :title="title"
      :is-collapsed="sidebarState.isCollapsed.value"
      :is-mobile="sidebarState.isMobile.value"
      @toggle-collapse="sidebarState.toggleCollapse"
      class="layout-with-sidebar__sidebar"
    />
    
    <div class="layout-with-sidebar__content">
      <BaseHeader
        :logo-link="headerProps.logoLink"
        :logo-icon="headerProps.logoIcon"
        :logo-image="headerProps.logoImage"
        :logo-text="headerProps.logoText"
        :show-search="headerProps.showSearch"
        :show-notifications="headerProps.showNotifications"
        :show-mobile-menu="!!sidebarState.isMobile"
        @toggle-mobile-menu="sidebarState.toggleCollapse"
      >
        <template #menu>
          <slot name="header-indicators" />
        </template>
      </BaseHeader>
      
      <main class="flex-1 p-6 bg-surface">
        <FlashMessage />
        <slot />
      </main>
      
      <BaseFooter v-if="showFooter" />
    </div>
    
    <MobileMenu
      v-if="sidebarState.isMobile"
      :model-value="sidebarState.isMobileOpen.value"
      @update:model-value="(value) => {
        if (value !== sidebarState.isMobileOpen.value) {
          sidebarState.toggleMobile();
        }
      }"
      :items="menuItems"
      :title="title"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import BaseHeader from '@/Components/Layout/BaseHeader.vue';
import BaseSidebar from '@/Components/Layout/BaseSidebar.vue';
import BaseFooter from '@/Components/Layout/BaseFooter.vue';
import MobileMenu from '@/Components/Layout/MobileMenu.vue';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import { getMenuItemsForRole } from '@/Utils/navigation';
import { useSidebar } from '@/Composables/useSidebar';

const props = defineProps({
  role: {
    type: String,
    required: true,
    validator: (value) => ['partner', 'student', 'admin', 'teacher'].includes(value),
  },
  title: {
    type: String,
    required: true,
  },
  showFooter: {
    type: Boolean,
    default: false,
  },
  headerProps: {
    type: Object,
    default: () => ({
      logoLink: '/',
      logoImage: '/images/logo/logo.png',
      logoText: 'Кластер',
      showSearch: false,
      showNotifications: false,
    }),
  },
});

const page = usePage();
const sidebarState = useSidebar(false);

const userRoles = computed(() => page.props.auth?.user?.roles || []);
const menuItems = computed(() => {
  // Для роли 'admin' ищем 'admin' или 'teacher'
  if (props.role === 'admin') {
    const role = userRoles.value.find(r => r === 'admin' || r === 'teacher') 
      || userRoles.value[0] 
      || 'admin';
    return getMenuItemsForRole(role);
  }
  
  // Для остальных ролей ищем точное совпадение
  const role = userRoles.value.find(r => r === props.role) 
    || userRoles.value[0] 
    || props.role;
  return getMenuItemsForRole(role);
});

const isCollapsed = computed(() => sidebarState.isCollapsed.value && !sidebarState.isMobile.value);

</script>

<style scoped>
@import '@/Styles/layout.css';
</style>

