<!-- InitialConfiguration.vue -->
<script setup>
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import VActionButton from '@/Components/VActionButton.vue';

const props = defineProps({
    category: String,
    yearsOfWork: Number,
    startYear: Number,
});

const emit = defineEmits([
    'update:category',
    'update:yearsOfWork',
    'update:startYear',
    'unlock',
]);

const localCategory = ref(props.category);
const localYearsOfWork = ref(props.yearsOfWork);
const localStartYear = ref(props.startYear);

const unlockMainConfiguration = () => {
    if (
        !localCategory.value ||
        localYearsOfWork.value <= 0 ||
        localStartYear.value <= 0
    ) {
        emit('unlock', false);
        return;
    }
    emit('unlock', true);
};
</script>

<template>
    <section class="mb-12">
        <h2 class="mb-6 text-2xl font-semibold text-gray-900">
            Начальное конфигурирование
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <InputLabel value="Категория" />
                <select
                    v-model="localCategory"
                    @change="emit('update:category', localCategory)"
                    class="select-bordered select mt-1 block w-full"
                >
                    <option value="">Выберите категорию</option>
                    <option value="Первая">Первая</option>
                    <option value="Высшая">Высшая</option>
                </select>
            </div>
            <div>
                <InputLabel value="Количество лет работы" />
                <input
                    v-model.number="localYearsOfWork"
                    @input="emit('update:yearsOfWork', localYearsOfWork)"
                    type="number"
                    min="1"
                    class="input-bordered input mt-1 block w-full"
                />
            </div>
            <div>
                <InputLabel value="Стартовый год" />
                <input
                    v-model.number="localStartYear"
                    @input="emit('update:startYear', localStartYear)"
                    type="number"
                    class="input-bordered input mt-1 block w-full"
                    min="1900"
                    max="2100"
                    placeholder="ГГГГ"
                />
            </div>
            <div class="flex items-end">
                <button
                    type="button"
                    @click="unlockMainConfiguration"
                    class="btn btn-primary btn-soft w-full"
                >
                    Разблокировать
                </button>
            </div>
        </div>
    </section>
</template>
