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
import VActionButton from '@/Components/VActionButton.vue';

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
        <v-action-button type="create" @click="showCreateModal = true" />
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
                                <v-action-button
                                    type="edit"
                                    :id="subject.id"
                                    @click="showEdit"
                                />
                                <v-action-button
                                    type="delete"
                                    :id="subject.id"
                                    @click="showDelete"
                                />
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
