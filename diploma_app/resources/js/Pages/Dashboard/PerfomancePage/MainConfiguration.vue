<!-- MainConfiguration.vue -->
<script setup>
import SemesterTable from '@/Pages/Dashboard/PerfomancePage/SemesterTable.vue';

const props = defineProps({
    tabsData: Array,
    activeTab: Number,
    disciplines: Array,
});

const emit = defineEmits(['update:activeTab', 'addRow', 'deleteRow']);

const handleDelete = (event) => {
    emit('deleteRow', event);
};

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
    'Средний балл',
    '',
];
</script>

<template>
    <section class="mb-12">
        <h2 class="mb-6 text-2xl font-semibold text-gray-900">
            Основное конфигурирование
        </h2>
        <div class="flex border-b border-gray-200">
            <button
                v-for="(tab, index) in tabsData"
                :key="index"
                @click="emit('update:activeTab', index)"
                class="px-4 py-2 text-sm font-medium transition-colors"
                :class="{
                    'border-b-2 border-blue-600 text-blue-600':
                        activeTab === index,
                    'text-gray-600 hover:text-blue-500': activeTab !== index,
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
            <SemesterTable
                :rows="tab.autumnWinter"
                :tabIndex="tabIndex"
                semesterKey="autumnWinter"
                :disciplines="disciplines"
                :tableHeaders="semesterTableHeaders"
                @addRow="emit('addRow', 'autumnWinter', tabIndex)"
                @deleteRow="handleDelete"
            >
                <template #title>Осенне-зимний семестр</template>
            </SemesterTable>

            <SemesterTable
                :rows="tab.springSummer"
                :tabIndex="tabIndex"
                semesterKey="springSummer"
                :disciplines="disciplines"
                :tableHeaders="semesterTableHeaders"
                @addRow="emit('addRow', 'springSummer', tabIndex)"
                @deleteRow="handleDelete"
            >
                <template #title>Весенне-летний семестр</template>
            </SemesterTable>
        </div>
    </section>
</template>
