<script setup>
import { useMediaStore } from "@/stores/media.js";
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

const mediaStore = useMediaStore();

// 実際に表示する画像パス
const displayedSrc = ref("");

const imageElement = ref(null);

const updateImage = () => {
    displayedSrc.value = mediaStore.srcLite;

    const tempImg = new Image();
    tempImg.src = mediaStore.src;

    tempImg.onload = () => {
        displayedSrc.value = mediaStore.src;
    };
};

onMounted(() => {
    mediaStore.setImageElement(imageElement.value);

    watch(
        () => [mediaStore.src, mediaStore.srcLite],
        () => {
            updateImage();
        },
        { immediate: true },
    );
});

onBeforeUnmount(() => {
    mediaStore.unsetImageElement();
});
</script>

<template>
    <img ref="imageElement" draggable="false" :src="displayedSrc" alt="media" />
</template>
