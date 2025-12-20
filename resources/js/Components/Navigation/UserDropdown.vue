<template>
  <div class="relative">
    <button
      @click="toggle"
      class="flex items-center gap-2 px-3 py-1 rounded-lg hover:bg-surface-hover transition-colors"
      aria-label="Меню пользователя"
    >
      <UserAvatar :user="user" size="sm" />
      <span v-if="showName" class="hidden md:block text-sm font-medium text-text-primary">
        {{ userName }}
      </span>
      <i class="pi pi-chevron-down text-xs text-text-muted transition-transform" :class="{ 'rotate-180': isMenuVisible }"></i>
    </button>

    <Menu
      ref="menu"
      :model="menuItems"
      :popup="true"
      class="user-dropdown-menu"
      @hide="handleMenuHide"
      @show="handleMenuShow"
    />

    <!-- Модалка подтверждения выхода -->
    <ConfirmDialog
      v-model:visible="showLogoutConfirm"
      title="Выход из аккаунта"
      message="Вы уверены, что хотите выйти из аккаунта?"
      confirm-text="Выйти"
      cancel-text="Отмена"
      confirm-variant="danger"
      type="warning"
      @confirm="handleLogout"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Menu from 'primevue/menu';
import UserAvatar from '@/Components/Shared/UserAvatar.vue';
import ConfirmDialog from '@/Components/UI/ConfirmDialog.vue';
import { useAuth } from '@/Composables/useAuth';

const props = defineProps({
  showName: {
    type: Boolean,
    default: true,
  },
});

const { user, hasRole } = useAuth();

const userName = computed(() => {
  const name = user.value?.name || 'Пользователь';
  
  // Если пользователь - партнер, добавляем название компании в скобках
  if (hasRole('partner')) {
    // Пробуем получить название компании из разных источников
    const companyName = user.value?.partner_profile?.company_name || 
                       user.value?.partner_profile?.companyName ||
                       user.value?.partner?.company_name ||
                       user.value?.partner?.companyName;
    
    if (companyName) {
      return `${name} (${companyName})`;
    }
  }
  
  return name;
});

const menu = ref(null);
const isMenuVisible = ref(false);
const showLogoutConfirm = ref(false);

const toggle = (event) => {
  menu.value.toggle(event);
  // Состояние будет обновлено через события @show/@hide
};

const handleMenuShow = () => {
  isMenuVisible.value = true;
};

const handleMenuHide = () => {
  isMenuVisible.value = false;
};

const getUserRole = () => {
  const roles = user.value?.roles || [];
  if (roles.includes('admin') || roles.includes('teacher')) {
    return 'admin';
  }
  return roles[0] || 'student';
};

const menuItems = computed(() => {
  const role = getUserRole();

  const items = [];

  // Профиль
  items.push({
    label: 'Профиль',
    icon: 'pi pi-user',
    command: () => {
      const routeName = `${role}.profile.show`;
      router.visit(route(routeName));
    },
  });

  // Разделитель
  items.push({
    separator: true,
  });

  // Выйти
  items.push({
    label: 'Выйти',
    icon: 'pi pi-sign-out',
    command: () => {
      showLogoutConfirm.value = true;
    },
  });

  return items;
});

const handleLogout = () => {
  router.post(route('logout'));
};
</script>

<style scoped>
:deep(.user-dropdown-menu) {
  min-width: 12rem;
}

:deep(.user-dropdown-menu .p-menu) {
  border: 1px solid var(--color-border-light);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border-radius: 0.5rem;
  margin-top: 0.5rem;
}

:deep(.user-dropdown-menu .p-menu-list) {
  padding: 0.5rem;
}

:deep(.user-dropdown-menu .p-menuitem-link) {
  border-radius: 0.375rem;
  padding: 0.625rem 0.75rem;
  transition: all 0.15s ease;
}

:deep(.user-dropdown-menu .p-menuitem-link:hover) {
  background-color: var(--color-surface-hover);
}

:deep(.user-dropdown-menu .p-menuitem-icon) {
  color: var(--color-text-secondary);
  margin-right: 0.75rem;
}

:deep(.user-dropdown-menu .p-menuitem-text) {
  color: var(--color-text-primary);
  font-size: 0.875rem;
}
</style>

