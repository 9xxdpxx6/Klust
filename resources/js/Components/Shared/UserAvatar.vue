<template>
  <component
    :is="link ? 'a' : 'div'"
    :href="link"
    :class="[
      'user-avatar',
      `user-avatar-${size}`,
      { 'cursor-pointer': link }
    ]"
  >
    <img
      v-if="user?.avatar"
      :src="user.avatar"
      :alt="userName"
      class="w-full h-full object-cover"
    />
    <span v-else class="flex items-center justify-center w-full h-full">
      {{ initials }}
    </span>
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  link: {
    type: [String, Boolean],
    default: false,
  },
});

const userName = computed(() => {
  return props.user?.name || 'Пользователь';
});

const initials = computed(() => {
  if (!props.user?.name) return 'П';
  
  const names = props.user.name.trim().split(/\s+/);
  if (names.length >= 2) {
    return (names[0][0] + names[names.length - 1][0]).toUpperCase();
  }
  return names[0][0].toUpperCase();
});
</script>

<style scoped>
@import '@/Styles/components.css';
</style>




