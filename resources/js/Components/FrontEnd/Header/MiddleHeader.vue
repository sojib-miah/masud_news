<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    headers: {
        type: Array,
        default: () => [],
    },
    siteSetting: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const DEFAULT_IMAGE = "/images/default.jpg";

// Convert a stored image path into a usable URL.
function imgUrl(path) {
    if (typeof path !== "string" || !path.trim()) {
        return DEFAULT_IMAGE;
    }

    const value = path.trim();

    if (/^https?:\/\//i.test(value)) {
        return value;
    }

    const cleanPath = value.replace(/^\/+/, "").replace(/^storage\/+/i, "");

    return `/storage/${cleanPath}`;
}

// Use the fallback only once to avoid an infinite error loop.
function onImgError(event) {
    const img = event.currentTarget;

    if (img.dataset.fallbackApplied === "true") {
        return;
    }

    img.dataset.fallbackApplied = "true";
    img.src = DEFAULT_IMAGE;
}

// Only display advertisements with an image path.
const validHeaders = computed(() => {
    return (props.headers || []).filter((item) => {
        return item && typeof item.ad === "string" && item.ad.trim() !== "";
    });
});

const currentIndex = ref(0);

const safeIndex = computed(() => {
    const length = validHeaders.value.length;

    if (length === 0) return -1;

    return ((currentIndex.value % length) + length) % length;
});

let intervalId = null;

function stopInterval() {
    if (intervalId !== null) {
        clearInterval(intervalId);
        intervalId = null;
    }
}

function preloadNext() {
    const length = validHeaders.value.length;

    if (length < 2) return;

    const nextIndex = (safeIndex.value + 1) % length;
    const nextAd = validHeaders.value[nextIndex];

    if (nextAd?.ad) {
        const image = new Image();
        image.src = imgUrl(nextAd.ad);
    }
}

function nextSlide() {
    const length = validHeaders.value.length;

    if (length < 2) return;

    preloadNext();
    currentIndex.value = (currentIndex.value + 1) % length;
}

function startInterval() {
    stopInterval();

    if (validHeaders.value.length > 1) {
        intervalId = setInterval(nextSlide, 10000);
    }
}

onMounted(startInterval);
onUnmounted(stopInterval);

watch(
    () => props.headers,
    () => {
        currentIndex.value = 0;
        startInterval();
    },
    { deep: true },
);
</script>

<template>
    <header class="site-header">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div
                class="flex flex-col lg:flex-row items-center justify-between gap-3 lg:gap-6 py-3 lg:py-4"
            >
                <!-- LOGO -->
                <Link
                    :href="route('home')"
                    class="logo-wrap shrink-0 flex items-center justify-center lg:justify-start w-full lg:w-auto"
                    aria-label="Home"
                >
                    <img
                        class="logo-img"
                        :src="imgUrl(page.props.site?.logo)"
                        alt="Logo"
                        @error="onImgError"
                    />
                </Link>

                <!-- AD SLIDER -->
                <div
                    class="ad-wrap w-full lg:flex-1 lg:max-w-[65%] flex justify-center lg:justify-end"
                >
                    <transition name="fade" mode="out-in">
                        <div
                            v-if="safeIndex !== -1"
                            :key="safeIndex"
                            class="ad-card"
                        >
                            <img
                                class="ad-img"
                                :src="imgUrl(validHeaders[safeIndex].ad)"
                                alt="Advertisement"
                                loading="eager"
                                @error="onImgError"
                            />
                        </div>

                        <div
                            v-else
                            key="empty-ad"
                            class="ad-card ad-card-empty"
                        ></div>
                    </transition>
                </div>
            </div>
        </div>

        <div class="header-accent" aria-hidden="true"></div>
    </header>
</template>

<style scoped>
/* ============ HEADER CONTAINER ============ */
.site-header {
    position: relative;
    background: linear-gradient(180deg, #ffffff 0%, #f8faf9 100%);
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.04),
        0 4px 14px rgba(0, 0, 0, 0.03);
    z-index: 20;
}

.header-accent {
    height: 3px;
    background: linear-gradient(90deg, #8aff90, #00930c, #8aff90);
    background-size: 200% 100%;
    animation: accentMove 8s linear infinite;
}

@keyframes accentMove {
    0% {
        background-position: 0% 50%;
    }
    100% {
        background-position: 200% 50%;
    }
}

/* ============ LOGO ============ */
.logo-wrap {
    transition: transform 0.25s ease;
}

.logo-wrap:hover {
    transform: scale(1.02);
}

.logo-img {
    height: 56px; /* mobile */
    width: auto;
    max-width: 220px;
    object-fit: contain;
    display: block;
}

@media (min-width: 640px) {
    .logo-img {
        height: 68px;
        max-width: 260px;
    }
}

@media (min-width: 1024px) {
    .logo-img {
        height: 80px;
        max-width: 300px;
    }
}

/* ============ AD CARD ============ */
.ad-card {
    display: block;
    height: 64px; /* mobile */
    width: 100%;
    max-width: 100%;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    transition:
        box-shadow 0.25s ease,
        transform 0.25s ease;
}

.ad-card:hover {
    box-shadow: 0 6px 20px rgba(0, 147, 12, 0.15);
    transform: translateY(-1px);
}

.ad-img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
}

@media (min-width: 640px) {
    .ad-card {
        height: 72px;
    }
}

@media (min-width: 1024px) {
    .ad-card {
        height: 84px;
    }
}

/* ============ FADE TRANSITION ============ */
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.8s ease,
        transform 0.8s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(-6px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(6px);
}

/* ============ REDUCE MOTION ============ */
@media (prefers-reduced-motion: reduce) {
    .header-accent {
        animation: none;
    }
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s;
    }
    .logo-wrap:hover,
    .ad-card:hover {
        transform: none;
    }
}
.ad-card-empty {
    background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
    background-size: 200% 100%;
    animation: shimmer 1.5s linear infinite;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
</style>
