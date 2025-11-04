<template>
  <div class="layout-container">
    <BaseHeader
      :logo-text="logoText"
      :logo-link="logoLink"
      :show-search="showSearch"
      :show-notifications="showNotifications"
      :show-mobile-menu="showMobileMenu"
      @toggle-mobile-menu="handleToggleMobileMenu"
    >
      <template #menu>
        <slot name="header-menu" />
      </template>
    </BaseHeader>
    
    <main class="layout-main">
      <slot />
    </main>
    
    <BaseFooter />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import BaseHeader from '@/Components/Layout/BaseHeader.vue';
import BaseFooter from '@/Components/Layout/BaseFooter.vue';

const props = defineProps({
  logoText: {
    type: String,
    default: 'Klust',
  },
  logoLink: {
    type: String,
    default: '/',
  },
  showSearch: {
    type: Boolean,
    default: false,
  },
  showNotifications: {
    type: Boolean,
    default: true,
  },
  showMobileMenu: {
    type: Boolean,
    default: false,
  },
});

const isMobileMenuOpen = ref(false);

const handleToggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};
</script>

<style scoped>
@import '@/Styles/layout.css';
</style>


