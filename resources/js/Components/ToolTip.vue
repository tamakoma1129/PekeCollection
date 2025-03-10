<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    message: {type: String, required: true},
});

const tooltip = ref(null);
const tooltipContainer = ref(null);

const adjustTooltipPosition = () => {
    if (!tooltip.value || !tooltipContainer.value) return;

    const containerRect = tooltipContainer.value.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    // 右側にはみ出す場合
    if (containerRect.right + tooltip.value.offsetWidth > viewportWidth) {
        tooltip.value.style.left = `${containerRect.left - tooltip.value.offsetWidth}px`;
    } else {
        tooltip.value.style.left = `${containerRect.right}px`;
    }

    // 下側にはみ出す場合 -56は再生バーの縦幅
    if (containerRect.bottom + tooltip.value.offsetHeight > viewportHeight - 56) {
        tooltip.value.style.top = `${containerRect.top - tooltip.value.offsetHeight}px`;
    } else {
        tooltip.value.style.top = `${containerRect.bottom}px`;
    }
};

const onHover = () => {
    adjustTooltipPosition();
};

onMounted(() => {
    window.addEventListener('resize', adjustTooltipPosition);
});

onUnmounted(() => {
    window.removeEventListener('resize', adjustTooltipPosition);
});
</script>

<template>
    <div class="relative" ref="tooltipContainer">
        <span class="peer" @mouseenter="onHover">
            <slot></slot>
        </span>
        <p ref="tooltip" class="rounded bg-white px-8 py-4 shadow-lg fixed w-[300px] transition border border-sumi-300 hidden peer-hover:block">
            {{ message }}
        </p>
    </div>
</template>
