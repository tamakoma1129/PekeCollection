import { defineStore } from "pinia";
import * as tus from "tus-js-client";
import {ulid} from "ulid";

export const useUploadQueueStore = defineStore("uploadQueue", {
    state: () => ({
        files: [],
        csrfToken: null,
    }),
    actions: {
        setCsrfToken(token) {
            this.csrfToken = token;
        },

        addFile(file) {
            this.files.push({
                queueId: ulid(),    // アップロード状況をバックエンドから更新できるようにするためだけの簡易的なID。一意であれば何でも良い。
                file: file,
                name: file.name,
                status: "待機中",
                errorMessage: null,
                progress: 0,
                uploadInstance: null,
            });
        },

        startTusUpload(index) {
            const item = this.files[index];
            if (!item || !item.file) {
                return;
            }

            item.status = "アップロード中";
            item.errorMessage = null;
            item.progress = 0;

            const upload = new tus.Upload(item.file, {
                endpoint: "http://"+window.location.hostname+":1080/uploads/",
                retryDelays: [2000],
                metadata: {
                    filename: item.file.name,
                    mimetype: item.file.type,
                    queueId: item.queueId,
                },
                onBeforeRequest: (req) => {
                    const xhr = req.getUnderlyingObject();
                    xhr.withCredentials = true;
                    xhr.setRequestHeader("X-CSRF-TOKEN", this.csrfToken);
                },
                onProgress: (bytesSent, bytesTotal) => {
                    item.progress = (bytesSent / bytesTotal) * 100;
                },
                onSuccess: () => {
                    item.status = "アップロード成功";
                    // メモリ解放（ファイル本体を空にする）
                    item.file = null;
                    item.uploadInstance = null;
                },
                onError: (error) => {
                    item.status = "アップロード失敗";
                    item.errorMessage = error ? error.toString() : "アップロード中にエラーが発生しました";
                },
            });

            // インスタンスを保持しておき、一時停止・再開で再利用
            item.uploadInstance = upload;

            upload.start();
        },

        pauseUpload(index) {
            const item = this.files[index];
            if (item?.uploadInstance && item.status === "アップロード中") {
                item.uploadInstance.abort();
                item.status = "一時停止";
            }
        },

        resumeUpload(index) {
            const item = this.files[index];
            if (item?.uploadInstance && item.status === "一時停止") {
                item.status = "アップロード中";
                item.uploadInstance.start();
            }
        },

        clearQueue() {
            this.files = [];
        },

        proceedJob(queueId) {
            const item = this.files.find((item) => item.queueId === queueId);
            if (item?.status && item.status === "アップロード成功") {
                item.status = "ジョブ処理成功"
            }
        }
    },
});
