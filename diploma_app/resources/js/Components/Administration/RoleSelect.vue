<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import { onMounted, ref } from 'vue';
import { getRoles } from '@/api/roles.js';
import NProgress from 'nprogress';
import { useNotificationStore } from '@/Store/NotificationStore.js';

const notification = useNotificationStore();
const { addNotification } = notification;

const roles = ref();

const getRolesList = async () => {
    try {
        NProgress.start();
        const response = await getRoles();
        roles.value = response.data;
    } catch (exception) {
        addNotification('error', 'При загрузке списка ролей произошла ошибка');
    } finally {
        NProgress.done();
    }
};

const modelValue = defineModel();

onMounted(getRolesList);
</script>

<template>
    <input-label for="role" value="Роль" class="sr-only" />

    <div>
        <input-label value="Выберите роль:"></input-label>
        <select
            id="role"
            ref="role"
            class="select select-primary mt-1"
            v-model="modelValue"
        >
            <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
            </option>
        </select>
    </div>
</template>

<style scoped></style>
