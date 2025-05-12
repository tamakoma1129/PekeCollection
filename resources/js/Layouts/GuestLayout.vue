<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-24 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo
                    class="h-104 w-104 fill-current text-gray-500"
                />
            </Link>
        </div>

        <div
            class="mt-24 w-full overflow-hidden bg-white px-24 py-16 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
