<template>
  <div class="relative">
    <Menu
      ref="menu"
      :model="menuItems"
      :popup="true"
      class="user-dropdown"
    >
      <template #start>
        <button
          @click="toggle"
          class="flex items-center gap-2 p-2 rounded-lg hover:bg-surface-hover transition-colors"
          aria-label="Меню пользователя"
        >
          <UserAvatar :user="user" size="md" />
          <span v-if="showName" class="hidden md:block text-sm font-medium text-text-primary">
            {{ userName }}
          </span>
          <i class="pi pi-chevron-down text-xs text-text-muted"></i>
        </button>
      </template>
    </Menu>
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

const toggle = (event) => {
  menu.value.toggle(event);
};

const menuItems = computed(() => [
  {
    label: 'Профиль',
    icon: 'pi pi-user',
    command: () => {
      // TODO: Определить роут профиля в зависимости от роли
      const role = user.value?.roles?.[0];
      if (role === 'student') {
        router.visit(route('student.profile.show'));
      } else if (role === 'partner') {
        router.visit(route('partner.profile.show'));
      }
    },
  },
  {
    label: 'Настройки',
    icon: 'pi pi-cog',
    command: () => {
      // TODO: Переход на страницу настроек
    },
  },
  {
    separator: true,
  },
  {
    label: 'Выйти',
    icon: 'pi pi-sign-out',
    command: () => {
      router.post(route('logout'));
    },
  },
]);
</script>

<style scoped>
:deep(.user-dropdown) {
  min-width: 12rem;
}

:deep(.p-menu) {
  border: 1px solid var(--color-border-light);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>

