<script setup>
import { onMounted, ref, watch } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import NProgress from 'nprogress';
import debounce from 'lodash/debounce.js';
import { getReportsList } from '@/api/reports.js';
import ShowReportModal from '@/Components/Reports/ShowReportModal.vue';
import VPagination from '@/Components/VPagination.vue';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import VNotFound from '@/Components/VNotFound.vue';

const notification = useNotificationStore();
const { addNotification } = notification;

const reports = ref([]);
const searchValue = ref('');
const pagination = ref();
const currentPage = ref(1);

const showReportModal = ref(false);
const chosenReportId = ref(null);

const getReports = async () => {
    try {
        NProgress.start();

        const { data } = await getReportsList(
            null,
            currentPage.value,
            searchValue.value,
        );

        reports.value = data.data;
        pagination.value = data.meta;
    } catch (exception) {
        addNotification(
            'error',
            'При загрузке списка отчетов произошла ошибка',
        );
    } finally {
        NProgress.done();
    }
};

const changePage = async (page) => {
    if (page < 1 || (pagination.value && page > pagination.value.last_page))
        return;
    currentPage.value = page;
    await getReports();
};

const showReport = (id) => {
    showReportModal.value = true;
    chosenReportId.value = id;
};

const closeReport = () => {
    showReportModal.value = false;
    chosenReportId.value = null;
};

const search = async () => {
    currentPage.value = 1;
    await getReports();
};

const debouncedSearch = debounce(search, 500);

watch(searchValue, debouncedSearch);

onMounted(getReports);
</script>

<template>
    <div class="flex flex-row items-center justify-start pb-4">
        <text-input
            v-model="searchValue"
            placeholder="Поиск..."
            class="mr-5 w-full"
        />
    </div>
    <div
        class="flex flex-col items-center space-y-6"
        v-if="reports && reports.length > 0"
    >
        <table class="table w-full">
            <thead>
                <tr>
                    <th class="w-[60%]">Наименование отчета</th>
                    <th class="w-[40%]">Пользователь</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="report in reports" :key="report.id">
                    <td>
                        <button
                            class="btn btn-link"
                            @click.prevent="showReport(report.id)"
                        >
                            {{ report.title }}
                        </button>
                    </td>
                    <td>
                        {{
                            `${report.user.last_name} ${report.user.name} ${report.user.surname}`
                        }}
                    </td>
                </tr>
            </tbody>
        </table>

        <v-pagination
            v-if="reports && reports.length > 0 && pagination"
            :pagination="pagination"
            @page-changed="changePage"
        />
    </div>

    <v-not-found v-else></v-not-found>

    <show-report-modal
        :id="chosenReportId"
        :show="showReportModal"
        @close-modal="closeReport"
    />
</template>

<style scoped></style>
