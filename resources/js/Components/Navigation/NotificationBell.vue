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
      <div :class="[
        'notification-panel',
        isMobile ? 'w-[90vw] max-w-[90vw]' : 'w-[28rem] max-w-[90vw]'
      ]">
        <div :class="[
          'flex items-center justify-between',
          isMobile ? 'mb-3' : 'mb-4',
          isMobile ? 'flex-col items-start gap-2' : ''
        ]">
          <h3 :class="[
            'font-semibold text-text-primary',
            isMobile ? 'text-base' : 'text-lg'
          ]">Уведомления</h3>
          <div :class="[
            'flex items-center',
            isMobile ? 'gap-2 w-full justify-between' : 'gap-2 whitespace-nowrap'
          ]">
            <Link
              :href="notificationsRoute"
              :class="[
                'text-primary hover:text-primary-light',
                isMobile ? 'text-xs' : 'text-sm',
                isMobile ? '' : 'whitespace-nowrap'
              ]"
              @click="closePopover"
            >
              Все уведомления
            </Link>
            <button
              v-if="hasUnread"
              @click="markAllAsRead"
              :class="[
                'font-medium text-white rounded-lg transition-colors flex items-center gap-1.5 mark-all-button',
                isMobile ? 'px-2 py-1 text-xs' : 'px-3 py-1.5 text-sm',
                isMobile ? '' : 'whitespace-nowrap'
              ]"
            >
              <i class="pi pi-check"></i>
              <span :class="isMobile ? 'hidden sm:inline' : ''">Отметить все</span>
              <span :class="isMobile ? 'sm:hidden' : 'hidden'">Все</span>
            </button>
          </div>
        </div>
        
        <div v-if="loading && notifications.length === 0" :class="[
          'text-center text-text-muted',
          isMobile ? 'py-6' : 'py-8'
        ]">
          <i :class="[
            'pi pi-spin pi-spinner mb-2',
            isMobile ? 'text-2xl' : 'text-4xl'
          ]"></i>
          <p :class="isMobile ? 'text-sm' : ''">Загрузка...</p>
        </div>

        <div v-else-if="notifications.length === 0" :class="[
          'text-center text-text-muted',
          isMobile ? 'py-6' : 'py-8'
        ]">
          <i :class="[
            'pi pi-inbox mb-2',
            isMobile ? 'text-2xl' : 'text-4xl'
          ]"></i>
          <p :class="isMobile ? 'text-sm' : ''">Нет новых уведомлений</p>
        </div>

        <div v-else :class="[
          'space-y-2 overflow-y-auto',
          isMobile ? 'max-h-80' : 'max-h-96'
        ]">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            :class="[
              'notification-item',
              { 'notification-item--read': notification.read_at },
              isMobile ? 'p-2' : 'p-3'
            ]"
            @click="handleNotificationClick(notification)"
          >
            <div :class="[
              'flex items-start',
              isMobile ? 'gap-2' : 'gap-3'
            ]">
              <!-- Иконка уведомления -->
              <div v-if="notification.data?.icon" :class="[
                'flex-shrink-0',
                isMobile ? 'mt-0.5' : 'mt-1'
              ]">
                <i :class="[
                  'pi',
                  notification.data.icon,
                  'text-primary',
                  isMobile ? 'text-sm' : ''
                ]"></i>
              </div>

              <!-- Контент уведомления -->
              <div class="flex-1 min-w-0">
                <p :class="[
                  'font-medium text-text-primary',
                  isMobile ? 'text-xs' : 'text-sm'
                ]">
                  {{ notification.data?.title || 'Уведомление' }}
                </p>
                <p :class="[
                  'text-text-muted mt-1 break-words',
                  isMobile ? 'text-xs' : 'text-xs'
                ]">
                  {{ notification.data?.message || '' }}
                </p>
                <p :class="[
                  'text-text-muted mt-1',
                  isMobile ? 'text-xs' : 'text-xs'
                ]">
                  {{ formatDate(notification.created_at) }}
                </p>
              </div>

              <!-- Кнопка "отметить как прочитанное" -->
              <button
                v-if="!notification.read_at"
                @click.stop="handleMarkAsRead(notification.id)"
                :class="[
                  'text-primary hover:text-primary-light flex-shrink-0',
                  isMobile ? 'text-xs p-1' : 'text-xs'
                ]"
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
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Badge from 'primevue/badge';
import Popover from 'primevue/popover';
import { useNotifications } from '@/Composables/useNotifications';
import { router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useResponsive } from '@/Composables/useResponsive';

// Автозагрузка уведомлений при монтировании
const {
  notifications,
  unreadCount,
  hasUnread,
  markAsRead,
  markAllAsRead,
  fetchUnreadCount,
  fetchRecentNotifications,
  loading
} = useNotifications(true);

const page = usePage();
const userRoles = computed(() => page.props.auth?.user?.roles || []);
const { isMobile } = useResponsive();

const popover = ref(null);
const targetButton = ref(null);

const togglePopover = async (event) => {
  if (popover.value && event.currentTarget) {
    popover.value.toggle(event, event.currentTarget);
    // Обновить данные при открытии popover
    await Promise.all([
      fetchUnreadCount(),
      fetchRecentNotifications()
    ]);
  }
};

const closePopover = () => {
  if (popover.value) {
    popover.value.hide();
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

const handleMarkAsRead = async (notificationId) => {
  await markAsRead(notificationId);
  // Обновить счетчик после пометки
  await fetchUnreadCount();
};

const handleNotificationClick = async (notification) => {
  // Отметить как прочитанное
  if (!notification.read_at) {
    await handleMarkAsRead(notification.id);
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

// Обновлять счетчик при изменении URL (когда пользователь возвращается на страницу)
watch(() => page.url, () => {
  fetchUnreadCount();
});

// Слушать события обновления уведомлений от других компонентов
const handleNotificationRead = async (event) => {
  const { notificationId } = event.detail;
  // Обновить локальное состояние
  const notification = notifications.value.find(n => n.id === notificationId);
  if (notification && !notification.read_at) {
    notification.read_at = new Date().toISOString();
  }
  // Обновить список уведомлений и счетчик через API для точности
  await Promise.all([
    fetchUnreadCount(),
    fetchRecentNotifications()
  ]);
};

const handleAllRead = async (event) => {
  // Обновить все уведомления
  notifications.value.forEach(n => {
    if (!n.read_at) {
      n.read_at = new Date().toISOString();
    }
  });
  // Всегда обновляем счетчик через API для точности
  await fetchUnreadCount();
};

onMounted(() => {
  window.addEventListener('notification-read', handleNotificationRead);
  window.addEventListener('notifications-all-read', handleAllRead);
});

onUnmounted(() => {
  window.removeEventListener('notification-read', handleNotificationRead);
  window.removeEventListener('notifications-all-read', handleAllRead);
});
</script>

<style scoped>
.notification-panel {
  min-width: 28rem;
}

@media (max-width: 767px) {
  .notification-panel {
    min-width: auto;
  }
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

.mark-all-button {
  background-color: rgb(65 99 223);
  border: 1px solid rgb(65 99 223);
}

.mark-all-button:hover {
  background-color: rgb(51 79 180);
  border-color: rgb(51 79 180);
}
</style>

