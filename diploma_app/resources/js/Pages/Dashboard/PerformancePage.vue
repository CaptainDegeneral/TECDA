<script setup>
import { ref, computed, onMounted } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import SemesterTable from '@/Pages/Dashboard/PerfomancePage/SemesterTable.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { createReport } from '@/api/reports.js';
import NProgress from 'nprogress';
import { getSubjectsCodeList } from '@/api/subjects.js';

// Основные состояния
const category = ref('');
const yearsOfWork = ref(0);
const startYear = ref(0);
const isConfigured = ref(false);
const activeTab = ref(0);
const showFinalResults = ref(false);
const showIntermediateResults = ref(false);

// Список дисциплин
const disciplines = ref();

// Заголовки таблицы семестра
const semesterTableHeaders = [
    'Дисциплина',
    'Группа',
    'Студентов',
    '5',
    '4',
    '3',
    'Оценок',
    'Успеваемость (%)',
    'Качество (%)',
    '', // для колонки "Действия"
];

// Данные вкладок
const tabsData = ref([]);

// Функция создания пустой строки таблицы
const createEmptyRow = () => ({
    discipline: '',
    group: '',
    students: 0,
    fives: 0,
    fours: 0,
    threes: 0,
});

// Генерация вкладок (не более 5)
const generateTabs = () => {
    const tabsCount = Math.min(yearsOfWork.value, 5);
    tabsData.value = Array.from({ length: tabsCount }, (_, index) => ({
        label: `${startYear.value + index}-${startYear.value + index + 1}`,
        autumnWinter: [createEmptyRow()],
        springSummer: [createEmptyRow()],
    }));
};

// Разблокирование основной конфигурации
const unlockMainConfiguration = () => {
    if (!category.value || yearsOfWork.value <= 0 || startYear.value <= 0) {
        alert('Пожалуйста, заполните все поля корректно.');
        return;
    }
    generateTabs();
    isConfigured.value = true;
};

// Функции управления строками
const addRow = (semester, tabIndex) => {
    tabsData.value[tabIndex][semester].push(createEmptyRow());
};
const deleteRow = (semester, tabIndex, rowIndex) => {
    tabsData.value[tabIndex][semester].splice(rowIndex, 1);
};

// Вспомогательные функции вычислений с возвращением null вместо "0.00"
const calculateTotalGrades = (row) => {
    if (!row.discipline) return null;
    const sum = row.fives + row.fours + row.threes;
    return sum === 0 ? null : sum;
};
const calculatePerformance = (row) => {
    if (!row.discipline) return null;
    const total = calculateTotalGrades(row);
    if (!total) return null;
    return (
        ((row.fives * 5 + row.fours * 4 + row.threes * 3) / (total * 5)) *
        100
    ).toFixed(2);
};
const calculateQuality = (row) => {
    if (!row.discipline) return null;
    if (row.students === 0) return null;
    return (((row.fives + row.fours) / row.students) * 100).toFixed(2);
};

// Функции переключения видимости блоков
const toggleIntermediateResults = () => {
    showIntermediateResults.value = !showIntermediateResults.value;
};
const toggleFinalResults = () => {
    showFinalResults.value = !showFinalResults.value;
};

// Вычисляем промежуточные результаты для каждой вкладки
const intermediateResults = computed(() =>
    tabsData.value.map((tab) => {
        const allRows = [...tab.autumnWinter, ...tab.springSummer];
        const disciplineMap = {};
        allRows.forEach((row) => {
            // Извлекаем code_name из объекта discipline, если он существует
            const discKey = row.discipline ? row.discipline.code_name : null;
            if (discKey) {
                // Инициализируем запись для данной дисциплины, если её ещё нет
                if (!disciplineMap[discKey]) {
                    disciplineMap[discKey] = {
                        performance: [],
                        quality: [],
                    };
                }
                // Вычисляем успеваемость и качество
                const perf = parseFloat(calculatePerformance(row));
                const qual = parseFloat(calculateQuality(row));
                // Добавляем значения, если они корректны
                if (!isNaN(perf) && perf !== null)
                    disciplineMap[discKey].performance.push(perf);
                if (!isNaN(qual) && qual !== null)
                    disciplineMap[discKey].quality.push(qual);
            }
        });
        // Преобразуем disciplineMap в массив объектов для таблицы
        return Object.entries(disciplineMap).map(([discKey, data]) => ({
            discipline: discKey, // discKey — это code_name
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
    }),
);

// Вычисляем конечные результаты по всем дисциплинам
const finalResults = computed(() => {
    const allDisciplines = new Set();
    intermediateResults.value.forEach((tabResults) => {
        tabResults.forEach((result) => {
            allDisciplines.add(result.discipline);
        });
    });
    const disciplinesList = Array.from(allDisciplines);
    const createTable = (prop) =>
        disciplinesList.map((discipline) => {
            const row = { discipline };
            tabsData.value.forEach((tab, index) => {
                const result = intermediateResults.value[index].find(
                    (r) => r.discipline === discipline,
                );
                row[tab.label] = result ? result[prop] : null;
            });
            return row;
        });
    return {
        performanceTable: createTable('performance'),
        qualityTable: createTable('quality'),
    };
});

// Функция для замены пустых значений на null
const sanitizeData = (data) => {
    if (data === undefined) return null;
    if (typeof data === 'string' && data.trim() === '') return null;
    if (Array.isArray(data)) return data.map(sanitizeData);
    if (data !== null && typeof data === 'object') {
        const sanitized = {};
        Object.keys(data).forEach((key) => {
            sanitized[key] = sanitizeData(data[key]);
        });
        return sanitized;
    }
    return data;
};

// Функция для получения данных с вычисляемыми колонками для каждой строки
const getEnhancedTabsData = () => {
    return tabsData.value.map((tab) => ({
        ...tab,
        autumnWinter: tab.autumnWinter.map((row) => ({
            ...row,
            totalGrades: calculateTotalGrades(row),
            performance: calculatePerformance(row),
            quality: calculateQuality(row),
        })),
        springSummer: tab.springSummer.map((row) => ({
            ...row,
            totalGrades: calculateTotalGrades(row),
            performance: calculatePerformance(row),
            quality: calculateQuality(row),
        })),
    }));
};

// Функция для сбора всех данных в JSON
const collectAllData = () => {
    const data = {
        configuration: {
            category: category.value || null,
            yearsOfWork: yearsOfWork.value > 0 ? yearsOfWork.value : null,
            startYear: startYear.value > 0 ? startYear.value : null,
        },
        tabsData: getEnhancedTabsData(),
        intermediateResults: intermediateResults.value,
        finalResults: finalResults.value,
    };
    return JSON.stringify(sanitizeData(data), null, 2);
};

// Привязываем результат сбора данных к computed для отображения в шаблоне (например, в <pre>)
const collectedData = computed(() => collectAllData());

const loading = ref(false);

const getSubjectsList = async () => {
    const { data } = await getSubjectsCodeList();

    disciplines.value = data;
};

const saveReport = async () => {
    try {
        NProgress.start();
        loading.value = true;

        await createReport(
            null,
            collectedData.value,
            'Успеваемость и качество образования',
        );
    } catch (exception) {
        //
    } finally {
        NProgress.done();
        loading.value = false;
    }
};

onMounted(getSubjectsList);
</script>

<template>
    <div class="py-8" :class="{ 'pointer-events-none opacity-50': loading }">
        <!-- Начальное конфигурирование -->
        <section class="mb-12">
            <h2 class="mb-6 text-2xl font-semibold text-gray-900">
                Начальное конфигурирование
            </h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div>
                    <InputLabel value="Категория" />
                    <select
                        v-model="category"
                        class="select-bordered select mt-1 block w-full"
                    >
                        <option value="">Выберите категорию</option>
                        <option value="Первая">Первая</option>
                        <option value="Высшая">Высшая</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Количество лет работы" />
                    <input
                        v-model.number="yearsOfWork"
                        type="number"
                        min="0"
                        class="input-bordered input mt-1 block w-full"
                    />
                </div>
                <div>
                    <InputLabel value="Стартовый год" />
                    <input
                        v-model.number="startYear"
                        type="number"
                        min="0"
                        class="input-bordered input mt-1 block w-full"
                    />
                </div>
                <div class="flex items-end">
                    <button
                        type="button"
                        @click="unlockMainConfiguration"
                        class="btn btn-primary w-full"
                    >
                        Разблокировать
                    </button>
                </div>
            </div>
        </section>

        <!-- Основное конфигурирование -->
        <section v-if="isConfigured" class="mb-12">
            <h2 class="mb-6 text-2xl font-semibold text-gray-900">
                Основное конфигурирование
            </h2>
            <div class="flex border-b border-gray-200">
                <button
                    v-for="(tab, index) in tabsData"
                    :key="index"
                    @click="activeTab = index"
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
                class="mt-6"
            >
                <!-- Осенне-зимний семестр -->
                <SemesterTable
                    :rows="tab.autumnWinter"
                    :tabIndex="tabIndex"
                    semesterKey="autumnWinter"
                    :disciplines="disciplines"
                    :tableHeaders="semesterTableHeaders"
                    @addRow="addRow"
                    @deleteRow="deleteRow"
                >
                    <template #title>Осенне-зимний семестр</template>
                </SemesterTable>
                <!-- Весенне-летний семестр -->
                <SemesterTable
                    :rows="tab.springSummer"
                    :tabIndex="tabIndex"
                    semesterKey="springSummer"
                    :disciplines="disciplines"
                    :tableHeaders="semesterTableHeaders"
                    @addRow="addRow"
                    @deleteRow="deleteRow"
                >
                    <template #title>Весенне-летний семестр</template>
                </SemesterTable>
            </div>
        </section>

        <!-- Промежуточный результат -->
        <section v-if="isConfigured" class="mb-12">
            <div class="mb-6 flex items-center justify-start">
                <h2 class="text-2xl font-semibold text-gray-900">
                    Промежуточный результат
                </h2>
                <button
                    @click="toggleIntermediateResults"
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
                        @click="activeTab = index"
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

        <!-- Конечный результат -->
        <section v-if="isConfigured">
            <div class="mb-6 flex items-center justify-start">
                <h2 class="text-2xl font-semibold text-gray-900">
                    Конечный результат
                </h2>
                <button
                    @click="toggleFinalResults"
                    class="btn btn-outline btn-sm ml-4"
                >
                    {{ showFinalResults ? 'Скрыть' : 'Показать' }}
                </button>
            </div>
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

            <primary-button type="submit" @click.prevent="saveReport">
                Сохранить отчёт
            </primary-button>
        </section>

        <!-- Вывод JSON для отладки -->
        <section class="mt-8">
            <h2 class="mb-4 text-xl font-semibold text-gray-900">
                Собранные данные (JSON)
            </h2>
            <pre class="rounded bg-gray-100 p-4">{{ collectedData }}</pre>
        </section>
    </div>
</template>

<style scoped></style>
