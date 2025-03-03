<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { reactive } from 'vue';
import { createSubject } from '@/api/subjects.js';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import { handleApiError } from '@/Utils/errorHandler.js';

const notification = useNotificationStore();
const { addNotification } = notification;

const emit = defineEmits(['closeModal', 'created']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const form = reactive({
    name: null,
    code: null,
});

const submit = async () => {
    try {
        await createSubject(form);

        emit('created');
        form.name = null;
        form.code = null;
        closeModal();
    } catch (exception) {
        handleApiError(exception, addNotification);
    }
};

const closeModal = () => {
    form.name = null;
    form.code = null;
    emit('closeModal');
};
</script>

<template>
    <modal :show="show">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Создание новой дисциплины
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Для создания новой дисциплины необходимо ввести ее название,
                например, "Основы алгоритмизации и программирования". Кодовое
                обозначение (например, МДК.00.00) указывается при наличии, за
                исключением производственной или учебной практики — в этом
                случае оно является обязательным.
            </p>
            <form @submit.prevent="submit">
                <div class="mt-6">
                    <input-label
                        for="name"
                        value="Название дисциплины"
                        class="sr-only"
                    />

                    <text-input
                        id="name"
                        ref="name"
                        v-model="form.name"
                        class="mt-1 block w-full"
                        placeholder="Название дисциплины"
                    />
                </div>

                <div class="mb-6 mt-6">
                    <input-label
                        for="code"
                        value="Код дисциплины"
                        class="sr-only"
                    />

                    <text-input
                        id="code"
                        ref="code"
                        v-model="form.code"
                        class="mt-1 block w-full"
                        placeholder="Код дисциплины"
                    />
                </div>

                <div class="flex flex-row">
                    <primary-button
                        type="submit"
                        class="mr-4"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Создать
                    </primary-button>

                    <secondary-button @click="closeModal">
                        Отмена
                    </secondary-button>
                </div>
            </form>
        </div>
    </modal>
</template>

<style scoped></style>
