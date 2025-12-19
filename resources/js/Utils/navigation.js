/**
 * Конфигурация меню навигации для разных ролей
 */

/**
 * Меню для администратора
 * Показываем только существующие роуты
 */
export const adminMenuItems = [
  {
    label: 'Панель управления',
    route: 'admin.dashboard',
    icon: 'pi pi-home',
  },
  {
    label: 'Пользователи',
    route: 'admin.users.index',
    icon: 'pi pi-users',
  },
  {
    label: 'Кейсы',
    route: 'admin.cases.index',
    icon: 'pi pi-briefcase',
  },
  {
    label: 'Навыки',
    route: 'admin.skills.index',
    icon: 'pi pi-star',
  },
  {
    label: 'Достижения',
    route: 'admin.badges.index',
    icon: 'pi pi-trophy',
  },
  {
    label: 'Симуляторы',
    route: 'admin.simulators.index',
    icon: 'pi pi-desktop',
  },
];

/**
 * Меню для студента
 * Показываем только существующие роуты
 */
export const studentMenuItems = [
  {
    label: 'Панель студента',
    route: 'student.dashboard',
    icon: 'pi pi-home',
  },
  {
    label: 'Мои кейсы',
    route: 'student.cases.my',
    icon: 'pi pi-briefcase',
  },
  {
    label: 'Доступные кейсы',
    route: 'student.cases.index',
    icon: 'pi pi-search',
  },
  {
    label: 'Навыки',
    route: 'student.skills.index',
    icon: 'pi pi-star',
  },
  {
    label: 'Достижения',
    route: 'student.badges.index',
    icon: 'pi pi-trophy',
  },
  {
    label: 'Симуляторы',
    route: 'student.simulators.index',
    icon: 'pi pi-desktop',
  },
];

/**
 * Меню для партнера
 * Показываем только существующие роуты
 */
export const partnerMenuItems = [
  {
    label: 'Панель партнера',
    route: 'partner.dashboard',
    icon: 'pi pi-home',
  },
  {
    label: 'Кейсы',
    route: 'partner.cases.index',
    icon: 'pi pi-briefcase',
  },
  {
    label: 'Команды',
    route: 'partner.teams.index',
    icon: 'pi pi-users',
  },
  {
    label: 'Аналитика',
    route: 'partner.analytics.index',
    icon: 'pi pi-chart-bar',
  },
];

/**
 * Получить меню для роли
 * @param {string} role - Роль пользователя
 * @returns {Array} Массив пунктов меню
 */
export function getMenuItemsForRole(role) {
  switch (role) {
    case 'admin':
    case 'teacher':
      return adminMenuItems;
    case 'student':
      return studentMenuItems;
    case 'partner':
      return partnerMenuItems;
    default:
      return [];
  }
}

/**
 * Фильтрация меню по правам доступа
 * @param {Array} items - Пункты меню
 * @param {Array} userRoles - Роли пользователя
 * @returns {Array} Отфильтрованные пункты меню
 */
export function filterMenuByRoles(items, userRoles) {
  // Если нужна дополнительная фильтрация, можно добавить логику
  return items;
}

