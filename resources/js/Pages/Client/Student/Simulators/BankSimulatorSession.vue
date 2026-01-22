<template>
  <div class="bank-simulator-page" ref="containerRef">
    <Head :title="`Симулятор: ${simulator.title}`" />
    
    <div class="simulator-container" ref="sceneContainerRef">
      <!-- 3D сцена -->
      <OfficeScene 
        :session-state="sessionState"
        :session-id="session.id"
        @phone-click="onPhoneClick"
        @calculator-click="onCalculatorClick"
        @documents-click="onDocumentsClick"
      />
      
      <!-- UI панели -->
      <div class="ui-overlay">
        <!-- Кнопка полноэкранного режима -->
        <button
          @click="toggleFullscreen"
          class="fullscreen-button"
          :title="isFullscreen ? 'Выйти из полноэкранного режима' : 'Полноэкранный режим'"
          aria-label="Полноэкранный режим"
        >
          <i :class="isFullscreen ? 'pi pi-window-minimize' : 'pi pi-window-maximize'"></i>
        </button>
        
        <!-- Здесь будут другие UI элементы -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import OfficeScene from '@/Components/Simulators/BankSimulator/OfficeScene.vue';

const props = defineProps({
  session: Object,
  simulator: Object,
});

const sessionState = computed(() => props.session.state || {});
const isFullscreen = ref(false);
const containerRef = ref(null);
const sceneContainerRef = ref(null);

// Обработчики событий (диалоги теперь внутри OfficeScene)
const onPhoneClick = () => {
  // Событие обрабатывается в OfficeScene
};

const onCalculatorClick = () => {
  // Событие обрабатывается в OfficeScene
};

const onDocumentsClick = () => {
  // Событие обрабатывается в OfficeScene
};

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
      // Войти в полноэкранный режим
      if (sceneContainerRef.value.requestFullscreen) {
        await sceneContainerRef.value.requestFullscreen();
      } else if (sceneContainerRef.value.webkitRequestFullscreen) {
        await sceneContainerRef.value.webkitRequestFullscreen();
      } else if (sceneContainerRef.value.msRequestFullscreen) {
        await sceneContainerRef.value.msRequestFullscreen();
      }
    } else {
      // Выйти из полноэкранного режима
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
  handleFullscreenChange(); // Проверяем начальное состояние
  document.addEventListener('fullscreenchange', handleFullscreenChange);
  document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
  document.addEventListener('msfullscreenchange', handleFullscreenChange);
});

onUnmounted(() => {
  document.removeEventListener('fullscreenchange', handleFullscreenChange);
  document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
  document.removeEventListener('msfullscreenchange', handleFullscreenChange);
});
</script>

<style scoped>
.bank-simulator-page {
  width: 100%;
  height: 100%;
}

.simulator-container {
  width: 100%;
  height: calc(100vh - 200px); /* Высота минус header и padding */
  min-height: 600px;
  max-height: 800px;
  position: relative;
  background: #E0F6FF;
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* В полноэкранном режиме контейнер занимает весь экран */
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

.fullscreen-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  z-index: 20;
}

.fullscreen-button:hover {
  background: rgba(255, 255, 255, 1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateY(-1px);
}

.fullscreen-button:active {
  transform: translateY(0);
}

.fullscreen-button i {
  font-size: 1.25rem;
  color: #333;
}
</style>
