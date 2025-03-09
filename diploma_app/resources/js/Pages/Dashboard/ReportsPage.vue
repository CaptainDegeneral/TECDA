<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, onMounted, ref, watch } from 'vue';
import { getReportsList } from '@/api/reports.js';
import NProgress from 'nprogress';
import TextInput from '@/Components/TextInput.vue';
import ShowReportModal from '@/Components/Reports/ShowReportModal.vue';
import debounce from 'lodash/debounce.js';
import VPagination from '@/Components/VPagination.vue';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import VNotFound from '@/Components/VNotFound.vue';

const notification = useNotificationStore();
const { addNotification } = notification;

const reports = ref([]);
const searchValue = ref('');
const page = usePage();
const user = computed(() => page.props.auth.user);

const showReportModal = ref(false);
const chosenReportId = ref(null);

const pagination = ref();
const currentPage = ref(1);

const getReports = async () => {
    try {
        NProgress.start();

        const { data } = await getReportsList(
            user.value.id,
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
    <Head title="Мои отчеты" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                >
                    <text-input
                        class="mb-4 w-3/4"
                        v-model="searchValue"
                        placeholder="Поиск..."
                    />

                    <div>
                        <v-not-found v-if="reports.length === 0"></v-not-found>

                        <div
                            v-else
                            class="flex flex-col items-center space-y-6"
                        >
                            <table class="table w-full">
                                <tbody>
                                    <tr
                                        v-for="report in reports"
                                        :key="report.id"
                                    >
                                        <td>
                                            <button
                                                class="btn btn-link"
                                                @click.prevent="
                                                    showReport(report.id)
                                                "
                                            >
                                                {{ report.title }}
                                            </button>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>

                            <v-pagination
                                v-if="
                                    reports && reports.length > 0 && pagination
                                "
                                :pagination="pagination"
                                @page-changed="changePage"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <show-report-modal
            :id="chosenReportId"
            :show="showReportModal"
            @close-modal="closeReport"
        />
    </AuthenticatedLayout>
</template>

<style scoped></style>
