<script setup>
import { usePage, Link } from "@inertiajs/vue3";

const page = usePage();
const breakingNews = page.props.breakingNews || [];
</script>

<template>
    <div class="container">
        <div class="flex my-3 items-center">
            <!-- Left Title -->
            <div
                class="bg-green-500 p-2 border border-green-500 flex justify-center items-center text-white shrink-0"
            >
                <span>শিরোনাম :</span>
            </div>

            <!-- Marquee -->
            <div class="marquee font-robo bg-white">
                <div class="marquee-track">
                    <!-- First Copy -->
                    <div class="marquee-content">
                        <Link
                            v-for="item in breakingNews"
                            :key="item.id"
                            :href="route('news.details', item.slug)"
                            class="mx-6 hover:text-green-600 flex gap-3 items-center"
                        >
                            {{ item.title }}
                            <img
                                :src="`/storage/${$page.props.site?.logo}`"
                                alt="logo"
                                class="h-5 w-5"
                            />
                        </Link>
                    </div>

                    <!-- Duplicate for smooth scroll -->
                    <div class="marquee-content">
                        <Link
                            v-for="item in breakingNews"
                            :key="'duplicate-' + item.id"
                            :href="route('news.details', item.slug)"
                            class="mx-6 hover:text-green-600 flex gap-3 items-center"
                        >
                            {{ item.title }}
                            <img
                                :src="`/storage/${$page.props.site?.logo}`"
                                alt="logo"
                                class="h-5 w-5"
                            />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.marquee {
    width: 100%;
    overflow: hidden;
    border: 2px solid #22c55e;
    padding: 6px 0;
    font-size: 16px;
}

.marquee-track {
    display: flex;
    width: max-content;
    animation: marquee 100s linear infinite;
}

.marquee-content {
    display: flex;
    white-space: nowrap;
}

.marquee-content a {
    padding: 0 50px;
    color: #000;
    text-decoration: none;
}

.marquee:hover .marquee-track {
    animation-play-state: paused;
}

@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}
</style>
