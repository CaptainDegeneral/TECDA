import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notificationStore', () => {
    const notifications = ref([]);
    const shownNotifications = ref(new Set());

    const addNotification = (type, message, duration = 5000) => {
        const notificationKey = `${type}-${message}`;
        if (shownNotifications.value.has(notificationKey)) {
            return;
        }
        const id = Date.now() + Math.random();
        notifications.value.push({ id, type, message, duration });
        shownNotifications.value.add(notificationKey);
        setTimeout(() => {
            shownNotifications.value.delete(notificationKey);
        }, duration);
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
