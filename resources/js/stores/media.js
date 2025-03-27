import { defineStore } from "pinia";
import {ref} from "vue";
import {getPrivateStoragePath} from "@/utils.js";

export const useMediaStore = defineStore("media2", () => {
    /*
     * 操作をコンポーネント側のwatchでしないのは、無限ループにしない為。
     */
    const mediaElement = ref(null);
    const imageElement = ref(null);

    // media固有のデータ
    const type = ref(null);
    const src = ref(null);
    const duration = ref(0);
    const width = ref(null);
    const height = ref(null);
    const title = ref(null);
    const preview_image_path = ref(null);
    const raw_image_path = ref(null);   // audioがもつデータ

    // 画像・漫画固有のデータ
    const srcLite = ref(null);

    // 再生に関するデータ
    const isPlaying = ref(false);
    const isMuted = ref(false);
    const currentVolume = ref(0.5);
    const currentTime = ref(0);

    const isOpenMediaSpace = ref(false);

    const setMediaData = (media, page) => {
        type.value = media.mediable_type;
        src.value = getPrivateStoragePath(media.path);
        title.value = media.title;
        preview_image_path.value = getPrivateStoragePath(media.preview_image_path);
        currentTime.value = 0;
        duration.value = 0;

        switch (type.value) {
            case "App\\Models\\Audio":
                isPlaying.value = true;
                raw_image_path.value = getPrivateStoragePath(media.mediable.raw_image_path);
                if (mediaElement.value && mediaElement.value instanceof HTMLAudioElement) {
                    mediaElement.value.src = src.value;
                    mediaElement.value.currentTime = 0;
                }
                break;
            case "App\\Models\\Manga":
                isPlaying.value = false;
                src.value = getPrivateStoragePath(media.mediable.pages[page].path);
                srcLite.value = getPrivateStoragePath(media.mediable.pages[page].lite_path);
                break
            case "App\\Models\\Video":
                isPlaying.value = true;
                if (mediaElement.value && mediaElement.value instanceof HTMLVideoElement) {
                    mediaElement.value.src = src.value;
                    mediaElement.value.currentTime = 0;
                }
                break
            case "App\\Models\\Image":
                isPlaying.value = false;
                srcLite.value = getPrivateStoragePath(media.preview_image_path);
                break
            default:
                console.warn("対応していないメディアタイプです");
        }
    };

    const setImageElement = (element) => {
        imageElement.value = element;

        imageElement.value.addEventListener("load", () => handleLoadImage());
    }

    const unsetImageElement = () => {
        imageElement.value.removeEventListener("load", () => handleLoadImage());
    }

    const handleLoadImage = () => {
        width.value = imageElement.value.naturalWidth;
        height.value = imageElement.value.naturalHeight;
    }

    const setMediaElement = (element) => {
        console.log("setMediaElement")
        mediaElement.value = element;

        mediaElement.value.addEventListener("loadedmetadata", () => handleLoadedMetadata());
        mediaElement.value.addEventListener("timeupdate", () => handleTimeupdate());
        mediaElement.value.addEventListener("play", () => handleOnplay());
        mediaElement.value.addEventListener("pause", () => handlePause());
        mediaElement.value.addEventListener("volumechange", () => handleOnvolumechange());
    }

    const unsetMediaElement = () => {
        console.log("unsetMediaElement")
        pause();

        mediaElement.value.removeEventListener("loadedmetadata", () => handleLoadedMetadata());
        mediaElement.value.removeEventListener("timeupdate", () => handleTimeupdate());
        mediaElement.value.removeEventListener("play", () => handleOnplay());
        mediaElement.value.removeEventListener("pause", () => handlePause());
        mediaElement.value.removeEventListener("volumechange", () => handleOnvolumechange());

        mediaElement.value = null;
    }

    const handleLoadedMetadata = () => {
        console.log("handleLoadedMetadata")
        if (mediaElement.value) {
            if (mediaElement.value instanceof HTMLVideoElement) {
                width.value = mediaElement.value.videoWidth;
                height.value = mediaElement.value.videoHeight;
                console.log(width.value + "*" + height.value)
            }
            duration.value = Math.round(mediaElement.value.duration);
            mediaElement.value.currentTime = 0;
            if (isPlaying.value) {
                play();
            } else {
                pause();
            }
        }
    };
    const handleTimeupdate = () => {
        console.log("handleTimeupdate")
        if (mediaElement.value) {
            currentTime.value = Math.round(mediaElement.value.currentTime);
        }
    };
    const handleOnplay = () => {
        console.log("handleOnplay")
        if (mediaElement.value) {
            isPlaying.value = true;
        }
    }
    const handlePause = () => {
        console.log("handlePause")
        if (mediaElement.value) {
            isPlaying.value = false;
        }
    }
    const handleOnvolumechange = () => {
        console.log("handleOnvolumechange")
        if (mediaElement.value) {
            currentVolume.value = mediaElement.value.volume;
            isMuted.value = mediaElement.value.muted;
        }
    }
    const changeVolume = (newVolume) => {
        if (mediaElement.value) {
            mediaElement.value.volume = newVolume;
        }
    };

    const changeCurrentTime = (newTime) => {
        if (mediaElement.value) {
            mediaElement.value.currentTime = newTime;
        }
    };

    const play = () => {
        if (mediaElement.value) {
            mediaElement.value.play();
        }
    };

    const pause = () => {
        if (mediaElement.value) {
            mediaElement.value.pause();
        }
    };

    const mute = () => {
        isMuted.value = true;
        if (mediaElement.value) {
            mediaElement.value.muted = true;
        }
    };

    const unmute = () => {
        isMuted.value = false;
        if (mediaElement.value) {
            mediaElement.value.muted = false;
        }
    };

    const openMediaSpace = () => {
        isOpenMediaSpace.value = true;
    };

    const closeMediaSpace = () => {
        isOpenMediaSpace.value = false;
    };

    return {
        type,
        src,
        srcLite,
        isPlaying,
        isMuted,
        currentVolume,
        currentTime,
        duration,
        width,
        height,
        title,
        preview_image_path,
        raw_image_path,

        setImageElement,
        unsetImageElement,
        setMediaElement,
        unsetMediaElement,
        setMediaData,
        changeVolume,
        changeCurrentTime,
        play,
        pause,
        mute,
        unmute,

        isOpenMediaSpace,
        openMediaSpace,
        closeMediaSpace,
    };
});
