<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout/AuthenticatedLayout.vue';
import {Head, router, WhenVisible} from '@inertiajs/vue3';
import MediaPrev from "@/Pages/Media/Index/Partials/MediaPrev.vue";
import { useMediaEditStore } from "@/stores/mediaEdit.js";
import {computed, watch} from "vue";
import MediaPrevEdit from "@/Pages/Media/Index/Partials/MediaPrevEdit.vue";

import TagEdit from "@/Pages/Media/Index/Partials/TagEdit.vue";
import {useMediaList} from "@/stores/mediaList.js";
import Search from "@/Pages/Media/Index/Partials/Search.vue";
import EditMenuBar from "@/Pages/Media/Index/Partials/EditMenuBar.vue";

defineOptions({
    layout: AuthenticatedLayout,
});

const mediaList = useMediaList();
const props = defineProps({
    medias: Array,
    currentPage: Number,
    lastPage: Number,
    mediaType: String,
})

const mediaEditStore = useMediaEditStore();

const isView = computed(() => mediaEditStore.mode === "view");
const isEdit = computed(() => mediaEditStore.mode === "edit");

watch(
    () => props.medias,
    (newMedias) => {
        if (props.currentPage === 1) {
            mediaList.setMediaList(newMedias);
        } else {
            mediaList.appendMediaList(newMedias);
        }
    },
    { immediate: true }
);


Echo.private("login")
    .listen("MediaProcessedEvent", () => {
        console.log("MediaProcessedEvent");
        router.reload({ reset: ['medias'], only: ["medias"]})
    })
</script>

<template>
    <Head :title=mediaType />

    <div class="bg-white rounded-xl py-8 mb-16">
        <div class="flex justify-between items-center pt-8 px-16">
            <h2 v-if="isView" class="font-semibold text-xl">検索</h2>
            <h2 v-else-if="isEdit" class="font-semibold text-xl">編集</h2>

            <div class="mb-4 flex">
                <button class="border-y border-y-sumi-400 border-l border-l-sumi-400 rounded-l-lg p-4 w-64 flex justify-center hover:bg-sumi-300"
                        :class="{'bg-blue-200' : isView}"
                        @click="mediaEditStore.modeToView()">
                    <i-material-symbols-view-cozy-sharp class="h-24 w-24" />
                </button>
                <button class="border-y border-y-sumi-400 border-r border-r-sumi-400 rounded-r-lg p-4 w-64 flex justify-center hover:bg-sumi-300"
                        :class="{'bg-blue-200' : isEdit}"
                        @click="mediaEditStore.modeToEdit()">
                    <i-icon-park-solid-edit-two class="h-24 w-24" />
                </button>
            </div>
        </div>
        <Search v-if="isView" />
    </div>

    <TagEdit v-if="isEdit" />

    <ul class="flex divide-y divide-sumi-400 p-16 bg-white rounded-xl"
        :class="{'flex-wrap gap-x-24 gap-y-40' : isView, 'flex-col' : isEdit}">
        <li v-for="media in medias" :key="media.id">
            <MediaPrev v-if="isView" :mediaFile="media" />
            <MediaPrevEdit v-else-if="isEdit" :mediaFile="media" />
        </li>
    </ul>

    <div v-show="currentPage < lastPage">
        <WhenVisible
            always
            :params="{
            data: {
              page: currentPage + 1
            },
            only: ['medias', 'currentPage'],
            preserveUrl: true
          }"
        >
            <!-- #fallbackを使わないのは、最後のページになったらLoading...が出ないようにするため -->
            <div class="flex justify-center items-center py-64">
                <i-svg-spinners-ring-resize class="w-24 h-24 mr-16" />
                <span class="text-2xl">Loading...</span>
            </div>
        </WhenVisible>
    </div>

    <EditMenuBar v-if="isEdit" />
</template>
