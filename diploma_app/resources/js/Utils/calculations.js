/**
 * Округляет число до заданного количества знаков после запятой с математическим округлением (round half up).
 * @param {number} number - Число для округления.
 * @param {number} decimals - Количество знаков после запятой.
 * @returns {string} Округленное число как строка.
 */
export const roundToDecimal = (number, decimals) => {
    const factor = Math.pow(10, decimals);
    return (Math.round(number * factor) / factor).toFixed(decimals);
};

/**
 * Вычисляет общее количество оценок для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки, тройки).
 * @returns {number|null} Общее количество оценок или null, если дисциплина не задана или сумма оценок равна нулю.
 */
export const calculateTotalGrades = (row) => {
    if (!row.discipline) return null;
    const sum = row.fives + row.fours + row.threes;
    return sum === 0 ? null : sum;
};

/**
 * Вычисляет процент успеваемости для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки и количество студентов.
 * @returns {string|null} Процент успеваемости, округленный до 2 знаков после запятой, или null, если данные недействительны.
 */
export const calculatePerformance = (row) => {
    if (!row.discipline || row.students === 0) return null;
    const totalGrades = calculateTotalGrades(row);
    if (totalGrades === null) return null;
    const value = (totalGrades / row.students) * 100;
    return roundToDecimal(value, 2);
};

/**
 * Вычисляет процент качества для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки) и количество студентов.
 * @returns {string|null} Процент качества, округленный до 2 знаков после запятой, или null, если данные недействительны.
 */
export const calculateQuality = (row) => {
    if (!row.discipline || row.students === 0) return null;
    const value = ((row.fives + row.fours) / row.students) * 100;
    return roundToDecimal(value, 2);
};

/**
 * Вычисляет средний балл для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки, тройки).
 * @returns {string|null} Средний балл, округленный до 2 знаков после запятой, или null, если данные недействительны.
 */
export const calculateAverageScore = (row) => {
    const totalGrades = calculateTotalGrades(row);
    if (!row.discipline || totalGrades === null || totalGrades === 0)
        return null;
    const weightedSum = row.fives * 5 + row.fours * 4 + row.threes * 3;
    const value = weightedSum / totalGrades;
    return roundToDecimal(value, 2);
};
