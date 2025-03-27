<script setup>
import {onMounted, onBeforeUnmount, ref} from "vue";
import {useMediaStore} from "@/stores/media.js";
import WaveSurfer from "wavesurfer.js";
import Hover from 'wavesurfer.js/dist/plugins/hover.esm.js'
import { Vibrant } from "node-vibrant/browser";

const mediaStore = useMediaStore();
const imageElement = ref();
const primaryColor = ref("#262626");    // sumi-800
const subColor = ref("#f5f5f5");    // sumi-100


onMounted(() => {
    Vibrant.from(mediaStore.preview_image_path)
        .getPalette()
        .then((palette) => {
            primaryColor.value = palette.Vibrant.hex;
            subColor.value = palette.LightMuted.hex;

            const wavesurfer = WaveSurfer.create({
                container: "#waveform",
                normalize: true,
                waveColor: "#e5e5e5", // sumi-200
                progressColor: primaryColor.value,   // sumi-50
                cursorWidth: 0,
                url: mediaStore.src,
                hideScrollbar: true,
                dragToSeek: true,
                plugins: [
                    Hover.create({
                        lineColor: primaryColor.value,   // teto-400
                        lineWidth: 3,
                        labelBackground: '#0a0a0a', // sumi-950
                        labelColor: '#e5e5e5',  // sumi-200
                        labelSize: '14px',
                    }),
                ],

                /**
                 * Render a waveform as a squiggly line
                 * @see https://css-tricks.com/making-an-audio-waveform-visualizer-with-vanilla-javascript/
                 */
                renderFunction: (channels, ctx) => {
                    const { width, height } = ctx.canvas
                    const scale = channels[0].length / width
                    const step = 8

                    ctx.translate(0, height / 2)
                    ctx.strokeStyle = ctx.fillStyle
                    ctx.beginPath()
                    ctx.lineWidth = 5;

                    for (let i = 0; i < width; i += step * 2) {
                        const index = Math.floor(i * scale)
                        const value = Math.abs(channels[0][index])
                        let x = i
                        let amplitude = height / 2
                        let y = value * amplitude

                        ctx.moveTo(x, 0)
                        ctx.lineTo(x, y)
                        ctx.arc(x + step / 2, y, step / 2, Math.PI, 0, true)
                        ctx.lineTo(x + step, 0)

                        x = x + step
                        y = -y
                        ctx.moveTo(x, 0)
                        ctx.lineTo(x, y)
                        ctx.arc(x + step / 2, y, step / 2, Math.PI, 0, false)
                        ctx.lineTo(x + step, 0)
                    }

                    ctx.stroke()
                    ctx.closePath()
                },
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
        })
});

onBeforeUnmount(() => {
    mediaStore.unsetMediaElement();
})
</script>

<template>
    <div class="w-[80vw] bottom-40 rounded fixed h-fit -translate-x-1/2" :style="`background: ${subColor}`">
        <img :src="mediaStore.raw_image_path"
             :alt="mediaStore.title"
             draggable="false"
             class="mx-auto my-16 rounded-lg overflow-hidden shadow-2xl select-none"
             ref="imageElement"/>
        <div id="waveform" class="mx-40"></div>
    </div>
</template>

