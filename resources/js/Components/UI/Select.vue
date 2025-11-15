<template>
  <div class="select-wrapper">
    <label
      v-if="label"
      :for="selectId"
      :class="['block text-sm font-medium mb-1', { 'text-red-600': error }]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <PrimeSelect
      :id="selectId"
      :modelValue="modelValue"
      :options="options"
      :optionLabel="optionLabel"
      :optionValue="optionValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :multiple="multiple"
      :filter="searchable"
      :class="[
        'w-full',
        {
          'p-invalid': error,
        }
      ]"
      @update:modelValue="$emit('update:modelValue', $event)"
      @change="$emit('change', $event)"
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
import PrimeSelect from 'primevue/select';

const props = defineProps({
  modelValue: {
    type: [String, Number, Array, Object],
    default: null,
  },
  options: {
    type: Array,
    default: () => [],
  },
  optionLabel: {
    type: [String, Function],
    default: 'label',
  },
  optionValue: {
    type: [String, Function],
    default: 'value',
  },
  label: {
    type: String,
    default: null,
  },
  placeholder: {
    type: String,
    default: 'Выберите...',
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
  multiple: {
    type: Boolean,
    default: false,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
});

defineEmits(['update:modelValue', 'change']);

const selectId = computed(() => `select-${Math.random().toString(36).substr(2, 9)}`);
</script>

<style scoped>
:deep(.p-select) {
  @apply w-full focus:outline-none;
}

:deep(.p-select.p-invalid) {
  @apply border-red-500;
}

:deep(.p-select:focus) {
  @apply outline-none;
}
</style>


