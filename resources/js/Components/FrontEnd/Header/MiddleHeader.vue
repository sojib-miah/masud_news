<script setup>
import { Link } from "@inertiajs/vue3";
import { defineProps, ref, onMounted, onUnmounted, computed } from "vue";
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

const currentIndex = ref(0);
let intervalId = null;

// Only rotate if more than 1 ad
const hasMultipleAds = computed(
    () => Array.isArray(props.headers) && props.headers.length > 1,
);

function nextSlide() {
    if (!props.headers?.length) return;
    currentIndex.value = (currentIndex.value + 1) % props.headers.length;
}

onMounted(() => {
    if (hasMultipleAds.value) {
        intervalId = setInterval(nextSlide, 10000);
    }
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
</script>

<template>
    <header class="site-header">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div
                class="flex flex-col lg:flex-row items-center justify-between gap-3 lg:gap-6 py-3 lg:py-4"
            >
                <!-- ============ LOGO ============ -->
                <Link
                    :href="route('home')"
                    class="logo-wrap shrink-0 flex items-center justify-center lg:justify-start w-full lg:w-auto"
                    aria-label="Home"
                >
                    <img
                        :src="`/storage/${$page.props.site?.logo}`"
                        alt="Logo"
                        class="logo-img"
                    />
                </Link>

                <!-- ============ AD SLIDER ============ -->
                <div
                    class="ad-wrap w-full lg:flex-1 lg:max-w-[65%] flex justify-center lg:justify-end"
                >
                    <transition name="fade" mode="out-in">
                        <a
                            v-if="props.headers?.[currentIndex]"
                            :key="currentIndex"
                            class="ad-card"
                            href="#"
                            @click.prevent
                        >
                            <img
                                class="ad-img"
                                :src="`/storage/${props.headers[currentIndex].ad}`"
                                alt="Advertisement"
                            />
                        </a>
                    </transition>
                </div>
            </div>
        </div>

        <!-- Subtle bottom accent -->
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
</style>
