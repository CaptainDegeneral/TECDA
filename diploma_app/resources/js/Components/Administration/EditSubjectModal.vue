<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { reactive, watch } from 'vue';
import { editSubject, getSubject } from '@/api/subjects.js';

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

const submit = async () => {
    try {
        await editSubject(props.id, form);

        emit('edited');
        closeModal();
    } catch (exception) {
        console.log(exception.response.data.message);
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
            <h2>Создать новую дисциплину</h2>
            <form @submit.prevent="submit">
                <div class="mt-6">
                    <input-label for="name" value="Название" class="sr-only" />

                    <text-input
                        id="name"
                        ref="name"
                        v-model="form.name"
                        class="mt-1 block w-full"
                        placeholder="Название"
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
                        Сохранить
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
