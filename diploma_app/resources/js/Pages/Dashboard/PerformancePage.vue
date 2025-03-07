<script setup>
import { ref, computed, onMounted } from 'vue';
import { createReport } from '@/api/reports.js';
import { getSubjectsCodeList } from '@/api/subjects.js';
import NProgress from 'nprogress';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import { handleApiError } from '@/Utils/errorHandler.js';
import {
    calculateIntermediateResults,
    calculateFinalResults,
    calculateOverallResults,
} from '@/Utils/resultsCalculations.js';
import {
    generateTabs,
    createEmptyRow,
    collectReportData,
} from '@/Utils/dataProcessing.js';
import { hasInvalidNulls } from '@/Utils/validation.js';

import InitialConfiguration from '@/Pages/Dashboard/PerfomancePage/InitialConfiguration.vue';
import MainConfiguration from '@/Pages/Dashboard/PerfomancePage/MainConfiguration.vue';
import IntermediateResults from '@/Pages/Dashboard/PerfomancePage/IntermediateResults.vue';
import FinalResults from '@/Pages/Dashboard/PerfomancePage/FinalResults.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ShowReportModal from '@/Components/Reports/ShowReportModal.vue';

// Инициализация хранилища уведомлений
const notification = useNotificationStore();
const { addNotification } = notification;

// Реактивные переменные
const category = ref('');
const yearsOfWork = ref(1);
const startYear = ref(new Date().getFullYear());
const isConfigured = ref(false);
const activeTab = ref(0);
const showFinalResults = ref(false);
const showIntermediateResults = ref(false);
const disciplines = ref([]);
const tabsData = ref([]);
const loading = ref(false);
const showReportModal = ref(false);
const chosenReportId = ref(null);
const showReportLink = ref(false);

// Разблокировка основной конфигурации
const unlockMainConfiguration = (isValid) => {
    if (!isValid) {
        addNotification('error', 'Пожалуйста, заполните все поля корректно');
        return;
    }
    tabsData.value = generateTabs(yearsOfWork.value, startYear.value);
    isConfigured.value = true;
};

// Добавление строки в таблицу семестра
const addRow = (semester, tabIndex) => {
    tabsData.value[tabIndex][semester].push(createEmptyRow());
};

// Удаление строки из таблицы семестра
const deleteRow = (event) => {
    console.log(event);
    tabsData.value[event.tab][event.semester].splice(event.index, 1);
};

// Переключение видимости промежуточных результатов
const toggleIntermediateResults = () => {
    showIntermediateResults.value = !showIntermediateResults.value;
};

// Переключение видимости конечных результатов
const toggleFinalResults = () => {
    showFinalResults.value = !showFinalResults.value;
};

// Вычисление промежуточных результатов
const intermediateResults = computed(() => {
    return calculateIntermediateResults(tabsData.value);
});

// Вычисление конечных результатов
const finalResults = computed(() => {
    return calculateFinalResults(tabsData.value, intermediateResults.value);
});

// Вычисление общих результатов
const overallResults = computed(() => {
    return calculateOverallResults(intermediateResults.value);
});

// Сбор всех данных для отчета
const collectedData = computed(() => {
    return collectReportData(
        {
            category: category.value,
            yearsOfWork: yearsOfWork.value,
            startYear: startYear.value,
        },
        tabsData.value,
        intermediateResults.value,
        finalResults.value,
        overallResults.value,
    );
});

// Получение списка дисциплин
const getSubjectsList = async () => {
    try {
        NProgress.start();
        const { data } = await getSubjectsCodeList();
        disciplines.value = data;
    } catch (exception) {
        addNotification(
            'error',
            'При загрузке списка дисциплин произошла ошибка',
        );
    } finally {
        NProgress.done();
    }
};

// Сохранение отчета
const saveReport = async () => {
    if (hasInvalidNulls(collectedData.value)) {
        addNotification(
            'error',
            'Для сохранения отчета необходимо заполнить все данные: ' +
                'дисциплины должны быть выбраны, а поля для групп, количества студентов ' +
                'и оценок должны быть заполнены',
        );
        return;
    }

    try {
        NProgress.start();
        loading.value = true;
        const response = await createReport(
            null,
            collectedData.value,
            'Успеваемость и качество образования',
        );
        chosenReportId.value = response.data.data.report.id;
        showReportLink.value = true;
        addNotification('success', 'Отчет успешно сохранен');
    } catch (exception) {
        handleApiError(exception, addNotification);
    } finally {
        NProgress.done();
        loading.value = false;
    }
};

// Открытие модального окна с отчетом
const showReport = () => {
    showReportModal.value = true;
};

// Закрытие модального окна
const closeReport = () => {
    showReportModal.value = false;
    chosenReportId.value = null;
    showReportLink.value = false;
};

// Загрузка списка дисциплин при монтировании компонента
onMounted(getSubjectsList);
</script>

<template>
    <div
        :class="[
            isConfigured ? 'py-8' : 'pt-8',
            { 'pointer-events-none opacity-50': loading },
        ]"
    >
        <InitialConfiguration
            :category="category"
            :years-of-work="yearsOfWork"
            :start-year="startYear"
            @update:category="category = $event"
            @update:years-of-work="yearsOfWork = $event"
            @update:start-year="startYear = $event"
            @unlock="unlockMainConfiguration"
        />

        <transition name="fade">
            <MainConfiguration
                v-if="isConfigured"
                :tabs-data="tabsData"
                :active-tab="activeTab"
                :disciplines="disciplines"
                @update:active-tab="activeTab = $event"
                @add-row="addRow"
                @delete-row="deleteRow"
            />
        </transition>

        <transition name="fade">
            <IntermediateResults
                v-if="isConfigured"
                :tabs-data="tabsData"
                :intermediate-results="intermediateResults"
                :active-tab="activeTab"
                :show-intermediate-results="showIntermediateResults"
                @update:active-tab="activeTab = $event"
                @toggle-intermediate-results="toggleIntermediateResults"
            />
        </transition>

        <transition name="fade">
            <FinalResults
                v-if="isConfigured"
                :tabs-data="tabsData"
                :final-results="finalResults"
                :overall-results="overallResults"
                :show-final-results="showFinalResults"
                @toggle-final-results="toggleFinalResults"
            />
        </transition>

        <transition name="fade">
            <div v-if="isConfigured" class="flex flex-row items-center gap-5">
                <PrimaryButton type="submit" @click.prevent="saveReport">
                    Сохранить отчёт
                </PrimaryButton>

                <transition name="fade">
                    <button
                        v-if="showReportLink"
                        class="btn btn-link flex items-center gap-2 text-blue-600"
                        @click.prevent="showReport"
                    >
                        Перейти к отчету
                    </button>
                </transition>
            </div>
        </transition>
    </div>

    <ShowReportModal
        :id="chosenReportId"
        :show="showReportModal"
        @close-modal="closeReport"
    />
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
