import { ref, watchEffect, shallowRef } from 'vue'
import { LinearToneMapping, PCFSoftShadowMap, SRGBColorSpace } from 'three'

/**
 * Composable for managing 3D scene lighting and shadow configuration
 * 
 * @returns {Object} Lighting configuration and shadow setup
 */
export function useSceneLighting() {
  const outputColorSpace = SRGBColorSpace
  const toneMapping = LinearToneMapping
  const toneMappingExposure = 1.0
  const shadowMapType = PCFSoftShadowMap
  const directionalLightRef = shallowRef(null)
  const shadowConfigured = ref(false)

  /**
   * Configure shadow settings for directional light
   * This watchEffect automatically configures shadows when light is available
   */
  watchEffect(() => {
    const light = directionalLightRef.value
    const shadow = light?.shadow
    if (!shadow || shadowConfigured.value) return

    shadow.mapSize?.set(4096, 4096)
    shadow.radius = 6
    shadow.bias = -0.0005
    shadow.normalBias = 0.03

    if (shadow.camera) {
      shadow.camera.near = 0.1
      shadow.camera.far = 25
      shadow.camera.left = -6
      shadow.camera.right = 6
      shadow.camera.top = 6
      shadow.camera.bottom = -6
      shadow.camera.updateProjectionMatrix()
    }

    shadow.needsUpdate = true
    shadowConfigured.value = true
  })

  return {
    // Lighting constants
    outputColorSpace,
    toneMapping,
    toneMappingExposure,
    shadowMapType,
    
    // Refs
    directionalLightRef,
    shadowConfigured
  }
}
