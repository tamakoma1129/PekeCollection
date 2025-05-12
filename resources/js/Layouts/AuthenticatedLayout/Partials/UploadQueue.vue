<script setup>
import { ref, computed } from "vue";
import { useUploadQueueStore } from "@/stores/uploadQueue.js";
import ToolTip from "@/Components/ToolTip.vue";
import { useToast } from "vue-toast-notification";

const uploadQueueStore = useUploadQueueStore();
const isExpanded = ref(true);
const $toast = useToast();

// アップロード一覧のアコーディオンを開閉
const toggleExpansion = () => {
    isExpanded.value = !isExpanded.value;
};

const clearQueue = () => {
    uploadQueueStore.clearQueue();
};

const successfulUploads = computed(() => {
    return uploadQueueStore.files.filter(
        (file) => file.status === "ジョブ処理成功",
    ).length;
});

const failedUploads = computed(() => {
    return uploadQueueStore.files.filter(
        (file) => file.status === "アップロード失敗",
    ).length;
});

const retryUpload = (file, index) => {
    if (file.file) {
        uploadQueueStore.startTusUpload(index);
    } else {
        $toast.error("このファイルは再アップロードできません", {
            position: "top-right",
            duration: 5000,
        });
    }
};

const pauseUpload = (index) => {
    uploadQueueStore.pauseUpload(index);
};

const resumeUpload = (index) => {
    uploadQueueStore.resumeUpload(index);
};
</script>

<template>
    <div
        class="fixed bottom-[50px] right-24 w-[300px] max-h-[50dvh] bg-sumi-50 p-16 border border-solid border-sumi-500 rounded overflow-hidden"
    >
        <header>
            <div class="flex items-center justify-between">
                <div>
                    <span v-if="isExpanded" class="font-bold select-none"
                        >アップロードキュー</span
                    >
                    <span v-else-if="failedUploads > 0"
                        >アップロード失敗: {{ failedUploads }}</span
                    >
                    <span v-else class="font-bold select-none"
                        >アップロード成功: {{ successfulUploads }} 件</span
                    >
                </div>
                <div class="flex gap-4">
                    <button
                        class="hover:bg-sumi-300 rounded-full p-4"
                        @click="toggleExpansion"
                    >
                        <i-icon-park-outline-down
                            v-if="isExpanded"
                            class="h-24 w-24"
                        />
                        <i-icon-park-outline-up v-else class="h-24 w-24" />
                    </button>
                    <button
                        class="hover:bg-sumi-300 rounded-full p-4"
                        @click="clearQueue"
                    >
                        <i-icon-park-outline-close-small class="h-24 w-24" />
                    </button>
                </div>
            </div>
        </header>

        <div v-show="isExpanded" class="mt-4">
            <!-- ファイルリスト -->
            <div class="overflow-y-auto max-h-[40vh]">
                <div
                    v-for="(file, index) in uploadQueueStore.files"
                    :key="index"
                    class="flex items-center justify-between mb-2"
                >
                    <!-- ファイル名 -->
                    <span
                        class="whitespace-nowrap overflow-hidden overflow-ellipsis mr-2"
                        :title="file.name"
                        >{{ file.name }}</span
                    >

                    <!-- アップロード中のアイコン -->
                    <i-line-md-uploading-loop
                        v-if="file.status === 'アップロード中'"
                        class="w-24 h-24 flex-shrink-0"
                    />

                    <!-- 一時停止中の表示 -->
                    <span
                        v-if="file.status === '一時停止'"
                        class="text-gray-500 text-sm"
                    >
                        一時停止中
                    </span>

                    <!-- アップロード成功時のアイコン -->
                    <i-line-md-loading-twotone-loop
                        v-if="file.status === 'アップロード成功'"
                        class="w-24 h-24 flex-shrink-0"
                    />

                    <!-- ジョブ処理成功時のアイコン -->
                    <i-ic-round-check-circle
                        v-if="file.status === 'ジョブ処理成功'"
                        class="text-green-500 w-24 h-24 flex-shrink-0"
                    />

                    <!-- アップロード失敗時（クリックで再アップロード） -->
                    <div
                        v-if="file.status === 'アップロード失敗'"
                        @click="retryUpload(file, index)"
                        class="flex items-center cursor-pointer"
                    >
                        <ToolTip
                            :message="
                                file.errorMessage || 'エラーが発生しました'
                            "
                        >
                            <i-tdesign-error-circle-filled
                                class="text-teto-500 w-24 h-24 flex-shrink-0"
                            />
                        </ToolTip>
                    </div>

                    <!-- 一時停止中の再開ボタン -->
                    <button
                        v-if="file.status === '一時停止'"
                        @click="resumeUpload(index)"
                        class="mt-8 text-sm bg-blue-500 text-white px-16 py-8 rounded"
                    >
                        ▶️ 再開
                    </button>

                    <!-- アップロード中は一時停止ボタンを表示 -->
                    <button
                        v-else-if="file.status === 'アップロード中'"
                        @click="pauseUpload(index)"
                        class="mt-8 text-sm bg-yellow-500 text-white px-16 py-8 rounded"
                    >
                        ⏸️ 一時停止
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
