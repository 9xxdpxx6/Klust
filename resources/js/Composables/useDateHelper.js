/**
 * Composable для работы с датами без смещения часового пояса
 * Решает проблему, когда даты с сервера (YYYY-MM-DD) смещаются на день
 * из-за преобразования UTC в локальный часовой пояс
 */

/**
 * Парсит строку даты в формате YYYY-MM-DD как локальную дату
 * без смещения из-за часового пояса
 * @param {string|Date|null} dateString - Дата в формате YYYY-MM-DD или Date объект
 * @returns {Date|null} - Date объект или null
 */
export function parseLocalDate(dateString) {
    if (!dateString) return null;
    
    // Если это уже Date объект, возвращаем как есть
    if (dateString instanceof Date) {
        return dateString;
    }
    
    // Если это не строка, пробуем стандартный парсинг
    if (typeof dateString !== 'string') {
        return new Date(dateString);
    }
    
    // Парсим дату в формате YYYY-MM-DD как локальную дату
    // Это предотвращает смещение из-за часового пояса
    // Формат: YYYY-MM-DD или YYYY-MM-DD HH:mm:ss
    const dateOnlyMatch = dateString.match(/^(\d{4})-(\d{2})-(\d{2})(?:\s|T|$)/);
    if (dateOnlyMatch) {
        const year = parseInt(dateOnlyMatch[1], 10);
        const month = parseInt(dateOnlyMatch[2], 10) - 1; // Месяцы в JS начинаются с 0
        const day = parseInt(dateOnlyMatch[3], 10);
        return new Date(year, month, day);
    }
    
    // Если формат не распознан, пробуем стандартный парсинг
    return new Date(dateString);
}

/**
 * Форматирует дату для отправки на сервер в формате YYYY-MM-DD
 * @param {Date|string|null} date - Date объект или строка
 * @returns {string|null} - Дата в формате YYYY-MM-DD или null
 */
export function formatDateForServer(date) {
    if (!date) return null;
    
    if (date instanceof Date) {
        // Используем локальные компоненты даты, чтобы избежать смещения
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Если это уже строка в формате YYYY-MM-DD, возвращаем как есть
    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}/.test(date)) {
        return date.split('T')[0].split(' ')[0];
    }
    
    return date;
}

/**
 * Экспорт всех функций для удобства
 */
export function useDateHelper() {
    return {
        parseLocalDate,
        formatDateForServer,
    };
}

