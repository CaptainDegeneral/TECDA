<script setup>
import { computed } from 'vue';

const props = defineProps({
    pagination: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['page-changed']);

const pages = computed(() => {
    if (!props.pagination) {
        return [];
    }

    const lastPage = props.pagination.last_page;
    const current = props.pagination.current_page;

    if (lastPage <= 7) {
        return Array.from({ length: lastPage }, (_, i) => i + 1);
    }

    const pages = [];
    pages.push(1);

    let left = Math.max(2, current - 2);
    let right = Math.min(lastPage - 1, current + 2);

    if (current <= 4) {
        left = 2;
        right = 5;
    }

    if (current >= lastPage - 3) {
        left = lastPage - 4;
        right = lastPage - 1;
    }

    if (left > 2) {
        pages.push('...');
    }

    for (let i = left; i <= right; i++) {
        pages.push(i);
    }

    if (right < lastPage - 1) {
        pages.push('...');
    }

    pages.push(lastPage);

    return pages;
});

// Вычисляем, отключены ли кнопки (если страница одна или на границах)
const isPrevDisabled = computed(
    () =>
        props.pagination.last_page === 1 || props.pagination.current_page === 1,
);

const isNextDisabled = computed(
    () =>
        props.pagination.last_page === 1 ||
        props.pagination.current_page === props.pagination.last_page,
);
</script>

<template>
    <div class="pt-2 join">
        <button
            class="btn join-item"
            :disabled="isPrevDisabled"
            @click="emit('page-changed', pagination.current_page - 1)"
        >
            <svg
                viewBox="0 0 15 15"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                :class="{ 'text-gray-500': isPrevDisabled }"
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
                        d="M8.84182 3.13514C9.04327 3.32401 9.05348 3.64042 8.86462 3.84188L5.43521 7.49991L8.86462 11.1579C9.05348 11.3594 9.04327 11.6758 8.84182 11.8647C8.64036 12.0535 8.32394 12.0433 8.13508 11.8419L4.38508 7.84188C4.20477 7.64955 4.20477 7.35027 4.38508 7.15794L8.13508 3.15794C8.32394 2.95648 8.64036 2.94628 8.84182 3.13514Z"
                        fill="currentColor"
                    ></path>
                </g>
            </svg>
        </button>

        <button
            v-for="(page, index) in pages"
            :key="index"
            class="btn join-item"
            :class="{
                'btn-active':
                    typeof page === 'number' &&
                    pagination.current_page === page,
            }"
            @click="typeof page === 'number' && emit('page-changed', page)"
            :disabled="typeof page !== 'number'"
        >
            <span v-if="typeof page === 'number'">{{ page }}</span>
            <span v-else>{{ page }}</span>
        </button>

        <button
            class="btn join-item"
            :disabled="isNextDisabled"
            @click="emit('page-changed', pagination.current_page + 1)"
        >
            <svg
                viewBox="0 0 15 15"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                :class="{ 'text-gray-500': isNextDisabled }"
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
                        d="M6.1584 3.13508C6.35985 2.94621 6.67627 2.95642 6.86514 3.15788L10.6151 7.15788C10.7954 7.3502 10.7954 7.64949 10.6151 7.84182L6.86514 11.8418C6.67627 12.0433 6.35985 12.0535 6.1584 11.8646C5.95694 11.6757 5.94673 11.3593 6.1356 11.1579L9.565 7.49985L6.1356 3.84182C5.94673 3.64036 5.95694 3.32394 6.1584 3.13508Z"
                        fill="currentColor"
                    ></path>
                </g>
            </svg>
        </button>
    </div>
</template>

<style scoped>
.btn svg {
    display: inline-block;
    vertical-align: middle;
}

.btn[disabled] svg {
    pointer-events: none;
}
</style>
