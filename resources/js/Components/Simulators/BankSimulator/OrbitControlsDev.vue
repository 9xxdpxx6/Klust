<script setup>
import { onUnmounted, watch } from 'vue'
import { useTres, useRenderLoop } from '@tresjs/core'
// TODO: DEV ONLY - Временное решение для разработки, убрать в продакшене
// OrbitControls добавлен для удобства просмотра сцены во время разработки
import { OrbitControls } from 'three/addons/controls/OrbitControls.js'

// TODO: DEV ONLY - OrbitControls для 360 просмотра (временно для разработки)
let controls = null
const STORAGE_KEY = 'bank-sim:orbit-controls'

const { camera, renderer } = useTres()
const { onLoop } = useRenderLoop()

const restoreControlsState = () => {
  if (!controls) return
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY)
    if (!raw) return
    const state = JSON.parse(raw)
    if (state?.target && Array.isArray(state.target)) {
      controls.target.set(state.target[0], state.target[1], state.target[2])
    }
    if (state?.position && Array.isArray(state.position)) {
      controls.object.position.set(state.position[0], state.position[1], state.position[2])
    }
    if (typeof state?.zoom === 'number') {
      controls.object.zoom = state.zoom
      controls.object.updateProjectionMatrix()
    }
    controls.update()
  } catch (error) {
    // SAFE FALLBACK
  }
}

const saveControlsState = () => {
  if (!controls) return
  try {
    const state = {
      target: [controls.target.x, controls.target.y, controls.target.z],
      position: [
        controls.object.position.x,
        controls.object.position.y,
        controls.object.position.z
      ],
      zoom: controls.object.zoom
    }
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
  } catch (error) {
    // SAFE FALLBACK
  }
}

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
    controls.addEventListener('change', saveControlsState)
    restoreControlsState()
    
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
    controls.removeEventListener('change', saveControlsState)
    controls.dispose()
    controls = null
  }
})
</script>

<template>
  <!-- Пустой компонент, логика в script -->
</template>
