<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div class="flex items-center space-x-2">
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-400" />
            </Link>
            <h1 class="text-4xl font-bold text-gray-400">TECDA</h1>
        </div>

        <div
            class="mt-4 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
