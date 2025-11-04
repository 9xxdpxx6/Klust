<template>
  <div class="checkbox-wrapper" :class="{ 'checkbox-disabled': disabled }">
    <div class="flex items-center">
      <input
        :id="checkboxId"
        :checked="modelValue"
        :disabled="disabled"
        type="checkbox"
        :class="['checkbox', { 'checkbox-error': error }]"
        @change="$emit('update:modelValue', $event.target.checked)"
      />
      <label
        v-if="label"
        :for="checkboxId"
        :class="['ml-2 text-sm', { 'text-red-600': error, 'cursor-not-allowed opacity-60': disabled }]"
      >
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
      </label>
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: null,
  },
  error: {
    type: String,
    default: null,
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['update:modelValue']);

const checkboxId = computed(() => `checkbox-${Math.random().toString(36).substr(2, 9)}`);
</script>

<style scoped>
.checkbox {
  @apply h-4 w-4 text-primary focus:ring-primary border-border rounded cursor-pointer;
}

.checkbox-error {
  @apply border-red-500;
}

.checkbox-disabled {
  @apply opacity-60 cursor-not-allowed;
}
</style>


