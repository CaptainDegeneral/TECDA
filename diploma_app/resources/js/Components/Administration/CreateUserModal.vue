<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { reactive, ref } from 'vue';
import RoleSelect from '@/Components/Administration/RoleSelect.vue';
import { createUser } from '@/api/users.js';
import NProgress from 'nprogress';
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
    last_name: null,
    name: null,
    surname: null,
    email: null,
    role_id: null,
    password: null,
});

const loading = ref(false);

const submit = async () => {
    try {
        NProgress.start();
        loading.value = true;

        await createUser(form);

        emit('created');
        resetForm();
        closeModal();
    } catch (exception) {
        handleApiError(exception, addNotification);
    } finally {
        NProgress.done();
        loading.value = false;
    }
};

const resetForm = () => {
    form.last_name = null;
    form.name = null;
    form.surname = null;
    form.email = null;
    form.role_id = null;
    form.password = null;
};

const closeModal = () => {
    resetForm();
    emit('closeModal');
};
</script>

<template>
    <modal :show="show">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Добавление нового пользователя
            </h2>
            <form @submit.prevent="submit">
                <div class="mt-6">
                    <input-label
                        for="last_name"
                        value="Фамилия"
                        class="sr-only"
                    />

                    <text-input
                        id="last_name"
                        ref="last_name"
                        v-model="form.last_name"
                        class="mt-1 block w-full"
                        placeholder="Фамилия"
                    />
                </div>

                <div class="mt-6">
                    <input-label for="name" value="Имя" class="sr-only" />

                    <text-input
                        id="name"
                        ref="name"
                        v-model="form.name"
                        class="mt-1 block w-full"
                        placeholder="Имя"
                    />
                </div>

                <div class="mt-6">
                    <input-label
                        for="surname"
                        value="Отчество"
                        class="sr-only"
                    />

                    <text-input
                        id="surname"
                        ref="surname"
                        v-model="form.surname"
                        class="mt-1 block w-full"
                        placeholder="Отчество"
                    />
                </div>

                <div class="mt-6">
                    <input-label for="email" value="Email" class="sr-only" />

                    <text-input
                        id="email"
                        ref="email"
                        type="email"
                        v-model="form.email"
                        class="mt-1 block w-full"
                        placeholder="Email"
                    />
                </div>

                <div class="mt-6">
                    <input-label
                        for="password"
                        value="Пароль"
                        class="sr-only"
                    />

                    <text-input
                        id="password"
                        ref="password"
                        type="password"
                        v-model="form.password"
                        class="mt-1 block w-full"
                        placeholder="Пароль"
                    />
                </div>

                <div class="mb-6 mt-6">
                    <role-select v-model="form.role_id"></role-select>
                </div>

                <div class="flex flex-row">
                    <primary-button
                        type="submit"
                        class="mr-3"
                        :class="{ 'opacity-25': loading }"
                        :disabled="loading"
                    >
                        Создать
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
