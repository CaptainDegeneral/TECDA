<script setup>
import { ref, onMounted, watch } from 'vue';
import { getAllSubjects } from '@/api/subjects.js';
import debounce from 'lodash/debounce.js';
import VPagination from '@/Components/VPagination.vue';
import CreateSubjectModal from '@/Components/Administration/CreateSubjectModal.vue';
import EditSubjectModal from '@/Components/Administration/EditSubjectModal.vue';
import DeleteSubjectModal from '@/Components/Administration/DeleteSubjectModal.vue';
import TextInput from '@/Components/TextInput.vue';

const searchValue = ref('');
const subjects = ref();
const pagination = ref();
const currentPage = ref(1);

const selectedSubjectId = ref(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const getSubjectsList = async () => {
    const response = await getAllSubjects(currentPage.value, searchValue.value);
    subjects.value = response.data.data;
    pagination.value = response.data.meta;
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
            class="mr-5 w-3/4"
        />
        <button
            class="btn btn-primary btn-soft"
            @click.prevent="showCreateModal = true"
        >
            Создать
        </button>
    </div>

    <div class="flex w-full flex-col items-center">
        <div class="w-full overflow-x-auto">
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
                        <td>{{ subject.name }}</td>
                        <td>{{ subject.code }}</td>
                        <td>
                            <div class="flex flex-row items-center justify-end">
                                <button
                                    class="btn btn-warning btn-soft mr-2"
                                    @click.prevent="showEdit(subject.id)"
                                >
                                    Редактировать
                                </button>
                                <button
                                    class="btn btn-error btn-soft"
                                    @click.prevent="showDelete(subject.id)"
                                >
                                    Удалить
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <v-pagination
            v-if="pagination"
            :pagination="pagination"
            @page-changed="changePage"
        />
    </div>

    <create-subject-modal
        :show="showCreateModal"
        @close-modal="showCreateModal = false"
        @created="search"
    />
    <edit-subject-modal
        :id="selectedSubjectId"
        :show="showEditModal"
        @edited="search"
        @close-modal="closeEditModal"
    />
    <delete-subject-modal
        :id="selectedSubjectId"
        :show="showDeleteModal"
        @deleted="search"
        @close-modal="closeDeleteModal"
    />
</template>
