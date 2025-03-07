<script setup>
import { defineProps, defineEmits, watch } from 'vue';
import SelectSearch from '@/Components/SelectSearch.vue';
import {
    calculateTotalGrades,
    calculatePerformance,
    calculateQuality,
    calculateAverageScore,
} from '@/Utils/calculations.js';
import { useNotificationStore } from '@/Store/NotificationStore.js';

const props = defineProps({
    rows: Array,
    tabIndex: Number,
    semesterKey: String,
    disciplines: Array,
    tableHeaders: Array,
});

const emit = defineEmits(['addRow', 'deleteRow']);

const notification = useNotificationStore();
const { addNotification } = notification;

const handleAdd = () => {
    emit('addRow', props.semesterKey, props.tabIndex);
};

const handleDelete = (rowIndex) => {
    emit('deleteRow', props.semesterKey, props.tabIndex, rowIndex);
};

const checkGradesExceedStudents = (row) => {
    const totalGrades = calculateTotalGrades(row);
    const students = row.students || 0;

    if (
        row.discipline &&
        row.group &&
        totalGrades !== null &&
        students > 0 &&
        totalGrades > students
    ) {
        const disciplineName =
            row.discipline.code_name || 'Неизвестная дисциплина';
        const groupName = row.group || 'Неизвестная группа';
        addNotification(
            'warning',
            `Внимание! В группе "${groupName}" по дисциплине "${disciplineName}" количество оценок (${totalGrades}) превышает количество студентов (${students}).`,
        );
    }
};

watch(
    () => props.rows,
    (newRows) => {
        newRows.forEach((row) => {
            checkGradesExceedStudents(row);
        });
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <div class="mb-6">
        <div class="mb-4 flex items-center justify-start">
            <h3 class="text-lg font-medium text-gray-700">
                <slot name="title"></slot>
            </h3>
            <button
                @click="handleAdd"
                class="btn btn-primary btn-soft btn-sm ml-4"
            >
                Добавить строку
            </button>
        </div>
        <table class="table w-full">
            <thead>
                <tr>
                    <th v-for="(header, index) in tableHeaders" :key="index">
                        {{ header }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
                    <td>
                        <select-search
                            v-model="row.discipline"
                            :options="disciplines"
                            labelKey="code_name"
                            valueKey="id"
                        />
                    </td>
                    <td>
                        <input
                            v-model="row.group"
                            class="input-bordered input w-full"
                        />
                    </td>
                    <td>
                        <input
                            v-model.number="row.students"
                            type="number"
                            min="0"
                            class="input-bordered input w-full"
                        />
                    </td>
                    <td>
                        <input
                            v-model.number="row.fives"
                            type="number"
                            min="0"
                            class="input-bordered input w-full"
                        />
                    </td>
                    <td>
                        <input
                            v-model.number="row.fours"
                            type="number"
                            min="0"
                            class="input-bordered input w-full"
                        />
                    </td>
                    <td>
                        <input
                            v-model.number="row.threes"
                            type="number"
                            min="0"
                            class="input-bordered input w-full"
                        />
                    </td>
                    <td>{{ calculateTotalGrades(row) }}</td>
                    <td>{{ calculatePerformance(row) }}</td>
                    <td>{{ calculateQuality(row) }}</td>
                    <td>{{ calculateAverageScore(row) }}</td>
                    <td>
                        <button
                            @click="handleDelete(rowIndex)"
                            class="btn btn-error btn-soft btn-sm"
                        >
                            Удалить
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped></style>
