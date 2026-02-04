<template>
  <!-- Пустой компонент -->
</template>

<script setup>
import { onMounted, onUnmounted, watch } from 'vue'
import { useTres, useRenderLoop } from '@tresjs/core'
import { MathUtils, Vector3 } from 'three'

const props = defineProps({
  position: { type: Array, default: () => [-1.05, 1.2, -0.75] },
  fov: { type: Number, default: 75 },
  maxYawLeft: { type: Number, default: 20 },
  maxYawRight: { type: Number, default: 20 },
  maxPitchUp: { type: Number, default: 20 },
  maxPitchDown: { type: Number, default: 20 },
  speed: { type: Number, default: 5 },
  baseYaw: { type: Number, default: 0 },
  basePitch: { type: Number, default: 0 }
})

let mouseX = 0.5
let mouseY = 0.5
let winW = 1
let winH = 1
let cam = null
let initialized = false

const onMouseMove = (e) => {
  mouseX = e.clientX
  mouseY = e.clientY
}

const maxYawLeftRad = MathUtils.degToRad(props.maxYawLeft)
const maxYawRightRad = MathUtils.degToRad(props.maxYawRight)
const maxPitchUpRad = MathUtils.degToRad(props.maxPitchUp)
const maxPitchDownRad = MathUtils.degToRad(props.maxPitchDown)
const lerpFactor = props.speed * 0.01

let currentYaw = 0
let currentPitch = 0
const lookTarget = new Vector3()

const { camera, renderer } = useTres()
const { onLoop } = useRenderLoop()

// Ждём готовности и камеры и рендерера (как в OrbitControlsDev)
watch([camera, renderer], ([newCamera, newRenderer]) => {
  if (newCamera && newRenderer && !initialized) {
    cam = newCamera
    cam.position.set(props.position[0], props.position[1], props.position[2])
    cam.fov = props.fov
    cam.updateProjectionMatrix()
    initialized = true
  }
}, { immediate: true })

onLoop(() => {
  if (!cam || !initialized) return

  const nx = (mouseX / winW) * 2 - 1
  const ny = (mouseY / winH) * 2 - 1
  
  // Вычисляем targetYaw с отдельными ограничениями для лево/право
  let targetYaw = 0
  if (nx < 0) {
    // Курсор слева - поворот влево
    targetYaw = -nx * maxYawLeftRad
  } else if (nx > 0) {
    // Курсор справа - поворот вправо
    targetYaw = -nx * maxYawRightRad
  }
  
  // Вычисляем targetPitch с отдельными ограничениями для верх/низ
  let targetPitch = 0
  if (ny < 0) {
    // Курсор вверху - поворот вверх
    targetPitch = -ny * maxPitchUpRad
  } else if (ny > 0) {
    // Курсор внизу - поворот вниз
    targetPitch = -ny * maxPitchDownRad
  }
  
  currentYaw += (targetYaw - currentYaw) * lerpFactor
  currentPitch += (targetPitch - currentPitch) * lerpFactor

  const yaw = props.baseYaw + currentYaw
  const pitch = props.basePitch + currentPitch
  
  lookTarget.set(
    cam.position.x - Math.sin(yaw) * Math.cos(pitch) * 10,
    cam.position.y + Math.sin(pitch) * 10,
    cam.position.z - Math.cos(yaw) * Math.cos(pitch) * 10
  )
  
  cam.lookAt(lookTarget)
})

onMounted(() => {
  winW = window.innerWidth
  winH = window.innerHeight
  mouseX = winW / 2
  mouseY = winH / 2
  
  window.addEventListener('mousemove', onMouseMove, { passive: true })
  window.addEventListener('resize', () => {
    winW = window.innerWidth
    winH = window.innerHeight
  }, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('mousemove', onMouseMove)
  cam = null
  initialized = false
})
</script>
