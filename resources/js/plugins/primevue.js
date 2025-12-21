import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';
import 'primeicons/primeicons.css';
import Aura from '@primeuix/themes/aura';
import { ruLocale } from '@/Utils/ruLocale';

/**
 * Настройка PrimeVue с темой Aura, цветами КубГТУ и русской локализацией
 */
export function setupPrimeVue(app) {
  app.use(PrimeVue, {
    locale: ruLocale,
    theme: {
      preset: Aura,
      options: {
        darkModeSelector: false, // Можно включить позже
        cssLayer: false,
      },
    },
  });
  
  // Регистрируем директиву Tooltip
  app.directive('tooltip', Tooltip);
}

