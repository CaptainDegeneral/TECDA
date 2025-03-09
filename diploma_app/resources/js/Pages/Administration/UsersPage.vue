<script setup>
import { ref, onMounted, watch } from 'vue';
import debounce from 'lodash/debounce.js';
import VPagination from '@/Components/VPagination.vue';
import TextInput from '@/Components/TextInput.vue';
import { getUsers } from '@/api/users.js';
import CreateUserModal from '@/Components/Administration/CreateUserModal.vue';
import EditUserModal from '@/Components/Administration/EditUserModal.vue';
import DeleteUserModal from '@/Components/Administration/DeleteUserModal.vue';
import NProgress from 'nprogress';
import { useNotificationStore } from '@/Store/NotificationStore.js';
import VNotFound from '@/Components/VNotFound.vue';
import VActionButton from '@/Components/VActionButton.vue';

const notification = useNotificationStore();
const { addNotification } = notification;

const searchValue = ref('');
const users = ref();
const pagination = ref();
const currentPage = ref(1);

const selectedUserId = ref(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

// Обработчики событий модальных окон
const handleCreated = () => {
    addNotification('success', 'Пользователь успешно создан');
    search();
};
const handleEdited = () => {
    addNotification('success', 'Пользователь успешно отредактирован');
    search();
};
const handleDeleted = () => {
    addNotification('success', 'Пользователь успешно удален');
    search();
};
const handleError = (message) => {
    addNotification('error', message);
};

const getUsersList = async () => {
    try {
        NProgress.start();
        const response = await getUsers(currentPage.value, searchValue.value);
        users.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (exception) {
        addNotification('error', 'При загрузке пользователей произошла ошибка');
    } finally {
        NProgress.done();
    }
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
            class="mr-5 w-[70%]"
        />
        <v-action-button type="create" @click="showCreateModal = true" />
    </div>

    <div class="flex w-full flex-col items-center space-y-6">
        <div v-if="users && users.length > 0" class="w-full overflow-x-auto">
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
                                <v-action-button
                                    type="edit"
                                    :id="user.id"
                                    @click="showEdit"
                                />
                                <v-action-button
                                    type="delete"
                                    :id="user.id"
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
            v-if="users && users.length > 0 && pagination"
            :pagination="pagination"
            @page-changed="changePage"
        />
    </div>

    <create-user-modal
        :show="showCreateModal"
        @close-modal="showCreateModal = false"
        @created="handleCreated"
        @error="handleError"
    />
    <edit-user-modal
        :id="selectedUserId"
        :show="showEditModal"
        @edited="handleEdited"
        @close-modal="closeEditModal"
        @error="handleError"
    />
    <delete-user-modal
        :id="selectedUserId"
        :show="showDeleteModal"
        @deleted="handleDeleted"
        @close-modal="closeDeleteModal"
        @error="handleError"
    />
</template>
