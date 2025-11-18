/**
 * Утилиты для работы с ролями пользователей
 */

/**
 * Переводит название роли на русский язык
 * @param {string} role - Название роли на английском
 * @returns {string} - Название роли на русском
 */
export function translateRole(role) {
    if (!role) {
        return 'Не указана';
    }

    const translations = {
        'student': 'Студент',
        'teacher': 'Преподаватель',
        'partner': 'Партнер',
        'admin': 'Администратор',
    };

    return translations[role.toLowerCase()] || role;
}

/**
 * Получает цвет для роли (для бейджей)
 * @param {string} role - Название роли
 * @returns {object} - Объект с классами для стилизации
 */
export function getRoleBadgeClass(role) {
    const classMap = {
        'student': 'bg-blue-100 text-blue-800 border-blue-200',
        'teacher': 'bg-purple-100 text-purple-800 border-purple-200',
        'partner': 'bg-green-100 text-green-800 border-green-200',
        'admin': 'bg-red-100 text-red-800 border-red-200',
    };

    return classMap[role?.toLowerCase()] || 'bg-gray-100 text-gray-800 border-gray-200';
}

