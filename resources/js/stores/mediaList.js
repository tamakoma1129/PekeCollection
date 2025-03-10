import {defineStore} from "pinia";
import {ref, watch} from "vue";
import {useMediaStore} from "@/stores/media.js";
import {getPrivateStoragePath} from "@/utils.js";

export const useMediaList = defineStore("mediaList", () => {
    /**
     * @typedef {[Object]} MediaEntry
     * @type {Array<MediaEntry>}
     */
    const mediaList = ref([]);
    const currentMediaIndex = ref(null);
    const currentMangaPageIndex = ref(null);

    const mediaStore = useMediaStore();


    const setMediaList = (list) => {
        mediaList.value = [];
        list.forEach((media) => {
            mediaList.value.push(media);
        });
    };

    const appendMediaList = (list) => {
        const existingIds = new Set(mediaList.value.map(item => item.id));
        list.forEach((media) => {
            if (!existingIds.has(media.id)) {
                mediaList.value.push(media);
            }
        });
    };

    const setMedia = (media) => {
        const index = mediaList.value.findIndex(item => item.id === media.id);
        if (index >= 0) {
            currentMediaIndex.value = index;
            if (media.mediable_type==="App\\Models\\Manga") {
                currentMangaPageIndex.value = 0;
                mediaStore.setMediaData(media, currentMangaPageIndex.value);
                return;
            }
            mediaStore.setMediaData(getCurrentMedia())
        } else {
            console.warn("メディアがリストに存在しません");
        }
    }

    const setNextMedia = () => {
        const currentMedia = getCurrentMedia();
        //　現在の漫画ページが最後じゃないなら次のページへ
        if (currentMedia.mediable_type==="App\\Models\\Manga" &&
            currentMangaPageIndex.value < currentMedia.mediable.pages.length-1
        ) {
            currentMangaPageIndex.value++;
            mediaStore.setMediaData(currentMedia, currentMangaPageIndex.value);
            return;
        }

        if (currentMediaIndex.value < mediaList.value.length - 1) {
            currentMediaIndex.value++;
            setMedia(getCurrentMedia())
        } else {
            console.warn("次のメディアがありません");
        }
    }

    const setPreviousMedia = () => {
        const currentMedia = getCurrentMedia();
        //　現在の漫画ページが最初じゃないなら前のページへ
        if (currentMedia.mediable_type==="App\\Models\\Manga" &&
            currentMangaPageIndex.value !== 0
        ) {
            currentMangaPageIndex.value--;
            mediaStore.setMediaData(currentMedia, currentMangaPageIndex.value);
            return;
        }

        if (currentMediaIndex.value > 0) {
            currentMediaIndex.value--;
            setMedia(getCurrentMedia())
        } else {
            console.warn("後ろのメディアがありません");
        }
    }

    const getMedia = (mediaIndex) => {
        return mediaList.value[mediaIndex];
    }

    const getCurrentMedia = () => {
        return getMedia(currentMediaIndex.value);
    }

    const getMediaData = (id) => {
        return mediaList.value.find((item) => item.id === id);
    };

    const getPage = (mediaIndex, pageIndex) => {
        return mediaList.value[mediaIndex].mediable.pages[pageIndex];
    }

    const preloadAroundImage = () => {
        const preloadRange = 3;
        let targetMediaIndex = currentMediaIndex.value;
        let targetMedia = getMedia(targetMediaIndex);
        let targetMangaPageIndex = currentMangaPageIndex.value;
        for (let i = 0; i <= preloadRange-1; i++) {
            // 現在が漫画で、次のページがあるならそれを読み込む
            if (
                targetMedia.mediable_type === "App\\Models\\Manga" &&
                targetMangaPageIndex < targetMedia.mediable.pages.length-1
            ) {
                targetMangaPageIndex++;
                preloadImage(getPrivateStoragePath(getPage(targetMediaIndex, targetMangaPageIndex).path));
            }
            // 次のメディアが存在するなら
            else if (targetMediaIndex < mediaList.value.length-1) {
                const nextMedia = getMedia(targetMediaIndex+1);
                const nextMediaIndex = targetMediaIndex+1;
                // 漫画だったらページの1ページ目を。画像ならそれを読み込む。
                if (nextMedia.mediable_type === "App\\Models\\Manga") {
                    targetMangaPageIndex = 0;
                    preloadImage(getPrivateStoragePath(getPage(nextMediaIndex, targetMangaPageIndex).path))

                } else if (nextMedia.mediable_type === "App\\Models\\Image") {
                    preloadImage(getPrivateStoragePath(getMedia(nextMediaIndex).path))
                }

                targetMedia = nextMedia;
                targetMediaIndex = nextMediaIndex;
            }
        }
    }

    const preloadImage = (src) => {
        const img = new Image();
        img.src = src;
        console.log(src+"をプレリロード")
    };

    watch(() => mediaStore.src,
        () => {
        preloadAroundImage();
    })

    return {
        mediaList,

        setMediaList,
        appendMediaList,
        setMedia,
        setNextMedia,
        setPreviousMedia,

        getMediaData
    }
})
