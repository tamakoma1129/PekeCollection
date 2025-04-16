<script setup>
import {useMediaStore} from "@/stores/media.js";
import MediaSpaceAudio from "@/Layouts/AuthenticatedLayout/Partials/MediaSpaceAudio.vue";
import MediaSpaceImage from "@/Layouts/AuthenticatedLayout/Partials/MediaSpaceImage.vue";
import MediaSpaceVideo from "@/Layouts/AuthenticatedLayout/Partials/MediaSpaceVideo.vue";
import {computed, onBeforeUnmount, onMounted, ref, watch} from "vue";
import {useMediaList} from "@/stores/mediaList";
import {useForm} from "@inertiajs/vue3";
import {useToast} from "vue-toast-notification";

const $toast = useToast();

const mediaStore = useMediaStore();
const mediaListStore = useMediaList();
const dialog = ref(null);

const screenWidth = ref(window.innerHeight - 1);
const screenHeight = ref(window.innerWdith - 1);

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
const isMouseOverControlArea = ref(false);
let hideControlsTimeout = null;

// 一定時間後にボタンを非表示にする
const startHideControlsTimer = () => {
    const disappearTime = 390;  // ms秒
    clearTimeout(hideControlsTimeout);
    hideControlsTimeout = setTimeout(() => {
        if (!isMouseOverControlArea.value) {
            isControlsVisible.value = false;
        }
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
    window.addEventListener("click", showControls);
    startHideControlsTimer();
})

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKeyDown);

    window.removeEventListener("mousemove", showControls);
    window.removeEventListener("touchstart", showControls);
    window.removeEventListener("click", showControls);
    clearTimeout(hideControlsTimeout);
});

const isMenuBarVisible = ref(false);

const setThumbnailFromCurrentTime = () => {
    useForm({
        "prev_time": mediaStore.currentTime,
    }).patch(route("media_file.update", mediaStore.id), {
        onSuccess: () => {
            $toast.success("サムネイルを変更しました", {
                position: 'top-right',
                duration: 5000
            });
        },
        onError: () => {
            $toast.error("サムネイルの変更でエラーが発生しました", {
                position: 'top-right',
                duration: 5000
            });
        },
    })
}

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
        <!--メディアスペースを閉じるアイコン-->
        <button @click="closeMediaSpace"
                class="fixed top-[2dvh] right-[1dvw] rounded px-4 text-white/50 transition-opacity duration-300"
                :class="{ 'opacity-0': !isControlsVisible, 'opacity-100': isControlsVisible }">
            <i-mingcute-close-fill class="h-[6dvh] w-[6dvw] focus:outline-none"/>
        </button>
        <!--次・前のメディアへ移動する矢印-->
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
        <!--左上のハンバーガー-->
        <div class="fixed top-[2dvh] left-[2dvw] transition-opacity duration-300"
             :class="{ 'opacity-0': !isControlsVisible, 'opacity-100': isControlsVisible }"
             @mouseenter="isMouseOverControlArea = true"
             @mouseleave="isMouseOverControlArea = false">
            <button @click="() => isMenuBarVisible = !isMenuBarVisible"
                    class="rounded text-white/50">
                <i-mingcute-menu-fill
                    class="h-[4dvh] w-fit"/>
            </button>
            <ul v-if="isMenuBarVisible"
                class="bg-white/50 py-8 rounded-lg
                       [&>li]:flex [&>li]:items-center [&>li]:justify-between [&>li]:py-8 [&>li]:px-4 [&>li]:cursor-pointer">
                <li v-if="mediaStore.type === 'App\\Models\\Video'"
                    @click="setThumbnailFromCurrentTime"
                    class="hover:bg-sumi-300">
                    <i-iconoir-screenshot class="text-black w-24"/>
                    <p>ここをサムネイルにする</p>
                </li>
            </ul>
        </div>
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
