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
</script>

<template>
    <div class="join pt-2">
        <button
            class="btn join-item"
            :disabled="pagination.current_page === 1"
            @click="emit('page-changed', pagination.current_page - 1)"
        >
            Предыдущая
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
            :disabled="pagination.current_page === pagination.last_page"
            @click="emit('page-changed', pagination.current_page + 1)"
        >
            Следующая
        </button>
    </div>
</template>
