<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import { onMounted, ref } from 'vue';
import { getRoles } from '@/api/roles.js';

const roles = ref();

const getRolesList = async () => {
    const response = await getRoles();

    roles.value = response.data;
};

const modelValue = defineModel();

onMounted(getRolesList);
</script>

<template>
    <input-label for="role" value="Роль" class="sr-only" />

    <select
        id="role"
        ref="role"
        class="select select-primary"
        v-model="modelValue"
    >
        <option disabled selected>Выберите роль</option>
        <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
        </option>
    </select>
</template>

<style scoped></style>
