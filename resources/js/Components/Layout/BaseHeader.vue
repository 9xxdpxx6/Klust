<template>
  <header :class="[
    'base-header',
    { 'base-header--mobile': isMobile }
  ]">
    <!-- Логотип -->
    <div class="base-header__logo">
      <slot name="logo">
        <Link :href="logoLink" class="flex items-center gap-2">
          <!-- Иконка (просто буква) - приоритет -->
          <template v-if="logoIcon">
            <img
              v-if="!imageError.icon"
              :src="logoIcon"
              :alt="logoText"
              :class="[
                'object-contain',
                isMobile ? 'h-6 w-6' : 'h-8 w-8'
              ]"
              @error="handleImageError('icon')"
            />
            <span v-else :class="[
              'text-primary font-bold',
              isMobile ? 'text-lg' : 'text-xl'
            ]">{{ logoText }}</span>
          </template>
          <!-- Длинное лого (весь текст) - если нет иконки -->
          <template v-else-if="logoImage">
            <img
              v-if="!imageError.image"
              :src="logoImage"
              :alt="logoText"
              :class="[
                'object-contain max-w-[200px]',
                isMobile ? 'h-8' : 'h-10',
                logoImageClass
              ]"
              @error="handleImageError('image')"
            />
            <span v-else :class="[
              'text-primary font-bold',
              isMobile ? 'text-lg' : 'text-xl'
            ]">{{ logoText }}</span>
          </template>
          <!-- Текстовое лого по умолчанию -->
          <span v-else :class="[
            'text-primary font-bold',
            isMobile ? 'text-lg' : 'text-xl'
          ]">{{ logoText }}</span>
        </Link>
      </slot>
    </div>
    
    <!-- Навигация (опционально) -->
    <nav v-if="$slots.menu || showMenu" class="base-header__menu hidden lg:flex items-center gap-4">
      <slot name="menu" />
    </nav>
    
    <!-- Правая область: профиль, уведомления -->
    <div class="base-header__actions">
      
      <!-- Уведомления - всегда видимы, справа от аватара -->
      <NotificationBell />
      
      <!-- Профиль пользователя -->
      <UserDropdown v-if="user" :show-name="!isMobile && showUserName" />
      
      <!-- Мобильное меню (бургер) -->
      <button
        v-if="showMobileMenu"
        @click="$emit('toggle-mobile-menu')"
        class="lg:hidden p-2 rounded-lg hover:bg-surface-hover transition-colors"
        aria-label="Меню"
      >
        <i class="pi pi-bars text-xl text-text-secondary"></i>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import UserDropdown from '@/Components/Navigation/UserDropdown.vue';
import NotificationBell from '@/Components/Navigation/NotificationBell.vue';
import { useAuth } from '@/Composables/useAuth';
import { useResponsive } from '@/Composables/useResponsive';

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
    // Путь к иконке (просто буква), например: '/images/logo/icon.svg'
  },
  logoImage: {
    type: String,
    default: null,
    // Путь к длинному лого (весь текст), например: '/images/logo/logo-full.svg'
  },
  logoImageClass: {
    type: String,
    default: '',
    // Дополнительные классы для длинного лого
  },
  showNotifications: {
    type: Boolean,
    default: true,
  },
  showMenu: {
    type: Boolean,
    default: false,
  },
  showMobileMenu: {
    type: Boolean,
    default: false,
  },
  showUserName: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['toggle-mobile-menu']);

const { user } = useAuth();
const { isMobile } = useResponsive();

const imageError = ref({ icon: false, image: false });

const handleImageError = (type) => {
  imageError.value[type] = true;
  // Если изображение не загрузилось, показываем текстовое лого
};
</script>

<style scoped>
@import '@/Styles/layout.css';
</style>

