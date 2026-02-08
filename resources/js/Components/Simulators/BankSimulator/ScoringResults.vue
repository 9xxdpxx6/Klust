<template>
  <div class="scoring-results">
    <h3 class="text-lg font-semibold mb-4">Результаты скоринга</h3>
    
    <div v-if="calculations?.credit_score !== undefined && calculations?.credit_score !== null" class="space-y-4">
      <!-- Круговой индикатор скоринга -->
      <div class="flex justify-center">
        <div class="relative w-32 h-32">
          <svg class="transform -rotate-90 w-32 h-32">
            <circle
              cx="64"
              cy="64"
              r="56"
              stroke="#e5e7eb"
              stroke-width="8"
              fill="none"
            />
            <circle
              cx="64"
              cy="64"
              r="56"
              :stroke="scoreColor"
              stroke-width="8"
              fill="none"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="circumference - (calculations.credit_score * circumference)"
              stroke-linecap="round"
              class="transition-all duration-500"
            />
          </svg>
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-2xl font-bold" :class="scoreTextColor">
              {{ Math.round(calculations.credit_score * 100) }}%
            </span>
          </div>
        </div>
      </div>
      
      <!-- Решение -->
      <div class="text-center">
        <div 
          class="inline-block px-4 py-2 rounded-full text-sm font-semibold"
          :class="decisionBadgeClass"
        >
          {{ decisionText }}
        </div>
      </div>
      
      <!-- Детали -->
      <div class="space-y-2">
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Процентная ставка:</span>
          <span class="text-sm font-semibold">{{ calculations.interest_rate }}%</span>
        </div>
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Кредитный лимит:</span>
          <span class="text-sm font-semibold">
            {{ formatCurrency(calculations.credit_limit) }}
          </span>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center text-gray-500 py-8">
      Данные для расчета скоринга отсутствуют
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  calculations: {
    type: Object,
    default: () => ({})
  }
})

const circumference = 2 * Math.PI * 56 // радиус 56

const scoreColor = computed(() => {
  const score = props.calculations?.credit_score || 0
  if (score >= 0.8) return '#10b981' // green
  if (score >= 0.5) return '#f59e0b' // yellow/amber
  if (score >= 0.3) return '#f97316' // orange
  return '#ef4444' // red
})

const scoreTextColor = computed(() => {
  const score = props.calculations?.credit_score || 0
  if (score >= 0.8) return 'text-green-600'
  if (score >= 0.5) return 'text-amber-600'
  if (score >= 0.3) return 'text-orange-600'
  return 'text-red-600'
})

const decisionText = computed(() => {
  const decision = props.calculations?.decision
  const decisions = {
    'auto_approve': 'Автоматическое одобрение',
    'approve_with_conditions': 'Одобрение с условиями',
    'manual_review': 'Ручная проверка',
    'auto_reject': 'Автоматический отказ'
  }
  return decisions[decision] || 'Не определено'
})

const decisionBadgeClass = computed(() => {
  const score = props.calculations?.credit_score || 0
  if (score >= 0.8) return 'bg-green-100 text-green-800'
  if (score >= 0.5) return 'bg-amber-100 text-amber-800'
  if (score >= 0.3) return 'bg-orange-100 text-orange-800'
  return 'bg-red-100 text-red-800'
})

const formatCurrency = (value) => {
  if (!value) return '0 ₽'
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}
</script>

<style scoped>
.scoring-results {
  padding: 1rem;
}
</style>
