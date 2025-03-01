<script setup>
import { ref, computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';

// Состояние
const category = ref('');
const yearsOfWork = ref(0);
const startYear = ref(0);
const isConfigured = ref(false);
const activeTab = ref(0);
const showFinalResults = ref(false);
const showIntermediateResults = ref(false);

// Список дисциплин
const disciplines = ref(['Математика', 'Физика', 'Информатика', 'История']);

// Заголовки таблицы для семестров
const semesterTableHeaders = ref([
    'Дисциплина',
    'Группа',
    'Студентов',
    '5',
    '4',
    '3',
    'Оценок',
    'Успеваемость (%)',
    'Качество (%)',
    '', // Пустой заголовок для колонки "Действия"
]);

// Данные вкладок
const tabsData = ref([]);

// Инициализация строки таблицы
const createEmptyRow = () => ({
    discipline: '',
    group: '',
    students: 0,
    fives: 0,
    fours: 0,
    threes: 0,
});

// Генерация вкладок
const generateTabs = () => {
    const tabsCount = Math.min(yearsOfWork.value, 5);
    tabsData.value = Array.from({ length: tabsCount }, (_, index) => ({
        label: `${startYear.value + index}-${startYear.value + index + 1}`,
        autumnWinter: [createEmptyRow()],
        springSummer: [createEmptyRow()],
    }));
};

// Разблокирование конфигурирования
const unlockMainConfiguration = () => {
    if (!category.value || yearsOfWork.value <= 0 || startYear.value <= 0) {
        alert('Пожалуйста, заполните все поля корректно.');
        return;
    }
    generateTabs();
    isConfigured.value = true;
};

// Управление строками
const addRow = (semester, tabIndex) =>
    tabsData.value[tabIndex][semester].push(createEmptyRow());
const deleteRow = (semester, tabIndex, rowIndex) =>
    tabsData.value[tabIndex][semester].splice(rowIndex, 1);

// Вычисления
const calculateTotalGrades = (row) => row.fives + row.fours + row.threes;
const calculatePerformance = (row) => {
    const total = calculateTotalGrades(row);
    return total === 0
        ? 0
        : (
              ((row.fives * 5 + row.fours * 4 + row.threes * 3) / (total * 5)) *
              100
          ).toFixed(2);
};
const calculateQuality = (row) =>
    row.students === 0
        ? 0
        : (((row.fives + row.fours) / row.students) * 100).toFixed(2);

// Промежуточные результаты
const intermediateResults = computed(() =>
    tabsData.value.map((tab) => {
        const allRows = [...tab.autumnWinter, ...tab.springSummer];
        const disciplineMap = {};
        allRows.forEach((row) => {
            if (!row.discipline) return;
            disciplineMap[row.discipline] = disciplineMap[row.discipline] || {
                performance: [],
                quality: [],
            };
            const perf = parseFloat(calculatePerformance(row));
            const qual = parseFloat(calculateQuality(row));
            if (!isNaN(perf))
                disciplineMap[row.discipline].performance.push(perf);
            if (!isNaN(qual)) disciplineMap[row.discipline].quality.push(qual);
        });
        return Object.entries(disciplineMap).map(([discipline, data]) => ({
            discipline,
            performance: (
                data.performance.reduce((sum, val) => sum + val, 0) /
                    data.performance.length || 0
            ).toFixed(2),
            quality: (
                data.quality.reduce((sum, val) => sum + val, 0) /
                    data.quality.length || 0
            ).toFixed(2),
        }));
    }),
);

// Конечные результаты
const finalResults = computed(() => {
    const disciplinesSet = new Set(
        tabsData.value.flatMap((_, index) =>
            intermediateResults.value[index].map((r) => r.discipline),
        ),
    );
    const disciplinesList = [...disciplinesSet];

    const createTable = (prop) =>
        disciplinesList.map((discipline) => {
            const row = { discipline };
            tabsData.value.forEach((tab, index) => {
                const result = intermediateResults.value[index].find(
                    (r) => r.discipline === discipline,
                );
                row[tab.label] = result ? result[prop] : '-';
            });
            return row;
        });

    return {
        performanceTable: createTable('performance'),
        qualityTable: createTable('quality'),
    };
});

// Переключение видимости
const toggleFinalResults = () => {
    showFinalResults.value = !showFinalResults.value;
};
const toggleIntermediateResults = () => {
    showIntermediateResults.value = !showIntermediateResults.value;
};
</script>

<template>
    <div class="py-8">
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
                <div class="mb-6">
                    <div class="mb-4 flex items-center justify-start">
                        <h3 class="text-lg font-medium text-gray-700">
                            Осенне-зимний семестр
                        </h3>
                        <button
                            @click="addRow('autumnWinter', tabIndex)"
                            class="btn btn-primary btn-soft btn-sm ml-4"
                        >
                            Добавить строку
                        </button>
                    </div>
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th
                                    v-for="(
                                        header, index
                                    ) in semesterTableHeaders"
                                    :key="index"
                                >
                                    {{ header }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, rowIndex) in tab.autumnWinter"
                                :key="rowIndex"
                            >
                                <td>
                                    <select
                                        v-model="row.discipline"
                                        class="select-bordered select w-full"
                                    >
                                        <option value="">Выберите</option>
                                        <option
                                            v-for="d in disciplines"
                                            :value="d"
                                            :key="d"
                                        >
                                            {{ d }}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        v-model="row.group"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.students"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.fives"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.fours"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.threes"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>{{ calculateTotalGrades(row) }}</td>
                                <td>{{ calculatePerformance(row) }}</td>
                                <td>{{ calculateQuality(row) }}</td>
                                <td>
                                    <button
                                        @click="
                                            deleteRow(
                                                'autumnWinter',
                                                tabIndex,
                                                rowIndex,
                                            )
                                        "
                                        class="btn btn-error btn-soft btn-sm"
                                    >
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Весенне-летний семестр -->
                <div>
                    <div class="mb-4 flex items-center justify-start">
                        <h3 class="text-lg font-medium text-gray-700">
                            Весенне-летний семестр
                        </h3>
                        <button
                            @click="addRow('springSummer', tabIndex)"
                            class="btn btn-primary btn-soft btn-sm ml-4"
                        >
                            Добавить строку
                        </button>
                    </div>
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th
                                    v-for="(
                                        header, index
                                    ) in semesterTableHeaders"
                                    :key="index"
                                >
                                    {{ header }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, rowIndex) in tab.springSummer"
                                :key="rowIndex"
                            >
                                <td>
                                    <select
                                        v-model="row.discipline"
                                        class="select-bordered select w-full"
                                    >
                                        <option value="">Выберите</option>
                                        <option
                                            v-for="d in disciplines"
                                            :value="d"
                                            :key="d"
                                        >
                                            {{ d }}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        v-model="row.group"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.students"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.fives"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.fours"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="row.threes"
                                        type="number"
                                        min="0"
                                        class="input-bordered input w-full"
                                    />
                                </td>
                                <td>{{ calculateTotalGrades(row) }}</td>
                                <td>{{ calculatePerformance(row) }}</td>
                                <td>{{ calculateQuality(row) }}</td>
                                <td>
                                    <button
                                        @click="
                                            deleteRow(
                                                'springSummer',
                                                tabIndex,
                                                rowIndex,
                                            )
                                        "
                                        class="btn btn-error btn-soft btn-sm"
                                    >
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
            <div v-if="showFinalResults">
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
        </section>
    </div>
</template>

<style scoped></style>
