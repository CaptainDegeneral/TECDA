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
        <button
            class="d-flex align-items-center btn btn-primary btn-soft w-[30%] gap-2"
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
                                <button
                                    class="d-flex align-items-center btn btn-warning btn-soft mr-2 gap-2"
                                    @click.prevent="showEdit(user.id)"
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
                                    class="d-flex align-items-center btn btn-error btn-soft gap-2"
                                    @click.prevent="showDelete(user.id)"
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

        <div
            v-else
            class="mt-8 flex h-full w-full flex-col items-center justify-center"
        >
            <svg
                viewBox="0 0 48 48"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="mb-4 h-20 w-20 fill-current text-gray-500"
            >
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g
                    id="SVGRepo_tracerCarrier"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                ></g>
                <g id="SVGRepo_iconCarrier">
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M24 42C33.9411 42 42 33.9411 42 24C42 14.0589 33.9411 6 24 6C14.0589 6 6 14.0589 6 24C6 33.9411 14.0589 42 24 42ZM24 44C35.0457 44 44 35.0457 44 24C44 12.9543 35.0457 4 24 4C12.9543 4 4 12.9543 4 24C4 35.0457 12.9543 44 24 44Z"
                    ></path>
                    <path
                        d="M19 20C19 21.1046 18.1046 22 17 22C15.8954 22 15 21.1046 15 20C15 18.8954 15.8954 18 17 18C18.1046 18 19 18.8954 19 20Z"
                    ></path>
                    <path
                        d="M33 20C33 21.1046 32.1046 22 31 22C29.8954 22 29 21.1046 29 20C29 18.8954 29.8954 18 31 18C32.1046 18 33 18.8954 33 20Z"
                    ></path>
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M18.5673 33.8235L18.5691 33.8223L18.5872 33.8101C18.6045 33.7986 18.6323 33.7803 18.6699 33.7563C18.7451 33.7082 18.8591 33.6371 19.0069 33.5507C19.303 33.3775 19.7307 33.1448 20.2485 32.9122C21.2987 32.4403 22.6508 32 24 32C25.3491 32 26.7012 32.4403 27.7514 32.9122C28.2693 33.1448 28.6969 33.3775 28.9931 33.5507C29.1408 33.6371 29.2549 33.7082 29.33 33.7563C29.3676 33.7803 29.3954 33.7986 29.4127 33.8101L29.4308 33.8223L29.4327 33.8235C29.4325 33.8234 29.4328 33.8236 29.4327 33.8235M29.4327 33.8235C29.4329 33.8237 29.4335 33.8241 29.4338 33.8243C29.8885 34.1366 30.5104 34.0217 30.8235 33.5673C31.1368 33.1125 31.0221 32.4898 30.5673 32.1765L30.0333 32.9516C30.5673 32.1765 30.5675 32.1766 30.5673 32.1765L30.5651 32.175L30.5619 32.1728L30.5523 32.1663L30.5205 32.1449C30.4938 32.1272 30.4562 32.1025 30.4083 32.0718C30.3126 32.0106 30.1757 31.9254 30.0028 31.8243C29.6578 31.6225 29.1662 31.3552 28.5711 31.0878C27.3955 30.5597 25.7476 30 24 30C22.2523 30 20.6045 30.5597 19.4289 31.0878C18.8338 31.3552 18.3421 31.6225 17.9971 31.8243C17.8243 31.9254 17.6873 32.0106 17.5916 32.0718C17.5438 32.1025 17.5062 32.1272 17.4795 32.1449L17.4476 32.1663L17.438 32.1728L17.4348 32.175L17.4336 32.1758C17.4334 32.176 17.4327 32.1765 18 33L17.4327 32.1765C16.9779 32.4898 16.8631 33.1125 17.1765 33.5673C17.4897 34.022 18.1125 34.1365 18.5673 33.8235"
                    ></path>
                </g>
            </svg>
            <p class="mb-8 text-lg text-gray-500">
                По вашему запросу ничего не найдено
            </p>
        </div>

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
