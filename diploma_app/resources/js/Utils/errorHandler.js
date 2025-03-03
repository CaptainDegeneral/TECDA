/**
 * Обрабатывает ошибки API и отправляет уведомления
 * @param {Object} exception - Ошибка, полученная из API
 * @param {Function} addNotification - Функция для добавления уведомлений
 * @param {string} [defaultMessage] - Сообщение по умолчанию, если конкретных ошибок нет
 */
const handleApiError = (
    exception,
    addNotification,
    defaultMessage = 'Произошла ошибка',
) => {
    const errors = exception.response?.data?.errors;

    if (errors) {
        Object.values(errors).forEach((errorMessages) => {
            errorMessages.forEach((message) => {
                addNotification('error', message);
            });
        });
    } else {
        addNotification(
            'error',
            exception.response?.data?.message || defaultMessage,
        );
    }
};

export { handleApiError };
