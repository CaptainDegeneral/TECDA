import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notificationStore', () => {
    const notifications = ref([]);

    const addNotification = (type, message, duration = 5000) => {
        const id = Date.now() + Math.random();
        notifications.value.push({ id, type, message, duration });
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
