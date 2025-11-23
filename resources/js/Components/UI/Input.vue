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
    
    <PrimeIconField v-if="leftIcon || rightIcon">
      <PrimeInputIcon v-if="leftIcon" :class="leftIcon" />
      <PrimeInputText
        :id="inputId"
        :type="type === 'email' ? 'text' : type"
        :modelValue="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :invalid="!!error"
        class="w-full"
        @update:modelValue="$emit('update:modelValue', $event)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      <PrimeInputIcon v-if="rightIcon" :class="rightIcon" />
    </PrimeIconField>
    
    <PrimeInputText
      v-else
      :id="inputId"
      :type="type === 'email' ? 'text' : type"
      :modelValue="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
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
import PrimeInputText from 'primevue/inputtext';
import PrimeIconField from 'primevue/iconfield';
import PrimeInputIcon from 'primevue/inputicon';

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
:deep(.p-inputtext) {
  @apply w-full focus:outline-none;
}

:deep(.p-inputtext.p-invalid) {
  @apply border-red-500;
}

/* Стили для IconField - правильный padding для иконки */
:deep(.p-icon-field) {
  @apply w-full;
}

:deep(.p-icon-field-left .p-inputtext) {
  @apply pl-10;
}

:deep(.p-icon-field-right .p-inputtext) {
  @apply pr-10;
}

:deep(.p-input-icon) {
  @apply text-gray-400;
}
</style>


