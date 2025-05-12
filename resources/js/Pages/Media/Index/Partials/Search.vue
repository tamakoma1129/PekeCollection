<script setup>
import { useTagsStore } from "@/stores/tags.js";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "vue-toast-notification";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const $toast = useToast();
const tagsStore = useTagsStore();

const allTags = computed(() => tagsStore.allTags);

const word = ref(null);
const selectedTags = ref([]);
const orientation = ref(null);

const search = () => {
    const mediaType = window.location.pathname.split("/")[1];

    router.get(
        route("media.index", { mediaType: mediaType }),
        {
            word: word.value,
            tags: selectedTags.value,
            orientation: orientation.value,
        },
        {
            preserveState: true,
            onSuccess: () => {
                $toast.success("検索しました", {
                    position: "top-right",
                    duration: 5000,
                });
            },
            onError: () => {
                $toast.error("検索に失敗しました", {
                    position: "top-right",
                    duration: 5000,
                });
            },
        },
    );
};
</script>

<template>
    <form
        @submit.prevent="search"
        class="flex items-center justify-center w-full gap-x-16"
    >
        <div class="relative flex items-center">
            <span
                v-if="$page.props.errors.word"
                class="text-teto-500 text-xs"
                >{{ $page.props.errors.word }}</span
            >
            <input
                v-model="word"
                type="text"
                placeholder="検索ワードを入力"
                autocomplete="off"
                class="pr-24"
            />
            <i-icon-park-outline-close-small
                class="h-24 w-24 pl-4 absolute right-4 cursor-pointer"
                @click="() => (word = '')"
            />
        </div>
        <div>
            <span
                v-if="$page.props.errors.tags"
                class="text-teto-500 text-xs"
                >{{ $page.props.errors.tags }}</span
            >
            <select v-model="selectedTags" multiple class="px-0">
                <option
                    v-for="tag in allTags"
                    :key="tag.name"
                    :value="tag.name"
                >
                    {{ tag.name }}
                </option>
            </select>
        </div>
        <div>
            <span
                v-if="$page.props.errors.orientation"
                class="text-teto-500 text-xs"
                >{{ $page.props.errors.orientation }}</span
            >
            <select v-model="orientation">
                <option :value="null">無指定</option>
                <option value="vertical">縦画面</option>
                <option value="horizon">横画面</option>
            </select>
        </div>
        <PrimaryButton type="submit">検索</PrimaryButton>
    </form>
</template>
