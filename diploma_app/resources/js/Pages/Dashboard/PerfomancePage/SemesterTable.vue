<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    rows: Array,
    tabIndex: Number,
    semesterKey: String,
    disciplines: Array,
    tableHeaders: Array,
});

const emit = defineEmits(['addRow', 'deleteRow']);

const handleAdd = () => {
    emit('addRow', props.semesterKey, props.tabIndex);
};

const handleDelete = (rowIndex) => {
    emit('deleteRow', props.semesterKey, props.tabIndex, rowIndex);
};

const calculateTotalGrades = (row) => {
    return row.fives + row.fours + row.threes;
};

const calculatePerformance = (row) => {
    const total = calculateTotalGrades(row);
    return total === 0
        ? '0.00'
        : (
              ((row.fives * 5 + row.fours * 4 + row.threes * 3) / (total * 5)) *
              100
          ).toFixed(2);
};

const calculateQuality = (row) => {
    return row.students === 0
        ? '0.00'
        : (((row.fives + row.fours) / row.students) * 100).toFixed(2);
};
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
                        <select
                            v-model="row.discipline"
                            class="select-bordered select w-full"
                        >
                            <option value="">Выберите</option>
                            <option
                                v-for="d in disciplines"
                                :value="d"
                                :key="d"
                            >
                                {{ d }}
                            </option>
                        </select>
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
