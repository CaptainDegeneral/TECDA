import {
    calculateAverageScore,
    calculatePerformance,
    calculateQuality,
} from '@/Utils/calculations.js';

import Decimal from 'decimal.js';

/**
 * Вычисляет промежуточные результаты на основе данных вкладок.
 * @param {Array} tabsData - Данные вкладок с семестрами.
 * @returns {Array} Массив промежуточных результатов по дисциплинам.
 */
export const calculateIntermediateResults = (tabsData) => {
    return tabsData.map((tab) => {
        const allRows = [...tab.autumnWinter, ...tab.springSummer];
        const disciplineMap = {};

        allRows.forEach((row) => {
            const discKey = row.discipline ? row.discipline.code_name : null;
            if (discKey) {
                if (!disciplineMap[discKey]) {
                    disciplineMap[discKey] = {
                        performance: [],
                        quality: [],
                        averageScore: [],
                    };
                }
                const perf = calculatePerformance(row);
                const qual = calculateQuality(row);
                const avgScore = calculateAverageScore(row);
                if (perf !== null)
                    disciplineMap[discKey].performance.push(perf);
                if (qual !== null) disciplineMap[discKey].quality.push(qual);
                if (avgScore !== null)
                    disciplineMap[discKey].averageScore.push(avgScore);
            }
        });

        return Object.entries(disciplineMap).map(([discKey, data]) => {
            const calcAverage = (values) =>
                values.length
                    ? values
                          .reduce((sum, val) => sum.plus(val), new Decimal(0))
                          .dividedBy(values.length)
                          .toDecimalPlaces(2, Decimal.ROUND_HALF_UP)
                    : null;

            return {
                discipline: discKey,
                performance: calcAverage(data.performance),
                quality: calcAverage(data.quality),
                averageScore: calcAverage(data.averageScore),
            };
        });
    });
};

/**
 * Вычисляет конечные результаты на основе промежуточных данных.
 * @param {Array} tabsData - Данные вкладок.
 * @param {Array} intermediateResults - Промежуточные результаты.
 * @returns {Object} Объект с таблицами успеваемости, качества и среднего балла.
 */
export const calculateFinalResults = (tabsData, intermediateResults) => {
    const allDisciplines = new Set();
    intermediateResults.forEach((tabResults) => {
        tabResults.forEach((result) => allDisciplines.add(result.discipline));
    });

    const disciplinesList = Array.from(allDisciplines);
    const createTable = (prop) => {
        return disciplinesList.map((discipline) => {
            const row = { discipline };
            tabsData.forEach((tab, index) => {
                const result = intermediateResults[index].find(
                    (r) => r.discipline === discipline,
                );
                row[tab.label] =
                    result && result[prop] !== null ? result[prop] : null;
            });
            return row;
        });
    };

    return {
        performanceTable: createTable('performance'),
        qualityTable: createTable('quality'),
        averageScoreTable: createTable('averageScore'),
    };
};

/**
 * Вычисляет общие результаты за все периоды.
 * @param {Array} intermediateResults - Промежуточные результаты.
 * @returns {Array} Массив общих результатов по дисциплинам.
 */
export const calculateOverallResults = (intermediateResults) => {
    const allDisciplines = new Set();
    intermediateResults.forEach((tabResults) => {
        tabResults.forEach((result) => allDisciplines.add(result.discipline));
    });

    const disciplinesList = Array.from(allDisciplines);
    return disciplinesList.map((discipline) => {
        const performanceValues = [];
        const qualityValues = [];
        const averageScoreValues = [];

        intermediateResults.forEach((tabResults) => {
            const result = tabResults.find((r) => r.discipline === discipline);
            if (result) {
                if (result.performance !== null)
                    performanceValues.push(result.performance);
                if (result.quality !== null) qualityValues.push(result.quality);
                if (result.averageScore !== null)
                    averageScoreValues.push(result.averageScore);
            }
        });

        const calcAverage = (values) =>
            values.length
                ? values
                      .reduce((sum, val) => sum.plus(val), new Decimal(0))
                      .dividedBy(values.length)
                      .toDecimalPlaces(2, Decimal.ROUND_HALF_UP)
                : null;

        return {
            discipline,
            avgPerformance: calcAverage(performanceValues),
            avgQuality: calcAverage(qualityValues),
            avgAverageScore: calcAverage(averageScoreValues),
        };
    });
};
