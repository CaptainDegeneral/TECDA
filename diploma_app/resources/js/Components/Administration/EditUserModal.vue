<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { reactive, ref, watch } from 'vue';
import RoleSelect from '@/Components/Administration/RoleSelect.vue';
import { editUser, getUser } from '@/api/users.js';
import Checkbox from '@/Components/Checkbox.vue';
import NProgress from 'nprogress';

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
    last_name: null,
    name: null,
    surname: null,
    email: null,
    role_id: null,
    password: null,
    verified: false,
});

const loading = ref(false);

const submit = async () => {
    try {
        NProgress.start();
        loading.value = true;

        await editUser(props.id, form);

        emit('edited');
        resetForm();
        closeModal();
    } catch (exception) {
        console.log(exception.response.data.message);
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
    form.verified = false;
};

const closeModal = () => {
    resetForm();
    emit('closeModal');
};

watch(
    () => props.id,
    async (newId) => {
        if (newId) {
            const { data } = await getUser(props.id);

            form.last_name = data.last_name;
            form.name = data.name;
            form.surname = data.surname;
            form.email = data.email;
            form.role_id = data.role_id;
            form.verified = !!data.email_verified_at;
        }
    },
);
</script>

<template>
    <modal :show="show">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Редактирование пользователя
            </h2>
            <form @submit.prevent="submit">
                <div class="mt-4">
                    <input-label for="last_name" value="Фамилия" />

                    <text-input
                        id="last_name"
                        ref="last_name"
                        v-model="form.last_name"
                        class="mt-1 block w-full"
                        placeholder="Фамилия"
                    />
                </div>

                <div class="mt-4">
                    <input-label for="name" value="Имя" />

                    <text-input
                        id="name"
                        ref="name"
                        v-model="form.name"
                        class="mt-1 block w-full"
                        placeholder="Имя"
                    />
                </div>

                <div class="mt-4">
                    <input-label for="surname" value="Отчество" />

                    <text-input
                        id="surname"
                        ref="surname"
                        v-model="form.surname"
                        class="mt-1 block w-full"
                        placeholder="Отчество"
                    />
                </div>

                <div class="mt-4">
                    <input-label for="email" value="Email" />

                    <text-input
                        id="email"
                        ref="email"
                        type="email"
                        v-model="form.email"
                        class="mt-1 block w-full"
                        placeholder="Email"
                    />
                </div>

                <div class="mt-4">
                    <input-label
                        for="password"
                        value="Новый пароль (при необходимости)"
                    />

                    <text-input
                        id="password"
                        ref="password"
                        type="password"
                        v-model="form.password"
                        class="mt-1 block w-full"
                        placeholder=""
                    />
                </div>

                <div class="mt-4">
                    <role-select v-model="form.role_id"></role-select>
                </div>

                <div class="mb-4 mt-4">
                    <label class="flex items-center">
                        <checkbox
                            id="verified"
                            v-model="form.verified"
                            :checked="form.verified"
                        />
                        <span class="ms-2 text-sm text-gray-600">
                            Аккаунт
                            {{ form.verified ? '' : ' не ' }} подтвержден
                        </span>
                    </label>
                </div>

                <div class="flex flex-row">
                    <primary-button
                        type="submit"
                        class="mr-3"
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
