<script setup>
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { deleteUser } from '@/api/users.js';

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
        await deleteUser(props.id);

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
            <h2>Удалить пользователя?</h2>
            <form @submit.prevent="submit">
                <danger-button type="submit"> Удалить </danger-button>
                <secondary-button @click="closeModal">
                    Отмена
                </secondary-button>
            </form>
        </div>
    </modal>
</template>

<style scoped></style>
