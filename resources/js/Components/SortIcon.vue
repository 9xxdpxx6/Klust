<template>
    <svg
        v-if="field === sortField"
        :class="[
      'ml-1 h-4 w-4 transition-transform',
      direction === 'asc' ? 'rotate-0' : 'rotate-180'
    ]"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
    </svg>
    <svg
        v-else
        class="ml-1 h-4 w-4 text-gray-300"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
    </svg>
</template>

<script setup>
defineProps({
    field: String,
    direction: String,
    sortField: String
})
</script>
