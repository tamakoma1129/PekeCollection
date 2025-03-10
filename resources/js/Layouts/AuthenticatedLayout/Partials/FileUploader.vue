<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useUploadQueueStore } from "@/stores/uploadQueue.js";
import { usePage } from "@inertiajs/vue3";

const uploadQueueStore = useUploadQueueStore();

const isDragging = ref(false);
const page = usePage();
uploadQueueStore.setCsrfToken(page.props.csrf_token);

const handleDragEnter = (event) => {
    if (event.dataTransfer.types.includes("Files")) {
        event.preventDefault();
        isDragging.value = true;
    }
};

const handleDragLeave = (event) => {
    event.preventDefault();
    // 子要素をまたぐとLeaveが発火するので、マウスがウィンドウ外に出た場合のみフラグをオフにする
    if (
        event.clientX <= 0 ||
        event.clientY <= 0 ||
        event.clientX >= window.innerWidth ||
        event.clientY >= window.innerHeight
    ) {
        isDragging.value = false;
    }
};

const handleDragOver = (event) => {
    if (event.dataTransfer.types.includes("Files")) {
        event.preventDefault();
    }
};

const handleDrop = (event) => {
    if (event.dataTransfer.types.includes("Files")) {
        event.preventDefault();
        isDragging.value = false;

        const droppedFiles = Array.from(event.dataTransfer.files);
        droppedFiles.forEach((file) => {
            // キューへファイルを追加
            uploadQueueStore.addFile(file);
            // このファイルのindex
            const index = uploadQueueStore.files.length - 1;
            // tusでアップロード開始
            uploadQueueStore.startTusUpload(index);
        });
    }
};

onMounted(() => {
    window.addEventListener("dragenter", handleDragEnter);
    window.addEventListener("dragover", handleDragOver);
    window.addEventListener("dragleave", handleDragLeave);
    window.addEventListener("drop", handleDrop);
});

onUnmounted(() => {
    window.removeEventListener("dragenter", handleDragEnter);
    window.removeEventListener("dragover", handleDragOver);
    window.removeEventListener("dragleave", handleDragLeave);
    window.removeEventListener("drop", handleDrop);
});
</script>

<template>
    <div
        v-if="isDragging"
        class="fixed inset-0 bg-black/70 pointer-events-none z-inf flex justify-center items-center"
    >
        <p class="text-white font-semibold text-3xl">D&Dでファイルをアップロード</p>
    </div>
</template>
