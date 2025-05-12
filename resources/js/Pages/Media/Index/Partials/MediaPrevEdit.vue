<script setup>
import { useMediaEditStore } from "@/stores/mediaEdit.js";
import { useTagsStore } from "@/stores/tags.js";
import { computed, ref } from "vue";
import { useToast } from "vue-toast-notification";
import { useForm } from "@inertiajs/vue3";
import { getPrivateStoragePath } from "@/utils.js";

const $toast = useToast();
const props = defineProps({
    mediaFile: Object,
});

const mediaEditStore = useMediaEditStore();
const tagsStore = useTagsStore();

const isSelected = computed(() =>
    mediaEditStore.selectedMediaIds.includes(props.mediaFile.id),
);

const title = ref(props.mediaFile.title);
const originalTitle = ref(props.mediaFile.title);

const updateFileName = () => {
    if (title.value === originalTitle.value) {
        return;
    }
    useForm({
        title: title.value,
    }).patch(route("media_file.update", props.mediaFile.id), {
        preserveScroll: true,
        onSuccess: () => {
            originalTitle.value = title.value;
            $toast.success("タイトルを変更しました", {
                position: "top-right",
                duration: 5000,
            });
        },
        onError: () => {
            title.value = originalTitle.value;
            $toast.error("タイトルの変更でエラーが発生しました", {
                position: "top-right",
                duration: 5000,
            });
        },
    });
};

const toggleSelection = (event) => {
    if (event.shiftKey) {
        mediaEditStore.shiftSelection(props.mediaFile.id);
    } else {
        if (isSelected.value) {
            mediaEditStore.removeSelection(props.mediaFile.id);
        } else {
            mediaEditStore.addSelection(props.mediaFile.id);
        }
    }
};

const startDrag = (tagName) => {
    tagsStore.startDrag(tagName);
};

const addTag = (tagName) => {
    if (tagName.trim()) {
        tagsStore.selectTag(tagName.trim());
    }
};
</script>

<template>
    <div
        class="flex items-center w-full h-40 gap-x-16 relative"
        :class="{ 'bg-blue-200': isSelected }"
        @click="toggleSelection"
    >
        <div>
            <!-- Audioのアイコン -->
            <span v-if="mediaFile.mediable_type === 'App\\Models\\Audio'">
                <i-fa6-solid-headphones
                    class="w-16 h-16 box-content bg-teto-500 p-4 text-white"
                />
            </span>
            <!-- Videoのアイコン -->
            <span
                v-else-if="mediaFile.mediable_type === 'App\\Models\\Video'"
                class="flex group-hover:bg-black h-full w-full items-center justify-center overflow-hidden"
            >
                <img
                    :src="
                        getPrivateStoragePath(
                            mediaFile.mediable.preview_video_path,
                        )
                    "
                    alt="Videoのホバー時カバー"
                    class="hidden group-hover:block my-auto mx-auto"
                />
                <i-pepicons-pop-clapperboard
                    class="w-16 h-16 box-content bg-blue-500 p-4 text-white"
                />
            </span>
            <!-- Imageのアイコン -->
            <span
                v-else-if="mediaFile.mediable_type === 'App\\Models\\Image'"
                class="flex h-full w-full items-center justify-center overflow-hidden"
            >
                <i-pepicons-pop-photo
                    class="w-16 h-16 box-content bg-green-500 p-4 text-white"
                />
            </span>
            <!-- Mangaのアイコン -->
            <span
                v-else-if="mediaFile.mediable_type === 'App\\Models\\Manga'"
                class="flex h-full w-full items-center justify-center overflow-hidden"
            >
                <i-bi-book
                    class="w-16 h-16 box-content bg-violet-500 p-4 text-white"
                />
            </span>
        </div>
        <!-- ホバー時 -->
        <div class="select-none w-40 h-40 p-4">
            <div v-if="mediaFile.preview_image_path" class="h-full w-full">
                <img
                    :src="getPrivateStoragePath(mediaFile.preview_image_path)"
                    :alt="mediaFile.title"
                    draggable="false"
                    class="peer h-full w-full object-contain"
                />
                <img
                    :src="getPrivateStoragePath(mediaFile.preview_image_path)"
                    :alt="mediaFile.title"
                    class="hidden absolute peer-hover:block z-1 max-w-[50vw] max-h-[50vh] object-contain bottom-40 ml-40"
                />
            </div>
            <div
                v-else
                class="h-full w-full bg-gradient-to-r from-slate-900 to-slate-700"
            />
        </div>
        <div class="w-1/4 select-none">
            <input
                type="text"
                v-model="title"
                style="field-sizing: content"
                class="w-auto min-w-104 max-w-full bg-transparent border-transparent py-4 overflow-x-auto select-none border-b border-b-sumi-300 focus:outline-none focus:ring-0 focus:border-x-0 focus:border-t-0 focus:border-b focus:border-b-sumi-500"
                :title="mediaFile.title"
                @click.stop
                @keydown.enter="(event) => event.target.blur()"
                @blur="updateFileName"
            />
        </div>
        <div class="min-w-104 overflow-x-auto">
            <ul class="flex gap-8">
                <li
                    v-for="tag in mediaFile.tags"
                    :key="tag.name"
                    class="flex items-center w-fit bg-sumi-200 px-8 rounded-xl cursor-pointer hover:bg-sumi-300"
                    draggable="true"
                    @dragstart="startDrag(tag.name)"
                    @click.stop="addTag(tag.name)"
                >
                    <span class="h-24 whitespace-nowrap">{{ tag.name }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>
