/**
 * Проверяет данные на наличие недопустимых значений (null или '0.00').
 * @param {string} data - JSON-строка с данными.
 * @returns {boolean} True, если есть недопустимые значения, иначе false.
 */
export const hasInvalidNulls = (data) => {
    const parsedData = JSON.parse(data);
    const checkForInvalidValues = (obj, path = []) => {
        if (path.length === 1 && path[0] === 'finalResults') return false;

        if (Array.isArray(obj)) {
            return obj.some((item, index) =>
                checkForInvalidValues(item, [...path, index]),
            );
        }

        if (obj !== null && typeof obj === 'object') {
            return Object.entries(obj).some(([key, value]) =>
                checkForInvalidValues(value, [...path, key]),
            );
        }

        return obj === null || obj === '0.00';
    };
    return checkForInvalidValues(parsedData);
};
