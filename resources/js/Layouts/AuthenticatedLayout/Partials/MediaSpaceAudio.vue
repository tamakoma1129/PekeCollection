<script setup>
import {onMounted, onBeforeUnmount} from "vue";
import {useMediaStore} from "@/stores/media.js";
import WaveSurfer from "wavesurfer.js";

const mediaStore = useMediaStore();

onMounted(() => {
    const wavesurfer = WaveSurfer.create({
        container: "#waveform",
        normalize: true,
        waveColor: '#fff',
        progressColor: '#40c1b3',
        cursorWidth: 0,
        barWidth: 3,
        barGap: 3,
        url: mediaStore.src
    })

    wavesurfer.on("click", () => {
        wavesurfer.play();
    })

    const element = wavesurfer.getMediaElement();
    mediaStore.setMediaElement(element);

    // 初期設定
    element.src = mediaStore.src;
    element.currentTime = mediaStore.currentTime || 0;
    element.volume = mediaStore.currentVolume || 1;
    element.muted = mediaStore.isMuted || false;
});

onBeforeUnmount(() => {
    mediaStore.unsetMediaElement();
})
</script>

<template>
    <div>
        <div id="waveform" class="w-[80vw]"></div>
    </div>
</template>

