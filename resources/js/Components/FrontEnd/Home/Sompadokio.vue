<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    sompadokiyos: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="py-4">
        <div class="container">
            <!-- Main Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Category Loop -->
                <div
                    v-for="category in sompadokiyos"
                    :key="category.id"
                    class="bg-slate-200 w-full"
                >
                    <!-- Header -->
                    <div class="border-l-8 border-b border-green-500 bg-white">
                        <div class="p-3">
                            <Link
                                :href="route('all.news', category.slug)"
                                class="text-lg font-bold"
                            >
                                {{ category.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- News Section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <!-- Left Big News -->
                        <div
                            v-if="category.news && category.news.length > 0"
                            class="font-robo bg-white p-2 w-full"
                        >
                            <div class="w-full mb-3">
                                <img
                                    class="w-full h-[200px] sm:h-[220px] object-cover"
                                    :src="
                                        category.news[0].image
                                            ? `/storage/${category.news[0].image}`
                                            : '/storage/images/default.jpg'
                                    "
                                    alt="News Image"
                                />
                            </div>

                            <div>
                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            category.news[0].slug,
                                        )
                                    "
                                    class="text-blue-400 hover:text-blue-500 text-md font-bold leading-6"
                                >
                                    <p class="text-justify">
                                        {{
                                            category.news[0].title
                                                .split(" ")
                                                .slice(0, 10)
                                                .join(" ")
                                        }}
                                        {{
                                            category.news[0].title.split(" ")
                                                .length > 10
                                                ? "..."
                                                : ""
                                        }}
                                    </p>
                                </Link>

                                <p
                                    class="text-justify leading-6 text-sm"
                                    v-html="
                                        category.news[0].description?.slice(
                                            0,
                                            150,
                                        )
                                    "
                                ></p>

                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            category.news[0].slug,
                                        )
                                    "
                                    class="text-blue-400"
                                >
                                    ...আরও পড়ুন
                                </Link>
                            </div>
                        </div>

                        <!-- Right List -->
                        <div class="font-robo bg-white p-2 w-full">
                            <div
                                v-for="post in category.news
                                    ? category.news.slice(1, 5)
                                    : []"
                                :key="post.id"
                                class="flex gap-3 border-b border-dotted pb-1 mb-1"
                            >
                                <!-- Image -->
                                <div class="w-[60px] sm:w-[80px] flex-shrink-0">
                                    <img
                                        class="w-full h-[60px] sm:h-[70px] object-cover rounded"
                                        :src="
                                            post.image
                                                ? `/storage/${post.image}`
                                                : '/storage/images/default.jpg'
                                        "
                                        alt="News Image"
                                    />
                                </div>

                                <!-- Title -->
                                <div>
                                    <Link
                                        :href="route('news.details', post.slug)"
                                    >
                                        <p
                                            class="leading-5 text-sm pt-2 text-justify hover:text-blue-500"
                                        >
                                            {{
                                                post.title
                                                    .split(" ")
                                                    .slice(0, 8)
                                                    .join(" ")
                                            }}
                                            {{
                                                post.title.split(" ").length >
                                                10
                                                    ? "..."
                                                    : ""
                                            }}
                                        </p>
                                    </Link>
                                </div>
                            </div>

                            <!-- More -->
                            <div class="flex justify-end mt-3">
                                <Link
                                    :href="route('all.news', category.slug)"
                                    class="text-blue-400"
                                >
                                    আরো সংবাদ...
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
