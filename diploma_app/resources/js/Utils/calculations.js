import { ROUNDING_PRECISION } from '@/Utils/constants.js';
import Decimal from 'decimal.js';

/**
 * Округляет число до заданного количества знаков после запятой с математическим округлением (round half up).
 * @param {number|string|Decimal} number - Число для округления.
 * @param {number} decimals - Количество знаков после запятой.
 * @returns {Decimal} Округленное число как объект Decimal.
 */
export const roundToDecimal = (number, decimals) => {
    return new Decimal(number).toDecimalPlaces(decimals, Decimal.ROUND_HALF_UP);
};

/**
 * Вычисляет общее количество оценок для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки, тройки).
 * @returns {Decimal|null} Общее количество оценок как Decimal или null, если данные некорректны.
 */
export const calculateTotalGrades = (row) => {
    if (!row.discipline) return null;
    const fives = new Decimal(row.fives || 0);
    const fours = new Decimal(row.fours || 0);
    const threes = new Decimal(row.threes || 0);
    const sum = fives.plus(fours).plus(threes);
    return sum.equals(0) ? null : sum;
};

/**
 * Вычисляет процент успеваемости для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки и количество студентов.
 * @returns {Decimal|null} Процент успеваемости как Decimal или null, если данные некорректны.
 */
export const calculatePerformance = (row) => {
    if (!row.discipline || !row.students || new Decimal(row.students).equals(0))
        return null;
    const totalGrades = calculateTotalGrades(row);
    if (totalGrades === null) return null;
    const students = new Decimal(row.students);
    return totalGrades
        .dividedBy(students)
        .times(100)
        .toDecimalPlaces(ROUNDING_PRECISION.PERFORMANCE, Decimal.ROUND_HALF_UP);
};

/**
 * Вычисляет процент качества для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки) и количество студентов.
 * @returns {Decimal|null} Процент качества как Decimal или null, если данные некорректны.
 */
export const calculateQuality = (row) => {
    if (!row.discipline || !row.students || new Decimal(row.students).equals(0))
        return null;
    const fives = new Decimal(row.fives || 0);
    const fours = new Decimal(row.fours || 0);
    const students = new Decimal(row.students);
    return fives
        .plus(fours)
        .dividedBy(students)
        .times(100)
        .toDecimalPlaces(ROUNDING_PRECISION.QUALITY, Decimal.ROUND_HALF_UP);
};

/**
 * Вычисляет средний балл для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки, тройки).
 * @returns {Decimal|null} Средний балл как Decimal или null, если данные некорректны.
 */
export const calculateAverageScore = (row) => {
    const totalGrades = calculateTotalGrades(row);
    if (!row.discipline || totalGrades === null || totalGrades.equals(0))
        return null;
    const fives = new Decimal(row.fives || 0);
    const fours = new Decimal(row.fours || 0);
    const threes = new Decimal(row.threes || 0);
    const weightedSum = fives
        .times(5)
        .plus(fours.times(4))
        .plus(threes.times(3));
    return weightedSum
        .dividedBy(totalGrades)
        .toDecimalPlaces(
            ROUNDING_PRECISION.AVERAGE_SCORE,
            Decimal.ROUND_HALF_UP,
        );
};
