<template>
  <div
    :class="[
      'layout-with-sidebar',
      { 'collapsed': sidebarState.isCollapsed && !sidebarState.isMobile }
    ]"
  >
    <BaseSidebar
      :items="menuItems"
      title="Студент"
      :initial-collapsed="false"
      class="layout-with-sidebar__sidebar"
    />
    
    <div class="layout-with-sidebar__content">
      <BaseHeader
        :show-search="false"
        :show-mobile-menu="!!sidebarState.isMobile"
        @toggle-mobile-menu="sidebarState.toggleCollapse"
      >
        <template #menu>
          <slot name="header-indicators" />
        </template>
      </BaseHeader>
      
      <main class="flex-1 p-6 bg-surface">
        <slot />
      </main>
      
      <BaseFooter />
    </div>
    
    <MobileMenu
      v-if="sidebarState.isMobile"
      v-model="mobileMenuOpen"
      :items="menuItems"
      title="Студент"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import BaseHeader from '@/Components/Layout/BaseHeader.vue';
import BaseFooter from '@/Components/Layout/BaseFooter.vue';
import BaseSidebar from '@/Components/Layout/BaseSidebar.vue';
import MobileMenu from '@/Components/Layout/MobileMenu.vue';
import { getMenuItemsForRole } from '@/Utils/navigation';
import { useSidebar } from '@/Composables/useSidebar';

const page = usePage();
const sidebarState = useSidebar(false);

const userRoles = computed(() => page.props.auth?.user?.roles || []);
const menuItems = computed(() => getMenuItemsForRole('student'));

const mobileMenuOpen = ref(false);

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

