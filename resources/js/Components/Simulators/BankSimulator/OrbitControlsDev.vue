<script setup>
import { onUnmounted, watch, nextTick } from 'vue'
import { useTres, useRenderLoop } from '@tresjs/core'
// TODO: DEV ONLY - Временное решение для разработки, убрать в продакшене
// OrbitControls добавлен для удобства просмотра сцены во время разработки
import { OrbitControls } from 'three/addons/controls/OrbitControls.js'

// TODO: DEV ONLY - OrbitControls для 360 просмотра (временно для разработки)
let controls = null
const STORAGE_KEY = 'bank-sim:orbit-controls'

const { camera, renderer } = useTres()
const { onLoop } = useRenderLoop()

const restoreControlsState = async () => {
  if (!controls) return
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY)
    if (!raw) return
    const state = JSON.parse(raw)
    
    // Временно отключаем damping для точного восстановления
    const wasDampingEnabled = controls.enableDamping
    controls.enableDamping = false
    
    if (state?.target && Array.isArray(state.target)) {
      controls.target.set(state.target[0], state.target[1], state.target[2])
    }
    if (state?.position && Array.isArray(state.position)) {
      controls.object.position.set(state.position[0], state.position[1], state.position[2])
    }
    if (typeof state?.zoom === 'number' && state.zoom > 0) {
      controls.object.zoom = state.zoom
      controls.object.updateProjectionMatrix()
    }
    
    controls.update()
    
    // Восстанавливаем damping после небольшой задержки
    await nextTick()
    setTimeout(() => {
      if (controls) {
        controls.enableDamping = wasDampingEnabled
      }
    }, 100)
  } catch (error) {
    // SAFE FALLBACK
  }
}

let saveTimeout = null
const saveControlsState = () => {
  if (!controls) return
  // Debounce сохранение, чтобы не спамить localStorage
  if (saveTimeout) {
    clearTimeout(saveTimeout)
  }
  saveTimeout = setTimeout(() => {
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
  }, 100)
}

// Ждем готовности renderer и camera
watch([renderer, camera], async ([newRenderer, newCamera]) => {
  if (newRenderer && newCamera && !controls) {
    controls = new OrbitControls(newCamera, newRenderer.domElement)
    controls.enableDamping = true
    controls.dampingFactor = 0.05
    controls.minDistance = 0
    controls.maxDistance = Infinity
    controls.minPolarAngle = 0
    controls.maxPolarAngle = Math.PI
    controls.addEventListener('change', saveControlsState)
    
    // Ждем полной инициализации перед восстановлением состояния
    await nextTick()
    setTimeout(() => {
      restoreControlsState()
    }, 50)
    
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
  if (saveTimeout) {
    clearTimeout(saveTimeout)
  }
  if (controls) {
    // Сохраняем состояние перед размонтированием
    saveControlsState()
    // Ждем завершения сохранения
    setTimeout(() => {
      if (controls) {
        controls.removeEventListener('change', saveControlsState)
        controls.dispose()
        controls = null
      }
    }, 150)
  }
})
</script>

<template>
  <!-- Пустой компонент, логика в script -->
</template>
