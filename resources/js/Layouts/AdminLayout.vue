<template>
  <ClientLayoutWithSidebar
    role="admin"
    title="Админ панель"
    :show-footer="false"
    :header-props="{
      logoText: 'КубГТУ',
      logoImage: '/images/logo/logo.png',
      showSearch: true,
      showNotifications: true,
    }"
  >
    <slot />
  </ClientLayoutWithSidebar>
</template>

<script setup>
import ClientLayoutWithSidebar from './ClientLayoutWithSidebar.vue';
</script>

