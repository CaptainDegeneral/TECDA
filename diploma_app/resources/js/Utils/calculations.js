/**
 * Округляет число до заданного количества знаков после запятой с математическим округлением (round half up).
 * @param {number} number - Число для округления.
 * @param {number} decimals - Количество знаков после запятой.
 * @returns {string} Округленное число как строка.
 */
const roundToDecimal = (number, decimals) => {
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
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки, тройки).
 * @returns {string|null} Процент успеваемости, округленный до 2 знаков после запятой, или null, если данные недействительны.
 */
export const calculatePerformance = (row) => {
    if (!row.discipline) return null;
    const total = calculateTotalGrades(row);
    if (!total) return null;
    const value =
        ((row.fives * 5 + row.fours * 4 + row.threes * 3) / (total * 5)) * 100;
    return roundToDecimal(value, 2);
};

/**
 * Вычисляет процент качества для заданной строки.
 * @param {Object} row - Данные строки, содержащие оценки (пятёрки, четвёрки) и количество студентов.
 * @returns {string|null} Процент качества, округленный до 2 знаков после запятой, или null, если данные недействительны.
 */
export const calculateQuality = (row) => {
    if (!row.discipline) return null;
    if (row.students === 0) return null;
    const value = ((row.fives + row.fours) / row.students) * 100;
    return roundToDecimal(value, 2);
};
