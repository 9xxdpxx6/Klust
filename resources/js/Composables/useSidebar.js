import { ref, computed, watch } from 'vue';
import { useMediaQuery, useToggle } from '@vueuse/core';

/**
 * Композабл для управления состоянием sidebar
 */
export function useSidebar(initialCollapsed = false) {
  const isCollapsed = ref(initialCollapsed);
  const [isMobileOpen, toggleMobile] = useToggle(false);
  
  // Определение мобильного устройства
  const isMobile = useMediaQuery('(max-width: 1024px)');
  const isDesktop = useMediaQuery('(min-width: 1024px)');
  
  // Автоматическое закрытие на мобильных при переключении на десктоп
  watch(isDesktop, (value) => {
    if (value && isMobileOpen.value) {
      toggleMobile();
    }
  });
  
  const toggleCollapse = () => {
    if (isMobile.value) {
      toggleMobile();
    } else {
      isCollapsed.value = !isCollapsed.value;
    }
  };
  
  const open = () => {
    if (isMobile.value) {
      if (!isMobileOpen.value) {
        toggleMobile();
      }
    } else {
      isCollapsed.value = false;
    }
  };
  
  const close = () => {
    if (isMobile.value) {
      if (isMobileOpen.value) {
        toggleMobile();
      }
    } else {
      isCollapsed.value = true;
    }
  };
  
  const isOpen = computed(() => {
    if (isMobile.value) {
      return isMobileOpen.value;
    }
    return !isCollapsed.value;
  });
  
  return {
    isCollapsed,
    isMobileOpen,
    isMobile,
    isDesktop,
    isOpen,
    toggleCollapse,
    open,
    close,
    toggleMobile,
  };
}


