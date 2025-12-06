<template>
  <ClientLayoutWithSidebar
    role="partner"
    title="Партнер"
    :show-footer="false"
    :header-props="{
      logoLink: route('guest.home'),
      logoImage: '/images/logo/logo.png',
      showSearch: false,
      showNotifications: false,
    }"
  >
    <template #header-indicators>
      <slot name="header-indicators" />
    </template>
    <slot />
  </ClientLayoutWithSidebar>
</template>

<script setup>
import ClientLayoutWithSidebar from './ClientLayoutWithSidebar.vue';
</script>

