import {
    calculateAverageScore,
    calculatePerformance,
    calculateQuality,
    roundToDecimal,
} from '@/Utils/calculations.js';

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
                const perf = parseFloat(calculatePerformance(row));
                const qual = parseFloat(calculateQuality(row));
                const avgScore = parseFloat(calculateAverageScore(row));
                if (!isNaN(perf) && perf !== null) {
                    disciplineMap[discKey].performance.push(perf);
                }
                if (!isNaN(qual) && qual !== null) {
                    disciplineMap[discKey].quality.push(qual);
                }
                if (!isNaN(avgScore) && avgScore !== null) {
                    disciplineMap[discKey].averageScore.push(avgScore);
                }
            }
        });

        return Object.entries(disciplineMap).map(([discKey, data]) => ({
            discipline: discKey,
            performance: data.performance.length
                ? roundToDecimal(
                      data.performance.reduce((sum, val) => sum + val, 0) /
                          data.performance.length,
                      2,
                  )
                : null,
            quality: data.quality.length
                ? roundToDecimal(
                      data.quality.reduce((sum, val) => sum + val, 0) /
                          data.quality.length,
                      2,
                  )
                : null,
            averageScore: data.averageScore.length
                ? roundToDecimal(
                      data.averageScore.reduce((sum, val) => sum + val, 0) /
                          data.averageScore.length,
                      2,
                  )
                : null,
        }));
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
                row[tab.label] = result ? result[prop] : null;
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
            if (result && result.performance !== null) {
                performanceValues.push(parseFloat(result.performance));
            }
            if (result && result.quality !== null) {
                qualityValues.push(parseFloat(result.quality));
            }
            if (result && result.averageScore !== null) {
                averageScoreValues.push(parseFloat(result.averageScore));
            }
        });
        const avgPerformance =
            performanceValues.length > 0
                ? roundToDecimal(
                      performanceValues.reduce((sum, val) => sum + val, 0) /
                          performanceValues.length,
                      2,
                  )
                : null;
        const avgQuality =
            qualityValues.length > 0
                ? roundToDecimal(
                      qualityValues.reduce((sum, val) => sum + val, 0) /
                          qualityValues.length,
                      2,
                  )
                : null;
        const avgAverageScore =
            averageScoreValues.length > 0
                ? roundToDecimal(
                      averageScoreValues.reduce((sum, val) => sum + val, 0) /
                          averageScoreValues.length,
                      2,
                  )
                : null;
        return {
            discipline,
            avgPerformance,
            avgQuality,
            avgAverageScore,
        };
    });
};
