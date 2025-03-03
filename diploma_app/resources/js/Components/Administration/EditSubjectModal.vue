<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { reactive, ref, watch } from 'vue';
import { editSubject, getSubject } from '@/api/subjects.js';
import NProgress from 'nprogress';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import { handleApiError } from '@/Utils/errorHandler.js';

const notification = useNotificationStore();
const { addNotification } = notification;

const emit = defineEmits(['closeModal', 'edited']);

const props = defineProps({
    id: {
        required: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const form = reactive({
    name: null,
    code: null,
});

const loading = ref(false);

const submit = async () => {
    try {
        NProgress.start();
        loading.value = true;

        await editSubject(props.id, form);

        emit('edited');
        closeModal();
    } catch (exception) {
        handleApiError(exception, addNotification);
    } finally {
        NProgress.done();
        loading.value = false;
    }
};

watch(
    () => props.id,
    async (newId) => {
        if (newId) {
            const response = await getSubject(newId);

            form.name = response.data.name;
            form.code = response.data.code;
        }
    },
);

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
                Редактирование дисциплины
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Для редактирования дисциплины можно изменить ее название и
                кодовое обозначение. Обратите внимание, что изменения,
                касающиеся производственной или учебной практики, потребуют
                обязательного заполнения кодового обозначения.
            </p>
            <form @submit.prevent="submit">
                <div class="mt-6">
                    <input-label for="name" value="Название дисциплины" />
                    <text-input
                        id="name"
                        ref="name"
                        v-model="form.name"
                        class="mt-1 block w-full"
                        placeholder="Название дисциплины"
                    />
                </div>

                <div class="mb-6 mt-6">
                    <input-label for="code" value="Код дисциплины" />
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
                        :class="{ 'opacity-25': loading }"
                        :disabled="loading"
                    >
                        Сохранить
                    </primary-button>

                    <secondary-button @click="closeModal" :disabled="loading">
                        Отмена
                    </secondary-button>
                </div>
            </form>
        </div>
    </modal>
</template>

<style scoped></style>
