<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ['create', 'edit', 'delete'].includes(value),
    },
    id: {
        type: [String, Number],
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const buttonConfig = computed(() => {
    switch (props.type) {
        case 'create':
            return {
                text: 'Создать',
                classes: 'btn btn-primary btn-soft w-[30%]',
                icon: `
          <path
            d="M7 12L12 12M12 12L17 12M12 12V7M12 12L12 17"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          ></path>
        `,
            };
        case 'edit':
            return {
                text: 'Изменить',
                classes: 'btn btn-warning btn-soft mr-2',
                icon: `
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
        `,
            };
        case 'delete':
            return {
                text: 'Удалить',
                classes: 'btn btn-error btn-soft',
                icon: `
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
        `,
            };
        default:
            return {};
    }
});

const handleClick = () => {
    if (!props.disabled) {
        emit('click', props.id);
    }
};
</script>

<template>
    <button
        :class="['d-flex align-items-center gap-2', buttonConfig.classes]"
        :disabled="disabled"
        @click.prevent="handleClick"
    >
        <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="icon"
            v-html="buttonConfig.icon"
        ></svg>
        {{ buttonConfig.text }}
    </button>
</template>
