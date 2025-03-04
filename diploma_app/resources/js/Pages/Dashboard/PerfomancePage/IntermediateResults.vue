<!-- IntermediateResults.vue -->
<script setup>
const props = defineProps({
    tabsData: Array,
    intermediateResults: Array,
    activeTab: Number,
    showIntermediateResults: Boolean,
});

const emit = defineEmits(['update:activeTab', 'toggleIntermediateResults']);
</script>

<template>
    <section class="mb-12">
        <div class="mb-6 flex items-center justify-start">
            <h2 class="text-2xl font-semibold text-gray-900">
                Промежуточный результат
            </h2>
            <button
                @click="emit('toggleIntermediateResults')"
                class="btn btn-outline btn-sm ml-4"
            >
                {{ showIntermediateResults ? 'Скрыть' : 'Показать' }}
            </button>
        </div>
        <div v-if="showIntermediateResults">
            <div class="mb-4 flex border-b border-gray-200">
                <button
                    v-for="(tab, index) in tabsData"
                    :key="index"
                    @click="emit('update:activeTab', index)"
                    class="px-4 py-2 text-sm font-medium transition-colors"
                    :class="{
                        'border-b-2 border-blue-600 text-blue-600':
                            activeTab === index,
                        'text-gray-600 hover:text-blue-500':
                            activeTab !== index,
                    }"
                >
                    {{ tab.label }}
                </button>
            </div>
            <div
                v-for="(tab, tabIndex) in tabsData"
                :key="tabIndex"
                v-show="activeTab === tabIndex"
            >
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="w-1/2">Дисциплина</th>
                            <th class="w-1/4">Успеваемость (%)</th>
                            <th class="w-1/4">Качество (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, rowIndex) in intermediateResults[
                                tabIndex
                            ]"
                            :key="rowIndex"
                        >
                            <td class="w-1/2">{{ row.discipline }}</td>
                            <td class="w-1/4">{{ row.performance }}</td>
                            <td class="w-1/4">{{ row.quality }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</template>
