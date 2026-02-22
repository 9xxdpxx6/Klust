<template>
  <div class="bank-simulator-page" ref="containerRef">
    <Head :title="`Симулятор: ${simulator.title}`" />
    
    <div class="simulator-container" ref="sceneContainerRef">
      <!-- 3D сцена -->
      <OfficeScene 
        :session-state="state"
        :session-id="session.id"
        :update-state="updateState"
        :auto-save="autoSave"
        :is-loading="isLoading"
        @complete-simulator="handleCompleteSimulator"
      />

      <!-- Прелоадер сцены -->
      <Transition name="preloader-fade">
        <div v-if="!sceneReady" class="scene-preloader">
          <div class="preloader-content">
            <div class="preloader-spinner">
              <i class="pi pi-spin pi-cog preloader-icon"></i>
            </div>
            <p class="preloader-text">Загрузка сцены...</p>
          </div>
        </div>
      </Transition>
      
      <!-- UI панели -->
      <div class="ui-overlay" v-show="sceneReady">
        <!-- Кнопка информации (левый верхний угол) -->
        <button
          @click="showInfo = !showInfo"
          class="info-button"
          title="Информация"
          aria-label="Информация"
        >
          <i class="pi pi-info-circle"></i>
        </button>

        <!-- Панель информации -->
        <Transition name="info-slide">
          <div v-if="showInfo" class="info-panel">
            <div class="info-panel-header">
              <span class="info-panel-title">
                <i class="pi pi-info-circle"></i>
                Как пользоваться симулятором
              </span>
              <button class="info-close-btn" @click="showInfo = false">
                <i class="pi pi-times"></i>
              </button>
            </div>
            <div class="info-panel-body">
              <div class="info-section">
                <h4><i class="pi pi-play"></i> Начало работы</h4>
                <p>Нажмите на <strong>дверь</strong> в сцене, чтобы вызвать клиента. Выберите тип консультации из предложенных вариантов.</p>
              </div>
              <div class="info-section">
                <h4><i class="pi pi-comments"></i> Диалог с клиентом</h4>
                <p>Клиент зайдёт и сядет напротив вас. Откроется окно диалога — выбирайте ответы, задавайте вопросы и собирайте информацию.</p>
              </div>
              <div class="info-section">
                <h4><i class="pi pi-desktop"></i> Ноутбук</h4>
                <p>На экране ноутбука отображается информация о клиенте. Нажмите на ноутбук, чтобы вернуться к диалогу, если закрыли его.</p>
              </div>
              <div class="info-section">
                <h4><i class="pi pi-calculator"></i> Инструменты</h4>
                <p>В процессе диалога могут открываться калькуляторы для расчёта кредитов и депозитов. Используйте их для принятия решений.</p>
              </div>
              <div class="info-section">
                <h4><i class="pi pi-star"></i> Оценка</h4>
                <p>Ваши ответы оцениваются. В конце консультации вы получите итоговый балл. Старайтесь быть внимательным и профессиональным!</p>
              </div>
            </div>
          </div>
        </Transition>

        <!-- Кнопка полноэкранного режима -->
        <button
          @click="toggleFullscreen"
          class="fullscreen-button"
          :title="isFullscreen ? 'Выйти из полноэкранного режима' : 'Полноэкранный режим'"
          aria-label="Полноэкранный режим"
        >
          <i :class="isFullscreen ? 'pi pi-window-minimize' : 'pi pi-window-maximize'"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import OfficeScene from '@/Components/Simulators/BankSimulator/OfficeScene.vue';
import { useSimulatorState } from '@/Composables/Simulators/BankSimulator/useSimulatorState';

const props = defineProps({
  session: Object,
  simulator: Object,
});

// Инициализация композабла для работы с состоянием
const { state, updateState, loadState, autoSave, isLoading, cleanup } = useSimulatorState(
  props.session.id,
  props.session.state || {}
);

const isFullscreen = ref(false);
const containerRef = ref(null);
const sceneContainerRef = ref(null);

// ── Timer for time_spent tracking ──
const timeSpent = ref(0);
let timerInterval = null;

// ── Completion form ──
const isCompleting = ref(false);

/**
 * Handle simulator completion emitted from OfficeScene.
 * All 4 variants are completed → aggregate score → POST to backend → redirect.
 */
const handleCompleteSimulator = (payload) => {
  if (isCompleting.value) return;
  isCompleting.value = true;

  const completeForm = useForm({
    score: payload.score,
    time_spent: timeSpent.value || 1,
    answers: payload.variants_progress || {}
  });

  completeForm.post(route('student.simulators.complete', props.session.id), {
    onSuccess: () => {
      router.visit(route('student.simulators.index'));
    },
    onError: (errors) => {
      console.error('Ошибка завершения сессии:', errors);
      isCompleting.value = false;
    }
  });
};

// --- Прелоадер ---
const sceneReady = ref(false);
let preloaderTimer = null;

// --- Панель информации ---
const showInfo = ref(false);

// Определяем, есть ли активный диалог с клиентом (клиент сидит напротив)
const hasActiveClient = computed(() => {
  return !!(state.client?.type);
});

// При загрузке: если нет активного диалога, открыть инфо-панель
watch(sceneReady, (ready) => {
  if (ready && !hasActiveClient.value) {
    showInfo.value = true;
  }
});

// Скрывать инфо-панель, когда начинается диалог с клиентом
watch(hasActiveClient, (active) => {
  if (active) {
    showInfo.value = false;
  }
});

const handleFullscreenChange = () => {
  isFullscreen.value = !!(
    document.fullscreenElement ||
    document.webkitFullscreenElement ||
    document.msFullscreenElement
  );
};

const toggleFullscreen = async () => {
  if (!sceneContainerRef.value) return;

  try {
    if (!isFullscreen.value) {
      if (sceneContainerRef.value.requestFullscreen) {
        await sceneContainerRef.value.requestFullscreen();
      } else if (sceneContainerRef.value.webkitRequestFullscreen) {
        await sceneContainerRef.value.webkitRequestFullscreen();
      } else if (sceneContainerRef.value.msRequestFullscreen) {
        await sceneContainerRef.value.msRequestFullscreen();
      }
    } else {
      if (document.exitFullscreen) {
        await document.exitFullscreen();
      } else if (document.webkitExitFullscreen) {
        await document.webkitExitFullscreen();
      } else if (document.msExitFullscreen) {
        await document.msExitFullscreen();
      }
    }
  } catch (error) {
    console.error('Ошибка при переключении полноэкранного режима:', error);
  }
};

// Регистрируем lifecycle hooks синхронно в setup
onMounted(() => {
  handleFullscreenChange();
  document.addEventListener('fullscreenchange', handleFullscreenChange);
  document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
  document.addEventListener('msfullscreenchange', handleFullscreenChange);
  
  // Загружаем состояние из backend при монтировании
  loadState().catch((error) => {
    console.error('Ошибка загрузки состояния:', error);
  });

  // Минимальное время прелоадера — 2 секунды (для загрузки GLB моделей)
  preloaderTimer = setTimeout(() => {
    sceneReady.value = true;
  }, 2000);

  // Start time tracking
  timerInterval = setInterval(() => {
    timeSpent.value++;
  }, 1000);
});

onUnmounted(() => {
  document.removeEventListener('fullscreenchange', handleFullscreenChange);
  document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
  document.removeEventListener('msfullscreenchange', handleFullscreenChange);
  
  if (preloaderTimer) clearTimeout(preloaderTimer);
  if (timerInterval) clearInterval(timerInterval);
  if (cleanup) cleanup();
});
</script>

<style scoped>
.bank-simulator-page {
  width: 100%;
  height: 100%;
}

.simulator-container {
  width: 100%;
  height: calc(100vh - 200px);
  min-height: 600px;
  max-height: 800px;
  position: relative;
  background: #E0F6FF;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.simulator-container:fullscreen,
.simulator-container:-webkit-full-screen,
.simulator-container:-moz-full-screen,
.simulator-container:-ms-fullscreen {
  width: 100vw;
  height: 100vh;
  max-height: 100vh;
  border-radius: 0;
  margin: 0;
  padding: 0;
}

/* ── Прелоадер ── */
.scene-preloader {
  position: absolute;
  inset: 0;
  z-index: 100;
  background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 50%, #ede9fe 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.preloader-content {
  text-align: center;
}

.preloader-spinner {
  margin-bottom: 1rem;
}

.preloader-icon {
  font-size: 2.5rem;
  color: #3b82f6;
}

.preloader-text {
  font-size: 0.95rem;
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.02em;
}

.preloader-fade-leave-active {
  transition: opacity 0.6s ease;
}
.preloader-fade-leave-to {
  opacity: 0;
}

/* ── UI оверлей ── */
.ui-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 10;
}

.ui-overlay > * {
  pointer-events: auto;
}

/* ── Кнопка информации ── */
.info-button {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: all 0.2s;
  z-index: 20;
  line-height: 1;
}

.info-button:hover {
  transform: scale(1.1);
}

.info-button:active {
  transform: scale(0.95);
}

.info-button i {
  font-size: 1.75rem;
  color: rgba(100, 116, 139, 0.55);
  transition: color 0.2s;
  text-shadow: 0 1px 3px rgba(255, 255, 255, 0.6);
}

.info-button:hover i {
  color: rgba(71, 85, 105, 0.85);
}

/* ── Панель информации ── */
.info-panel {
  position: absolute;
  top: 4rem;
  left: 1rem;
  width: 340px;
  max-height: calc(100% - 5rem);
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.6);
  border-radius: 0.75rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  z-index: 15;
  display: flex;
  flex-direction: column;
}

.info-panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: rgba(248, 250, 252, 0.7);
}

.info-panel-title {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.info-panel-title i {
  color: #3b82f6;
  font-size: 0.9rem;
}

.info-close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 0.25rem;
  color: #94a3b8;
  transition: color 0.15s, background 0.15s;
}

.info-close-btn:hover {
  color: #475569;
  background: rgba(0, 0, 0, 0.05);
}

.info-panel-body {
  padding: 0.75rem 1rem;
  overflow-y: auto;
  flex: 1;
}

.info-section {
  margin-bottom: 0.75rem;
}

.info-section:last-child {
  margin-bottom: 0;
}

.info-section h4 {
  font-size: 0.8rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.info-section h4 i {
  font-size: 0.75rem;
  color: #3b82f6;
}

.info-section p {
  font-size: 0.78rem;
  line-height: 1.5;
  color: #64748b;
  margin: 0;
}

/* Анимация появления/скрытия инфо-панели */
.info-slide-enter-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.info-slide-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.info-slide-enter-from {
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
}
.info-slide-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
}

/* ── Кнопка полноэкранного режима ── */
.fullscreen-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(8px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  z-index: 20;
}

.fullscreen-button:hover {
  background: rgba(255, 255, 255, 0.85);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-1px);
}

.fullscreen-button:active {
  transform: translateY(0);
}

.fullscreen-button i {
  font-size: 1.25rem;
  color: #475569;
}
</style>
