<template>
  <!-- CSS3DObject рендерится через CSS3DRenderer -->
  <primitive v-if="css3dObject" :object="css3dObject" />
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, provide, reactive, h } from 'vue'
import { CSS3DObject } from 'three/addons/renderers/CSS3DRenderer.js'
import { Vector3 } from 'three'
import { useTres } from '@tresjs/core'
import { createApp } from 'vue'
import { setupPrimeVue } from '@/plugins/primevue'
import BankInterface from './BankInterface.vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: true
  },
  position: {
    type: Array,
    default: () => [0.0, 0.102, -0.148]
  },
  rotation: {
    type: Array,
    default: () => [-0.35, 0, 0]
  },
  width: {
    type: Number,
    default: 600
  },
  height: {
    type: Number,
    default: 400
  },
  scale: {
    type: Number,
    default: 0.0005
  },
  client: {
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
  }
})

const emit = defineEmits(['update:activeTab'])

const { scene } = useTres()
const css3dObject = ref(null)
const screenElement = ref(null)
const screenApp = ref(null)

// Реактивные данные для передачи в BankInterface
const bankInterfaceData = reactive({
  client: { ...(props.client || {}) },
  calculations: { ...(props.calculations || {}) },
  activeTab: props.activeTab || '0'
})

// Функция обработки изменения вкладки
const handleTabChangeRef = ref((value) => {
  bankInterfaceData.activeTab = value
  emit('update:activeTab', value)
})

onMounted(() => {
  initScreen()
})

onUnmounted(() => {
  if (screenApp.value) {
    screenApp.value.unmount()
    screenApp.value = null
  }
  if (css3dObject.value && scene.value) {
    scene.value.remove(css3dObject.value)
  }
  if (screenElement.value && screenElement.value.parentNode) {
    screenElement.value.parentNode.removeChild(screenElement.value)
  }
})

const initScreen = () => {
  // Коэффициент увеличения контента (~1.75x крупнее текст)
  // Уменьшаем CSS-пиксели и компенсируем масштабом CSS3DObject —
  // физический размер экрана в 3D остаётся прежним
  const contentScale = 1.75
  const scaledWidth = Math.round(props.width / contentScale)
  const scaledHeight = Math.round(props.height / contentScale)
  const adjustedScale = props.scale * contentScale

  // Создаем элемент для экрана
  const element = document.createElement('div')
  element.style.width = `${scaledWidth}px`
  element.style.height = `${scaledHeight}px`
  element.style.background = 'white'
  element.style.borderRadius = '0.5rem'
  element.style.overflow = 'hidden'
  element.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)'
  
  screenElement.value = element
  
  // Создаем wrapper компонент с render функцией (без template для production сборки)
  const BankInterfaceWrapper = {
    setup() {
      return () => h(BankInterface, {
        client: bankInterfaceData.client,
        calculations: bankInterfaceData.calculations,
        activeTab: bankInterfaceData.activeTab,
        'onUpdate:activeTab': handleTabChangeRef.value
      })
    }
  }
  
  // Монтируем wrapper компонент
  const app = createApp(BankInterfaceWrapper)
  
  // Устанавливаем PrimeVue плагин
  setupPrimeVue(app)
  
  app.mount(element)
  screenApp.value = app
  
  // Создаем CSS3DObject
  const object = new CSS3DObject(element)
  object.position.set(...props.position)
  object.rotation.set(...props.rotation)
  object.scale.set(adjustedScale, adjustedScale, adjustedScale)
  
  css3dObject.value = object
  
  // Добавляем в сцену
  if (scene.value) {
    scene.value.add(object)
  }
}

// Обновляем реактивные данные при изменении props
// ВАЖНО: заменяем объект целиком чтобы гарантировать реактивность в дочернем Vue app
watch(() => props.client, (newClient) => {
  bankInterfaceData.client = { ...(newClient || {}) }
}, { deep: true })

watch(() => props.calculations, (newCalculations) => {
  const newCalc = { ...(newCalculations || {}) }
  const hadScore = bankInterfaceData.calculations?.credit_score != null
  const hasScore = newCalc.credit_score != null

  bankInterfaceData.calculations = newCalc

  // Авто-переключение на вкладку «Скоринг» при появлении результата
  if (!hadScore && hasScore) {
    bankInterfaceData.activeTab = '1'
    emit('update:activeTab', '1')
  }
}, { deep: true })

watch(() => props.activeTab, (newTab) => {
  if (newTab !== bankInterfaceData.activeTab) {
    bankInterfaceData.activeTab = newTab
  }
})
</script>
