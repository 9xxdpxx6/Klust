<template>
  <ClientLayoutWithSidebar
    role="admin"
    title="Админ панель"
    :show-footer="false"
    :header-props="{
      logoLink: route('guest.home'),
      logoText: 'КубГТУ',
      logoImage: '/images/logo/logo.png',
      showNotifications: true,
    }"
  >
    <slot />
  </ClientLayoutWithSidebar>
</template>

<script setup>
import ClientLayoutWithSidebar from './ClientLayoutWithSidebar.vue';
</script>

