<template>
  <!-- CSS3DObject рендерится через CSS3DRenderer -->
  <primitive v-if="css3dObject" :object="css3dObject" />
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
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
  dialogueMessages: {
    type: Array,
    default: () => []
  }
})

const { scene } = useTres()
const css3dObject = ref(null)
const screenElement = ref(null)
const screenApp = ref(null)

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
  // Создаем элемент для экрана
  const element = document.createElement('div')
  element.style.width = `${props.width}px`
  element.style.height = `${props.height}px`
  element.style.background = 'white'
  element.style.borderRadius = '0.5rem'
  element.style.overflow = 'hidden'
  element.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)'
  
  screenElement.value = element
  
  // Монтируем BankInterface в элемент
  const app = createApp(BankInterface, {
    client: props.client,
    calculations: props.calculations,
    dialogueMessages: props.dialogueMessages
  })
  
  // Устанавливаем PrimeVue плагин
  setupPrimeVue(app)
  
  app.mount(element)
  screenApp.value = app
  
  // Создаем CSS3DObject
  const object = new CSS3DObject(element)
  object.position.set(...props.position)
  object.rotation.set(...props.rotation)
  object.scale.set(props.scale, props.scale, props.scale)
  
  css3dObject.value = object
  
  // Добавляем в сцену
  if (scene.value) {
    scene.value.add(object)
  }
}

// Обновляем props при изменении - пересоздаем компонент
watch(() => [props.client, props.calculations, props.dialogueMessages], () => {
  if (screenApp.value && screenElement.value) {
    screenApp.value.unmount()
    nextTick(() => {
      const app = createApp(BankInterface, {
        client: props.client,
        calculations: props.calculations,
        dialogueMessages: props.dialogueMessages
      })
      
      // Устанавливаем PrimeVue плагин
      setupPrimeVue(app)
      
      app.mount(screenElement.value)
      screenApp.value = app
    })
  }
}, { deep: true })
</script>
