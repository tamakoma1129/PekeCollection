<script setup>
import {useMediaStore} from "@/stores/media.js";
import MediaSpaceAudio from "@/Layouts/AuthenticatedLayout/Partials/MediaSpaceAudio.vue";
import MediaSpaceImage from "@/Layouts/AuthenticatedLayout/Partials/MediaSpaceImage.vue";
import MediaSpaceVideo from "@/Layouts/AuthenticatedLayout/Partials/MediaSpaceVideo.vue";
import {computed, onBeforeUnmount, onMounted, ref, watch} from "vue";
import {useMediaList} from "@/stores/mediaList";

const mediaStore = useMediaStore();
const mediaListStore = useMediaList();
const dialog = ref(null);

const screenWidth = ref(window.innerHeight - 1)
const screenHeight = ref(window.innerWdith - 1)

/**
 * Mediaと画面比率どちらが狭いかを判定し、それによってwidthを付けるかheightを付けるか判断する。
 * @type {ComputedRef<null|boolean>}
 */
const isMediaNarrower = computed(() => {
    if (!mediaStore.width || !mediaStore.height) {
        return null;
    }
    let mediaRatio = mediaStore.width / mediaStore.height;
    let windowRatio = screenWidth.value / screenHeight.value;
    console.log(mediaRatio+"と"+windowRatio);
    return mediaRatio < windowRatio;
})

const showDialog = () => {
    const html = document.documentElement;
    screenHeight.value = window.innerHeight - 1;
    screenWidth.value = html.clientWidth - 1;
    if (dialog.value && !dialog.value.open) {
        dialog.value.showModal();
    }
};

const closeDialog = () => {
    if (dialog.value && dialog.value.open) {
        dialog.value.close();
    }
};

watch(
    () => mediaStore.isOpenMediaSpace,
    (isOpenMediaSpace) => {
        if (isOpenMediaSpace) {
            showDialog();
        } else {
            closeDialog();
        }
    }
);

const closeMediaSpace = () => {
    mediaStore.closeMediaSpace();
};

const handleKeyDown = (event) => {
    if (mediaStore.isOpenMediaSpace) {
        if (event.key === "ArrowRight") {
            event.preventDefault();
            setNextMedia();
        } else if (event.key === "ArrowLeft") {
            event.preventDefault();
            setPreviousMedia();
        }
    }
};

const isControlsVisible = ref(true);
let hideControlsTimeout = null;

// 一定時間後にボタンを非表示にする
const startHideControlsTimer = () => {
    const disappearTime = 390;  // ms秒
    clearTimeout(hideControlsTimeout);
    hideControlsTimeout = setTimeout(() => {
        isControlsVisible.value = false;
    }, disappearTime);
};

const showControls = () => {
    isControlsVisible.value = true;
    startHideControlsTimer();
};

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);

    window.addEventListener("mousemove", showControls);
    window.addEventListener("touchstart", showControls);
    startHideControlsTimer();
})

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeyDown);

    window.removeEventListener("mousemove", showControls);
    window.removeEventListener("touchstart", showControls);
    clearTimeout(hideControlsTimeout);
});

const setNextMedia = () => {
    mediaListStore.setNextMedia();
}

const setPreviousMedia = () => {
    mediaListStore.setPreviousMedia();
}
</script>

<template>
    <dialog ref="dialog"
            class="media-dialog backdrop-blur backdrop:bg-sumi-800/90 focus:outline-none"
            @click.self="closeMediaSpace">
        <div id="media-space-container" class="transform-none">
            <MediaSpaceImage v-if="mediaStore.type === 'App\\Models\\Image' || mediaStore.type === 'App\\Models\\Manga'"
                             class="media-element"
                             :style="isMediaNarrower ? `height: ${screenHeight-1}px` : `width: ${screenWidth-1}px`"/>
            <MediaSpaceAudio v-else-if="mediaStore.type === 'App\\Models\\Audio'"
                             class="media-element"/>
            <MediaSpaceVideo v-else-if="mediaStore.type === 'App\\Models\\Video'"
                             class="media-element"
                             :style="isMediaNarrower ? `height: ${screenHeight-1}px` : `width: ${screenWidth-1}px`"/>
        </div>
        <button @click="closeMediaSpace"
                class="fixed top-[2dvh] right-[1dvw] rounded px-4 text-white/50 transition-opacity duration-300"
                :class="{ 'opacity-0': !isControlsVisible, 'opacity-100': isControlsVisible }">
            <i-mingcute-close-fill class="h-[6dvh] w-[6dvw] focus:outline-none"/>
        </button>

        <button @click="setPreviousMedia"
                class="fixed left-[1dvw] top-1/2 text-white/50 focus:outline-none transition-opacity duration-300"
                :class="{ 'opacity-0': !isControlsVisible, 'opacity-100': isControlsVisible }">
            <i-mingcute-left-fill class="h-[6dvh] w-[6dvw]"/>
        </button>
        <button @click="setNextMedia"
                class="fixed right-[1dvw] top-1/2 text-white/50 focus:outline-none transition-opacity duration-300"
                :class="{ 'opacity-0': !isControlsVisible, 'opacity-100': isControlsVisible }">
            <i-mingcute-right-fill class="h-[6dvh] w-[6dvw]"/>
        </button>
    </dialog>
</template>

<style scoped>
.media-dialog {
    all: unset;
    display: none;
    position: fixed;
    max-width: 100%;
    max-height: 100%;
    inset: 0;
    margin: auto;
    padding: 0;
    border: none;
    overflow: auto;
}

.media-dialog[open] {
    display: flex;
    justify-content: center;
    align-items: center;
}

#media-space-container :deep(.media-element) {
    max-width: none;
    max-height: none;
    object-fit: contain;
}
</style>
