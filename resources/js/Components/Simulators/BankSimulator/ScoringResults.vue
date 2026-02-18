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
        <div v-if="calculations.interest_rate != null" class="flex justify-between">
          <span class="text-sm text-gray-600">Процентная ставка:</span>
          <span class="text-sm font-semibold">{{ calculations.interest_rate }}%</span>
        </div>
        <div v-if="calculations.credit_limit != null" class="flex justify-between">
          <span class="text-sm text-gray-600">Кредитный лимит:</span>
          <span class="text-sm font-semibold">
            {{ formatCurrency(calculations.credit_limit) }}
          </span>
        </div>
        <div v-if="calculations.monthly_payment != null" class="flex justify-between">
          <span class="text-sm text-gray-600">Платёж/мес:</span>
          <span class="text-sm font-semibold">
            {{ formatCurrency(calculations.monthly_payment) }}
          </span>
        </div>
      </div>

      <!-- Условия одобрения (для решений с условиями) -->
      <div v-if="hasConditions" class="mt-6 pt-4 border-t border-gray-200">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Условия одобрения:</h4>
        <ul class="space-y-2">
          <li v-if="calculations.requires_insurance" class="flex items-start text-sm text-gray-700">
            <span class="text-amber-600 mr-2">•</span>
            <span>Обязательное оформление страховки кредита</span>
          </li>
          <li v-if="isLimitedLimit" class="flex items-start text-sm text-gray-700">
            <span class="text-amber-600 mr-2">•</span>
            <span>Установлен ограниченный кредитный лимит</span>
          </li>
          <li v-if="isElevatedRate" class="flex items-start text-sm text-gray-700">
            <span class="text-amber-600 mr-2">•</span>
            <span>Применена повышенная процентная ставка</span>
          </li>
          <li v-if="calculations.decision === 'manual_review'" class="flex items-start text-sm text-gray-700">
            <span class="text-amber-600 mr-2">•</span>
            <span>Требуется дополнительная проверка документов</span>
          </li>
          <li v-if="calculations.decision === 'approve_with_conditions' && !calculations.requires_insurance && !isLimitedLimit && !isElevatedRate" class="flex items-start text-sm text-gray-700">
            <span class="text-amber-600 mr-2">•</span>
            <span>Стандартные условия кредитования</span>
          </li>
        </ul>
      </div>

      <!-- Факторы оценки -->
      <div v-if="scoreFactors.length > 0" class="mt-4 pt-4 border-t border-gray-100">
        <div class="text-xs text-gray-500">
          <div class="font-medium text-gray-600 mb-1">Факторы оценки:</div>
          <ul class="space-y-0.5">
            <li v-for="(factor, index) in scoreFactors" :key="index" class="flex items-center">
              <span class="w-1.5 h-1.5 rounded-full mr-2" :class="factor.color"></span>
              <span>{{ factor.text }}</span>
            </li>
          </ul>
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

// Проверка наличия условий
const hasConditions = computed(() => {
  const decision = props.calculations?.decision
  return decision === 'approve_with_conditions' || decision === 'manual_review'
})

// Проверка ограниченного лимита
const isLimitedLimit = computed(() => {
  const multiplier = props.calculations?.limit_multiplier
  return multiplier != null && multiplier < 1.0
})

// Проверка повышенной ставки (выше 15%)
const isElevatedRate = computed(() => {
  const rate = props.calculations?.interest_rate
  return rate != null && rate > 15.0
})

// Факторы, влияющие на оценку
const scoreFactors = computed(() => {
  const factors = []
  const score = props.calculations?.credit_score || 0
  
  if (score >= 0.8) {
    factors.push({ text: 'Отличная кредитная история', color: 'bg-green-500' })
    factors.push({ text: 'Высокий доход', color: 'bg-green-500' })
  } else if (score >= 0.5) {
    factors.push({ text: 'Хорошая кредитная история', color: 'bg-amber-500' })
    if (score < 0.65) {
      factors.push({ text: 'Умеренный доход', color: 'bg-amber-500' })
    }
  } else if (score >= 0.3) {
    factors.push({ text: 'Средняя кредитная история', color: 'bg-orange-500' })
    factors.push({ text: 'Низкий доход или высокие расходы', color: 'bg-orange-500' })
  } else {
    factors.push({ text: 'Проблемы с кредитной историей', color: 'bg-red-500' })
    factors.push({ text: 'Недостаточный доход', color: 'bg-red-500' })
  }
  
  return factors
})
</script>

<style scoped>
.scoring-results {
  padding: 1rem;
}
</style>
