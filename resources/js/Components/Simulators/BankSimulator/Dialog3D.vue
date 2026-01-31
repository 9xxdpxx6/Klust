<template>
  <!-- CSS3DObject рендерится через CSS3DRenderer -->
  <primitive v-if="css3dObject && props.visible" :object="css3dObject" />
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { CSS3DObject } from 'three/addons/renderers/CSS3DRenderer.js'
import { Vector3 } from 'three'
import { useTres } from '@tresjs/core'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  header: {
    type: String,
    default: ''
  },
  position: {
    type: Array,
    default: () => [0, 1.6, -1.5]
  },
  width: {
    type: Number,
    default: 800
  },
  height: {
    type: Number,
    default: 400
  }
})

const emit = defineEmits(['update:visible', 'close'])

const { scene, camera } = useTres()
const css3dObject = ref(null)
const dialogElement = ref(null)
const dialogElementContent = ref(null)

onMounted(() => {
  // Ждем инициализации сцены
  const initDialog = () => {
    if (!scene.value) {
      setTimeout(initDialog, 100)
      return
    }
    
    // Создаем HTML элемент для диалога
    const element = document.createElement('div')
    element.style.width = props.width + 'px'
    element.style.height = props.height + 'px'
    element.style.padding = '1.5rem'
    element.style.background = 'white'
    element.style.borderRadius = '0.5rem'
    element.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'
    element.style.opacity = '0.95'
    element.style.pointerEvents = 'auto'
    element.style.overflow = 'auto'
    element.style.maxHeight = '90vh'
    
    // Заголовок
    const headerEl = document.createElement('div')
    headerEl.style.fontSize = '1.5rem'
    headerEl.style.fontWeight = '600'
    headerEl.style.marginBottom = '1rem'
    headerEl.style.color = '#1f2937'
    headerEl.textContent = props.header
    element.appendChild(headerEl)
    
    // Кнопка закрытия
    const closeBtn = document.createElement('button')
    closeBtn.style.position = 'absolute'
    closeBtn.style.top = '0.75rem'
    closeBtn.style.right = '0.75rem'
    closeBtn.style.width = '2rem'
    closeBtn.style.height = '2rem'
    closeBtn.style.border = 'none'
    closeBtn.style.background = 'transparent'
    closeBtn.style.cursor = 'pointer'
    closeBtn.style.fontSize = '1.5rem'
    closeBtn.style.color = '#6b7280'
    closeBtn.textContent = '×'
    closeBtn.onclick = () => {
      emit('update:visible', false)
      emit('close')
    }
    element.appendChild(closeBtn)
    
    // Слот для контента
    const contentEl = document.createElement('div')
    contentEl.className = 'dialog-content'
    contentEl.style.color = '#374151'
    contentEl.style.fontSize = '1rem'
    contentEl.style.lineHeight = '1.5'
    element.appendChild(contentEl)
    
    dialogElement.value = element
    dialogElementContent.value = contentEl
    
    // Создаем CSS3DObject
    // Масштабируем размеры для 3D пространства (1 единица = примерно 200px)
    // Уменьшенный масштаб для более разумного размера в 3D
    const scale = 0.002
    const object = new CSS3DObject(element)
    object.position.set(...props.position)
    object.scale.set(scale, scale, scale)
    
    // Ориентация диалога к работнику банка (черное кресло находится в [-1.05, 0, -0.75])
    // Позиция глаз работника: [-1.05, 1.6, -0.75]
    // Диалог должен быть виден с черного кресла работника
    const workerEyePosition = new Vector3(-0.97, 1.2, -0.75)
    object.lookAt(workerEyePosition)
    
    css3dObject.value = object
    
    // Добавляем в сцену
    if (scene.value) {
      scene.value.add(object)
    }
    
    // Обновляем видимость
    updateVisibility()
  }
  
  initDialog()
})

onUnmounted(() => {
  if (css3dObject.value && scene.value) {
    scene.value.remove(css3dObject.value)
  }
  if (dialogElement.value && dialogElement.value.parentNode) {
    dialogElement.value.parentNode.removeChild(dialogElement.value)
  }
})

watch(() => props.visible, (newVal) => {
  updateVisibility()
})

watch(() => props.position, (newPos) => {
  if (css3dObject.value) {
    css3dObject.value.position.set(...newPos)
  }
}, { deep: true })

watch(() => props.header, (newHeader) => {
  if (dialogElement.value) {
    const headerEl = dialogElement.value.querySelector('div')
    if (headerEl) {
      headerEl.textContent = newHeader
    }
  }
})

const updateVisibility = () => {
  if (css3dObject.value) {
    css3dObject.value.visible = props.visible
  }
}

// Обновляем контент при монтировании
watch(() => props.visible, (newVal) => {
  if (newVal && dialogElementContent.value && !dialogElementContent.value.textContent) {
    // Устанавливаем дефолтный контент если пусто
    dialogElementContent.value.textContent = 'Интерфейс...'
  }
})

// Метод для обновления контента
defineExpose({
  setContent: (html) => {
    if (dialogElementContent.value) {
      dialogElementContent.value.innerHTML = html
    }
  }
})
</script>
