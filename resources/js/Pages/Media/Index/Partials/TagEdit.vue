<script setup>
import { computed, ref } from "vue";
import { useToast } from "vue-toast-notification";
import { useTagsStore } from "@/stores/tags.js";
import { useMediaEditStore } from "@/stores/mediaEdit.js";
import TagSelect from "@/Pages/Media/Index/Partials/TagSelect.vue";
import ToolTip from "@/Components/ToolTip.vue";
import { useForm } from "@inertiajs/vue3";

const $toast = useToast();
const tagsStore = useTagsStore();
const mediaEditStore = useMediaEditStore();

const selectedTags = computed(() => tagsStore.selectedTags);
const top30Tags = computed(() => tagsStore.top30Tags);
const tempTags = computed(() => tagsStore.tempTags);
const isViewTempTags = computed(() => tagsStore.isViewTempTags);
const isOpen = ref(true);

const updateTags = () => {
    tagsStore.updateTags();
    $toast.success("タグ情報を更新しました", {
        position: "top-right",
        duration: 5000,
    });
};

const addTag = (tagName) => {
    if (tagName.trim()) {
        tagsStore.selectTag(tagName.trim());
    }
};

const unselectTag = (tagName) => {
    tagsStore.unselectTag(tagName);
};

const cleanSelectTag = () => {
    tagsStore.cleanSelectTag();
};

const toggleViewTempTags = () => {
    tagsStore.toggleViewTempTags();
};

const toggleOpen = () => {
    isOpen.value = !isOpen.value;
};

const removeTempTag = (tagName) => {
    tagsStore.removeTempTag(tagName);
};

const attachTags = () => {
    if (tagsStore.selectedTags.length === 0) {
        $toast.error("タグが選択されていません", {
            position: "top-right",
            duration: 5000,
        });
        return;
    }
    if (mediaEditStore.selectedMediaIds.length === 0) {
        $toast.error("メディアが選択されていません", {
            position: "top-right",
            duration: 5000,
        });
        return;
    }

    const form = useForm({
        media_ids: mediaEditStore.selectedMediaIds,
        tags: tagsStore.selectedTags,
    });

    form.post(route("tag.attach"), {
        preserveScroll: true,
        onSuccess: () => {
            $toast.success("タグを付与しました", {
                position: "top-right",
                duration: 5000,
            });
            tagsStore.updateTags();
        },
        onError: () => {
            $toast.error("タグの付与に失敗しました", {
                position: "top-right",
                duration: 5000,
            });
        },
    });
};

const detachTags = () => {
    if (tagsStore.selectedTags.length === 0) {
        $toast.error("タグが選択されていません", {
            position: "top-right",
            duration: 5000,
        });
        return;
    }
    if (mediaEditStore.selectedMediaIds.length === 0) {
        $toast.error("メディアが選択されていません", {
            position: "top-right",
            duration: 5000,
        });
        return;
    }

    const form = useForm({
        media_ids: mediaEditStore.selectedMediaIds,
        tags: tagsStore.selectedTags,
    });

    form.delete(route("tag.detach"), {
        preserveScroll: true,
        onSuccess: () => {
            $toast.success("タグをメディアから削除しました", {
                position: "top-right",
                duration: 5000,
            });
            tagsStore.updateTags();
        },
        onError: () => {
            $toast.error("タグをメディアから削除するのに失敗しました", {
                position: "top-right",
                duration: 5000,
            });
        },
    });
};

const startDrag = (tagName) => {
    tagsStore.startDrag(tagName);
};

const onDropInSelect = () => {
    tagsStore.onDropInSelect();
};

const onDropInTemp = () => {
    tagsStore.onDropInTemp();
};
</script>

<template>
    <div
        class="my-16 bg-white rounded-xl cursor-pointer"
        @click.self="toggleOpen"
    >
        <button
            @click="toggleOpen"
            class="box-content flex items-center pl-4 pr-16"
        >
            <i-icon-park-outline-down v-if="isOpen" class="h-24 w-24" />
            <i-icon-park-outline-up v-else class="h-24 w-24" />
            <span
                class="font-semibold"
                :class="{ 'text-base': !isOpen, 'text-lg': isOpen }"
                >タグの編集</span
            >
        </button>
        <div v-if="isOpen" class="px-16 pb-8 cursor-default">
            <!-- よく使われるタグ -->
            <div class="mt-8 px-16 border border-sumi-400 rounded-xl">
                <div class="flex justify-between items-center pb-8">
                    <p class="text-xl">よく使われるタグ</p>
                    <tool-tip message="タグ情報を更新する">
                        <button @click="updateTags">
                            <i-tabler-reload class="w-24 h-24" />
                        </button>
                    </tool-tip>
                </div>
                <ul class="flex flex-wrap gap-4 pb-16">
                    <li
                        v-for="tag in top30Tags"
                        :key="tag.name"
                        class="flex bg-sumi-200 rounded-lg px-8 divide-sumi-400 divide-x cursor-pointer"
                        draggable="true"
                        @dragstart="startDrag(tag.name)"
                        @click="addTag(tag.name)"
                    >
                        <span class="pr-4">{{ tag.name }}</span>
                        <span class="pl-4">{{ tag.count }}</span>
                    </li>
                </ul>
            </div>
            <!-- 一時タグ置き場 -->
            <div class="mt-8">
                <button
                    v-if="!isViewTempTags"
                    @click="toggleViewTempTags"
                    class="box-content border border-sumi-400 rounded-full flex pl-8 pr-16 gap-8"
                >
                    <i-icon-park-outline-up class="h-24 w-24" />
                    <span>一時タグ置き場</span>
                </button>
                <div
                    v-if="isViewTempTags"
                    class="border border-sumi-400 rounded-xl pl-8 pr-16"
                    @dragover.prevent
                    @drop="onDropInTemp"
                >
                    <button @click="toggleViewTempTags" class="flex gap-8 pb-4">
                        <i-icon-park-outline-down class="h-24 w-24" />
                        <span class="text-xl">一時タグ置き場</span>
                    </button>
                    <ul class="flex flex-wrap gap-4 pb-16 pl-8">
                        <li
                            v-for="tag in tempTags"
                            :key="tag"
                            class="flex items-center divide-sumi-400 divide-x w-fit"
                            draggable="true"
                            @dragstart="startDrag(tag)"
                        >
                            <span class="px-8 bg-sumi-200 rounded-l-xl h-24">{{
                                tag
                            }}</span>
                            <button
                                class="px-4 bg-sumi-200 hover:bg-teto-400 rounded-r-xl h-24"
                                @click="removeTempTag(tag)"
                            >
                                <i-mdi-close class="w-full h-auto" />
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- 選択されているタグ -->
            <div
                class="px-16 border border-sumi-400 rounded-xl mt-8"
                @dragover.prevent
                @drop="onDropInSelect"
            >
                <div class="pb-8 flex justify-between pt-4">
                    <p class="text-xl">選択されているタグ</p>
                    <ToolTip message="選択されているタグを全て削除">
                        <button @click="cleanSelectTag">
                            <i-mingcute-fire-fill class="w-24 h-24" />
                        </button>
                    </ToolTip>
                </div>
                <ul class="flex gap-4">
                    <li
                        v-for="selectedTag in selectedTags"
                        :key="selectedTag"
                        class="flex items-center divide-sumi-400 divide-x w-fit"
                        draggable="true"
                        @dragstart="startDrag(selectedTag)"
                    >
                        <span class="px-8 bg-sumi-200 rounded-l-xl h-24">{{
                            selectedTag
                        }}</span>
                        <button
                            class="px-4 bg-sumi-200 hover:bg-teto-400 rounded-r-xl h-24"
                            @click="unselectTag(selectedTag)"
                        >
                            <i-mdi-close class="w-full h-auto" />
                        </button>
                    </li>
                </ul>
                <div class="flex items-center mt-16 pb-16">
                    <TagSelect />
                </div>
            </div>
            <div class="flex items-center justify-center mt-8">
                <p>
                    選択されているタグ を 選択中の
                    {{ mediaEditStore.selectedMediaIds.length }} 個のメディア
                </p>
                <div class="flex flex-col gap-8">
                    <div class="flex items-center">
                        <button
                            @click="attachTags"
                            class="w-fit py-4 px-8 bg-miku-500 hover:bg-miku-600 text-white rounded-xl"
                        >
                            に適用
                        </button>
                    </div>
                    <div class="flex items-center">
                        <button
                            @click="detachTags"
                            class="w-fit py-4 px-8 bg-teto-500 hover:bg-teto-600 text-white rounded-xl"
                        >
                            から削除
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
