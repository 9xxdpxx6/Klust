<template>
  <!-- This component configures CSS3DRenderer -->
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import { useTres, useLoop } from '@tresjs/core'
import { CSS3DRenderer } from 'three/addons/renderers/CSS3DRenderer.js'

const { renderer, scene, camera, sizes } = useTres()
const { onBeforeRender } = useLoop()

let css3dRenderer = null
let resizeObserver = null
let stopSizesWatch = null
let initTimeout = null
let lastWidth = -1
let lastHeight = -1

const getCanvasSize = () => {
  const webglCanvas = renderer.value?.domElement
  const width = Math.max(1, Math.round(webglCanvas?.clientWidth || sizes.value?.width || window.innerWidth))
  const height = Math.max(1, Math.round(webglCanvas?.clientHeight || sizes.value?.height || window.innerHeight))
  return { width, height }
}

const updateSize = (force = false) => {
  if (!css3dRenderer) return

  const { width, height } = getCanvasSize()
  if (!force && width === lastWidth && height === lastHeight) return

  css3dRenderer.setSize(width, height)
  css3dRenderer.domElement.style.width = `${width}px`
  css3dRenderer.domElement.style.height = `${height}px`
  lastWidth = width
  lastHeight = height
}

const onWindowResize = () => updateSize(true)
const onFullscreenChange = () => updateSize(true)
const onVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    updateSize(true)
  }
}

onMounted(() => {
  const initCSS3DRenderer = () => {
    if (!renderer.value || !scene.value) {
      initTimeout = setTimeout(initCSS3DRenderer, 100)
      return
    }

    const webglCanvas = renderer.value.domElement
    css3dRenderer = new CSS3DRenderer()
    css3dRenderer.domElement.style.position = 'absolute'
    css3dRenderer.domElement.style.top = '0'
    css3dRenderer.domElement.style.left = '0'
    css3dRenderer.domElement.style.pointerEvents = 'none'
    css3dRenderer.domElement.style.zIndex = '1'

    if (webglCanvas?.parentElement) {
      const parent = webglCanvas.parentElement
      if (window.getComputedStyle(parent).position === 'static') {
        parent.style.position = 'relative'
      }
      parent.appendChild(css3dRenderer.domElement)
    }

    updateSize(true)

    onBeforeRender(() => {
      updateSize()
      if (css3dRenderer && scene.value && camera.value) {
        css3dRenderer.render(scene.value, camera.value)
      }
    })

    stopSizesWatch = watch(
      () => [sizes.value?.width, sizes.value?.height],
      () => updateSize(true),
      { immediate: true }
    )

    if (window.ResizeObserver && webglCanvas) {
      resizeObserver = new ResizeObserver(() => updateSize(true))
      resizeObserver.observe(webglCanvas)
      if (webglCanvas.parentElement) {
        resizeObserver.observe(webglCanvas.parentElement)
      }
    }

    window.addEventListener('resize', onWindowResize)
    window.addEventListener('fullscreenchange', onFullscreenChange)
    document.addEventListener('visibilitychange', onVisibilityChange)
  }

  initCSS3DRenderer()
})

onUnmounted(() => {
  if (initTimeout) {
    clearTimeout(initTimeout)
  }
  if (stopSizesWatch) {
    stopSizesWatch()
    stopSizesWatch = null
  }
  if (resizeObserver) {
    resizeObserver.disconnect()
    resizeObserver = null
  }

  window.removeEventListener('resize', onWindowResize)
  window.removeEventListener('fullscreenchange', onFullscreenChange)
  document.removeEventListener('visibilitychange', onVisibilityChange)

  if (css3dRenderer?.domElement?.parentNode) {
    css3dRenderer.domElement.parentNode.removeChild(css3dRenderer.domElement)
  }

  lastWidth = -1
  lastHeight = -1
  css3dRenderer = null
})
</script>
