<template>
  <div :class="[
    'grid gap-4',
    gridColsClass,
    props.class
  ]">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useResponsive } from '@/Composables/useResponsive';

const props = defineProps({
  cols: {
    type: Object,
    default: () => ({
      mobile: 1,
      tablet: 2,
      desktop: 3,
      large: 4,
    }),
  },
  class: {
    type: String,
    default: '',
  },
});

const { isMobile, isTablet, isDesktop, isLargeDesktop } = useResponsive();

// Маппинг значений на статические классы Tailwind
const gridColsMap = {
  1: 'grid-cols-1',
  2: 'grid-cols-2',
  3: 'grid-cols-3',
  4: 'grid-cols-4',
  5: 'grid-cols-5',
  6: 'grid-cols-6',
  7: 'grid-cols-7',
  8: 'grid-cols-8',
  9: 'grid-cols-9',
  10: 'grid-cols-10',
  11: 'grid-cols-11',
  12: 'grid-cols-12',
};

const gridColsClass = computed(() => {
  if (isLargeDesktop.value) {
    const cols = props.cols.large || props.cols.desktop;
    return gridColsMap[cols] || `grid-cols-${cols}`;
  }
  if (isDesktop.value) {
    const cols = props.cols.desktop;
    return gridColsMap[cols] || `grid-cols-${cols}`;
  }
  if (isTablet.value) {
    const cols = props.cols.tablet;
    return gridColsMap[cols] || `grid-cols-${cols}`;
  }
  const cols = props.cols.mobile;
  return gridColsMap[cols] || `grid-cols-${cols}`;
});
</script>

