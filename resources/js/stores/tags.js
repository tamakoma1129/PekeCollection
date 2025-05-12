import { defineStore } from "pinia";
import { ref, onMounted } from "vue";
import { useToast } from "vue-toast-notification";

const $toast = useToast();

export const useTagsStore = defineStore("tags", () => {
    const selectedTags = ref([]);
    const top30Tags = ref([]);
    const allTags = ref([]);
    const tempTags = ref([]);
    const isViewTempTags = ref(false);

    const draggedTag = ref("");

    const selectTag = (tagName) => {
        if (!selectedTags.value.includes(tagName)) {
            selectedTags.value.push(tagName);
        }
    };

    const unselectTag = (tagName) => {
        if (selectedTags.value.includes(tagName)) {
            selectedTags.value = selectedTags.value.filter(
                (name) => name !== tagName,
            );
        }
    };

    const cleanSelectTag = () => {
        selectedTags.value = [];
    };

    const addTempTag = (tagName) => {
        if (!tempTags.value.includes(tagName)) {
            tempTags.value.push(tagName);
        }
    };

    const removeTempTag = (tagName) => {
        if (tempTags.value.includes(tagName)) {
            tempTags.value = tempTags.value.filter((name) => name !== tagName);
        }
    };

    const updateTags = () => {
        fetch("/tag")
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((tags) => {
                allTags.value = tags;
                top30Tags.value = tags.slice(0, 30);
            })
            .catch((error) => {
                $toast.error("タグ一覧の取得でエラーが発生しました", {
                    position: "top-right",
                    duration: 5000,
                });
            });
    };

    const toggleViewTempTags = () => {
        isViewTempTags.value = !isViewTempTags.value;
    };

    const startDrag = (tagName) => {
        draggedTag.value = tagName;
    };

    const onDropInSelect = () => {
        if (draggedTag.value) {
            selectTag(draggedTag.value);
            draggedTag.value = "";
        }
    };

    const onDropInTemp = () => {
        if (draggedTag.value) {
            addTempTag(draggedTag.value);
            draggedTag.value = "";
        }
    };

    onMounted(() => {
        updateTags();
    });

    return {
        selectedTags,
        top30Tags,
        allTags,
        tempTags,
        isViewTempTags,
        draggedTag,
        selectTag,
        unselectTag,
        cleanSelectTag,
        updateTags,
        addTempTag,
        removeTempTag,
        toggleViewTempTags,
        startDrag,
        onDropInSelect,
        onDropInTemp,
    };
});
