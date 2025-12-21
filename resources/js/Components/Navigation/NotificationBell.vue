<template>
  <div class="relative">
    <button
      ref="targetButton"
      @click="togglePopover"
      class="relative p-2 rounded-lg hover:bg-surface-hover transition-colors flex items-center mt-0.5"
      aria-label="Уведомления"
    >
      <i class="pi pi-bell text-text-secondary" style="font-size: 1.3rem;"></i>
      <Badge
        v-if="hasUnread"
        :value="unreadCount"
        severity="danger"
        class="absolute -top-1 -right-1"
      />
    </button>
    
    <Popover ref="popover">
      <div class="notification-panel w-80 max-w-[90vw]">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-text-primary">Уведомления</h3>
          <div class="flex items-center gap-2">
            <Link
              :href="notificationsRoute"
              class="text-sm text-primary hover:text-primary-light"
            >
              Все уведомления
            </Link>
            <button
              v-if="hasUnread"
              @click="markAllAsRead"
              class="text-sm text-primary hover:text-primary-light"
            >
              Отметить все
            </button>
          </div>
        </div>
        
        <div v-if="loading && notifications.length === 0" class="text-center py-8 text-text-muted">
          <i class="pi pi-spin pi-spinner text-4xl mb-2"></i>
          <p>Загрузка...</p>
        </div>

        <div v-else-if="notifications.length === 0" class="text-center py-8 text-text-muted">
          <i class="pi pi-inbox text-4xl mb-2"></i>
          <p>Нет уведомлений</p>
        </div>

        <div v-else class="space-y-2 max-h-96 overflow-y-auto">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            :class="[
              'notification-item',
              { 'notification-item--read': notification.read_at }
            ]"
            @click="handleNotificationClick(notification)"
          >
            <div class="flex items-start gap-3">
              <!-- Иконка уведомления -->
              <div v-if="notification.data?.icon" class="flex-shrink-0 mt-1">
                <i :class="['pi', notification.data.icon, 'text-primary']"></i>
              </div>

              <!-- Контент уведомления -->
              <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-text-primary">
                  {{ notification.data?.title || 'Уведомление' }}
                </p>
                <p class="text-xs text-text-muted mt-1 break-words">
                  {{ notification.data?.message || '' }}
                </p>
                <p class="text-xs text-text-muted mt-1">
                  {{ formatDate(notification.created_at) }}
                </p>
              </div>

              <!-- Кнопка "отметить как прочитанное" -->
              <button
                v-if="!notification.read_at"
                @click.stop="markAsRead(notification.id)"
                class="text-xs text-primary hover:text-primary-light flex-shrink-0"
                title="Отметить как прочитанное"
              >
                <i class="pi pi-check"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Popover>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Badge from 'primevue/badge';
import Popover from 'primevue/popover';
import { useNotifications } from '@/Composables/useNotifications';
import { router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

// Автозагрузка уведомлений при монтировании
const {
  notifications,
  unreadCount,
  hasUnread,
  markAsRead,
  markAllAsRead,
  loading
} = useNotifications(true);

const page = usePage();
const userRoles = computed(() => page.props.auth?.user?.roles || []);

const popover = ref(null);
const targetButton = ref(null);

const togglePopover = (event) => {
  if (popover.value && event.currentTarget) {
    popover.value.toggle(event, event.currentTarget);
  }
};

// Определяем маршрут уведомлений на основе роли
const notificationsRoute = computed(() => {
  const roles = userRoles.value;
  if (roles.includes('admin') || roles.includes('teacher')) {
    return route('admin.notifications.index');
  } else if (roles.includes('student')) {
    return route('student.notifications.index');
  } else if (roles.includes('partner')) {
    return route('partner.notifications.index');
  }
  return route('student.notifications.index'); // По умолчанию
});

const handleNotificationClick = async (notification) => {
  // Отметить как прочитанное
  if (!notification.read_at) {
    await markAsRead(notification.id);
  }

  // Переход по ссылке, если она есть
  if (notification.data?.link) {
    router.visit(notification.data.link);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(minutes / 60);
  const days = Math.floor(hours / 24);

  if (minutes < 1) return 'Только что';
  if (minutes < 60) return `${minutes} мин. назад`;
  if (hours < 24) return `${hours} ч. назад`;
  if (days < 7) return `${days} дн. назад`;

  return date.toLocaleDateString('ru-RU');
};
</script>

<style scoped>
.notification-panel {
  min-width: 20rem;
}

/* Увеличиваем размер иконки колокольчика в 2 раза */
button .pi-bell {
  font-size: 1.5rem;
}

.notification-item {
  padding: 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.2s;
  border-left: 3px solid transparent;
}

.notification-item:hover {
  background-color: var(--color-surface);
}

.notification-item--read {
  opacity: 0.7;
}

.notification-item:not(.notification-item--read) {
  border-left-color: var(--color-primary);
  background-color: var(--color-surface-light);
}
</style>

