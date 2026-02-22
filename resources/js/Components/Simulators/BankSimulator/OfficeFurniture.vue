<template>
  <!-- Кабинет (сборка из GLB) -->
  <OfficeInterior 
    :position="[0, 0, 0]" 
    :rotation="[0, -Math.PI / 2, 0]" 
    :scale="[1, 1, 1]"
    :window-position="[3.2, 0, 0]"
    :door-position="[3.95, 0, 0.5]"
    :palm-left-position="[2.6, 0, 2.1]"
    :palm-right-position="[2.6, 0, -2.1]"
    :plant-position="[-1.2, 0, -3.6]"
    :sofa-position="[0, 0, 0]"
    @door-click="$emit('door-click', $event)"
  />
  
  <!-- Стол -->
  <Desk 
    :position="[-0.9, 0, -0.4]"
    :rotation="[0, Math.PI, 0]"
    :scale="[1, 1, 1]"
  />
  
  <!-- Ноутбук на столе -->
  <Laptop 
    :position="[-0.98, 0.7974, -0.4]"
    :rotation="[0, 3.2, 0]"
    :color="laptopColor"
    @click="$emit('laptop-click')"
  />
  
  <!-- Экран ноутбука с интерфейсом банковской системы (показывается только когда есть клиент) -->
  <LaptopScreen
    v-if="hasClient"
    :client="clientData"
    :calculations="calculations"
    :active-tab="activeTab"
    @update:active-tab="$emit('bank-tab-change', $event)"
    :position="[-1.67, 1.15, 0.1]"
    :rotation="[0.0, 2.9, 0]"
    :width="800"
    :height="600"
    :scale="0.001"
  />
  
  <!-- Телефон (декоративный, не кликабельный) -->
  <Phone 
    :position="[-1.4, 0.7955, -0.55]"
    :base-scale="[0.001, 0.001, 0.001]"
    :base-rotation="[Math.PI / 2, 0, 0.5]"
    :is-ringing="isPhoneRinging"
    :interactive="false"
  />
  
  <!-- Документы (декоративные, не кликабельные) -->
  <Documents 
    :position="[-1.27, 0.79, -0.37]"
    :rotation="[0, 3.25, 0]"
    :count="1"
    :scale="[0.45, 0.45, 0.45]"
    :interactive="false"
  />
  
  <!-- Кактус -->
  <Cactus 
    :position="[1.3, 0.0, -1.55]"
    :rotation="[0, 0, 0]"
    :scale="[0.7, 0.7, 0.7]"
  />

  <!-- Кресло работника -->
  <Armchair 
    :position="[-0.95, 0, -0.95]"
    :rotation="[0, -0.25, 0]"
    :scale="[1, 1, 1]"
  />

  <!-- Кресла клиентов (2 шт) -->
  <Chair 
    :position="[-1.3, 0, 0.4]"
    :rotation="[0, 2.7, 0]"
    :scale="[1, 1, 1]"
  />
  <Chair 
    :position="[-0.3, 0, 0.4]"
    :rotation="[0, -2.65, 0]"
    :scale="[1, 1, 1]"
  />
  
  <!-- Клиент напротив -->
  <ClientCharacter 
    v-if="isClientVisible"
    :position="clientPosition"
    :rotation="clientRotation"
    :animation="clientAnimation"
    :is-speaking="isClientSpeaking"
    :preloaded-model="preloadedClientModel"
    :model-path="clientModelPath"
    @animation-finished="$emit('client-animation-finished', $event)"
  />
</template>

<script setup>
import { computed } from 'vue'
import OfficeInterior from './OfficeInterior.vue'
import Desk from './Desk.vue'
import Laptop from './Laptop.vue'
import LaptopScreen from './LaptopScreen.vue'
import Phone from './Phone.vue'
import Documents from './Documents.vue'
import Cactus from './Cactus.vue'
import Armchair from './Armchair.vue'
import Chair from './Chair.vue'
import ClientCharacter from './ClientCharacter.vue'

const props = defineProps({
  laptopColor: {
    type: String,
    default: '#1e40af'
  },
  hasClient: {
    type: Boolean,
    default: false
  },
  clientData: {
    type: Object,
    default: () => ({})
  },
  calculations: {
    type: Object,
    default: () => ({})
  },
  activeTab: {
    type: String,
    default: '0'
  },
  isPhoneRinging: {
    type: Boolean,
    default: false
  },
  isClientVisible: {
    type: Boolean,
    default: false
  },
  clientPosition: {
    type: Array,
    default: () => [0, 0, 0]
  },
  clientRotation: {
    type: Array,
    default: () => [0, 0, 0]
  },
  clientAnimation: {
    type: String,
    default: 'idle'
  },
  isClientSpeaking: {
    type: Boolean,
    default: false
  },
  preloadedClientModel: {
    type: Object,
    default: null
  },
  clientModelPath: {
    type: String,
    default: '/models/characters/female1.glb'
  }
})

defineEmits(['door-click', 'laptop-click', 'bank-tab-change', 'client-animation-finished'])
</script>
