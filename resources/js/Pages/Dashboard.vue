<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout/AuthenticatedLayout.vue";

defineOptions({
    layout: AuthenticatedLayout,
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="py-40">
        <div class="mx-auto max-w-7xl sm:px-24 lg:px-40">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-24 text-gray-900">You're logged in!</div>
            </div>
        </div>
    </div>

</template>
