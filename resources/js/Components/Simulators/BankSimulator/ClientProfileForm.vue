<template>
  <div class="client-profile-form">
    <h3 class="text-lg font-semibold mb-4">Данные клиента</h3>
    
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
        <InputText 
          v-model="clientName" 
          :disabled="true"
          class="w-full"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Возраст</label>
        <InputNumber 
          v-model="clientAge" 
          :min="18"
          :max="100"
          :disabled="!editable"
          class="w-full"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Доход (руб.)</label>
        <InputNumber 
          v-model="clientIncome" 
          :min="0"
          :disabled="!editable"
          class="w-full"
          :use-grouping="true"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Расходы (руб.)</label>
        <InputNumber 
          v-model="clientExpenses" 
          :min="0"
          :disabled="!editable"
          class="w-full"
          :use-grouping="true"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Кредитная история</label>
        <Select 
          v-model="clientCreditHistory" 
          :options="creditHistoryOptions"
          optionLabel="label"
          optionValue="value"
          :disabled="!editable"
          class="w-full"
          placeholder="Выберите кредитную историю"
        />
      </div>
      
      <div class="flex items-center">
        <Checkbox 
          v-model="clientHasDeposit" 
          :disabled="!editable"
          inputId="hasDeposit"
        />
        <label for="hasDeposit" class="ml-2 text-sm text-gray-700">
          Есть вклад в банке
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Checkbox from 'primevue/checkbox'

const props = defineProps({
  client: {
    type: Object,
    required: true
  },
  editable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:client'])

const clientName = computed({
  get: () => props.client?.name || '',
  set: (value) => updateClient('name', value)
})

const clientAge = computed({
  get: () => props.client?.age || 0,
  set: (value) => updateClient('age', value)
})

const clientIncome = computed({
  get: () => props.client?.income || 0,
  set: (value) => updateClient('income', value)
})

const clientExpenses = computed({
  get: () => props.client?.expenses || 0,
  set: (value) => updateClient('expenses', value)
})

const clientCreditHistory = computed({
  get: () => props.client?.credit_history || 'none',
  set: (value) => updateClient('credit_history', value)
})

const clientHasDeposit = computed({
  get: () => props.client?.has_deposit || false,
  set: (value) => updateClient('has_deposit', value)
})

const creditHistoryOptions = [
  { label: 'Отличная', value: 'excellent' },
  { label: 'Хорошая', value: 'good' },
  { label: 'Средняя', value: 'fair' },
  { label: 'Плохая', value: 'poor' },
  { label: 'Нет кредитной истории', value: 'none' }
]

const updateClient = (field, value) => {
  emit('update:client', {
    ...props.client,
    [field]: value
  })
}
</script>

<style scoped>
.client-profile-form {
  padding: 1rem;
}
</style>
