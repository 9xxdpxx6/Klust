import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { isRouteActive, routeExists } from '@/Utils/routes';

/**
 * Композабл для работы с навигацией
 */
export function useNavigation() {
  const page = usePage();
  
  const currentUrl = computed(() => page.url || window.location.pathname);
  
  const currentRoute = computed(() => {
    try {
      // Попытка получить имя роута из Ziggy
      if (page.props.ziggy?.location) {
        return page.props.ziggy.location;
      }
      return currentUrl.value;
    } catch (e) {
      return currentUrl.value;
    }
  });
  
  /**
   * Проверка активности пункта меню
   * @param {string} routeName - Имя роута
   * @param {boolean} exact - Точное совпадение
   * @returns {boolean}
   */
  const isActive = (routeName, exact = false) => {
    try {
      // Проверяем существование роута перед использованием
      if (!routeExists(routeName)) {
        return false;
      }
      
      if (typeof route === 'function') {
        const routeUrl = route(routeName);
        if (exact) {
          return currentUrl.value === routeUrl;
        }
        return currentUrl.value === routeUrl || currentUrl.value.startsWith(routeUrl + '/');
      }
      
      // Fallback: проверка по имени роута из URL
      return isRouteActive(routeName, currentUrl.value);
    } catch (e) {
      return false;
    }
  };
  
  /**
   * Проверка, начинается ли текущий URL с указанного пути
   * @param {string} path - Путь
   * @returns {boolean}
   */
  const isPathActive = (path) => {
    return currentUrl.value.startsWith(path);
  };
  
  return {
    currentUrl,
    currentRoute,
    isActive,
    isPathActive,
  };
}

