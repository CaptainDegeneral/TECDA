import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notificationStore', () => {
    const notifications = ref([]);

    const addNotification = (type, message, duration = 5000) => {
        notifications.value.push({ type, message, duration });
    };

    const removeNotification = (index) => {
        notifications.value.splice(index, 1);
    };

    return {
        notifications,
        addNotification,
        removeNotification,
    };
});
