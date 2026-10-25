<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";

defineProps({
    video: Array,
});

const page = usePage();

const latestNews = page.props.sidebarLatest ?? [];
const popularNews = page.props.sidebarPopular ?? [];

const activeFilter = ref("latest");

const filteredNews = computed(() => {
    return activeFilter.value === "latest" ? latestNews : popularNews;
});

const setFilter = (filter) => {
    activeFilter.value = filter;
};
function extractYoutubeId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return match && match[2].length === 11 ? match[2] : null;
}
</script>

<template>
    <div class="bg-slate-200 py-2">
        <div class="container">
            <div class="flex flex-col lg:flex-row gap-4 my-6 lg:my-8">
                <!-- Video Section -->
                <div class="w-full lg:w-2/3">
                    <div
                        class="relative pb-[60.25%] h-0 overflow-hidden rounded-lg"
                    >
                        <iframe
                            v-if="video.length > 0"
                            class="absolute top-0 left-0 w-full h-full rounded-lg"
                            :src="`https://www.youtube.com/embed/${extractYoutubeId(video[0].link)}`"
                            title="YouTube video player"
                            frameborder="0"
                            allow="
                                accelerometer;
                                autoplay;
                                clipboard-write;
                                encrypted-media;
                                gyroscope;
                                picture-in-picture;
                            "
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>

                <!-- News Section -->
                <div class="w-full lg:w-1/3">
                    <!-- Tabs -->
                    <div class="flex w-full mb-3 rounded overflow-hidden">
                        <button
                            :class="[
                                'w-1/2 py-2 sm:py-3 text-white text-sm sm:text-base transition',
                                activeFilter === 'latest'
                                    ? 'bg-black'
                                    : 'bg-slate-400',
                            ]"
                            @click="setFilter('latest')"
                        >
                            সর্বশেষ সংবাদ
                        </button>

                        <button
                            :class="[
                                'w-1/2 py-2 sm:py-3 text-white text-sm sm:text-base transition',
                                activeFilter === 'popular'
                                    ? 'bg-black'
                                    : 'bg-slate-400',
                            ]"
                            @click="setFilter('popular')"
                        >
                            জনপ্রিয় সংবাদ
                        </button>
                    </div>

                    <!-- News List -->
                    <div class="p-2 sm:p-3 bg-slate-300 rounded">
                        <div
                            class="bg-white p-2 max-h-[300px] sm:max-h-[350px] lg:max-h-[420px] overflow-y-auto rounded"
                        >
                            <div
                                v-for="item in filteredNews.slice(0, 10)"
                                :key="item.id"
                                class="flex gap-2 sm:gap-3 border-b border-dotted pb-2 mt-2 items-start"
                            >
                                <!-- Image -->
                                <div
                                    class="w-[80px] sm:w-[100px] flex-shrink-0"
                                >
                                    <img
                                        :src="
                                            item.image
                                                ? `/storage/${item.image}`
                                                : '/storage/images/default.jpg'
                                        "
                                        class="w-full h-[50px] sm:h-[60px] object-cover rounded"
                                        alt="news"
                                    />
                                </div>

                                <!-- Title -->
                                <div class="flex-1">
                                    <Link :href="`/news/details/${item.slug}`">
                                        <p
                                            class="text-sm sm:text-base leading-5 font-medium hover:text-blue-500"
                                        >
                                            {{
                                                item.title
                                                    .split(" ")
                                                    .slice(0, 8)
                                                    .join(" ")
                                            }}
                                            {{
                                                item.title.split(" ").length >
                                                10
                                                    ? "..."
                                                    : ""
                                            }}
                                        </p>
                                    </Link>
                                </div>
                            </div>

                            <!-- Empty -->
                            <div
                                v-if="filteredNews.length === 0"
                                class="text-center text-gray-500 py-5 text-sm"
                            >
                                কোনো সংবাদ পাওয়া যায়নি।
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
p {
    margin-bottom: 0 !important;
}
</style>
