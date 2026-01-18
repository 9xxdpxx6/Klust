<template>
  <div class="bank-simulator-page">
    <Head :title="`Симулятор: ${simulator.title}`" />
    
    <div class="simulator-container">
      <!-- 3D сцена (заглушка) -->
      <OfficeScene :session-state="sessionState" />
      
      <!-- UI панели (будут добавлены позже) -->
      <div class="ui-overlay">
        <!-- Здесь будут UI элементы -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import OfficeScene from '@/Components/Simulators/BankSimulator/OfficeScene.vue';

const props = defineProps({
  session: Object,
  simulator: Object,
});

const sessionState = computed(() => props.session.state || {});
</script>

<style scoped>
.bank-simulator-page {
  width: 100%;
  height: 100vh;
  overflow: hidden;
}

.simulator-container {
  width: 100%;
  height: 100%;
  position: relative;
}

.ui-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.ui-overlay > * {
  pointer-events: auto;
}
</style>
