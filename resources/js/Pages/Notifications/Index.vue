<template>
  <div class="notifications-page">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-text-primary">Уведомления</h1>
      <Button
        v-if="hasUnread"
        label="Отметить все как прочитанные"
        icon="pi pi-check"
        @click="markAllAsRead"
        :loading="loading"
        outlined
        class="mark-all-read-button"
      />
    </div>

    <div v-if="loading && notifications.data.length === 0" class="text-center py-12">
      <i class="pi pi-spin pi-spinner text-4xl text-text-muted mb-4"></i>
      <p class="text-text-muted">Загрузка уведомлений...</p>
    </div>

    <div v-else-if="notifications.data.length === 0" class="text-center py-12">
      <i class="pi pi-inbox text-6xl text-text-muted mb-4"></i>
      <p class="text-lg text-text-muted">Нет уведомлений</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="notification in notifications.data"
        :key="notification.id"
          :class="[
          'notification-card p-4 rounded-lg border',
          notification.read_at ? 'bg-surface border-border' : 'bg-surface-light border-primary'
        ]"
      >
        <div class="flex items-start gap-4">
          <!-- Иконка -->
          <div v-if="notification.data?.icon" class="flex-shrink-0 mt-1">
            <i :class="['pi', notification.data.icon, 'text-xl', notification.read_at ? 'text-text-muted' : 'text-primary']"></i>
          </div>

          <!-- Контент -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <h3 class="font-semibold text-text-primary mb-1">
                  {{ notification.data?.title || 'Уведомление' }}
                </h3>
                <p class="text-sm text-text-muted mb-2">
                  {{ notification.data?.message || '' }}
                </p>
                <div class="flex items-center gap-4 text-xs text-text-muted">
                  <span>{{ formatDate(notification.created_at) }}</span>
                  <span v-if="!notification.read_at" class="px-2 py-1 bg-primary/10 text-primary rounded">
                    Новое
                  </span>
                </div>
              </div>

              <!-- Действия -->
              <div class="flex items-center gap-2 flex-shrink-0">
                <Button
                  v-if="!notification.read_at"
                  icon="pi pi-check"
                  @click="markAsRead(notification.id)"
                  :loading="markingRead === notification.id"
                  text
                  rounded
                  severity="success"
                  v-tooltip.top="'Отметить как прочитанное'"
                />
                <Button
                  icon="pi pi-trash"
                  @click="remove(notification.id)"
                  :loading="removing === notification.id"
                  text
                  rounded
                  severity="danger"
                  v-tooltip.top="'Удалить'"
                />
              </div>
            </div>

            <!-- Ссылка действия -->
            <div v-if="notification.data?.link" class="mt-3">
              <Link
                :href="notification.data.link"
                class="text-sm text-primary hover:opacity-80 inline-flex items-center gap-1 transition-opacity"
              >
                {{ notification.data.action_text || 'Перейти' }}
                <i class="pi pi-arrow-right text-xs"></i>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Пагинация -->
    <div v-if="notifications.links && notifications.links.length > 2" class="mt-6">
      <Pagination :links="notifications.links" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Pagination from '@/Components/Pagination.vue';
import { useNotifications } from '@/Composables/useNotifications';

const props = defineProps({
  notifications: {
    type: Object,
    required: true,
  },
});

const {
  unreadCount,
  markAsRead: markAsReadComposable,
  markAllAsRead: markAllAsReadComposable,
  remove: removeComposable,
  loading,
} = useNotifications(false);

const markingRead = ref(null);
const removing = ref(null);

const hasUnread = computed(() => {
  return props.notifications.data.some(n => !n.read_at);
});

const markAsRead = async (notificationId) => {
  markingRead.value = notificationId;
  try {
    await markAsReadComposable(notificationId);
    // Обновляем страницу для синхронизации данных
    router.reload({ only: ['notifications'] });
  } finally {
    markingRead.value = null;
  }
};

const markAllAsRead = async () => {
  try {
    await markAllAsReadComposable();
    router.reload({ only: ['notifications'] });
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};

const remove = async (notificationId) => {
  removing.value = notificationId;
  try {
    await removeComposable(notificationId);
    router.reload({ only: ['notifications'] });
  } finally {
    removing.value = null;
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

  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
  });
};
</script>

<style scoped>
.notifications-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.notification-card {
  /* Ховер эффект убран */
}

/* Стили для кнопки "Отметить все как прочитанные" */
:deep(.mark-all-read-button.p-button-outlined) {
  color: rgb(65 99 223);
  border-color: rgb(65 99 223);
}

:deep(.mark-all-read-button.p-button-outlined:hover) {
  background-color: rgb(65 99 223 / 0.1);
  border-color: rgb(65 99 223);
  color: rgb(65 99 223);
}

:deep(.mark-all-read-button.p-button-outlined:focus) {
  box-shadow: 0 0 0 0.2rem rgb(65 99 223 / 0.2);
}
</style>

