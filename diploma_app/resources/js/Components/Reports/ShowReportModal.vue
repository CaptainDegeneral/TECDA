<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, ref, watch } from 'vue';
import { getReport } from '@/api/reports.js';
import NProgress from 'nprogress';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const emit = defineEmits(['closeModal', 'created']);

const props = defineProps({
    id: { required: true },
    show: { type: Boolean, default: false },
});

const report = ref(null);
const loading = ref(false);

const parsedData = computed(() => {
    if (report.value && report.value.data) {
        try {
            return JSON.parse(report.value.data);
        } catch (e) {
            console.error('Ошибка при парсинге JSON:', e);
            return {};
        }
    }
    return {};
});

const finalResults = computed(() => parsedData.value.finalResults || {});

const years = computed(() => {
    if (
        finalResults.value.performanceTable &&
        finalResults.value.performanceTable.length > 0
    ) {
        return Object.keys(finalResults.value.performanceTable[0]).filter(
            (key) => key !== 'discipline',
        );
    }
    return [];
});

const getReportData = async () => {
    try {
        NProgress.start();
        loading.value = true;

        const { data } = await getReport(props.id);

        report.value = data;
    } catch (exception) {
        console.error('Ошибка при загрузке отчета:', exception);
    } finally {
        NProgress.done();
        loading.value = false;
    }
};

const closeModal = () => {
    emit('closeModal');
};

watch(
    () => props.id,
    async (newId) => {
        if (newId) {
            report.value = null;
            await getReportData();
        }
    },
);
</script>

<template>
    <modal :show="show" max-width="3xl">
        <div v-if="report" class="p-6">
            <section>
                <div class="mb-6 flex flex-row items-center justify-between">
                    <h2 class="text-2xl font-semibold text-gray-900">
                        {{ report.title }}
                    </h2>
                    <div class="flex flex-row items-center space-x-3">
                        <primary-button @click=""> Скачать </primary-button>
                        <danger-button @click=""> Удалить </danger-button>
                        <secondary-button @click="closeModal">
                            Отмена
                        </secondary-button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <!-- Таблица успеваемости -->
                    <div class="mb-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-700">
                            Успеваемость
                        </h3>
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
                                <tr
                                    v-for="row in finalResults.performanceTable"
                                    :key="row.discipline"
                                >
                                    <td class="w-1/3">{{ row.discipline }}</td>
                                    <td
                                        v-for="year in years"
                                        :key="year"
                                        class="w-[calc(66.66%/5)]"
                                    >
                                        {{ row[year] || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Таблица качества -->
                    <div>
                        <h3 class="mb-4 text-lg font-medium text-gray-700">
                            Качество образования
                        </h3>
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
                                <tr
                                    v-for="row in finalResults.qualityTable"
                                    :key="row.discipline"
                                >
                                    <td class="w-1/3">{{ row.discipline }}</td>
                                    <td
                                        v-for="year in years"
                                        :key="year"
                                        class="w-[calc(66.66%/5)]"
                                    >
                                        {{ row[year] || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
        <div v-else class="flex flex-row items-center p-6">
            <svg
                class="-ml-1 mr-3 size-10 animate-spin text-gray-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
            <p class="text-2xl font-medium text-gray-500">Загрузка...</p>
        </div>
    </modal>
</template>

<style scoped></style>
