<script setup>
import { ref } from "vue";
import { useToast } from "vue-toast-notification";
import {Head, usePage} from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout/AuthenticatedLayout.vue";
import * as zip from "@zip.js/zip.js";
import {convertToValidWindowsFileName} from "@/utils.js";
import {useUploadQueueStore} from "@/stores/uploadQueue.js";

defineOptions({
    layout: AuthenticatedLayout,
});

const uploadQueueStore = useUploadQueueStore();
const images = ref([]);
const mangaTitle = ref("");
let draggedImageIndex = ref(null);
const isDragging = ref(false);
const selectedImages = ref([]);
const $toast = useToast();
const page = usePage();
uploadQueueStore.setCsrfToken(page.props.csrf_token);

const handleFiles = (event) => {
    const files = event.target.files;
    processFiles(files);
};

const handleDrop = (event) => {
    const files = event.dataTransfer.files;
    processFiles(files);
};

const processFiles = (files) => {
    for (let file of files) {
        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = (e) => {
                images.value.push({ file, preview: e.target.result });
            };
            reader.readAsDataURL(file);
        }
    }
};

const sortImagesByName = () => {
    images.value.sort((a, b) => {
        const nameA = a.file.name.toLowerCase();
        const nameB = b.file.name.toLowerCase();

        const numA = parseInt(nameA.match(/\d+/)?.[0] || "0", 10);
        const numB = parseInt(nameB.match(/\d+/)?.[0] || "0", 10);

        if (numA !== numB) {
            return numA - numB;
        } else {
            return nameA < nameB ? -1 : nameA > nameB ? 1 : 0;
        }
    });

    $toast.success("フォルダ名順で並べました", {
        position: "top-right",
        duration: 5000
    });
};

const toggleSelection = (index) => {
    if (selectedImages.value.includes(index)) {
        selectedImages.value = selectedImages.value.filter((i) => i !== index);
    } else {
        selectedImages.value.push(index);
        selectedImages.value.sort((a, b) => a - b);
    }
};

const startDrag = (index) => {
    if (!selectedImages.value.includes(index)) {
        selectedImages.value = [index];
    }
    draggedImageIndex.value = index;
    isDragging.value = true;
};

const endDrag = () => {
    draggedImageIndex.value = null;
    isDragging.value = false;
};

const dropImage = (targetIndex) => {
    if (selectedImages.value.length > 0) {
        const movedImages = selectedImages.value.map((i) => images.value[i]);
        images.value = images.value.filter((_, i) => !selectedImages.value.includes(i));
        images.value.splice(targetIndex, 0, ...movedImages);
        selectedImages.value = [];
    }
    isDragging.value = false;
};

const dropToDelete = () => {
    if (selectedImages.value.length > 0) {
        images.value = images.value.filter((_, i) => !selectedImages.value.includes(i));
        selectedImages.value = [];
    }
    isDragging.value = false;
};

// =============== ZIP化のヘルパー関数 ===============
async function createZip(files, title) {
    const zipWriter = new zip.ZipWriter(new zip.BlobWriter("application/zip"));

    for (const [index, file] of files.entries()) {
        const extension = file.name.split('.').pop(); // .tar.gzなどには対応できないが、メディアファイルの拡張子にそのような例外は無い(はず)
        const newFileName = `${index + 1}.${extension}`;

        await zipWriter.add(newFileName, new zip.BlobReader(file));
    }


    const filename = convertToValidWindowsFileName(title);
    const blob = await zipWriter.close();
    blob.name = filename;

    return blob;
}

// =============== アップロードを実行するメイン関数 ===============
const uploadManga = async () => {
    if (!mangaTitle.value) {
        $toast.error("タイトルを入力してください。", {
            position: "top-right",
            duration: 5000
        });
        return;
    }
    if (images.value.length === 0) {
        $toast.error("画像がありません。", {
            position: "top-right",
            duration: 5000
        });
        return;
    }

    try {
        // ZIP化する
        $toast.info("ZIP圧縮中...", { position: "top-right" });
        const files = images.value.map((img) => img.file);
        const zipBlob = await createZip(files, mangaTitle.value);

        // tusでアップロード
        $toast.info("アップロード開始...", { position: "top-right" });
        uploadQueueStore.addFile(zipBlob);
        const index = uploadQueueStore.files.length - 1;
        uploadQueueStore.startTusUpload(index);

        // リセット
        images.value = [];
        mangaTitle.value = "";
    } catch (err) {
        console.error("アップロード中にエラー:", err);
        $toast.error("アップロード中にエラーが発生しました", {
            position: "top-right",
            duration: 5000
        });
    }
};
</script>

<template>
    <Head title="漫画追加" />
    <div class="space-y-24 flex flex-col justify-center items-center pb-168">

        <!-- ドロップエリア -->
        <div
            @dragover.prevent
            @drop.prevent="handleDrop"
            class="border-4 border-dashed border-gray-400 m-16 p-16 text-center w-full"
        >
            <p class="text-gray-500">ここに画像をドラッグ＆ドロップしてください</p>
            <input type="file" multiple hidden ref="fileInput" @change="handleFiles" />
            <button
                @click="$refs.fileInput.click()"
                class="mt-8 bg-gray-500 text-white p-16 rounded"
            >
                ファイルを選択
            </button>
        </div>

        <!-- フォルダ名順に並べ替え -->
        <button
            @click="sortImagesByName"
            class="bg-green-500 text-white py-4 w-168"
        >
            フォルダ名順に並べる
        </button>

        <!-- プレビューと並べ替え -->
        <div class="flex flex-wrap gap-16">
            <div
                v-for="(image, index) in images"
                :key="index"
                :class="{
                    'cursor-grab': true,
                    'border-blue-500 border-4': selectedImages.includes(index),
                }"
                draggable="true"
                @click="toggleSelection(index)"
                @dragstart="startDrag(index)"
                @dragend="endDrag"
                @dragover.prevent
                @drop="dropImage(index)"
                class="m-8 border-4 border-gray-400 p-4"
            >
                <p class="text-sm text-center">
                    {{ index + 1 }}ページ
                </p>
                <img :src="image.preview" alt="Preview" class="w-104 h-auto rounded" />
                <p class="text-sm text-center">
                    {{ image.file.name }}
                </p>
            </div>
        </div>

        <!-- 削除エリア -->
        <div
            v-if="isDragging"
            class="fixed bottom-0 left-0 right-0 border-4 border-dashed border-red-500 bg-red-100/90 h-168 flex justify-center items-center"
            @dragover.prevent
            @drop.prevent="dropToDelete"
        >
            <p class="font-bold text-3xl text-red-600">ここにドラッグして削除</p>
        </div>

        <!-- タイトル入力欄 -->
        <input
            v-model="mangaTitle"
            type="text"
            autocomplete="off"
            placeholder="漫画のタイトルを入力"
            class="w-full border border-gray-300 rounded"
        />

        <!-- アップロードボタン -->
        <button
            @click="uploadManga"
            class="bg-blue-500 text-white py-8 px-16 rounded w-168"
        >
            アップロード
        </button>
    </div>
</template>

<style scoped>
.cursor-grab {
    cursor: grab;
}
.cursor-grab:active {
    cursor: grabbing;
}
</style>
