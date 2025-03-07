import {
    calculateAverageScore,
    calculatePerformance,
    calculateQuality,
    calculateTotalGrades,
} from '@/Utils/calculations.js';
import Decimal from 'decimal.js';

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
 * Форматирует число Decimal в строку, убирая незначащие нули.
 * @param {Decimal} decimal - Число Decimal для форматирования.
 * @returns {string} Отформатированная строка.
 */
const formatNumberString = (decimal) => {
    const str = decimal.toFixed(2); // Сначала фиксируем до 2 знаков
    if (str.endsWith('.00')) {
        return str.slice(0, -3); // Убираем .00 для целых чисел
    }
    if (str.endsWith('0')) {
        return str.slice(0, -1); // Убираем последний ноль, если он незначащий
    }
    return str;
};

/**
 * Финализирует данные вкладок, добавляя вычисленные значения в виде строк без незначащих нулей.
 * @param {Array} tabsData - Массив данных вкладок.
 * @returns {Array} Обогащенные данные вкладок с вычисленными значениями.
 */
export const getEnhancedTabsData = (tabsData) => {
    return tabsData.map((tab) => ({
        ...tab,
        autumnWinter: tab.autumnWinter.map((row) => {
            const totalGrades = calculateTotalGrades(row);
            const performance = calculatePerformance(row);
            const quality = calculateQuality(row);
            const averageScore = calculateAverageScore(row);

            return {
                ...row,
                totalGrades:
                    totalGrades !== null ? totalGrades.toString() : null,
                performance:
                    performance !== null
                        ? formatNumberString(performance)
                        : null,
                quality: quality !== null ? formatNumberString(quality) : null,
                averageScore:
                    averageScore !== null
                        ? formatNumberString(averageScore)
                        : null,
            };
        }),
        springSummer: tab.springSummer.map((row) => {
            const totalGrades = calculateTotalGrades(row);
            const performance = calculatePerformance(row);
            const quality = calculateQuality(row);
            const averageScore = calculateAverageScore(row);

            return {
                ...row,
                totalGrades:
                    totalGrades !== null ? totalGrades.toString() : null,
                performance:
                    performance !== null
                        ? formatNumberString(performance)
                        : null,
                quality: quality !== null ? formatNumberString(quality) : null,
                averageScore:
                    averageScore !== null
                        ? formatNumberString(averageScore)
                        : null,
            };
        }),
    }));
};

/**
 * Создаёт пустую строку для таблицы семестра.
 * @returns {Object} Пустая строка с начальными значениями.
 */
export const createEmptyRow = () => ({
    discipline: '',
    group: '',
    students: 0,
    fives: 0,
    fours: 0,
    threes: 0,
});

/**
 * Генерирует вкладки на основе конфигурации.
 * @param {number} yearsOfWork - Количество лет работы.
 * @param {number} startYear - Стартовый год.
 * @returns {Array} Массив вкладок с семестрами.
 */
export const generateTabs = (yearsOfWork, startYear) => {
    const tabsCount = Math.min(yearsOfWork, 5);
    return Array.from({ length: tabsCount }, (_, index) => ({
        label: `${startYear + index}-${startYear + index + 1}`,
        autumnWinter: [createEmptyRow()],
        springSummer: [createEmptyRow()],
    }));
};

/**
 * Преобразует значения Decimal в строки без незначащих нулей для сериализации.
 * @param {any} data - Данные для обработки.
 * @returns {any} Данные с преобразованными значениями.
 */
const formatDecimalValues = (data) => {
    if (data === null || data === undefined) return data;

    if (data instanceof Decimal) {
        return formatNumberString(data);
    }

    if (Array.isArray(data)) {
        return data.map(formatDecimalValues);
    }

    if (typeof data === 'object') {
        const formatted = {};
        Object.keys(data).forEach((key) => {
            formatted[key] = formatDecimalValues(data[key]);
        });
        return formatted;
    }

    return data;
};

/**
 * Собирает все данные для отчета, преобразуя Decimal в строки без незначащих нулей.
 * @param {Object} config - Конфигурация (category, yearsOfWork, startYear).
 * @param {Array} tabsData - Данные вкладок.
 * @param {Array} intermediateResults - Промежуточные результаты.
 * @param {Object} finalResults - Конечные результаты.
 * @param {Array} overallResults - Общие результаты.
 * @returns {string} JSON-строка с очищенными данными и строковыми значениями.
 */
export const collectReportData = (
    config,
    tabsData,
    intermediateResults,
    finalResults,
    overallResults,
) => {
    const data = {
        configuration: {
            category: config.category || null,
            yearsOfWork: config.yearsOfWork > 0 ? config.yearsOfWork : null,
            startYear: config.startYear > 0 ? config.startYear : null,
        },
        tabsData: getEnhancedTabsData(tabsData),
        intermediateResults: formatDecimalValues(intermediateResults),
        finalResults: formatDecimalValues(finalResults),
        overallResults: formatDecimalValues(overallResults),
    };
    return JSON.stringify(sanitizeData(data), null, 2);
};
