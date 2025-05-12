<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? "inline-flex items-center px-4 pt-16 border-b-4 border-miku-400 text-lg font-semibold leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
        : "inline-flex items-center px-4 pt-16 border-b-4 border-transparent text-lg font-semibold leading-5 text-sumi-400 hover:text-sumi-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 transition duration-150 ease-in-out",
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
