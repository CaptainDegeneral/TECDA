<script setup>
defineProps({
    title: { type: String, required: true },
    data: { type: Array, default: () => [] },
    years: { type: Array, default: () => [] },
});
</script>

<template>
    <div v-if="data && data.length > 0" class="mb-6">
        <h3 class="mb-4 text-lg font-medium text-gray-700">{{ title }}</h3>
        <table class="table w-full">
            <thead>
                <tr>
                    <th class="w-1/3">Дисциплина</th>
                    <th
                        v-for="year in years"
                        :key="year"
                        class="w-[calc(66.66%/5)]"
                    >
                        {{ year }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in data" :key="row.discipline">
                    <td class="w-1/3">{{ row.discipline }}</td>
                    <td
                        v-for="year in years"
                        :key="year"
                        class="w-[calc(66.66%/5)]"
                    >
                        {{ row[year] || '—' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped></style>
