/**
 * Composable для настройки календаря PrimeVue
 * Предоставляет общие настройки (локаль настроена глобально в plugins/primevue.js)
 */
export function useCalendar() {
  const dateFormat = 'dd.mm.yy';
  const defaultPlaceholder = 'дд.мм.гггг';
  const showIcon = true;
  const inputClass = 'w-full';

  return {
    dateFormat,
    defaultPlaceholder,
    showIcon,
    inputClass,
  };
}

