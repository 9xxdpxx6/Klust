<template>
  <div
    :class="[
      'layout-with-sidebar',
      { 'collapsed': sidebarState.isCollapsed && !sidebarState.isMobile }
    ]"
  >
    <BaseSidebar
      :items="menuItems"
      title="Админ панель"
      :initial-collapsed="false"
      class="layout-with-sidebar__sidebar"
    />
    
    <div class="layout-with-sidebar__content">
      <BaseHeader
        logo-text="КубГТУ"
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
      v-model="mobileMenuOpen"
      :items="menuItems"
      title="Админ панель"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
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
  const role = userRoles.value[0] || 'admin';
  return getMenuItemsForRole(role);
});

const mobileMenuOpen = ref(false);

// Синхронизация с sidebar состоянием
watch(() => sidebarState.isMobileOpen, (value) => {
  mobileMenuOpen.value = value;
});

watch(mobileMenuOpen, (value) => {
  if (value !== sidebarState.isMobileOpen) {
    sidebarState.toggleMobile();
  }
});
</script>

<style scoped>
@import '@/Styles/layout.css';
</style>

