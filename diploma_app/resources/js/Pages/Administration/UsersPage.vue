<script setup>
import { ref, onMounted, watch } from 'vue';
import debounce from 'lodash/debounce.js';
import VPagination from '@/Components/VPagination.vue';
import TextInput from '@/Components/TextInput.vue';
import { getUsers } from '@/api/users.js';
import CreateUserModal from '@/Components/Administration/CreateUserModal.vue';
import EditUserModal from '@/Components/Administration/EditUserModal.vue';
import DeleteUserModal from '@/Components/Administration/DeleteUserModal.vue';

const searchValue = ref('');
const users = ref();
const pagination = ref();
const currentPage = ref(1);

const selectedUserId = ref(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const getUsersList = async () => {
    const response = await getUsers(currentPage.value, searchValue.value);
    users.value = response.data.data;
    pagination.value = response.data.meta;
};

const changePage = async (page) => {
    if (page < 1 || (pagination.value && page > pagination.value.last_page))
        return;
    currentPage.value = page;
    await getUsersList();
};

const showEdit = (id) => {
    selectedUserId.value = id;
    showEditModal.value = true;
};

const showDelete = (id) => {
    selectedUserId.value = id;
    showDeleteModal.value = true;
};

const closeEditModal = () => {
    selectedUserId.value = null;
    showEditModal.value = false;
};

const closeDeleteModal = () => {
    selectedUserId.value = null;
    showDeleteModal.value = false;
};

const search = async () => {
    currentPage.value = 1;
    await getUsersList();
};

const debouncedSearch = debounce(search, 500);

watch(searchValue, debouncedSearch);

onMounted(getUsersList);
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
                        <th class="w-[50%]">ФИО</th>
                        <th class="w-[20%]">Email</th>
                        <th class="w-[20%]">Роль</th>
                        <th class="w-[10%]"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>
                            {{
                                `${user.last_name} ${user.name} ${user.surname}`
                            }}
                        </td>
                        <td>{{ user.email }}</td>
                        <td>{{ user.role.name }}</td>
                        <td>
                            <div class="flex flex-row items-center justify-end">
                                <button
                                    class="btn btn-warning btn-soft mr-2"
                                    @click.prevent="showEdit(user.id)"
                                >
                                    Редактировать
                                </button>
                                <button
                                    class="btn btn-error btn-soft"
                                    @click.prevent="showDelete(user.id)"
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

    <create-user-modal
        :show="showCreateModal"
        @close-modal="showCreateModal = false"
        @created="search"
    />
    <edit-user-modal
        :id="selectedUserId"
        :show="showEditModal"
        @edited="search"
        @close-modal="closeEditModal"
    />
    <delete-user-modal
        :id="selectedUserId"
        :show="showDeleteModal"
        @deleted="search"
        @close-modal="closeDeleteModal"
    />
</template>
