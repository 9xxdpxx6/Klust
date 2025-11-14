<template>
  <div class="textarea-wrapper">
    <label
      v-if="label"
      :for="textareaId"
      :class="['block text-sm font-medium mb-1', { 'text-red-600': error }]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <textarea
      :id="textareaId"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :rows="rows"
      :class="[
        'textarea',
        {
          'textarea-error': error,
          'textarea-disabled': disabled,
        }
      ]"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur', $event)"
      @focus="$emit('focus', $event)"
    />
    
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
    type: String,
    default: '',
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
  rows: {
    type: Number,
    default: 3,
  },
});

defineEmits(['update:modelValue', 'blur', 'focus']);

const textareaId = computed(() => `textarea-${Math.random().toString(36).substr(2, 9)}`);
</script>

<style scoped>
.textarea {
  /* Базовые стили наследуются от глобальных стилей textarea */
  @apply sm:text-sm;
}

.textarea-error {
  @apply border-red-500 focus:ring-red-500 focus:border-red-500;
}

.textarea-disabled {
  @apply bg-surface cursor-not-allowed opacity-60;
}
</style>


