<script setup>
defineProps({
    data: { type: Array, default: () => [] },
});
</script>

<template>
    <div v-if="data && data.length > 0" class="mb-6">
        <h3 class="mb-4 text-lg font-medium text-gray-700">
            Средние значения (все периоды)
        </h3>
        <table class="table w-full">
            <thead>
                <tr>
                    <th class="w-auto">Дисциплина</th>
                    <th class="w-1/6">Успеваемость (%)</th>
                    <th class="w-1/6">Качество (%)</th>
                    <th class="w-1/6">Средний балл</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in data" :key="row.discipline">
                    <td class="w-auto">{{ row.discipline }}</td>
                    <td class="w-1/6">{{ row.avgPerformance || '—' }}</td>
                    <td class="w-1/6">{{ row.avgQuality || '—' }}</td>
                    <td class="w-1/6">{{ row.avgAverageScore || '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped></style>
