<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useMediaStore } from "@/stores/media.js";

const mediaStore = useMediaStore();
const videoElement = ref(null);

onMounted(() => {
    const element = videoElement.value;
    mediaStore.setMediaElement(element);

    // 初期設定
    element.src = mediaStore.src;
    element.currentTime = mediaStore.currentTime || 0;
    element.volume = mediaStore.currentVolume || 1;
    element.muted = mediaStore.isMuted || false;

    if (mediaStore.isPlaying) {
        element.play();
    }
});

onBeforeUnmount(() => {
    mediaStore.unsetMediaElement();
});
</script>

<template>
    <video ref="videoElement" controls></video>
</template>
