<script setup>
import { ref, computed, onMounted } from 'vue';
import { createReport } from '@/api/reports.js';
import NProgress from 'nprogress';
import { getSubjectsCodeList } from '@/api/subjects.js';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import { handleApiError } from '@/Utils/errorHandler.js';
import {
    calculatePerformance,
    calculateQuality,
} from '@/Utils/calculations.js';
import { sanitizeData, getEnhancedTabsData } from '@/Utils/dataProcessing.js';

import InitialConfiguration from '@/Pages/Dashboard/PerfomancePage/InitialConfiguration.vue';
import MainConfiguration from '@/Pages/Dashboard/PerfomancePage/MainConfiguration.vue';
import IntermediateResults from '@/Pages/Dashboard/PerfomancePage/IntermediateResults.vue';
import FinalResults from '@/Pages/Dashboard/PerfomancePage/FinalResults.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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

// Создание пустой строки таблицы
const createEmptyRow = () => ({
    discipline: '',
    group: '',
    students: 0,
    fives: 0,
    fours: 0,
    threes: 0,
});

// Генерация вкладок на основе конфигурации
const generateTabs = () => {
    const tabsCount = Math.min(yearsOfWork.value, 5);
    tabsData.value = Array.from({ length: tabsCount }, (_, index) => ({
        label: `${startYear.value + index}-${startYear.value + index + 1}`,
        autumnWinter: [createEmptyRow()],
        springSummer: [createEmptyRow()],
    }));
};

// Разблокировка основной конфигурации
const unlockMainConfiguration = (isValid) => {
    if (!isValid) {
        addNotification('error', 'Пожалуйста, заполните все поля корректно');
        return;
    }
    generateTabs();
    isConfigured.value = true;
};

// Добавление строки в таблицу семестра
const addRow = (semester, tabIndex) => {
    tabsData.value[tabIndex][semester].push(createEmptyRow());
};

// Удаление строки из таблицы семестра
const deleteRow = (semester, tabIndex, rowIndex) => {
    tabsData.value[tabIndex][semester].splice(rowIndex, 1);
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
    return tabsData.value.map((tab) => {
        const allRows = [...tab.autumnWinter, ...tab.springSummer];
        const disciplineMap = {};

        allRows.forEach((row) => {
            const discKey = row.discipline ? row.discipline.code_name : null;
            if (discKey) {
                if (!disciplineMap[discKey]) {
                    disciplineMap[discKey] = {
                        performance: [],
                        quality: [],
                    };
                }
                const perf = parseFloat(calculatePerformance(row));
                const qual = parseFloat(calculateQuality(row));
                if (!isNaN(perf) && perf !== null) {
                    disciplineMap[discKey].performance.push(perf);
                }
                if (!isNaN(qual) && qual !== null) {
                    disciplineMap[discKey].quality.push(qual);
                }
            }
        });

        return Object.entries(disciplineMap).map(([discKey, data]) => ({
            discipline: discKey,
            performance: data.performance.length
                ? (
                      data.performance.reduce((sum, val) => sum + val, 0) /
                      data.performance.length
                  ).toFixed(2)
                : null,
            quality: data.quality.length
                ? (
                      data.quality.reduce((sum, val) => sum + val, 0) /
                      data.quality.length
                  ).toFixed(2)
                : null,
        }));
    });
});

// Вычисление конечных результатов
const finalResults = computed(() => {
    const allDisciplines = new Set();
    intermediateResults.value.forEach((tabResults) => {
        tabResults.forEach((result) => allDisciplines.add(result.discipline));
    });

    const disciplinesList = Array.from(allDisciplines);
    const createTable = (prop) => {
        return disciplinesList.map((discipline) => {
            const row = { discipline };
            tabsData.value.forEach((tab, index) => {
                const result = intermediateResults.value[index].find(
                    (r) => r.discipline === discipline,
                );
                row[tab.label] = result ? result[prop] : null;
            });
            return row;
        });
    };

    return {
        performanceTable: createTable('performance'),
        qualityTable: createTable('quality'),
    };
});

// Сбор всех данных для отчета
const collectAllData = () => {
    const data = {
        configuration: {
            category: category.value || null,
            yearsOfWork: yearsOfWork.value > 0 ? yearsOfWork.value : null,
            startYear: startYear.value > 0 ? startYear.value : null,
        },
        tabsData: getEnhancedTabsData(tabsData.value),
        intermediateResults: intermediateResults.value,
        finalResults: finalResults.value,
    };
    return JSON.stringify(sanitizeData(data), null, 2);
};

// Вычисляемое свойство для собранных данных
const collectedData = computed(() => collectAllData());

// Проверка на наличие недопустимых значений (null или '0.00')
const hasInvalidNulls = (data) => {
    const parsedData = JSON.parse(data);
    const checkForInvalidValues = (obj, path = []) => {
        if (path.length === 1 && path[0] === 'finalResults') return false;

        if (Array.isArray(obj)) {
            return obj.some((item, index) =>
                checkForInvalidValues(item, [...path, index]),
            );
        }

        if (obj !== null && typeof obj === 'object') {
            return Object.entries(obj).some(([key, value]) =>
                checkForInvalidValues(value, [...path, key]),
            );
        }

        return obj === null || obj === '0.00';
    };
    return checkForInvalidValues(parsedData);
};

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
        await createReport(
            null,
            collectedData.value,
            'Успеваемость и качество образования',
        );
        addNotification('success', 'Отчет успешно сохранен');
    } catch (exception) {
        handleApiError(exception, addNotification);
    } finally {
        NProgress.done();
        loading.value = false;
    }
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

        <MainConfiguration
            v-if="isConfigured"
            :tabs-data="tabsData"
            :active-tab="activeTab"
            :disciplines="disciplines"
            @update:active-tab="activeTab = $event"
            @add-row="addRow"
            @delete-row="deleteRow"
        />

        <IntermediateResults
            v-if="isConfigured"
            :tabs-data="tabsData"
            :intermediate-results="intermediateResults"
            :active-tab="activeTab"
            :show-intermediate-results="showIntermediateResults"
            @update:active-tab="activeTab = $event"
            @toggle-intermediate-results="toggleIntermediateResults"
        />

        <FinalResults
            v-if="isConfigured"
            :tabs-data="tabsData"
            :final-results="finalResults"
            :show-final-results="showFinalResults"
            @toggle-final-results="toggleFinalResults"
        />

        <div v-if="isConfigured" class="flex flex-row items-center gap-5">
            <PrimaryButton type="submit" @click.prevent="saveReport">
                Сохранить отчёт
            </PrimaryButton>

            <svg
                v-if="loading"
                class="-ml-1 mr-3 size-7 animate-spin text-gray-500"
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
        </div>
    </div>
</template>
