import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

/**
 * Композабл для работы с уведомлениями
 */
export function useNotifications(autoLoad = false) {
  const notifications = ref([]);
  const unreadCount = ref(0);
  const loading = ref(false);

  const hasUnread = computed(() => unreadCount.value > 0);

  /**
   * Загрузить последние непрочитанные уведомления
   */
  const fetchRecentNotifications = async () => {
    try {
      loading.value = true;
      const response = await axios.get('/notifications/recent');
      notifications.value = response.data;
      return response.data;
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
      return [];
    } finally {
      loading.value = false;
    }
  };

  /**
   * Загрузить количество непрочитанных уведомлений
   */
  const fetchUnreadCount = async () => {
    try {
      const response = await axios.get('/notifications/unread-count');
      unreadCount.value = response.data.count;
      return response.data.count;
    } catch (error) {
      console.error('Failed to fetch unread count:', error);
      return 0;
    }
  };

  /**
   * Отметить уведомление как прочитанное
   * @param {number} notificationId - ID уведомления
   */
  const markAsRead = async (notificationId) => {
    try {
      await axios.post(`/notifications/${notificationId}/read`);

      // Обновить локальное состояние
      const notification = notifications.value.find(n => n.id === notificationId);
      if (notification && !notification.is_read) {
        notification.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }

      return true;
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
      return false;
    }
  };

  /**
   * Отметить все как прочитанные
   */
  const markAllAsRead = async () => {
    try {
      await axios.post('/notifications/read-all');

      // Обновить локальное состояние
      notifications.value.forEach(n => {
        n.is_read = true;
      });
      unreadCount.value = 0;

      return true;
    } catch (error) {
      console.error('Failed to mark all as read:', error);
      return false;
    }
  };

  /**
   * Удалить уведомление
   * @param {number} notificationId - ID уведомления
   */
  const remove = async (notificationId) => {
    try {
      await axios.delete(`/notifications/${notificationId}`);

      // Удалить из локального состояния
      const index = notifications.value.findIndex(n => n.id === notificationId);
      if (index !== -1) {
        const notification = notifications.value[index];
        if (!notification.is_read) {
          unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
        notifications.value.splice(index, 1);
      }

      return true;
    } catch (error) {
      console.error('Failed to remove notification:', error);
      return false;
    }
  };

  /**
   * Обновить уведомления (загрузить заново)
   */
  const refresh = async () => {
    await Promise.all([
      fetchRecentNotifications(),
      fetchUnreadCount()
    ]);
  };

  // Автоматическая загрузка при монтировании, если включено
  if (autoLoad) {
    onMounted(() => {
      refresh();
    });
  }

  return {
    notifications,
    unreadCount,
    hasUnread,
    loading,
    markAsRead,
    markAllAsRead,
    remove,
    fetchRecentNotifications,
    fetchUnreadCount,
    refresh,
  };
}




