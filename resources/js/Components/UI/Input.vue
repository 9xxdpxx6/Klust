<template>
  <div class="input-wrapper">
    <label
      v-if="label"
      :for="inputId"
      :class="['block text-sm font-medium mb-1', { 'text-red-600': error }]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <i
        v-if="leftIcon"
        :class="['absolute left-3 top-1/2 transform -translate-y-1/2 text-text-muted', leftIcon]"
      />
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="[
          'input',
          {
            'input-error': error,
            'input-disabled': disabled,
            'pl-10': leftIcon,
            'pr-10': rightIcon,
          }
        ]"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      <i
        v-if="rightIcon"
        :class="['absolute right-3 top-1/2 transform -translate-y-1/2 text-text-muted', rightIcon]"
      />
    </div>
    
    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1 text-sm text-text-muted">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  label: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: null,
  },
  error: {
    type: String,
    default: null,
  },
  hint: {
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
  leftIcon: {
    type: String,
    default: null,
  },
  rightIcon: {
    type: String,
    default: null,
  },
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const inputId = computed(() => `input-${Math.random().toString(36).substr(2, 9)}`);
</script>

<style scoped>
.input {
  /* Базовые стили наследуются от глобальных стилей input */
  @apply sm:text-sm;
}

.input-error {
  @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}

.input-disabled {
  @apply bg-surface cursor-not-allowed opacity-60;
}
</style>


