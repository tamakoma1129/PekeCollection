<script setup>
import { useMediaStore } from "@/stores/media.js";
import {computed} from "vue";
import {useMediaList} from "@/stores/mediaList";
import {getPrivateStoragePath} from "@/utils.js";

const props = defineProps({
    mediaFile: Object,
});

const mediaStore = useMediaStore();
const mediaListStore = useMediaList();
const isPlaying = computed(() => mediaStore.isPlaying);

const toggleOpenMediaSpace = () => {
    mediaStore.openMediaSpace();
}
const setMedia = (mediaFile) => {
    if (mediaStore.src !== getPrivateStoragePath(mediaFile.path)) {
        mediaListStore.setMedia(mediaFile);
    }
}

const togglePlay = () => {
    if (isPlaying.value) {
        mediaStore.pause();
    } else {
        mediaStore.play();
    }
}
</script>

<template>
    <div class="w-[184px] h-[184px] bg-sumi-900 overflow-hidden relative shadow">
        <img v-if="mediaFile.preview_image_path"
             :src="getPrivateStoragePath(mediaFile.preview_image_path)"
             :alt="mediaFile.title"
             class="w-full h-auto"
        />
        <div v-else class="w-full h-full bg-gradient-to-r from-slate-900 to-slate-700"/>
        <button v-if="mediaFile.mediable_type === 'App\\Models\\Audio'"
                @click="() => {setMedia(mediaFile); togglePlay();}" class="absolute inset-0 flex items-center justify-center group">
            <!-- Audioのカバー -->
            <span>
                <i-emojione-pause-button v-if="isPlaying && getPrivateStoragePath(mediaFile.path) === mediaStore.src" class="h-64 w-64 text-miku-400 fill-white transform text-center overflow-hidden" />
                <i-emojione-play-button v-else class="h-64 w-64 text-miku-400 fill-white hidden group-hover:block" />
                <i-fa6-solid-headphones class="w-24 h-24 absolute top-0 right-0 box-content bg-teto-500 p-4 text-white drop-shadow-xl" />
            </span>
        </button>
        <button v-else-if="mediaFile.mediable_type === 'App\\Models\\Video'"
                @click="() => {toggleOpenMediaSpace(); setMedia(mediaFile);}" class="absolute inset-0 flex items-center justify-center group">
            <!-- Videoのカバー -->
            <span  class="flex group-hover:bg-black h-full w-full items-center justify-center overflow-hidden">
                <img :src="getPrivateStoragePath(mediaFile.mediable.preview_video_path)" alt="Videoのホバー時カバー" class="hidden group-hover:block my-auto mx-auto">
                <i-pepicons-pop-clapperboard class="w-24 h-24 absolute top-0 right-0 box-content bg-blue-500 p-4 text-white drop-shadow-xl" />
            </span>
        </button>
        <button v-else-if="mediaFile.mediable_type === 'App\\Models\\Image'"
                @click="() => {toggleOpenMediaSpace(); setMedia(mediaFile);}" class="absolute inset-0 flex items-center justify-center group">
            <!-- Imageのカバー -->
            <span  class="flex h-full w-full items-center justify-center overflow-hidden">
                <i-pepicons-pop-photo class="w-24 h-24 absolute top-0 right-0 box-content bg-green-500 p-4 text-white drop-shadow-xl" />
            </span>
        </button>
        <button v-else-if="mediaFile.mediable_type === 'App\\Models\\Manga'"
                @click="() => {toggleOpenMediaSpace(); setMedia(mediaFile);}" class="absolute inset-0 flex items-center justify-center group">
            <!-- Mangaのカバー -->
            <span  class="flex h-full w-full items-center justify-center overflow-hidden">
                <i-bi-book class="w-24 h-24 absolute top-0 right-0 box-content bg-violet-500 p-4 text-white drop-shadow-xl" />
            </span>
        </button>
    </div>
    <p class="text-base w-[184px] inline-block whitespace-nowrap overflow-hidden overflow-ellipsis"
       :title="mediaFile.title">
        {{ mediaFile.title }}
    </p>
</template>
