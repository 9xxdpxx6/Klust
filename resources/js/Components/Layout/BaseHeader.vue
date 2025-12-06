<template>
  <header class="base-header">
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
              class="h-8 w-8 object-contain"
              @error="handleImageError('icon')"
            />
            <span v-else class="text-primary font-bold text-xl">{{ logoText }}</span>
          </template>
          <!-- Длинное лого (весь текст) - если нет иконки -->
          <template v-else-if="logoImage">
            <img
              v-if="!imageError.image"
              :src="logoImage"
              :alt="logoText"
              class="h-10 object-contain max-w-[200px]"
              :class="logoImageClass"
              @error="handleImageError('image')"
            />
            <span v-else class="text-primary font-bold text-xl">{{ logoText }}</span>
          </template>
          <!-- Текстовое лого по умолчанию -->
          <span v-else class="text-primary font-bold text-xl">{{ logoText }}</span>
        </Link>
      </slot>
    </div>
    
    <!-- Навигация (опционально) -->
    <nav v-if="$slots.menu || showMenu" class="base-header__menu hidden lg:flex items-center gap-4">
      <slot name="menu" />
    </nav>
    
    <!-- Правая область: поиск, уведомления, профиль -->
    <div class="base-header__actions">
      <!-- Поиск (для админки) -->
      <div v-if="showSearch" class="base-header__search">
        <slot name="search">
          <IconField>
            <InputIcon class="pi pi-search" />
            <InputText
              v-model="searchValue"
              type="text"
              placeholder="Поиск..."
              @update:modelValue="handleSearchInput"
            />
          </IconField>
        </slot>
      </div>
      
      <!-- Уведомления -->
      <NotificationBell v-if="showNotifications" />
      
      <!-- Профиль пользователя -->
      <UserDropdown v-if="user" :show-name="showUserName" />
      
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
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import UserDropdown from '@/Components/Navigation/UserDropdown.vue';
import NotificationBell from '@/Components/Navigation/NotificationBell.vue';
import { useAuth } from '@/Composables/useAuth';

const searchValue = ref('');

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
  showSearch: {
    type: Boolean,
    default: false,
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

const emit = defineEmits(['toggle-mobile-menu', 'search']);

const { user } = useAuth();

const imageError = ref({ icon: false, image: false });

const handleImageError = (type) => {
  imageError.value[type] = true;
  // Если изображение не загрузилось, показываем текстовое лого
};

const handleSearchInput = (value) => {
  emit('search', value);
};
</script>

<style scoped>
@import '@/Styles/layout.css';
</style>

