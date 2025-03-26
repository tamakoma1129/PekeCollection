<script setup>
import ToolTip from "@/Components/ToolTip.vue";
import {useMediaEditStore} from "@/stores/mediaEdit.js";
import {useMediaList} from "@/stores/mediaList.js";
import {ref} from "vue";
import DeleteEdit from "@/Pages/Media/Index/Partials/DeleteEdit.vue";

const mediaList = useMediaList();
const mediaEditStore = useMediaEditStore();
const isOpenActionMenu = ref(false);
const deleteEditRef = ref(null);
</script>

<template>
    <div
        class="fixed bottom-104 inset-x-0 max-w-max mx-auto bg-white/65 rounded-xl shadow-black shadow-[0px_0px_12px] py-8 px-24 flex items-center justify-center">
        <p class="select-none">{{ mediaEditStore.selectedMediaIds.length }} / {{ mediaList.mediaList.length }} 選択中</p>
        <ToolTip message="全ての選択を解除" class="ml-16">
            <button class="flex items-center  rounded-full p-4"
                    :class="mediaEditStore.selectedMediaIds.length === 0 ? 'text-sumi-400' : 'hover:bg-sumi-200'"
                    :disabled="mediaEditStore.selectedMediaIds.length === 0"
                    @click="mediaEditStore.clearSelection">
                <i-hugeicons-check-unread-04 class="w-24 h-24"/>
            </button>
        </ToolTip>
        <div class="flex items-center ml-8 relative">
            <button @click="isOpenActionMenu = !isOpenActionMenu"
                    v-click-outside="() => isOpenActionMenu = false"
                    class="hover:bg-sumi-200 rounded-full p-4">
                <i-iconamoon-menu-kebab-vertical class="w-24 h-24"/>
            </button>
            <div class="absolute bottom-40 bg-white/75 flex flex-col items-center rounded-xl shadow-black shadow-[0px_0px_12px] py-16"
                 v-if="isOpenActionMenu">
                <button
                    class="w-full py-4 pl-8 pr-16 whitespace-nowrap hover:bg-teto-200 hover:text-teto-800 flex items-center justify-center gap-x-16 select-none"
                    @click.stop="deleteEditRef.openDeleteDialog">
                    <i-bytesize-trash class="h-full w-auto" />
                    メディアを削除
                </button>
            </div>
        </div>
    </div>

    <DeleteEdit ref="deleteEditRef" />
</template>
