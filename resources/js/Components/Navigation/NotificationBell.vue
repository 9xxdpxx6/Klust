<template>
  <div class="relative">
    <Popover>
      <template #target="{ toggle: togglePopover }">
        <button
          @click="togglePopover"
          class="relative p-2 rounded-lg hover:bg-surface-hover transition-colors"
          aria-label="Уведомления"
        >
          <i class="pi pi-bell text-xl text-text-secondary"></i>
          <Badge
            v-if="hasUnread"
            :value="unreadCount"
            severity="danger"
            class="absolute -top-1 -right-1"
          />
        </button>
      </template>
      
      <div class="notification-panel w-80 max-w-[90vw]">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-text-primary">Уведомления</h3>
          <button
            v-if="hasUnread"
            @click="markAllAsRead"
            class="text-sm text-primary hover:text-primary-light"
          >
            Отметить все как прочитанные
          </button>
        </div>
        
        <div v-if="notifications.length === 0" class="text-center py-8 text-text-muted">
          <i class="pi pi-inbox text-4xl mb-2"></i>
          <p>Нет уведомлений</p>
        </div>
        
        <div v-else class="space-y-2 max-h-96 overflow-y-auto">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            :class="[
              'notification-item',
              { 'notification-item--read': notification.read }
            ]"
            @click="handleNotificationClick(notification)"
          >
            <div class="flex items-start gap-3">
              <div class="flex-1">
                <p class="font-medium text-sm text-text-primary">
                  {{ notification.title }}
                </p>
                <p class="text-xs text-text-muted mt-1">
                  {{ notification.message }}
                </p>
                <p class="text-xs text-text-muted mt-1">
                  {{ formatDate(notification.createdAt) }}
                </p>
              </div>
              <button
                v-if="!notification.read"
                @click.stop="markAsRead(notification.id)"
                class="text-xs text-primary hover:text-primary-light"
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
import Badge from 'primevue/badge';
import Popover from 'primevue/popover';
import { useNotifications } from '@/Composables/useNotifications';

const { notifications, unreadCount, hasUnread, markAsRead, markAllAsRead } = useNotifications();

const handleNotificationClick = (notification) => {
  if (!notification.read) {
    markAsRead(notification.id);
  }
  // TODO: Переход на связанную страницу, если есть
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

