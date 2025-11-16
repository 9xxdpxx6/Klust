<template>
  <div
    :class="[
      'layout-with-sidebar',
      { 'collapsed': isCollapsed }
    ]"
  >
    <BaseSidebar
      :items="menuItems"
      title="Партнер"
      :is-collapsed="sidebarState.isCollapsed.value"
      :is-mobile="sidebarState.isMobile.value"
      @toggle-collapse="sidebarState.toggleCollapse"
      class="layout-with-sidebar__sidebar"
    />
    
    <div class="layout-with-sidebar__content">
      <BaseHeader
        logo-icon="/images/logo/icon.png"
        logo-image="/images/logo/logo.png"
        :show-search="false"
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
      title="Партнер"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import BaseHeader from '@/Components/Layout/BaseHeader.vue';
import BaseSidebar from '@/Components/Layout/BaseSidebar.vue';
import MobileMenu from '@/Components/Layout/MobileMenu.vue';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import { getMenuItemsForRole } from '@/Utils/navigation';
import { useSidebar } from '@/Composables/useSidebar';

const page = usePage();
const sidebarState = useSidebar(false);

const userRoles = computed(() => page.props.auth?.user?.roles || []);
const menuItems = computed(() => {
  // Ищем роль partner, иначе используем первую роль или partner по умолчанию
  const role = userRoles.value.find(r => r === 'partner') 
    || userRoles.value[0] 
    || 'partner';
  return getMenuItemsForRole(role);
});

const isCollapsed = computed(() => sidebarState.isCollapsed.value && !sidebarState.isMobile.value);

</script>

<style scoped>
@import '@/Styles/layout.css';
</style>

