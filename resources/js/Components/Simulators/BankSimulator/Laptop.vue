<template>
  <TresGroup 
    :position="position" 
    :rotation="rotation"
    @pointer-enter="onHoverEnter"
    @pointer-leave="onHoverLeave"
  >
    <TresGroup v-if="isModelLoaded" :scale="scaleArray">
      <primitive :object="gltfScene" />
      
      <!-- Экран ноутбука с изображением рабочего стола -->
      <TresMesh 
        v-if="screenTexture && screenGeometry"
        :position="screenPosition" 
        :rotation="screenRotation"
        :geometry="screenGeometry"
      >
        <TresMeshBasicMaterial 
          :map="screenTexture" 
          :toneMapped="false"
        />
      </TresMesh>
    </TresGroup>
    <TresGroup v-else :scale="scaleArray">
      <!-- Экран -->
      <TresMesh :position="[0, 0, 0]">
        <TresBoxGeometry :args="[0.6, 0.4, 0.05]" />
        <TresMeshStandardMaterial :color="color" />
      </TresMesh>
      
      <!-- Подставка -->
      <TresMesh :position="[0, -0.25, 0]">
        <TresBoxGeometry :args="[0.2, 0.1, 0.2]" />
        <TresMeshStandardMaterial color="#333333" />
      </TresMesh>
    </TresGroup>
  </TresGroup>
</template>

<script setup>
import { ref, shallowRef, watchEffect, onMounted, computed } from 'vue'
import { useTres, useLoop } from '@tresjs/core'
import { useGLTF } from '@tresjs/cientos'
import { LinearFilter, LinearMipMapLinearFilter, SRGBColorSpace, TextureLoader, Shape, ShapeGeometry, MathUtils } from 'three'

const props = defineProps({
  position: {
    type: Array,
    default: () => [0, 1.2, -0.8]
  },
  rotation: {
    type: Array,
    default: () => [0, 0, 0]
  },
  color: {
    type: String,
    default: '#1e40af'
  },
  // Путь к изображению экрана
  screenImage: {
    type: String,
    default: '/images/assets/desktop.png'
  },
  // Позиция экрана относительно ноутбука (подстроить под модель)
  screenPosition: {
    type: Array,
    default: () => [0.0, 0.102, -0.148]
  },
  // Поворот экрана (наклон под углом крышки ноутбука)
  screenRotation: {
    type: Array,
    default: () => [-0.35, 0, 0]
  },
  // Размер экрана [width, height]
  screenSize: {
    type: Array,
    default: () => [0.30, 0.2]
  },
  // Радиус скругления верхних углов
  screenCornerRadius: {
    type: Number,
    default: 0.0075
  }
})

const scale = ref(1)
const targetScale = ref(1)
const isModelLoaded = ref(false)
const gltfScene = shallowRef(null)
const gltfResult = shallowRef(null)
const screenTexture = shallowRef(null)
const screenGeometry = shallowRef(null)
const { renderer } = useTres()

// Computed scale как массив для реактивности TresGroup
const scaleArray = computed(() => [scale.value, scale.value, scale.value])

// Hover обработчики
const onHoverEnter = () => {
  targetScale.value = 1.04
}

const onHoverLeave = () => {
  targetScale.value = 1
}

// Создание геометрии экрана с закруглёнными верхними углами
const createScreenGeometry = () => {
  const width = props.screenSize[0]
  const height = props.screenSize[1]
  const radius = props.screenCornerRadius
  
  const shape = new Shape()
  
  // Начинаем с нижнего левого угла (без скругления)
  shape.moveTo(-width / 2, -height / 2)
  
  // Нижняя сторона → нижний правый угол (без скругления)
  shape.lineTo(width / 2, -height / 2)
  
  // Правая сторона → верхний правый угол (со скруглением)
  shape.lineTo(width / 2, height / 2 - radius)
  shape.quadraticCurveTo(width / 2, height / 2, width / 2 - radius, height / 2)
  
  // Верхняя сторона → верхний левый угол (со скруглением)
  shape.lineTo(-width / 2 + radius, height / 2)
  shape.quadraticCurveTo(-width / 2, height / 2, -width / 2, height / 2 - radius)
  
  // Левая сторона → обратно к началу
  shape.lineTo(-width / 2, -height / 2)
  
  const geometry = new ShapeGeometry(shape)
  
  // Пересчитываем UV-координаты чтобы текстура заполняла весь экран
  const uvAttribute = geometry.attributes.uv
  const posAttribute = geometry.attributes.position
  
  for (let i = 0; i < uvAttribute.count; i++) {
    const x = posAttribute.getX(i)
    const y = posAttribute.getY(i)
    
    // Преобразуем координаты из [-width/2, width/2] в [0, 1]
    const u = (x + width / 2) / width
    const v = (y + height / 2) / height
    
    uvAttribute.setXY(i, u, v)
  }
  
  uvAttribute.needsUpdate = true
  
  return geometry
}

// Загрузка текстуры экрана и создание геометрии
onMounted(() => {
  // Создаём геометрию экрана со скруглёнными верхними углами
  screenGeometry.value = createScreenGeometry()
  
  if (props.screenImage) {
    const loader = new TextureLoader()
    loader.load(
      props.screenImage,
      (texture) => {
        texture.colorSpace = SRGBColorSpace
        texture.flipY = true
        texture.needsUpdate = true
        screenTexture.value = texture
      },
      undefined,
      (error) => {
      }
    )
  }
})

const configureScene = (scene) => {
  scene.traverse((object) => {
    if (!object.isMesh) return
    object.castShadow = true
    object.receiveShadow = true
    const materials = Array.isArray(object.material) ? object.material : [object.material]
    materials.forEach((material) => {
      if (!material) return
      const maxAnisotropy = renderer.value?.capabilities?.getMaxAnisotropy?.() ?? 1
      const maps = [material.map, material.emissiveMap].filter(Boolean)
      maps.forEach((map) => {
        map.colorSpace = SRGBColorSpace
        map.anisotropy = maxAnisotropy
        map.generateMipmaps = true
        map.minFilter = LinearMipMapLinearFilter
        map.magFilter = LinearFilter
        map.needsUpdate = true
      })
      material.needsUpdate = true
    })
  })
}

try {
  const gltfPromise = useGLTF('/models/laptop.glb')
  if (gltfPromise && typeof gltfPromise.then === 'function') {
    gltfPromise
      .then((result) => {
        gltfResult.value = result
      })
      .catch(() => {
        // SAFE FALLBACK
      })
  }
} catch (error) {
  // SAFE FALLBACK
}

watchEffect(() => {
  const loadedScene = gltfResult.value?.scene?.value ?? gltfResult.value?.scene ?? null
  const hasError = gltfResult.value?.error?.value ?? gltfResult.value?.error ?? null
  if (hasError || !loadedScene) {
    isModelLoaded.value = false
    return
  }
  if (gltfScene.value !== loadedScene) {
    configureScene(loadedScene)
    gltfScene.value = loadedScene
  }
  isModelLoaded.value = true
})

// Анимация scale при hover
const { onBeforeRender } = useLoop()

onBeforeRender(({ delta }) => {
  scale.value = MathUtils.damp(scale.value, targetScale.value, 18, delta)
})
</script>
