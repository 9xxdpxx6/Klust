<template>
  <div class="date-picker-wrapper">
    <label
      v-if="label"
      :for="datePickerId"
      :class="['block text-sm font-medium mb-1', { 'text-red-600': error }]"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <DatePicker
      :id="datePickerId"
      :modelValue="modelValue"
      :dateFormat="dateFormat"
      :placeholder="placeholder || defaultPlaceholder"
      :showIcon="showIcon"
      :inputClass="inputClass"
      :minDate="minDate"
      :maxDate="maxDate"
      :disabled="disabled"
      :required="required"
      :class="[
        {
          'p-invalid': error,
        }
      ]"
      @update:modelValue="$emit('update:modelValue', $event)"
      @date-select="$emit('date-select', $event)"
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
import DatePicker from 'primevue/datepicker';
import { useCalendar } from '@/Composables/useCalendar';

const props = defineProps({
  modelValue: {
    type: [Date, String, null],
    default: null,
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
  minDate: {
    type: Date,
    default: null,
  },
  maxDate: {
    type: Date,
    default: null,
  },
});

defineEmits(['update:modelValue', 'date-select', 'blur', 'focus']);

const calendarConfig = useCalendar();
const { dateFormat, defaultPlaceholder, showIcon, inputClass } = calendarConfig;

const datePickerId = computed(() => `datepicker-${Math.random().toString(36).substr(2, 9)}`);
</script>

<style scoped>
/* Стили применяются глобально через components.css */
</style>

