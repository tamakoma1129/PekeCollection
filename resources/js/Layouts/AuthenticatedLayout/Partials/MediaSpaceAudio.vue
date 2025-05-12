<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from "vue";
import { useMediaStore } from "@/stores/media.js";
import WaveSurfer from "wavesurfer.js";
import Hover from "wavesurfer.js/dist/plugins/hover.esm.js";
import { Vibrant } from "node-vibrant/browser";
import { formatSecondsToTime } from "../../../utils.js";

const mediaStore = useMediaStore();
const imageElement = ref();
const primaryColor = ref("#262626"); // sumi-800
const subColor = ref("#f5f5f5"); // sumi-100
const wavesurfer = ref(null);

const togglePlay = () => {
    if (mediaStore.isPlaying) {
        mediaStore.pause();
    } else {
        mediaStore.play();
    }
};

const createWaveSurfer = async () => {
    let peaks = [0];
    if (mediaStore.waveform_path) {
        try {
            const response = await fetch(mediaStore.waveform_path);

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const json = await response.json();

            peaks = json.data ?? [0];
        } catch (err) {
            console.warn("waveform fetch error:", err);
        }
    } else if (mediaStore.duration && mediaStore.duration <= 60 * 10) {
        // 10分以内なら動的生成
        peaks = null;
    }

    wavesurfer.value = WaveSurfer.create({
        container: "#waveform",
        normalize: true,
        waveColor: "#e5e5e5", // sumi-200
        progressColor: primaryColor.value, // sumi-50
        cursorWidth: 0,
        url: mediaStore.src,
        peaks: peaks,
        hideScrollbar: true,
        dragToSeek: true,
        plugins: [
            Hover.create({
                lineColor: primaryColor.value, // teto-400
                lineWidth: 3,
                labelBackground: "#0a0a0a", // sumi-950
                labelColor: "#e5e5e5", // sumi-200
                labelSize: "14px",
            }),
        ],

        renderFunction: (channels, ctx) => {
            const { width, height } = ctx.canvas;
            const scale = channels[0].length / width;
            const step = 8;

            ctx.translate(0, height / 2);
            ctx.strokeStyle = ctx.fillStyle;
            ctx.beginPath();
            ctx.lineWidth = 5;

            for (let i = 0; i < width; i += step * 2) {
                const index = Math.floor(i * scale);
                const value = Math.abs(channels[0][index]);
                let x = i;
                let amplitude = height / 2.5;
                let y = value * amplitude;

                ctx.moveTo(x, 0);
                ctx.lineTo(x, y);
                ctx.arc(x + step / 2, y, step / 2, Math.PI, 0, true);
                ctx.lineTo(x + step, 0);

                x = x + step;
                y = -y;
                ctx.moveTo(x, 0);
                ctx.lineTo(x, y);
                ctx.arc(x + step / 2, y, step / 2, Math.PI, 0, false);
                ctx.lineTo(x + step, 0);
            }

            ctx.stroke();
            ctx.closePath();
        },
    });

    wavesurfer.value.on("click", () => {
        wavesurfer.value.play();
    });

    wavesurfer.value.on("ready", () => {
        const peaks = wavesurfer.value.exportPeaks();
        localStorage.setItem("cache" + mediaStore.src, JSON.stringify(peaks));
    });

    wavesurfer.value.on("timeupdate", (time) => {
        const container = document.querySelector("#waveform");
        const label = document.querySelector("#cursor-label");

        const duration = wavesurfer.value.getDuration();
        const containerWidth = container.clientWidth;
        const x = containerWidth * (time / duration);

        label.style.left = `${x}px`;
    });

    const element = wavesurfer.value.getMediaElement();
    mediaStore.setMediaElement(element);

    // 初期設定
    element.currentTime = mediaStore.currentTime || 0;
    element.volume = mediaStore.currentVolume || 1;
    element.muted = mediaStore.isMuted || false;
};

const updateAudio = () => {
    if (wavesurfer.value) {
        wavesurfer.value.destroy();
        wavesurfer.value = null;
        primaryColor.value = "#262626";
        subColor.value = "#f5f5f5";
    }

    if (mediaStore.preview_image_path) {
        Vibrant.from(mediaStore.preview_image_path)
            .getPalette()
            .then((palette) => {
                primaryColor.value = palette.Vibrant.hex;
                subColor.value = palette.LightMuted.hex;
                createWaveSurfer();
            })
            .catch((err) => {
                console.warn("色の取得に失敗しました: ", err);
                createWaveSurfer();
            });
        return;
    }

    createWaveSurfer();
};

onMounted(() => {
    watch(
        () => mediaStore.src,
        () => {
            updateAudio();
        },
        { immediate: true },
    );
});

onBeforeUnmount(() => {
    mediaStore.unsetMediaElement();
    if (wavesurfer.value) {
        wavesurfer.value.destroy();
    }
});
</script>

<template>
    <div
        class="w-[80vw] h-[90vh] bottom-[5vh] rounded-xl fixed -translate-x-1/2 flex flex-col justify-between py-40"
        :style="`background: ${subColor}`"
    >
        <img
            v-if="mediaStore.raw_image_path"
            :src="mediaStore.raw_image_path"
            :alt="mediaStore.title"
            draggable="false"
            class="mx-auto my-16 rounded-lg overflow-hidden shadow-2xl select-none"
            ref="imageElement"
        />
        <div
            v-else
            class="w-full h-full bg-gradient-to-r from-slate-900 to-slate-700"
        />
        <h2 class="text-center text-2xl mb-16">{{ mediaStore.title }}</h2>
        <div class="flex items-center justify-center p-16">
            <button @click="togglePlay()">
                <i-typcn-media-pause
                    v-if="mediaStore.isPlaying"
                    class="h-64 w-64 text-sumi-50 rounded-full box-content p-8"
                    :style="`background: ${primaryColor}`"
                />
                <i-typcn-media-play
                    v-else
                    class="h-64 w-64 text-sumi-50 rounded-full box-content p-8"
                    :style="`background: ${primaryColor}`"
                />
            </button>
            <div id="waveform" class="flex-1 relative">
                <div
                    id="cursor-label"
                    class="absolute text-sm px-4 font-mono font-bold whitespace-nowrap"
                    style="transform: translateX(-50%); bottom: -2em"
                    :style="`color: ${primaryColor}`"
                >
                    {{ formatSecondsToTime(mediaStore.currentTime) }}|{{
                        formatSecondsToTime(mediaStore.duration)
                    }}
                </div>
            </div>
        </div>
    </div>
</template>
