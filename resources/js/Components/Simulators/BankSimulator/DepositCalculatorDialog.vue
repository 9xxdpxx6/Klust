<template>
  <Dialog
    :visible="visible"
    modal
    header="Калькулятор вкладов"
    :style="{ width: '500px' }"
    @update:visible="$emit('update:visible', $event)"
    @hide="onClose"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Сумма вклада (руб.)
        </label>
        <InputNumber
          v-model="form.initial_amount"
          :min="1000"
          :max="10000000"
          :use-grouping="true"
          class="w-full"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Срок вклада (лет)
        </label>
        <InputNumber
          v-model="form.years"
          :min="1"
          :max="10"
          class="w-full"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Процентная ставка (% годовых)
        </label>
        <InputNumber
          v-model="form.annual_rate"
          :min="0"
          :max="100"
          :min-fraction-digits="2"
          :max-fraction-digits="2"
          class="w-full"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Период капитализации
        </label>
        <Select
          v-model="form.capitalization_periods"
          :options="capitalizationOptions"
          optionLabel="label"
          optionValue="value"
          class="w-full"
          placeholder="Выберите период капитализации"
        />
      </div>
      
      <Button
        label="Рассчитать"
        icon="pi pi-calculator"
        @click="calculate"
        :loading="isCalculating"
        class="w-full"
      />
      
      <div v-if="results" class="mt-6 p-4 bg-gray-50 rounded-lg space-y-2">
        <h4 class="font-semibold mb-3">Результаты расчета:</h4>
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Итоговая сумма вклада:</span>
          <span class="text-sm font-semibold">{{ formatCurrency(results.final_amount) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Доход:</span>
          <span class="text-sm font-semibold text-green-600">{{ formatCurrency(results.income) }}</span>
        </div>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref } from 'vue'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
import Select from 'primevue/select'
import Button from 'primevue/button'
import axios from 'axios'
import { route } from 'ziggy-js'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  sessionId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['update:visible', 'close'])

const form = ref({
  initial_amount: 100000,
  years: 3,
  annual_rate: 8.0,
  capitalization_periods: 12
})

const results = ref(null)
const isCalculating = ref(false)

const capitalizationOptions = [
  { label: 'Ежемесячно', value: 12 },
  { label: 'Ежеквартально', value: 4 },
  { label: 'Ежегодно', value: 1 }
]

const calculate = async () => {
  if (!form.value.initial_amount || !form.value.years || !form.value.annual_rate) {
    return
  }
  
  isCalculating.value = true
  try {
    const url = route('student.simulators.calculate-deposit', { session: props.sessionId })
    const response = await axios.post(url, {
      initial_amount: form.value.initial_amount,
      annual_rate: form.value.annual_rate,
      years: form.value.years,
      capitalization_periods: form.value.capitalization_periods
    })
    
    results.value = response.data
  } catch (error) {
    // Error handling - можно добавить уведомление пользователю
  } finally {
    isCalculating.value = false
  }
}

const formatCurrency = (value) => {
  if (!value) return '0 ₽'
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

const onClose = () => {
  emit('update:visible', false)
  emit('close')
}
</script>

<style scoped>
.space-y-4 > * + * {
  margin-top: 1rem;
}

.space-y-2 > * + * {
  margin-top: 0.5rem;
}
</style>
