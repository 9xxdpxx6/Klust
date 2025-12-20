import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { isRouteActive, routeExists } from '@/Utils/routes';

/**
 * Композабл для работы с навигацией
 */
export function useNavigation() {
  const page = usePage();
  
  const currentUrl = computed(() => page.url || window.location.pathname);
  
  const currentRoute = computed(() => {
    try {
      // Попытка получить имя роута из Ziggy (теперь это имя роута, а не URL)
      if (page.props.ziggy?.location) {
        const location = page.props.ziggy.location;
        // Если это имя роута (содержит точку), возвращаем его
        if (typeof location === 'string' && location.includes('.')) {
          return location;
        }
        // Иначе это может быть URL, используем его
        return location;
      }
      // Попытка получить имя роута через route().current()
      if (typeof route === 'function') {
        try {
          const router = route();
          if (router && typeof router.current === 'function') {
            const currentRouteName = router.current();
            if (currentRouteName) {
              return currentRouteName;
            }
          }
        } catch (e) {
          // Игнорируем ошибку
        }
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
      
      // Получаем текущее имя роута через Ziggy
      let currentRouteName = null;
      
      // Специальные исключения: роуты, которые не должны подсвечиваться одновременно
      // Это единственное место в проекте, где такие роуты на одном уровне
      const exclusiveRoutes = {
        'student.cases.my': ['student.cases.index'],
        'student.cases.index': ['student.cases.my'],
      };
      
      try {
        // Пытаемся получить имя роута через route().current()
        if (typeof route === 'function') {
          const router = route();
          if (router && typeof router.current === 'function') {
            currentRouteName = router.current();
          }
        }
      } catch (e) {
        // Игнорируем ошибку
      }
      
      // Fallback: пытаемся получить из props (теперь это имя роута)
      if (!currentRouteName && page.props.ziggy?.location) {
        const location = page.props.ziggy.location;
        // Если это имя роута (содержит точку), используем его
        if (typeof location === 'string' && location.includes('.')) {
          currentRouteName = location;
        }
      }
      
      // Если получили имя роута, проверяем по имени
      if (currentRouteName && typeof currentRouteName === 'string') {
        // Проверяем исключения: если текущий роут в списке исключений для этого пункта меню
        if (exclusiveRoutes[routeName] && exclusiveRoutes[routeName].includes(currentRouteName)) {
          return false;
        }
        
        // Точное совпадение - всегда активно
        if (currentRouteName === routeName) {
          return true;
        }
        
        // Если не точное совпадение, проверяем префикс только для дочерних роутов
        // Например: admin.cases.show должен подсвечивать admin.cases.index
        // Но student.cases.my НЕ должен подсвечивать student.cases.index (они на одном уровне)
        if (!exact) {
          const routeParts = routeName.split('.');
          const currentParts = currentRouteName.split('.');
          
          // Проверяем, что текущий роут является дочерним (имеет больше частей)
          // и начинается с префикса роута пункта меню
          if (currentParts.length > routeParts.length) {
            // Текущий роут должен начинаться с имени роута пункта меню
            if (currentRouteName.startsWith(routeName + '.')) {
              return true;
            }
          }
        }
      }
      
      // Fallback: проверка по URL
      if (typeof route === 'function') {
        try {
          const routeUrl = route(routeName);
          // Убираем домен и протокол из URL для сравнения
          const routePath = routeUrl.replace(/^https?:\/\/[^\/]+/, '').replace(/\/$/, '');
          const currentPath = currentUrl.value.replace(/^https?:\/\/[^\/]+/, '').replace(/\/$/, '');
          
          // Проверяем исключения по URL для student.cases.my и student.cases.index
          // /student/cases/my не должен подсвечиваться когда мы на /student/cases
          if (routeName === 'student.cases.my' && currentPath === '/student/cases') {
            return false;
          }
          if (routeName === 'student.cases.index' && currentPath === '/student/cases/my') {
            return false;
          }
          
          if (exact) {
            return currentPath === routePath;
          }
          
          // Точное совпадение
          if (currentPath === routePath) {
            return true;
          }
          
          // Проверяем, что текущий путь является дочерним (начинается с пути роута + /)
          // Это позволяет подсвечивать родительский раздел для вложенных страниц
          // Например: /admin/cases/1 подсветит /admin/cases
          // Но /admin/cases/my НЕ подсветит /admin/cases/index (если они разные роуты)
          if (currentPath.startsWith(routePath + '/')) {
            return true;
          }
        } catch (e) {
          // Игнорируем ошибку
        }
      }
      
      // Если ничего не сработало, возвращаем false
      return false;
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

