<script setup>
import { ref } from "vue";
import { useTagsStore } from "@/stores/tags";

const tagsStore = useTagsStore();

const inputTag = ref("");
const filteredTags = ref([]);
const showSelector = ref(false);

const filterTags = () => {
    if (!inputTag.value.trim()) {
        filteredTags.value = [];
        showSelector.value = false;
        return;
    }
    filteredTags.value = tagsStore.allTags.filter((tag) =>
        normalizeText(tag.name).includes(normalizeText(inputTag.value)),
    );
    showSelector.value = true;
};

const normalizeText = (text) => {
    return text
        .toLowerCase() // 小文字に変換
        .normalize("NFKC") // 半角を全角に
        .replace(/[\u3041-\u3096]/g, (char) => {
            // ひらがなをカタカナに変換
            return String.fromCharCode(char.charCodeAt(0) + 0x60);
        });
};

const selectTag = (tagName) => {
    if (tagName.trim()) {
        tagsStore.selectTag(tagName.trim());
    }

    inputTag.value = "";
    filteredTags.value = [];
};

const hideSelector = () => {
    showSelector.value = false;
};
</script>

<template>
    <div class="relative flex items-center" v-click-outside="hideSelector">
        <input
            type="text"
            autocomplete="off"
            v-model="inputTag"
            @input="filterTags"
            @keydown.enter.prevent="selectTag(inputTag)"
            placeholder="タグを入力"
            class="w-272"
        />
        <!-- 24*7 = 168 で固定 -->
        <ul
            v-if="filteredTags.length && inputTag && showSelector"
            class="flex flex-col-reverse absolute h-168 overflow-y-auto bg-transparent z-1 w-272 -top-[calc(168px)]"
        >
            <li
                v-for="tag in filteredTags"
                :key="tag.name"
                class="flex justify-between items-center px-8 bg-white hover:bg-sumi-200 cursor-pointer h-24 border-x border-x-sumi-400 border-t border-t-sumi-400"
                @click="selectTag(tag.name)"
            >
                <span>{{ tag.name }}</span>
                <span>{{ tag.count }}</span>
            </li>
        </ul>
        <button
            class="mx-8 rounded px-24 py-8 bg-sumi-200 border border-sumi-400 hover:bg-sumi-300"
            @click="selectTag(inputTag)"
        >
            追加
        </button>
    </div>
</template>
