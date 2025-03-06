<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ReportTable from '@/Components/Reports/ReportTable.vue';
import OverallResultsTable from '@/Components/Reports/OverallResultsTable.vue';
import { computed, ref, watch } from 'vue';
import { getReport, exportReport } from '@/api/reports.js';
import NProgress from 'nprogress';
import { useNotificationStore } from '@/Store/NotificationStore.js';

const emit = defineEmits(['closeModal', 'created']);

const props = defineProps({
    id: { required: true },
    show: { type: Boolean, default: false },
});

const notification = useNotificationStore();
const { addNotification } = notification;

const report = ref(null);
const loading = ref(false);
const downloading = ref(false);

const parsedData = computed(() => {
    if (report.value && report.value.data) {
        try {
            return JSON.parse(report.value.data);
        } catch (e) {
            addNotification('error', 'Ошибка при обработке данных отчета');
            return {};
        }
    }
    return {};
});

const finalResults = computed(() => parsedData.value.finalResults || {});
const overallResults = computed(() => parsedData.value.overallResults || []);

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
        addNotification('error', 'При загрузке отчета произошла ошибка');
    } finally {
        NProgress.done();
        loading.value = false;
    }
};

const downloadReport = async () => {
    downloading.value = true;
    try {
        await exportReport(props.id);
        addNotification('success', 'Отчет успешно скачан');
    } catch (error) {
        addNotification('error', 'Ошибка при скачивании отчета');
    } finally {
        downloading.value = false;
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
                        <svg
                            v-if="downloading"
                            class="-ml-1 mr-2 size-6 animate-spin text-gray-500"
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
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            />
                        </svg>
                        <primary-button
                            @click="downloadReport"
                            :disabled="downloading"
                        >
                            {{ downloading ? 'Скачивание...' : 'Скачать' }}
                        </primary-button>
                        <danger-button @click="">Удалить</danger-button>
                        <secondary-button @click="closeModal">
                            Отмена
                        </secondary-button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <report-table
                        title="Успеваемость (%)"
                        :data="finalResults.performanceTable"
                        :years="years"
                    />
                    <report-table
                        title="Качество образования (%)"
                        :data="finalResults.qualityTable"
                        :years="years"
                    />
                    <report-table
                        title="Средний балл"
                        :data="finalResults.averageScoreTable"
                        :years="years"
                    />
                    <overall-results-table :data="overallResults" />
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
