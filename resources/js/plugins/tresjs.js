import { TresPlugin } from '@tresjs/core';

/**
 * Настройка TresJS для работы с Three.js в Vue 3
 */
export function setupTresJS(app) {
  app.use(TresPlugin);
}
