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
                                class="select select-primary mr-2 w-[90%]"
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
                            <button
                                class="btn btn-primary btn-soft w-[10%]"
                                :disabled="!selectedReport"
                            >
                                <svg
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="icon"
                                >
                                    <path
                                        d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    ></path>
                                    <path
                                        d="M9 12H15"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    ></path>
                                    <path
                                        d="M9 16H12"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    ></path>
                                    <path
                                        d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    ></path>
                                </svg>
                                Мои отчеты
                            </button>
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
