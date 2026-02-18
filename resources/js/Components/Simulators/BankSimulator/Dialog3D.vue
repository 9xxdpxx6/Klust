<template>
  <!-- CSS3DObject рендерится через CSS3DRenderer -->
  <primitive v-if="css3dObject" :object="css3dObject" />
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
  },
  // Масштаб диалога в 3D пространстве (меньше = меньший диалог)
  dialogScale: {
    type: Number,
    default: 0.0012
  }
})

const emit = defineEmits(['update:visible', 'close'])

const { scene, camera } = useTres()
const css3dObject = ref(null)
const dialogElement = ref(null)
const dialogElementContent = ref(null)
const headerElement = ref(null)
const isAnimating = ref(false)

// CSS для анимаций
const animationStyles = `
  .dialog-3d-container {
    transition: opacity 0.3s ease-out, transform 0.3s ease-out;
    transform-origin: center center;
  }
  .dialog-3d-container.hidden {
    opacity: 0 !important;
    transform: scale(0.85) translateY(20px);
    pointer-events: none !important;
  }
  .dialog-3d-container.visible {
    opacity: 0.95;
    transform: scale(1) translateY(0);
  }
  .dialog-3d-header {
    transition: opacity 0.2s ease-out, transform 0.2s ease-out;
  }
  .dialog-3d-header.changing {
    opacity: 0;
    transform: translateY(-10px);
  }
  .dialog-3d-content {
    transition: opacity 0.25s ease-out, transform 0.25s ease-out;
    transition-delay: 0.1s;
  }
  .dialog-3d-content.changing {
    opacity: 0;
    transform: translateY(15px);
  }
  .dialog-3d-close-btn {
    transition: color 0.15s ease, transform 0.15s ease, background 0.15s ease;
    border-radius: 50%;
  }
  .dialog-3d-close-btn:hover {
    color: #1f2937 !important;
    background: #f3f4f6 !important;
    transform: scale(1.1);
  }
`

// Добавляем стили один раз
const injectStyles = () => {
  if (!document.getElementById('dialog-3d-styles')) {
    const styleEl = document.createElement('style')
    styleEl.id = 'dialog-3d-styles'
    styleEl.textContent = animationStyles
    document.head.appendChild(styleEl)
  }
}

onMounted(() => {
  injectStyles()
  
  // Ждем инициализации сцены
  const initDialog = () => {
    if (!scene.value) {
      setTimeout(initDialog, 100)
      return
    }
    
    // Создаем HTML элемент для диалога
    const element = document.createElement('div')
    element.className = 'dialog-3d-container hidden'
    element.style.width = props.width + 'px'
    element.style.height = props.height + 'px'
    element.style.padding = '1.5rem'
    element.style.background = 'white'
    element.style.borderRadius = '0.75rem'
    element.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1)'
    element.style.pointerEvents = 'auto'
    element.style.overflow = 'auto'
    element.style.maxHeight = '90vh'
    element.style.position = 'relative'
    
    // Заголовок с анимацией
    const headerEl = document.createElement('div')
    headerEl.className = 'dialog-3d-header'
    headerEl.style.fontSize = '1.5rem'
    headerEl.style.fontWeight = '600'
    headerEl.style.marginBottom = '1rem'
    headerEl.style.color = '#1f2937'
    headerEl.style.paddingRight = '2rem'
    headerEl.textContent = props.header
    element.appendChild(headerEl)
    headerElement.value = headerEl
    
    // Кнопка закрытия с hover эффектом
    const closeBtn = document.createElement('button')
    closeBtn.className = 'dialog-3d-close-btn'
    closeBtn.style.position = 'absolute'
    closeBtn.style.top = '0.75rem'
    closeBtn.style.right = '0.75rem'
    closeBtn.style.width = '2rem'
    closeBtn.style.height = '2rem'
    closeBtn.style.border = 'none'
    closeBtn.style.background = 'transparent'
    closeBtn.style.cursor = 'pointer'
    closeBtn.style.fontSize = '1.25rem'
    closeBtn.style.color = '#9ca3af'
    closeBtn.style.display = 'flex'
    closeBtn.style.alignItems = 'center'
    closeBtn.style.justifyContent = 'center'
    closeBtn.textContent = '×'
    closeBtn.onclick = () => {
      animateOut(() => {
        emit('update:visible', false)
        emit('close')
      })
    }
    element.appendChild(closeBtn)
    
    // Слот для контента с анимацией
    const contentEl = document.createElement('div')
    contentEl.className = 'dialog-3d-content dialog-content'
    contentEl.style.color = '#374151'
    contentEl.style.fontSize = '1rem'
    contentEl.style.lineHeight = '1.5'
    element.appendChild(contentEl)
    
    dialogElement.value = element
    dialogElementContent.value = contentEl
    
    // Создаем CSS3DObject
    const scale = props.dialogScale
    const object = new CSS3DObject(element)
    object.position.set(...props.position)
    object.scale.set(scale, scale, scale)
    
    // Ориентация диалога к работнику банка
    const workerEyePosition = new Vector3(-0.97, 1.2, -0.75)
    object.lookAt(workerEyePosition)
    
    css3dObject.value = object
    
    // Добавляем в сцену
    if (scene.value) {
      scene.value.add(object)
    }
    
    // Обновляем видимость с анимацией
    if (props.visible) {
      // Небольшая задержка для инициализации
      setTimeout(() => animateIn(), 50)
    }
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

// Анимация появления
const animateIn = () => {
  if (!dialogElement.value || isAnimating.value) return
  isAnimating.value = true
  
  dialogElement.value.classList.remove('hidden')
  dialogElement.value.classList.add('visible')
  
  setTimeout(() => {
    isAnimating.value = false
  }, 300)
}

// Анимация исчезновения
const animateOut = (callback) => {
  if (!dialogElement.value || isAnimating.value) {
    callback?.()
    return
  }
  isAnimating.value = true
  
  dialogElement.value.classList.remove('visible')
  dialogElement.value.classList.add('hidden')
  
  setTimeout(() => {
    isAnimating.value = false
    callback?.()
  }, 300)
}

// Анимация смены контента (заголовок + контент)
const animateContentChange = (newHeader, callback) => {
  if (!dialogElement.value || !headerElement.value) {
    callback?.()
    return
  }
  
  const contentEl = dialogElementContent.value
  
  // Фаза 1: Скрываем старый контент
  headerElement.value.classList.add('changing')
  if (contentEl) contentEl.classList.add('changing')
  
  setTimeout(() => {
    // Фаза 2: Меняем контент
    headerElement.value.textContent = newHeader
    callback?.()
    
    // Фаза 3: Показываем новый контент
    setTimeout(() => {
      headerElement.value.classList.remove('changing')
      if (contentEl) contentEl.classList.remove('changing')
    }, 50)
  }, 200)
}

watch(() => props.visible, (newVal, oldVal) => {
  if (newVal && !oldVal) {
    animateIn()
  } else if (!newVal && oldVal) {
    // Не анимируем здесь - анимация в кнопке закрытия
    if (dialogElement.value) {
      dialogElement.value.classList.remove('visible')
      dialogElement.value.classList.add('hidden')
    }
  }
})

watch(() => props.position, (newPos) => {
  if (css3dObject.value) {
    css3dObject.value.position.set(...newPos)
  }
}, { deep: true })

watch(() => props.header, (newHeader, oldHeader) => {
  if (newHeader !== oldHeader && dialogElement.value) {
    animateContentChange(newHeader)
  }
})

// Обновляем контент при монтировании
watch(() => props.visible, (newVal) => {
  if (newVal && dialogElementContent.value && !dialogElementContent.value.textContent) {
    dialogElementContent.value.textContent = 'Интерфейс...'
  }
})

// Метод для обновления контента с анимацией
defineExpose({
  setContent: (html) => {
    if (dialogElementContent.value) {
      // Анимация смены контента
      dialogElementContent.value.classList.add('changing')
      setTimeout(() => {
        dialogElementContent.value.innerHTML = html
        setTimeout(() => {
          dialogElementContent.value.classList.remove('changing')
        }, 50)
      }, 200)
    }
  },
  animateContentChange,
  /**
   * Вернуть DOM-элемент содержимого диалога.
   * Используется для монтирования Vue-интерфейсов (диалог с клиентом и т.п.).
   */
  getContentElement: () => dialogElementContent.value
})
</script>
