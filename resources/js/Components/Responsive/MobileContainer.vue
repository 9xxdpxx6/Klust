<template>
  <div :class="[
    'w-full mx-auto',
    containerClass,
    props.class
  ]">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useResponsive } from '@/Composables/useResponsive';

const props = defineProps({
  maxWidth: {
    type: String,
    default: '7xl', // Tailwind max-width
  },
  padding: {
    type: Boolean,
    default: true,
  },
  class: {
    type: String,
    default: '',
  },
});

const { isMobile, paddingX } = useResponsive();

const containerClass = computed(() => {
  const classes = [`max-w-${props.maxWidth}`];
  if (props.padding) {
    classes.push(isMobile.value ? 'px-4' : 'px-6');
  }
  return classes.join(' ');
});
</script>

