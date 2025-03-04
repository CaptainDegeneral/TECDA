<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PerformancePage from '@/Pages/Dashboard/PerformancePage.vue';
import OtherPage from '@/Pages/Dashboard/OtherPage.vue';

const reports = [
    {
        key: 'performance',
        title: 'Успеваемость и качество образования',
        component: PerformancePage,
    },
    {
        key: 'other',
        title: 'Другой отчет',
        component: OtherPage,
    },
];

const selectedReport = ref('');

const selectedComponent = computed(() => {
    const report = reports.find((r) => r.key === selectedReport.value);
    return report ? report.component : null;
});
</script>

<template>
    <Head title="Главная" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4 flex flex-row items-center">
                            <select
                                class="select select-primary mr-2 w-full"
                                v-model="selectedReport"
                            >
                                <option disabled value="">
                                    Выберите отчет
                                </option>
                                <option
                                    v-for="report in reports"
                                    :key="report.key"
                                    :value="report.key"
                                >
                                    {{ report.title }}
                                </option>
                            </select>
                        </div>

                        <div v-if="selectedComponent">
                            <component :is="selectedComponent" />
                        </div>
                        <p
                            class="mb-10 mt-16 w-full text-center text-2xl font-medium text-gray-500"
                            v-else
                        >
                            Пожалуйста, выберите вариант отчета для продолжения
                            работы
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
