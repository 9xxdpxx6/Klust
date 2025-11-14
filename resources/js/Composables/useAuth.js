import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Композабл для работы с аутентификацией
 */
export function useAuth() {
  const page = usePage();
  
  const user = computed(() => page.props.auth?.user || null);
  
  const isAuthenticated = computed(() => !!user.value);
  
  const roles = computed(() => user.value?.roles || []);
  
  /**
   * Проверка наличия роли
   * @param {string|Array} roleNames - Роль или массив ролей
   * @returns {boolean}
   */
  const hasRole = (roleNames) => {
    if (!user.value) return false;
    
    const userRoles = roles.value;
    if (Array.isArray(roleNames)) {
      return roleNames.some(role => userRoles.includes(role));
    }
    return userRoles.includes(roleNames);
  };
  
  /**
   * Проверка наличия любой из ролей
   * @param {Array} roleNames - Массив ролей
   * @returns {boolean}
   */
  const hasAnyRole = (roleNames) => {
    return hasRole(roleNames);
  };
  
  /**
   * Проверка наличия всех ролей
   * @param {Array} roleNames - Массив ролей
   * @returns {boolean}
   */
  const hasAllRoles = (roleNames) => {
    if (!user.value) return false;
    
    const userRoles = roles.value;
    return roleNames.every(role => userRoles.includes(role));
  };
  
  return {
    user,
    isAuthenticated,
    roles,
    hasRole,
    hasAnyRole,
    hasAllRoles,
  };
}




