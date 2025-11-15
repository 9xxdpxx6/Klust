<template>
  <div
    :class="[
      'layout-with-sidebar',
      { 'collapsed': isCollapsed }
    ]"
  >
    <BaseSidebar
      :items="menuItems"
      title="Админ панель"
      :is-collapsed="sidebarState.isCollapsed.value"
      :is-mobile="sidebarState.isMobile.value"
      @toggle-collapse="sidebarState.toggleCollapse"
      class="layout-with-sidebar__sidebar"
    />
    
    <div class="layout-with-sidebar__content">
      <BaseHeader
        logo-text="КубГТУ"
        logo-image="/images/logo/logo.png"
        :show-search="true"
        :show-notifications="true"
        :show-mobile-menu="!!sidebarState.isMobile"
        @toggle-mobile-menu="sidebarState.toggleCollapse"
      />
      
      <main class="flex-1 p-6 bg-surface">
        <slot />
      </main>
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
      title="Админ панель"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import BaseHeader from '@/Components/Layout/BaseHeader.vue';
import BaseSidebar from '@/Components/Layout/BaseSidebar.vue';
import MobileMenu from '@/Components/Layout/MobileMenu.vue';
import { getMenuItemsForRole } from '@/Utils/navigation';
import { useSidebar } from '@/Composables/useSidebar';

const page = usePage();
const sidebarState = useSidebar(false);

const userRoles = computed(() => page.props.auth?.user?.roles || []);
const menuItems = computed(() => {
  // Ищем роль admin или teacher, иначе используем первую роль или admin по умолчанию
  const role = userRoles.value.find(r => r === 'admin' || r === 'teacher') 
    || userRoles.value[0] 
    || 'admin';
  return getMenuItemsForRole(role);
});

const isCollapsed = computed(() => {
  const collapsed = sidebarState.isCollapsed.value;
  const mobile = sidebarState.isMobile.value;
  return collapsed && !mobile;
});

</script>

<style scoped>
@import '@/Styles/layout.css';
</style>

