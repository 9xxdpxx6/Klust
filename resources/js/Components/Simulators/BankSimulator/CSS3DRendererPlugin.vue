<template>
  <!-- Этот компонент настраивает CSS3DRenderer -->
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import { useTres, useLoop } from '@tresjs/core'
import { CSS3DRenderer } from 'three/addons/renderers/CSS3DRenderer.js'

const { renderer, scene, camera, sizes } = useTres()

let css3dRenderer = null

onMounted(() => {
  // Ждем инициализации renderer и scene
  const initCSS3DRenderer = () => {
    if (!renderer.value || !scene.value) {
      // Повторяем попытку через небольшую задержку
      setTimeout(initCSS3DRenderer, 100)
      return
    }
    
    // Получаем размеры из canvas или sizes
    const webglCanvas = renderer.value.domElement
    const width = sizes.value?.width || webglCanvas?.clientWidth || window.innerWidth
    const height = sizes.value?.height || webglCanvas?.clientHeight || window.innerHeight
    
    // Создаем CSS3DRenderer
    css3dRenderer = new CSS3DRenderer()
    css3dRenderer.setSize(width, height)
    css3dRenderer.domElement.style.position = 'absolute'
    css3dRenderer.domElement.style.top = '0'
    css3dRenderer.domElement.style.left = '0'
    css3dRenderer.domElement.style.pointerEvents = 'none'
    css3dRenderer.domElement.style.zIndex = '1'
    
    // Добавляем CSS3DRenderer в DOM (после WebGL canvas)
    if (webglCanvas && webglCanvas.parentNode) {
      webglCanvas.parentNode.appendChild(css3dRenderer.domElement)
    }
    
    // Обновляем размеры при изменении
    const updateSize = () => {
      if (!css3dRenderer) return
      
      const webglCanvas = renderer.value?.domElement
      const newWidth = sizes.value?.width || webglCanvas?.clientWidth || window.innerWidth
      const newHeight = sizes.value?.height || webglCanvas?.clientHeight || window.innerHeight
      
      css3dRenderer.setSize(newWidth, newHeight)
    }
    
    // Рендерим CSS3D сцену в каждом кадре через useLoop
    const { onBeforeRender } = useLoop()
    onBeforeRender(() => {
      if (css3dRenderer && scene.value && camera.value) {
        css3dRenderer.render(scene.value, camera.value)
      }
    })
    
    // Слушаем изменения размеров
    if (sizes.value) {
      watch(() => sizes.value, updateSize, { deep: true })
    }
    
    // Также слушаем изменения размеров окна
    window.addEventListener('resize', updateSize)
  }
  
  initCSS3DRenderer()
})

onUnmounted(() => {
  window.removeEventListener('resize', () => {})
  if (css3dRenderer && css3dRenderer.domElement && css3dRenderer.domElement.parentNode) {
    css3dRenderer.domElement.parentNode.removeChild(css3dRenderer.domElement)
  }
  css3dRenderer = null
})
</script>
