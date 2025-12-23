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

const gridColsClass = computed(() => {
  if (isLargeDesktop.value) {
    return `grid-cols-${props.cols.large || props.cols.desktop}`;
  }
  if (isDesktop.value) {
    return `grid-cols-${props.cols.desktop}`;
  }
  if (isTablet.value) {
    return `grid-cols-${props.cols.tablet}`;
  }
  return `grid-cols-${props.cols.mobile}`;
});
</script>

