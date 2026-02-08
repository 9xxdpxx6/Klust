<template>
  <Dialog
    :visible="visible"
    modal
    header="Кредитный калькулятор"
    :style="{ width: '500px' }"
    @update:visible="$emit('update:visible', $event)"
    @hide="onClose"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Сумма кредита (руб.)
        </label>
        <InputNumber
          v-model="form.amount"
          :min="1000"
          :max="10000000"
          :use-grouping="true"
          class="w-full"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Срок кредита (месяцев)
        </label>
        <InputNumber
          v-model="form.months"
          :min="1"
          :max="120"
          class="w-full"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Процентная ставка (% годовых)
        </label>
        <InputNumber
          v-model="form.rate"
          :min="0"
          :max="100"
          :min-fraction-digits="2"
          :max-fraction-digits="2"
          class="w-full"
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
          <span class="text-sm text-gray-600">Ежемесячный платеж:</span>
          <span class="text-sm font-semibold">{{ formatCurrency(results.monthly_payment) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Общая сумма платежа:</span>
          <span class="text-sm font-semibold">{{ formatCurrency(results.total_payment) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Переплата:</span>
          <span class="text-sm font-semibold">{{ formatCurrency(results.overpayment) }}</span>
        </div>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
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
  },
  defaultRate: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:visible', 'close'])

const form = ref({
  amount: 500000,
  months: 60,
  rate: props.defaultRate || 15.0
})

const results = ref(null)
const isCalculating = ref(false)

watch(() => props.defaultRate, (newRate) => {
  if (newRate && !form.value.rate) {
    form.value.rate = newRate
  }
}, { immediate: true })

const calculate = async () => {
  if (!form.value.amount || !form.value.months || !form.value.rate) {
    return
  }
  
  isCalculating.value = true
  try {
    const url = route('student.simulators.calculate-credit', { session: props.sessionId })
    const response = await axios.post(url, {
      amount: form.value.amount,
      months: form.value.months,
      rate: form.value.rate
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
