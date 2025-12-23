<template>
  <div :class="[
    'rounded-xl shadow-md border border-gray-200 overflow-hidden',
    paddingClass,
    props.class
  ]">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useResponsive } from '@/Composables/useResponsive';

const props = defineProps({
  padding: {
    type: Object,
    default: () => ({
      mobile: 'p-4',
      tablet: 'p-5',
      desktop: 'p-6',
    }),
  },
  class: {
    type: String,
    default: '',
  },
});

const { isMobile, isTablet, isDesktop } = useResponsive();

const paddingClass = computed(() => {
  if (isDesktop.value) return props.padding.desktop;
  if (isTablet.value) return props.padding.tablet;
  return props.padding.mobile;
});
</script>

