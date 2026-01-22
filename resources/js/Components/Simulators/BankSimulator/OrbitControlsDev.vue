<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import { useTres, useRenderLoop } from '@tresjs/core'
// TODO: DEV ONLY - Временное решение для разработки, убрать в продакшене
// OrbitControls добавлен для удобства просмотра сцены во время разработки
import { OrbitControls } from 'three/addons/controls/OrbitControls.js'

// TODO: DEV ONLY - OrbitControls для 360 просмотра (временно для разработки)
let controls = null

const { camera, renderer } = useTres()
const { onLoop } = useRenderLoop()

// Ждем готовности renderer и camera
watch([renderer, camera], ([newRenderer, newCamera]) => {
  if (newRenderer && newCamera && !controls) {
    controls = new OrbitControls(newCamera, newRenderer.domElement)
    controls.enableDamping = true
    controls.dampingFactor = 0.05
    controls.minDistance = 2
    controls.maxDistance = 10
    controls.minPolarAngle = Math.PI / 6
    controls.maxPolarAngle = Math.PI / 2.5
    
    // Обновляем controls в каждом кадре
    onLoop(() => {
      if (controls) {
        controls.update()
      }
    })
  }
}, { immediate: true })

onUnmounted(() => {
  // TODO: DEV ONLY - Очистка OrbitControls (временно для разработки)
  if (controls) {
    controls.dispose()
    controls = null
  }
})
</script>

<template>
  <!-- Пустой компонент, логика в script -->
</template>
