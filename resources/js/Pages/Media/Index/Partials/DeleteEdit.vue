<script setup>
import {useToast} from "vue-toast-notification";
import {useMediaEditStore} from "@/stores/mediaEdit.js";
import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import DangerButton from "@/Components/DangerButton.vue";
import {useMediaList} from "@/stores/mediaList.js";
import {getPrivateStoragePath} from '@/utils';

const $toast = useToast();
const mediaEditStore = useMediaEditStore();
const mediaListStore = useMediaList();

const isOpen = ref(false);
const selectedDeleteMedia = ref([]);
const deleteModal = ref();

const form = useForm({
    media_ids: null,
    password: null,
});

const openDeleteDialog = () => {
    if (mediaEditStore.selectedMediaIds.length === 0) {
        $toast.error("メディアが選択されていません", {
            position: 'top-right',
            duration: 5000
        });
        return;
    }

    selectedDeleteMedia.value = [];
    mediaEditStore.selectedMediaIds.forEach((id) => {
        selectedDeleteMedia.value.push(mediaListStore.getMediaData(id));
    });

    deleteModal.value.showModal();
};

const deleteMedia = () => {
    form.media_ids = selectedDeleteMedia.value.map((item)=>(item.id));
    form.delete(route("media_file.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            $toast.success("データとそのファイルを削除しました", {
                position: 'top-right',
                duration: 5000
            });
            closeDeleteModal();
            mediaEditStore.clearSelection();
        },
        onError: () => {
            $toast.error("ファイル削除に失敗しました", {
                position: 'top-right',
                duration: 5000
            });
        },
    })
}

const closeDeleteModal = () => {
    deleteModal.value.close();
}

const toggleOpen = () => {
    isOpen.value = !isOpen.value;
}
</script>

<template>
    <div class="my-16 bg-white rounded-xl cursor-pointer" @click.self="toggleOpen">
        <button @click="toggleOpen" class="box-content flex items-center pl-4 pr-16">
            <i-icon-park-outline-down v-if="isOpen" class="h-24 w-24"/>
            <i-icon-park-outline-up v-else class="h-24 w-24"/>
            <span class="font-semibold" :class="{'text-base': !isOpen, 'text-lg': isOpen}">メディアを削除</span>
        </button>
        <div v-if="isOpen" class="px-16 pb-8 cursor-default">
            <div class="flex justify-center items-center pb-8">
                <p class="mr-8">選択中の {{ mediaEditStore.selectedMediaIds.length }} 個のメディアを</p>
                <button @click.stop="openDeleteDialog" class="w-fit py-4 px-8 bg-teto-500 hover:bg-teto-600 text-white rounded-xl">
                    削除
                </button>
            </div>
        </div>
        <dialog ref="deleteModal" class="rounded-xl backdrop:bg-sumi-800/50 bg-white w-2/3 h-5/6  overflow-y-auto">
            <div class="h-full w-full" v-click-outside="closeDeleteModal">
                <h2 class="text-center text-2xl mt-16">以下のメディアを削除しますか？</h2>
                <div class="flex flex-wrap px-8 gap-8">
                    <div v-for="mediaFile in selectedDeleteMedia" :key="mediaFile.id" class="border border-sumi-300 w-[184px]">
                        <div
                            class="w-[184px] h-[184px] bg-sumi-900 overflow-hidden relative">
                            <img v-if="mediaFile.preview_image_path"
                                 :src="getPrivateStoragePath(mediaFile.preview_image_path)"
                                 :alt="mediaFile.title"
                                 class="w-full h-auto"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-r from-slate-900 to-slate-700"/>
                            <div class="absolute inset-0 flex items-center justify-center group">
                                <!-- Audioのカバー -->
                                <span v-if="mediaFile.mediable_type === 'App\\Models\\Audio'">
                            <i-fa6-solid-headphones class="w-24 h-24 absolute top-0 right-0 box-content bg-teto-500 p-4 text-white drop-shadow-xl" />
                        </span>
                                <!-- Videoのカバー -->
                                <span v-else-if="mediaFile.mediable_type === 'App\\Models\\Video'" class="flex group-hover:bg-black h-full w-full items-center justify-center overflow-hidden">
                            <img :src="getPrivateStoragePath(mediaFile.mediable.preview_video_path)" alt="Videoのホバー時カバー" class="hidden group-hover:block my-auto mx-auto">
                            <i-pepicons-pop-clapperboard class="w-24 h-24 absolute top-0 right-0 box-content bg-blue-500 p-4 text-white drop-shadow-xl" />
                        </span>
                                <!-- Imageのカバー -->
                                <span v-else-if="mediaFile.mediable_type === 'App\\Models\\Image'" class="flex h-full w-full items-center justify-center overflow-hidden">
                            <i-pepicons-pop-photo class="w-24 h-24 absolute top-0 right-0 box-content bg-green-500 p-4 text-white drop-shadow-xl" />
                        </span>
                                <!-- Mangaのカバー -->
                                <span v-else-if="mediaFile.mediable_type === 'App\\Models\\Manga'" class="flex h-full w-full items-center justify-center overflow-hidden">
                            <i-bi-book class="w-24 h-24 absolute top-0 right-0 box-content bg-violet-500 p-4 text-white drop-shadow-xl" />
                        </span>
                            </div>
                        </div>
                        <p class="text-base w-[184px] inline-block whitespace-nowrap overflow-hidden overflow-ellipsis"
                           :title="mediaFile.title">{{ mediaFile.title }}</p>
                    </div>
                </div>
                <div class="flex items-end justify-center gap-16 mt-16 pb-40">
                    <div>
                        <p v-if="form.errors.password" class="text-teto-500">{{ form.errors.password }}</p>
                        <input type="password" v-model="form.password" placeholder="パスワードを入力">
                    </div>
                    <danger-button @click="deleteMedia">
                        削除する
                    </danger-button>
                </div>
            </div>
        </dialog>
    </div>
</template>
