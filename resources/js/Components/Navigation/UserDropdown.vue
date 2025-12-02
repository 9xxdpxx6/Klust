<template>
  <div class="relative">
    <button
      @click="toggle"
      class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-surface-hover transition-colors"
      aria-label="Меню пользователя"
    >
      <UserAvatar :user="user" size="md" />
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
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Menu from 'primevue/menu';
import UserAvatar from '@/Components/Shared/UserAvatar.vue';
import { useAuth } from '@/Composables/useAuth';

const props = defineProps({
  showName: {
    type: Boolean,
    default: true,
  },
});

const { user } = useAuth();

const userName = computed(() => user.value?.name || 'Пользователь');

const menu = ref(null);
const isMenuVisible = ref(false);

const toggle = (event) => {
  menu.value.toggle(event);
  isMenuVisible.value = !isMenuVisible.value;
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
      router.post(route('logout'));
    },
  });

  return items;
});
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

