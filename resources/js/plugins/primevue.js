import PrimeVue from 'primevue/config';
import 'primeicons/primeicons.css';

/**
 * Настройка PrimeVue с цветами КубГТУ
 */
export function setupPrimeVue(app) {
  app.use(PrimeVue, {
    theme: {
      options: {
        darkModeSelector: false, // Можно включить позже
        cssLayer: false,
      },
    },
  });
}

