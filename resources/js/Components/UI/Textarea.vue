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
    
    <PrimeTextarea
      :id="textareaId"
      :modelValue="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :rows="rows"
      :invalid="!!error"
      class="w-full"
      @update:modelValue="$emit('update:modelValue', $event)"
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
import PrimeTextarea from 'primevue/textarea';

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
:deep(.p-inputtextarea) {
  @apply w-full focus:outline-none;
  /* Textarea имеет свою высоту из-за rows, но min-height для первой строки */
  min-height: 2.5rem;
}

:deep(.p-inputtextarea.p-invalid) {
  @apply border-red-500;
}
</style>


