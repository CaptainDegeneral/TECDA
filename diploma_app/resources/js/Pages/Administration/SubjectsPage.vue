<script setup>
import { ref, onMounted, watch } from 'vue';
import { getAllSubjects } from '@/api/subjects.js';
import debounce from 'lodash/debounce.js';
import VPagination from '@/Components/VPagination.vue';
import CreateSubjectModal from '@/Components/Administration/CreateSubjectModal.vue';
import EditSubjectModal from '@/Components/Administration/EditSubjectModal.vue';
import DeleteSubjectModal from '@/Components/Administration/DeleteSubjectModal.vue';
import TextInput from '@/Components/TextInput.vue';
import NProgress from 'nprogress';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import VNotFound from '@/Components/VNotFound.vue';

const notification = useNotificationStore();
const { addNotification } = notification;

const searchValue = ref('');
const subjects = ref();
const pagination = ref();
const currentPage = ref(1);

const selectedSubjectId = ref(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const handleCreated = () => {
    addNotification('success', 'Дисциплина успешно создана');
    search();
};
const handleEdited = () => {
    addNotification('success', 'Дисциплина успешно отредактирована');
    search();
};
const handleDeleted = () => {
    addNotification('success', 'Дисциплина успешно удалена');
    search();
};
const handleError = (message) => {
    addNotification('error', message);
};

const getSubjectsList = async () => {
    try {
        NProgress.start();
        const response = await getAllSubjects(
            currentPage.value,
            searchValue.value,
        );
        subjects.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (exception) {
        addNotification('error', 'При загрузке дисциплин произошла ошибка');
    } finally {
        NProgress.done();
    }
};

const changePage = async (page) => {
    if (page < 1 || (pagination.value && page > pagination.value.last_page))
        return;
    currentPage.value = page;
    await getSubjectsList();
};

const showEdit = (id) => {
    selectedSubjectId.value = id;
    showEditModal.value = true;
};

const showDelete = (id) => {
    selectedSubjectId.value = id;
    showDeleteModal.value = true;
};

const closeEditModal = () => {
    selectedSubjectId.value = null;
    showEditModal.value = false;
};

const closeDeleteModal = () => {
    selectedSubjectId.value = null;
    showDeleteModal.value = false;
};

const search = async () => {
    currentPage.value = 1;
    await getSubjectsList();
};

const debouncedSearch = debounce(search, 500);

watch(searchValue, debouncedSearch);
onMounted(getSubjectsList);
</script>

<template>
    <div class="mb-4 flex flex-row items-center justify-start">
        <text-input
            v-model="searchValue"
            placeholder="Поиск"
            class="mr-5 w-[70%]"
        />
        <button
            class="btn btn-primary btn-soft w-[30%]"
            @click.prevent="showCreateModal = true"
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
                    d="M7 12L12 12M12 12L17 12M12 12V7M12 12L12 17"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                ></path>
            </svg>
            Создать
        </button>
    </div>

    <div class="flex w-full flex-col items-center space-y-6">
        <div
            v-if="subjects && subjects.length > 0"
            class="w-full overflow-x-auto"
        >
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-[70%]">Название</th>
                        <th class="w-[20%]">Код</th>
                        <th class="w-[10%]"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="subject in subjects" :key="subject.id">
                        <td :class="subject.name ? '' : 'italic text-gray-400'">
                            {{ subject.name || 'Не задано' }}
                        </td>
                        <td :class="subject.code ? '' : 'italic text-gray-400'">
                            {{ subject.code || 'Не задано' }}
                        </td>
                        <td>
                            <div class="flex flex-row items-center justify-end">
                                <button
                                    class="btn btn-warning btn-soft mr-2"
                                    @click.prevent="showEdit(subject.id)"
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
                                            d="M18.3785 8.44975L11.4637 15.3647C11.1845 15.6439 10.8289 15.8342 10.4417 15.9117L7.49994 16.5L8.08829 13.5582C8.16572 13.1711 8.35603 12.8155 8.63522 12.5363L15.5501 5.62132M18.3785 8.44975L19.7927 7.03553C20.1832 6.64501 20.1832 6.01184 19.7927 5.62132L18.3785 4.20711C17.988 3.81658 17.3548 3.81658 16.9643 4.20711L15.5501 5.62132M18.3785 8.44975L15.5501 5.62132"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></path>
                                        <path
                                            d="M5 20H19"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></path>
                                    </svg>
                                    Изменить
                                </button>
                                <button
                                    class="btn btn-error btn-soft"
                                    @click.prevent="showDelete(subject.id)"
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
                                            d="M10 12V17"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></path>
                                        <path
                                            d="M14 12V17"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></path>
                                        <path
                                            d="M4 7H20"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></path>
                                        <path
                                            d="M6 10V18C6 19.6569 7.34315 21 9 21H15C16.6569 21 18 19.6569 18 18V10"
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
                                    Удалить
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <v-not-found v-else></v-not-found>

        <v-pagination
            v-if="subjects && subjects.length > 0 && pagination"
            :pagination="pagination"
            @page-changed="changePage"
        />
    </div>

    <create-subject-modal
        :show="showCreateModal"
        @close-modal="showCreateModal = false"
        @created="handleCreated"
        @error="handleError"
    />
    <edit-subject-modal
        :id="selectedSubjectId"
        :show="showEditModal"
        @edited="handleEdited"
        @close-modal="closeEditModal"
        @error="handleError"
    />
    <delete-subject-modal
        :id="selectedSubjectId"
        :show="showDeleteModal"
        @deleted="handleDeleted"
        @close-modal="closeDeleteModal"
        @error="handleError"
    />
</template>
