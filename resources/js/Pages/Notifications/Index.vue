<template>
  <MobileContainer :padding="false">
    <div class="notifications-page">
      <div :class="[
        'flex items-center justify-between',
        isMobile ? 'mb-4 flex-col items-start gap-3' : 'mb-6'
      ]">
        <h1 :class="[
          'font-bold text-text-primary',
          isMobile ? 'text-xl' : 'text-2xl'
        ]">Уведомления</h1>
        <Button
          v-if="hasUnread"
          :label="isMobile ? 'Отметить все' : 'Отметить все как прочитанные'"
          icon="pi pi-check"
          @click="markAllAsRead"
          :loading="loading"
          outlined
          :size="isMobile ? 'small' : undefined"
          class="mark-all-read-button"
        />
      </div>

      <div v-if="loading && notifications.data.length === 0" :class="[
        'text-center text-text-muted',
        isMobile ? 'py-8' : 'py-12'
      ]">
        <i :class="[
          'pi pi-spin pi-spinner mb-4',
          isMobile ? 'text-2xl' : 'text-4xl'
        ]"></i>
        <p :class="isMobile ? 'text-sm' : ''">Загрузка уведомлений...</p>
      </div>

      <div v-else-if="notifications.data.length === 0" :class="[
        'text-center text-text-muted',
        isMobile ? 'py-8' : 'py-12'
      ]">
        <i :class="[
          'pi pi-inbox mb-4',
          isMobile ? 'text-4xl' : 'text-6xl'
        ]"></i>
        <p :class="isMobile ? 'text-base' : 'text-lg'">Нет уведомлений</p>
      </div>

    <div v-else :class="[
      isMobile ? 'space-y-2' : 'space-y-3'
    ]">
      <div
        v-for="notification in notifications.data"
        :key="notification.id"
        :class="[
          'notification-card rounded-lg border',
          notification.read_at ? 'bg-surface border-border' : 'bg-surface-light border-primary',
          isMobile ? 'p-3' : 'p-4'
        ]"
      >
        <div :class="[
          'flex items-start',
          isMobile ? 'gap-2' : 'gap-4'
        ]">
          <!-- Иконка -->
          <div v-if="notification.data?.icon" :class="[
            'flex-shrink-0',
            isMobile ? 'mt-0.5' : 'mt-1'
          ]">
            <i :class="[
              'pi',
              notification.data.icon,
              notification.read_at ? 'text-text-muted' : 'text-primary',
              isMobile ? 'text-lg' : 'text-xl'
            ]"></i>
          </div>

          <!-- Контент -->
          <div class="flex-1 min-w-0">
            <div :class="[
              'flex items-start justify-between',
              isMobile ? 'flex-col gap-2' : 'gap-4'
            ]">
              <div class="flex-1 w-full">
                <h3 :class="[
                  'font-semibold text-text-primary mb-1',
                  isMobile ? 'text-sm' : ''
                ]">
                  {{ notification.data?.title || 'Уведомление' }}
                </h3>
                <p :class="[
                  'text-text-muted mb-2',
                  isMobile ? 'text-xs' : 'text-sm'
                ]">
                  {{ notification.data?.message || '' }}
                </p>
                <div :class="[
                  'flex items-center text-text-muted',
                  isMobile ? 'gap-2 text-xs flex-wrap' : 'gap-4 text-xs'
                ]">
                  <span>{{ formatDate(notification.created_at) }}</span>
                  <span v-if="!notification.read_at" :class="[
                    'px-2 py-1 bg-primary/10 text-primary rounded',
                    isMobile ? 'text-xs' : ''
                  ]">
                    Новое
                  </span>
                </div>
              </div>

              <!-- Действия -->
              <div :class="[
                'flex items-center flex-shrink-0',
                isMobile ? 'gap-1 absolute top-3 right-3' : 'gap-2'
              ]">
                <Button
                  v-if="!notification.read_at"
                  icon="pi pi-check"
                  @click="markAsRead(notification.id)"
                  :loading="markingRead === notification.id"
                  text
                  rounded
                  :size="isMobile ? 'small' : undefined"
                  severity="success"
                  v-tooltip.top="'Отметить как прочитанное'"
                />
                <Button
                  icon="pi pi-trash"
                  @click="remove(notification.id)"
                  :loading="removing === notification.id"
                  text
                  rounded
                  :size="isMobile ? 'small' : undefined"
                  severity="danger"
                  v-tooltip.top="'Удалить'"
                />
              </div>
            </div>

            <!-- Ссылка действия -->
            <div v-if="notification.data?.link" :class="[
              isMobile ? 'mt-2' : 'mt-3'
            ]">
              <Link
                :href="notification.data.link"
                :class="[
                  'text-primary hover:opacity-80 inline-flex items-center gap-1 transition-opacity',
                  isMobile ? 'text-xs' : 'text-sm'
                ]"
                @click="handleLinkClick(notification)"
              >
                {{ notification.data.action_text || 'Перейти' }}
                <i :class="[
                  'pi pi-arrow-right',
                  isMobile ? 'text-xs' : 'text-xs'
                ]"></i>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Пагинация -->
    <div v-if="notifications.links && notifications.links.length > 2" :class="[
      isMobile ? 'mt-4' : 'mt-6'
    ]">
      <Pagination :links="notifications.links" />
    </div>
    </div>
  </MobileContainer>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Pagination from '@/Components/Pagination.vue';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import { useNotifications } from '@/Composables/useNotifications';
import { useResponsive } from '@/Composables/useResponsive';

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
  fetchUnreadCount,
  loading,
} = useNotifications(false);

const { isMobile } = useResponsive();
const markingRead = ref(null);
const removing = ref(null);

const hasUnread = computed(() => {
  return props.notifications.data.some(n => !n.read_at);
});

const markAsRead = async (notificationId) => {
  markingRead.value = notificationId;
  try {
    await markAsReadComposable(notificationId);
    // Обновить счетчик (событие уже отправлено из composable)
    await fetchUnreadCount();
    // Обновляем страницу для синхронизации данных
    router.reload({ only: ['notifications'] });
  } finally {
    markingRead.value = null;
  }
};

const handleLinkClick = async (notification) => {
  // Пометить уведомление как прочитанное при клике на ссылку
  if (!notification.read_at) {
    await markAsReadComposable(notification.id);
  }
};

const markAllAsRead = async () => {
  try {
    await markAllAsReadComposable();
    // Обновить счетчик (событие уже отправлено из composable)
    await fetchUnreadCount();
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
}

@media (max-width: 767px) {
  .notifications-page {
    padding: 0;
  }
}

@media (min-width: 768px) {
  .notifications-page {
    padding: 1.5rem;
  }
}

.notification-card {
  position: relative;
  /* Ховер эффект убран */
}

@media (max-width: 767px) {
  .notification-card {
    /* На мобильных карточки более компактные */
  }
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

