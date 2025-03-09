<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    columnKey: {
        type: String,
        default: 'label',
    },
});
</script>

<template>
    <div class="mb-6">
        <h3 class="mb-4 text-lg font-medium text-gray-700">{{ title }}</h3>
        <table class="table w-full">
            <thead>
                <tr>
                    <th class="w-1/3">Дисциплина</th>
                    <th
                        v-for="col in columns"
                        :key="col[columnKey]"
                        class="flex-1"
                    >
                        {{ col[columnKey] }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in data" :key="row.discipline">
                    <td class="w-1/3">{{ row.discipline }}</td>
                    <td
                        v-for="col in columns"
                        :key="col[columnKey]"
                        class="flex-1"
                    >
                        {{
                            row[col[columnKey]] !== null
                                ? row[col[columnKey]]
                                : '—'
                        }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
