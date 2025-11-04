import { ref, computed } from 'vue';

/**
 * Композабл для работы с уведомлениями
 * Пока использует заглушку, позже будет интегрирован с API
 */
export function useNotifications() {
  // Заглушка: потом будет из API
  const notifications = ref([
    // Пример структуры:
    // { id: 1, title: 'Новое уведомление', message: '...', read: false, createdAt: '...' }
  ]);
  
  const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read).length;
  });
  
  const hasUnread = computed(() => unreadCount.value > 0);
  
  /**
   * Отметить уведомление как прочитанное
   * @param {number} notificationId - ID уведомления
   */
  const markAsRead = (notificationId) => {
    const notification = notifications.value.find(n => n.id === notificationId);
    if (notification) {
      notification.read = true;
      // TODO: Отправить запрос на сервер
    }
  };
  
  /**
   * Отметить все как прочитанные
   */
  const markAllAsRead = () => {
    notifications.value.forEach(n => {
      n.read = true;
    });
    // TODO: Отправить запрос на сервер
  };
  
  /**
   * Удалить уведомление
   * @param {number} notificationId - ID уведомления
   */
  const remove = (notificationId) => {
    const index = notifications.value.findIndex(n => n.id === notificationId);
    if (index !== -1) {
      notifications.value.splice(index, 1);
      // TODO: Отправить запрос на сервер
    }
  };
  
  return {
    notifications,
    unreadCount,
    hasUnread,
    markAsRead,
    markAllAsRead,
    remove,
  };
}


