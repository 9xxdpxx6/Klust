/**
 * Утилиты для работы с роутами Ziggy
 */

/**
 * Проверка существования роута
 * @param {string} routeName - Имя роута
 * @returns {boolean}
 */
export function routeExists(routeName) {
  try {
    // Проверяем через Ziggy routes из window
    if (typeof window !== 'undefined') {
      // Вариант 1: через window.Ziggy (если доступен)
      if (window.Ziggy && window.Ziggy.routes) {
        return routeName in window.Ziggy.routes;
      }
      
      // Вариант 2: через route() функцию (если доступна)
      // Нужно быть осторожным, т.к. route() может выбросить ошибку
      if (typeof route === 'function') {
        try {
          // Проверяем через try-catch, чтобы избежать ошибок
          const url = route(routeName);
          // Если роут не найден, Ziggy выбрасывает ошибку или возвращает '#'
          return url !== '#' && url !== '' && !url.includes('undefined') && !url.includes('null');
        } catch (e) {
          // Если Ziggy выбрасывает ошибку, роут не существует
          return false;
        }
      }
    }
    return false;
  } catch (e) {
    return false;
  }
}

/**
 * Безопасное получение URL роута
 * @param {string} routeName - Имя роута
 * @param {object} params - Параметры роута
 * @param {string} fallback - URL по умолчанию, если роут не найден
 * @returns {string}
 */
export function getRouteUrl(routeName, params = {}, fallback = '#') {
  try {
    if (typeof route === 'function') {
      // Проверяем существование роута перед использованием
      if (!routeExists(routeName)) {
        console.warn(`Route "${routeName}" not found, using fallback`);
        return fallback;
      }
      return route(routeName, params);
    }
    return fallback;
  } catch (e) {
    console.warn(`Route "${routeName}" not found:`, e.message);
    return fallback;
  }
}

/**
 * Проверка, является ли роут активным
 * @param {string} routeName - Имя роута
 * @param {string} currentUrl - Текущий URL
 * @returns {boolean}
 */
export function isRouteActive(routeName, currentUrl) {
  try {
    if (typeof route === 'function') {
      const routeUrl = route(routeName);
      return currentUrl === routeUrl || currentUrl.startsWith(routeUrl + '/');
    }
    return false;
  } catch (e) {
    return false;
  }
}

