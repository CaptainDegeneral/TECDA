import {
    calculatePerformance,
    calculateQuality,
    calculateTotalGrades,
} from '@/Utils/calculations.js';

/**
 * Очищает данные, удаляя пустые строки, неопределенные значения и преобразуя их в null.
 * @param {any} data - Данные для очистки.
 * @returns {any} Очищенные данные.
 */
export const sanitizeData = (data) => {
    if (data === undefined) return null;

    if (typeof data === 'string' && data.trim() === '') return null;

    if (Array.isArray(data)) return data.map(sanitizeData);

    if (data !== null && typeof data === 'object') {
        const sanitized = {};

        Object.keys(data).forEach((key) => {
            sanitized[key] = sanitizeData(data[key]);
        });

        return sanitized;
    }
    return data;
};

/**
 * Финализирует данные вкладок, добавляя вычисленные общее количество оценок, успеваемость и качество для каждой строки.
 * @param {Array} tabsData - Массив данных вкладок, содержащий строки для осенне-зимнего и весенне-летнего семестров.
 * @returns {Array} Обогащенные данные вкладок с вычисленными значениями.
 */
export const getEnhancedTabsData = (tabsData) => {
    return tabsData.map((tab) => ({
        ...tab,
        autumnWinter: tab.autumnWinter.map((row) => ({
            ...row,
            totalGrades: calculateTotalGrades(row),
            performance: calculatePerformance(row),
            quality: calculateQuality(row),
        })),
        springSummer: tab.springSummer.map((row) => ({
            ...row,
            totalGrades: calculateTotalGrades(row),
            performance: calculatePerformance(row),
            quality: calculateQuality(row),
        })),
    }));
};
