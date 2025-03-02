<template>
    <div class="search-select" ref="selectRef">
        <div class="selected" @click="toggleDropdown">
            <span v-if="selectedItem">{{ selectedItem[labelKey] }}</span>
            <span v-else class="placeholder">Выберите дисциплину</span>
            <span class="arrow">{{ dropdownVisible ? '▲' : '▼' }}</span>
        </div>
        <div v-if="dropdownVisible" class="dropdown">
            <input
                type="text"
                v-model="searchQuery"
                placeholder="Поиск..."
                class="search-input"
            />
            <ul class="options-list">
                <li
                    v-for="(option, index) in filteredOptions"
                    :key="option[valueKey]"
                    @click="selectOption(option)"
                >
                    {{ option[labelKey] }}
                </li>
                <li v-if="filteredOptions.length === 0" class="no-results">
                    Дисциплины не найдены
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    options: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: [String, Number, Object],
        default: null,
    },
    labelKey: {
        type: String,
        default: 'label',
    },
    valueKey: {
        type: String,
        default: 'value',
    },
});

const emit = defineEmits(['update:modelValue']);

const dropdownVisible = ref(false);
const searchQuery = ref('');
const selectedItem = ref(null);
const selectRef = ref(null);

const toggleDropdown = () => {
    dropdownVisible.value = !dropdownVisible.value;
    if (dropdownVisible.value) {
        searchQuery.value = '';
    }
};

const selectOption = (option) => {
    selectedItem.value = option;
    emit('update:modelValue', option);
    dropdownVisible.value = false;
    searchQuery.value = '';
};

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    return props.options.filter((option) =>
        option[props.labelKey]
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase()),
    );
});

watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) {
            const found = props.options.find(
                (option) =>
                    option[props.valueKey] === newVal ||
                    (typeof newVal === 'object' &&
                        option[props.valueKey] === newVal[props.valueKey]),
            );
            if (found) {
                selectedItem.value = found;
            }
        } else {
            selectedItem.value = null;
        }
    },
    { immediate: true },
);

const handleClickOutside = (event) => {
    if (selectRef.value && !selectRef.value.contains(event.target)) {
        dropdownVisible.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.search-select {
    position: relative;
    width: 250px;
    font-family: Arial, sans-serif;
}

.selected {
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 8px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fff;
}

.placeholder {
    color: #999;
}

.arrow {
    margin-left: 8px;
    font-size: 12px;
}

.dropdown {
    position: absolute;
    width: 100%;
    top: 100%;
    left: 0;
    right: 0;
    border: 1px solid #ccc;
    background-color: #fff;
    z-index: 10;
    max-height: 200px;
    overflow-y: auto;
}

.search-input {
    width: 100%;
    box-sizing: border-box;
    padding: 6px;
    border: none;
    border-bottom: 1px solid #ccc;
    outline: none;
}

.options-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.options-list li {
    padding: 8px;
    cursor: pointer;
}

.options-list li:hover {
    background-color: #f0f0f0;
}

.no-results {
    padding: 8px;
    text-align: center;
    color: #999;
}
</style>
