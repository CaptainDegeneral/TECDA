<script setup>
import Modal from '@/Components/Modal.vue';
import { deleteSubject } from '@/api/subjects.js';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

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

const submit = async () => {
    try {
        await deleteSubject(props.id);

        emit('deleted');
        closeModal();
    } catch (exception) {
        console.log(exception.response.data.message);
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
                <danger-button class="mt-6" type="submit"
                    >Удалить</danger-button
                >
                <secondary-button class="ms-3" @click="closeModal">
                    Отмена
                </secondary-button>
            </form>
        </div>
    </modal>
</template>

<style scoped></style>
