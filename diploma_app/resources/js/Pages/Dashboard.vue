<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Главная" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">Здесь главная страница</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
