<script setup>
import {computed, onMounted, onUnmounted,} from "vue";
import { useMediaStore } from "@/stores/media.js";
import MediaSpace from "./MediaSpace.vue";
import {useMediaList} from "@/stores/mediaList.js";
import {getPrivateStoragePath} from "@/utils.js";

const mediaStore = useMediaStore();
const mediaListStore = useMediaList();

const isPlaying = computed(() => mediaStore.isPlaying);
const currentTime = computed({
    get: () => mediaStore.currentTime,
    set: (value) => mediaStore.changeCurrentTime(value),
});
const duration = computed(() => mediaStore.duration);
const volume = computed({
    get: () => mediaStore.currentVolume,
    set: (value) => mediaStore.changeVolume(parseFloat(value)),
});
const isMuted = computed(() => mediaStore.isMuted);
const isOpenMediaSpace = computed(() => mediaStore.isOpenMediaSpace);

const togglePlay = () => {
    if (isPlaying.value) {
        mediaStore.pause();
    } else {
        mediaStore.play();
    }
};

const toggleMute = () => {
    if (isMuted.value) {
        mediaStore.unmute();
    } else {
        mediaStore.mute();
    }
};

const formatSecondsToTime = (seconds) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = Math.floor(seconds % 60);

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, "0")}:${remainingSeconds.toString().padStart(2, "0")}`;
    }
    return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
}

const handleKeydown = (event) => {
    const tagName = event.target.tagName.toLowerCase();
    if (tagName === "input" || tagName === "textarea") {
        return;
    }

    if (event.code === "Space") {
        event.preventDefault();
        togglePlay();
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
});
const setNextMedia = () => {
    mediaListStore.setNextMedia();
};

const setPreviousMedia = () => {
    mediaListStore.setPreviousMedia();
};
</script>

<template>
    <div
        class="fixed bottom-0 w-full bg-stone-100 h-[56px] flex items-center justify-center border-t border-t-sumi-400 z-inf"
    >
        <!-- 再生/停止ボタン -->
        <div class="flex gap-16 mr-16">
            <button @click="setPreviousMedia">
              <i-iconoir-skip-prev-solid class="h-24 w-24 text-sumi-800" />
            </button>
            <button @click="togglePlay">
                <i-iconoir-pause-solid
                    v-if="isPlaying"
                    class="h-24 w-24 text-sumi-800"
                />
                <i-iconoir-play-solid
                    v-else
                    class="h-24 w-24 text-sumi-800"
                />
            </button>
            <button @click="setNextMedia">
              <i-iconoir-skip-next-solid class="h-24 w-24 text-sumi-800" />
            </button>
            <!-- プレビュー画像 -->
            <div class="w-40 h-40 bg-sumi-900 overflow-hidden border border-sumi-400 box-content">
                <img
                    v-if="mediaStore.preview_image_path"
                    :src="mediaStore.preview_image_path"
                    :alt="mediaStore.title"
                    class="w-full h-full"
                />
                <div
                    v-else
                    class="w-full h-full bg-gradient-to-r from-slate-900 to-slate-700"
                />
            </div>
        </div>

        <!-- 再生位置スライダー -->
        <div class="flex items-center">
            <div class="w-40 h-full text-sm mr-8">
                <p class="text-right text-miku-400 whitespace-nowrap font-semibold">{{ formatSecondsToTime(currentTime) }}</p>
            </div>
            <div class="w-[600px]">
                <p class="text-center whitespace-nowrap overflow-hidden overflow-ellipsis">
                    {{ mediaStore.title }}
                </p>
                <input
                    class="w-[600px]"
                    type="range"
                    :min="0"
                    :max="duration"
                    step="1"
                    v-model="currentTime"
                />
            </div>
            <div class="w-40 h-full text-sm">
                <p class="text-right whitespace-nowrap font-semibold">{{ formatSecondsToTime(duration) }}</p>
            </div>
        </div>


        <div class="ml-16 flex items-center gap-16">
            <!-- 音量コントロール -->
            <div class="relative">
                <div class="peer" @click="toggleMute">
                    <i-pepicons-pop-speaker-low-off
                        v-if="isMuted || volume === 0"
                        class="h-24 w-24"
                    />
                    <i-pepicons-pop-speaker-low
                        v-else-if="volume <= 0.5"
                        class="h-24 w-24"
                    />
                    <i-pepicons-pop-speaker-high
                        v-else
                        class="h-24 w-24"
                    />
                </div>
                <div class="hidden absolute bottom-24 -right-8 peer-hover:flex hover:flex w-40 border-0 animate-scale-up-bottom bg-gray-100 py-8 justify-center">
                    <input
                        type="range"
                        min="0"
                        max="1"
                        step="0.1"
                        v-model="volume"
                        style="writing-mode: vertical-lr; direction: rtl"
                        class="w-24"
                    />
                </div>
            </div>
            <!-- メディアスペース -->
            <div class="flex items-center">
                <button @click="mediaStore.openMediaSpace">
                    <i-mingcute-expand-player-fill class="h-24 w-24" />
                </button>
                <MediaSpace />
            </div>
        </div>
    </div>
</template>
