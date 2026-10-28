<script setup>
import { Link } from "@inertiajs/vue3";
import { defineProps, ref, onMounted, onUnmounted } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    headers: Array,
    siteSetting: Object,
});
const currentIndex = ref(0);
let intervalId;

onMounted(() => {
    intervalId = setInterval(() => {
        currentIndex.value = (currentIndex.value + 1) % props.headers.length;
    }, 10000);
});

onUnmounted(() => {
    clearInterval(intervalId);
});
</script>

<template>
    <div class="bg-slate-300 py-2">
        <div class="container">
            <div
                class="flex flex-col lg:flex-row items-center justify-between gap-3"
            >
                <!-- Logo -->
                <div class="logo w-full lg:w-auto text-center lg:text-left">
                    <Link :href="route('home')">
                        <img
                            :src="`/storage/${$page.props.site?.logo}`"
                            alt="logo"
                            class="mx-auto lg:mx-0"
                        />
                    </Link>
                </div>

                <!-- Ad -->
                <transition name="fade" mode="out-in">
                    <div
                        class="ad w-full lg:w-auto flex justify-center"
                        :key="currentIndex"
                    >
                        <!-- <Link :href="route('home')"> -->
                        <img
                            v-if="props.headers?.[currentIndex]"
                            :src="`/storage/${props.headers[currentIndex].ad}`"
                            alt="Ad"
                        />
                        <!-- </Link> -->
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bg-slate-300 {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
}
.logo {
    height: 90px;
    min-width: 160px;
    max-width: 260px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    background: #ffffff;
    border: 1px solid #eaeef4;
    border-radius: 16px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    transition:
        box-shadow 0.3s ease,
        border-color 0.3s ease;
}

.logo:hover {
    border-color: #cbd5e1;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
}

.logo a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.logo img {
    display: block;
    max-width: 100%;
    max-height: 70px;
    width: auto;
    height: auto;
    object-fit: contain;
}
.ad {
    position: relative;
    width: 100%;
    max-width: 970px;
    height: 90px;
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #ffffff;
    border: 1px solid #eaeef4;
    border-radius: 16px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    transition: box-shadow 0.3s ease;
}

.ad:hover {
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
}

.ad a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    padding: 5px;
}

.ad img {
    display: block;
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 90px;
    object-fit: contain;
    border-radius: 10px;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.6s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
@media (min-width: 640px) and (max-width: 1023px) {
    .bg-slate-300 > .container {
        padding: 12px 16px;
    }

    .logo {
        height: 80px;
        min-width: 140px;
        max-width: 220px;
        padding: 8px 14px;
        border-radius: 12px;
    }

    .logo img {
        max-height: 64px;
    }

    .ad {
        height: 80px;
        min-height: 80px;
        border-radius: 12px;
    }

    .ad img {
        max-height: 80px;
    }
}
@media (max-width: 639px) {
    .bg-slate-300 > .container {
        padding: 10px 12px;
    }

    .bg-slate-300 .flex {
        gap: 10px;
    }

    .logo {
        height: 65px;
        min-width: 0;
        max-width: 220px;
        padding: 6px 12px;
        border-radius: 12px;
    }

    .logo img {
        max-height: 52px;
    }

    .ad {
        height: 65px;
        min-height: 65px;
        max-width: 100%;
        border-radius: 12px;
    }

    .ad a {
        padding: 4px;
    }

    .ad img {
        max-height: 65px;
        border-radius: 8px;
    }
}

/* Fade animation */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
