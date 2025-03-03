<script setup>
import Modal from '@/Components/Modal.vue';
import { deleteSubject } from '@/api/subjects.js';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';
import NProgress from 'nprogress';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import { handleApiError } from '@/Utils/errorHandler.js';

const notification = useNotificationStore();
const { addNotification } = notification;

const emit = defineEmits(['closeModal', 'deleted']);

const props = defineProps({
    id: {
        required: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const loading = ref(false);

const submit = async () => {
    try {
        NProgress.start();
        loading.value = true;

        await deleteSubject(props.id);

        emit('deleted');
        closeModal();
    } catch (exception) {
        handleApiError(exception, addNotification);
    } finally {
        NProgress.done();
        loading.value = false;
    }
};

const closeModal = () => {
    emit('closeModal');
};
</script>

<template>
    <modal :show="show">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Удаление дисциплины
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Удаление дисциплины является безвозвратным процессом. После
                подтверждения операции вся информация о дисциплине будет удалена
                навсегда.
            </p>

            <form @submit.prevent="submit">
                <danger-button
                    class="mt-6"
                    type="submit"
                    :class="{ 'opacity-25': loading }"
                    :disabled="loading"
                >
                    Удалить
                </danger-button>
                <secondary-button
                    class="ms-3"
                    @click="closeModal"
                    :disabled="loading"
                >
                    Отмена
                </secondary-button>
            </form>
        </div>
    </modal>
</template>

<style scoped></style>
