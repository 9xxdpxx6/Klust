<template>
  <div class="search-input-wrapper">
    <label
      v-if="label"
      :for="inputId"
      :class="['block text-sm font-medium mb-1', { 'text-red-600': error }]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <PrimeIconField>
      <PrimeInputIcon class="pi pi-search" />
      <PrimeInputText
        :id="inputId"
        :modelValue="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :invalid="!!error"
        class="w-full"
        @update:modelValue="handleInput"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
    </PrimeIconField>
    
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
  label: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: 'Поиск...',
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
  debounce: {
    type: Number,
    default: 500,
  },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'input']);

const inputId = computed(() => `search-input-${Math.random().toString(36).substr(2, 9)}`);

let debounceTimeout = null;

const handleInput = (value) => {
  emit('update:modelValue', value);
  
  if (props.debounce > 0) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
      emit('input', value);
    }, props.debounce);
  } else {
    emit('input', value);
  }
};
</script>

<style scoped>
:deep(.p-inputtext) {
  @apply w-full focus:outline-none;
}

:deep(.p-inputtext.p-invalid) {
  @apply border-red-500;
}

/* Стили для IconField - правильный padding для иконки поиска */
:deep(.p-icon-field) {
  @apply w-full;
}

:deep(.p-icon-field-left .p-inputtext) {
  @apply pl-10;
}

:deep(.p-input-icon) {
  @apply text-gray-400;
}
</style>

