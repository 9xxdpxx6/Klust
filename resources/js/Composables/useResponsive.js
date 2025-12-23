import { computed } from 'vue';
import { useMediaQuery } from '@vueuse/core';

/**
 * Универсальный composable для адаптивности
 * Использует breakpoints согласно Tailwind CSS
 */
export function useResponsive() {
  // Breakpoints (соответствуют Tailwind)
  // Mobile: < 768px (до md)
  // Tablet: 768px - 1023px (md до lg)
  // Desktop: ≥ 1024px (lg+)
  // Large Desktop: ≥ 1280px (xl+)
  
  const isMobile = useMediaQuery('(max-width: 767px)');
  const isTablet = useMediaQuery('(min-width: 768px) and (max-width: 1023px)');
  const isDesktop = useMediaQuery('(min-width: 1024px)');
  const isLargeDesktop = useMediaQuery('(min-width: 1280px)');
  
  // Композитные состояния
  const isMobileOrTablet = computed(() => isMobile.value || isTablet.value);
  const isTabletOrDesktop = computed(() => isTablet.value || isDesktop.value);
  
  // Утилиты для классов
  const gridCols = computed(() => {
    if (isMobile.value) return 'grid-cols-1';
    if (isTablet.value) return 'grid-cols-2';
    if (isDesktop.value) return 'grid-cols-3';
    return 'grid-cols-4';
  });
  
  const padding = computed(() => {
    if (isMobile.value) return 'p-4';
    if (isTablet.value) return 'p-5';
    return 'p-6';
  });
  
  const paddingX = computed(() => {
    if (isMobile.value) return 'px-4';
    if (isTablet.value) return 'px-5';
    return 'px-6';
  });
  
  const paddingY = computed(() => {
    if (isMobile.value) return 'py-4';
    if (isTablet.value) return 'py-5';
    return 'py-6';
  });
  
  return {
    isMobile,
    isTablet,
    isDesktop,
    isLargeDesktop,
    isMobileOrTablet,
    isTabletOrDesktop,
    gridCols,
    padding,
    paddingX,
    paddingY,
  };
}

