<template>
  <Transition name="variant-selector">
    <div v-if="visible" class="variant-selector-overlay" @click.self="$emit('close')">
      <div class="variant-selector-panel">
        <div class="variant-selector-header">
          <h2 class="variant-selector-title">Выберите сценарий</h2>
          <p class="variant-selector-subtitle">
            Пройдите все сценарии для полного завершения симулятора
          </p>
          <button class="variant-close-btn" @click="$emit('close')" title="Закрыть">
            <i class="pi pi-times"></i>
          </button>
        </div>

        <div class="variant-grid">
          <button
            v-for="variant in variants"
            :key="variant.key"
            class="variant-card"
            :class="getVariantCardClass(variant)"
            @click="selectVariant(variant.key)"
          >
            <div class="variant-icon">{{ variant.icon }}</div>
            <div class="variant-info">
              <h3 class="variant-name">{{ variant.name }}</h3>
              <p class="variant-description">{{ variant.description }}</p>
            </div>
            <div class="variant-status">
              <template v-if="variant.progress?.status === 'completed'">
                <span class="status-badge" :class="getStatusBadgeClass(variant.progress.normalized_score)">
                  <i class="pi pi-check-circle"></i>
                  Пройден
                </span>
                <span class="variant-score" :class="getScoreClass(variant.progress.normalized_score)">
                  {{ variant.progress.normalized_score }}%
                </span>
              </template>
              <template v-else-if="variant.progress?.status === 'in_progress'">
                <span class="status-badge status-in-progress">
                  <i class="pi pi-play-circle"></i>
                  В процессе
                </span>
              </template>
              <template v-else>
                <span class="status-badge status-not-started">
                  Не пройден
                </span>
              </template>
            </div>
          </button>
        </div>

        <!-- Progress summary -->
        <div class="variant-summary">
          <div class="summary-bar">
            <div
              class="summary-fill"
              :style="{ width: completionPercent + '%' }"
            ></div>
          </div>
          <span class="summary-text">
            Пройдено: {{ completedCount }} / {{ variants.length }}
          </span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue'

const VARIANT_CONFIG = [
  {
    key: 'credit_card',
    name: 'Кредитная карта',
    description: 'Консультация клиента по оформлению кредитной карты',
    icon: '💳'
  },
  {
    key: 'mortgage',
    name: 'Ипотека',
    description: 'Консультация по ипотечному кредитованию',
    icon: '🏠'
  },
  {
    key: 'consumer_loan',
    name: 'Потребительский кредит',
    description: 'Оформление потребительского или авто кредита',
    icon: '🚗'
  },
  {
    key: 'deposit',
    name: 'Вклад',
    description: 'Консультация по открытию вклада',
    icon: '💰'
  }
]

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  variantsProgress: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['select', 'close'])

const variants = computed(() => {
  return VARIANT_CONFIG.map(v => ({
    ...v,
    progress: props.variantsProgress?.[v.key] || null
  }))
})

const completedCount = computed(() => {
  return variants.value.filter(v => v.progress?.status === 'completed').length
})

const completionPercent = computed(() => {
  return Math.round((completedCount.value / variants.value.length) * 100)
})

const getScoreClass = (score) => {
  if (score >= 80) return 'score-excellent'
  if (score >= 60) return 'score-good'
  if (score >= 40) return 'score-average'
  return 'score-poor'
}

/**
 * Returns card-level CSS class based on variant status and score.
 * Completed cards use score-based colors matching the "Оценка" display.
 */
const getVariantCardClass = (variant) => {
  if (variant.progress?.status === 'completed') {
    const score = variant.progress?.normalized_score ?? 0
    if (score >= 80) return 'variant-completed variant-grade-excellent'
    if (score >= 60) return 'variant-completed variant-grade-good'
    if (score >= 40) return 'variant-completed variant-grade-average'
    return 'variant-completed variant-grade-poor'
  }
  if (variant.progress?.status === 'in_progress') {
    return 'variant-in-progress'
  }
  return ''
}

/**
 * Returns status badge class based on score (same palette as "Оценка").
 */
const getStatusBadgeClass = (score) => {
  if (score >= 80) return 'status-grade-excellent'
  if (score >= 60) return 'status-grade-good'
  if (score >= 40) return 'status-grade-average'
  return 'status-grade-poor'
}

const selectVariant = (key) => {
  emit('select', key)
}
</script>

<style scoped>
.variant-selector-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.variant-selector-panel {
  background: #fff;
  border-radius: 1rem;
  padding: 1.5rem;
  width: 90%;
  max-width: 640px;
  max-height: 90%;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
  position: relative;
}

.variant-selector-header {
  text-align: center;
  margin-bottom: 1.25rem;
  position: relative;
}

.variant-selector-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.25rem;
}

.variant-selector-subtitle {
  font-size: 0.8rem;
  color: #64748b;
  margin: 0;
}

.variant-close-btn {
  position: absolute;
  top: -0.25rem;
  right: -0.25rem;
  width: 2rem;
  height: 2rem;
  border: none;
  background: #f1f5f9;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
  color: #64748b;
  font-size: 0.85rem;
}

.variant-close-btn:hover {
  background: #e2e8f0;
  color: #334155;
}

.variant-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.variant-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.5rem;
  padding: 1rem 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  overflow: hidden;
}

.variant-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent 60%, rgba(59, 130, 246, 0.04));
  pointer-events: none;
}

.variant-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
  transform: translateY(-2px);
}

/* Completed: score-based colors matching "Оценка" in DialogueInterface */
.variant-card.variant-grade-excellent {
  border-color: #10b981;
  background: #ecfdf5;
}
.variant-card.variant-grade-excellent:hover {
  border-color: #059669;
  box-shadow: 0 4px 16px rgba(16, 185, 129, 0.15);
}

.variant-card.variant-grade-good {
  border-color: #3b82f6;
  background: #eff6ff;
}
.variant-card.variant-grade-good:hover {
  border-color: #2563eb;
  box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
}

.variant-card.variant-grade-average {
  border-color: #f59e0b;
  background: #fffbeb;
}
.variant-card.variant-grade-average:hover {
  border-color: #d97706;
  box-shadow: 0 4px 16px rgba(245, 158, 11, 0.15);
}

.variant-card.variant-grade-poor {
  border-color: #ef4444;
  background: #fef2f2;
}
.variant-card.variant-grade-poor:hover {
  border-color: #dc2626;
  box-shadow: 0 4px 16px rgba(239, 68, 68, 0.15);
}

/* In progress: neutral slate/blue-gray (не путается с оценкой) */
.variant-card.variant-in-progress {
  border-color: #94a3b8;
  background: #f8fafc;
}

.variant-card.variant-in-progress:hover {
  border-color: #64748b;
  box-shadow: 0 4px 16px rgba(100, 116, 139, 0.15);
}

.variant-icon {
  font-size: 2rem;
  line-height: 1;
}

.variant-info {
  flex: 1;
}

.variant-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.15rem;
}

.variant-description {
  font-size: 0.7rem;
  color: #64748b;
  margin: 0;
  line-height: 1.3;
}

.variant-status {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
}

.status-badge {
  font-size: 0.7rem;
  font-weight: 500;
  padding: 0.15rem 0.5rem;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Score-based status badges (same palette as "Оценка") */
.status-grade-excellent {
  background: #d1fae5;
  color: #065f46;
}
.status-grade-good {
  background: #dbeafe;
  color: #1e40af;
}
.status-grade-average {
  background: #fef3c7;
  color: #92400e;
}
.status-grade-poor {
  background: #fee2e2;
  color: #991b1b;
}

.status-in-progress {
  background: #f1f5f9;
  color: #475569;
}

.status-not-started {
  background: #f1f5f9;
  color: #64748b;
}

.variant-score {
  font-size: 0.85rem;
  font-weight: 700;
}

.score-excellent { color: #16a34a; }
.score-good { color: #2563eb; }
.score-average { color: #d97706; }
.score-poor { color: #dc2626; }

.variant-summary {
  margin-top: 1rem;
  text-align: center;
}

.summary-bar {
  width: 100%;
  height: 6px;
  background: #e2e8f0;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.summary-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #22c55e);
  border-radius: 3px;
  transition: width 0.5s ease;
}

.summary-text {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
}

/* Transitions */
.variant-selector-enter-active {
  transition: opacity 0.25s ease;
}
.variant-selector-enter-active .variant-selector-panel {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.variant-selector-leave-active {
  transition: opacity 0.2s ease;
}
.variant-selector-leave-active .variant-selector-panel {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.variant-selector-enter-from {
  opacity: 0;
}
.variant-selector-enter-from .variant-selector-panel {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}
.variant-selector-leave-to {
  opacity: 0;
}
.variant-selector-leave-to .variant-selector-panel {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}
</style>
