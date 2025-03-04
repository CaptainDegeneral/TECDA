<!-- FinalResults.vue -->
<script setup>
const props = defineProps({
    tabsData: Array,
    finalResults: Object,
    showFinalResults: Boolean,
});

const emit = defineEmits(['toggleFinalResults']);
</script>

<template>
    <section>
        <div class="mb-6 flex items-center justify-start">
            <h2 class="text-2xl font-semibold text-gray-900">
                Конечный результат
            </h2>
            <button
                @click="emit('toggleFinalResults')"
                class="btn btn-outline btn-sm ml-4"
            >
                {{ showFinalResults ? 'Скрыть' : 'Показать' }}
            </button>
        </div>
        <transition name="fade">
            <div class="mb-6" v-if="showFinalResults">
                <div class="mb-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-700">
                        Успеваемость
                    </h3>
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="w-1/3">Дисциплина</th>
                                <th
                                    v-for="tab in tabsData"
                                    :key="tab.label"
                                    class="w-[calc(66.66%/5)]"
                                >
                                    {{ tab.label }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in finalResults.performanceTable"
                                :key="row.discipline"
                            >
                                <td class="w-1/3">{{ row.discipline }}</td>
                                <td
                                    v-for="tab in tabsData"
                                    :key="tab.label"
                                    class="w-[calc(66.66%/5)]"
                                >
                                    {{ row[tab.label] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 class="mb-4 text-lg font-medium text-gray-700">
                        Качество образования
                    </h3>
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="w-1/3">Дисциплина</th>
                                <th
                                    v-for="tab in tabsData"
                                    :key="tab.label"
                                    class="w-[calc(66.66%/5)]"
                                >
                                    {{ tab.label }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in finalResults.qualityTable"
                                :key="row.discipline"
                            >
                                <td class="w-1/3">{{ row.discipline }}</td>
                                <td
                                    v-for="tab in tabsData"
                                    :key="tab.label"
                                    class="w-[calc(66.66%/5)]"
                                >
                                    {{ row[tab.label] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </transition>
    </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
