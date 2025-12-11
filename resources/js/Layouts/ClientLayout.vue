<template>
  <div class="layout-container">
    <BaseHeader
      :logo-text="logoText"
      :logo-link="logoLink"
      :logo-icon="logoIcon"
      :logo-image="logoImage"
      :logo-image-class="logoImageClass"
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
    default: 'Кластер',
  },
  logoLink: {
    type: String,
    default: '/',
  },
  logoIcon: {
    type: String,
    default: null,
    // Иконка (просто буква) - приоритет над logoImage
  },
  logoImage: {
    type: String,
    default: '/images/logo/logo.png',
    // Длинное лого (весь текст) - используется если logoIcon не указан
  },
  logoImageClass: {
    type: String,
    default: '',
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


