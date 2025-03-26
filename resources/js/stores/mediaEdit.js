import { defineStore } from "pinia";
import {computed, ref} from "vue";
import {useMediaList} from "@/stores/mediaList.js";

export const useMediaEditStore = defineStore("mediaEdit", () => {
    const mediaList = useMediaList();

    const selectedMediaIds = ref([]);
    const mode = ref("view");
    const mediaIds = computed(() => mediaList.mediaList.map((i) => i.id));

    const addSelection = (id) => {
        if (!selectedMediaIds.value.includes(id)) {
            selectedMediaIds.value.push(id);
        }
    };

    const removeSelection = (id) => {
        selectedMediaIds.value = selectedMediaIds.value.filter(
            (selectedId) => selectedId !== id
        );
    };

    const shiftSelection = (id) => {
        const isSelected = selectedMediaIds.value.includes(id);

        const clickedIndex = mediaIds.value.indexOf(id);

        if (clickedIndex === -1) {
            console.warn("指定されたIDが存在しません");
            return;
        }

        if (isSelected) {
            // 解除モード
            // クリックされたIDから奥方向に連続して選択されたIDをすべて解除
            let index = clickedIndex + 1;   // +1する理由は、クリックされたメディアは選択解除しないようにするため
            while (index >= 0 && selectedMediaIds.value.includes(mediaIds.value[index])) {
                removeSelection(mediaIds.value[index]);
                index++;
            }
        } else {
            // 選択モード
            // クリックされたIDから手前方向に最も近い選択済みのIDまでを選択
            let index = clickedIndex;
            while (
                index >= 0 &&
                !selectedMediaIds.value.includes(mediaIds.value[index])
                ) {
                addSelection(mediaIds.value[index]);
                index--;
            }
        }
    };

    const clearSelection = () => {
        selectedMediaIds.value = [];
    };

    const modeToView = () => {
        mode.value = "view";
    };

    const modeToEdit = () => {
        mode.value = "edit";
    };

    return {
        selectedMediaIds,
        mode,
        addSelection,
        removeSelection,
        shiftSelection,
        clearSelection,
        modeToView,
        modeToEdit,
    };
});
