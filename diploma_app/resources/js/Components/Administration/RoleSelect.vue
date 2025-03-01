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
