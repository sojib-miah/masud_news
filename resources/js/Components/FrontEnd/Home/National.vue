<script setup>
import { Link } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
defineProps({
    homeCategories: Array,
});
const page = usePage();
const homeCategories = page.props.homeCategories ?? [];
</script>

<template>
    <div class="py-4">
        <div class="container">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-4">
                <div
                    v-for="category in homeCategories"
                    :key="category.id"
                    class="bg-slate-200 rounded overflow-hidden"
                >
                    <!-- Title -->
                    <div class="border-l-8 border-green-500 border-b">
                        <div class="p-2">
                            <Link
                                :href="route('all.news', category.slug)"
                                class="font-bold text-base sm:text-lg"
                            >
                                {{ category.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex flex-col sm:flex-row gap-2 p-2">
                        <!-- Featured News -->
                        <div
                            v-if="category.news.length"
                            class="bg-white p-2 w-full sm:w-1/2"
                        >
                            <img
                                :src="
                                    category.news[0].image
                                        ? `/storage/${category.news[0].image}`
                                        : '/storage/images/default.jpg'
                                "
                                class="w-full h-[150px] sm:h-[180px] object-cover rounded mb-2"
                                alt="news"
                            />

                            <Link
                                :href="
                                    route('news.details', category.news[0].slug)
                                "
                                class="text-blue-400 hover:text-blue-500 text-sm sm:text-base font-bold leading-5"
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
                                    <span class="text-xs sm:text-sm">
                                        আরও পড়ুন</span
                                    >
                                </p>
                            </Link>
                        </div>

                        <!-- List News -->
                        <div class="bg-white p-2 w-full sm:w-1/2">
                            <div
                                v-for="news in category.news.slice(1)"
                                :key="news.id"
                                class="flex gap-2 border-b border-dotted pb-2 mb-2"
                            >
                                <img
                                    :src="
                                        news.image
                                            ? `/storage/${news.image}`
                                            : '/storage/images/default.jpg'
                                    "
                                    class="w-[80px] sm:w-[100px] h-[60px] object-cover rounded"
                                    alt="news"
                                />

                                <div>
                                    <Link
                                        :href="route('news.details', news.slug)"
                                    >
                                        <p
                                            class="text-xs sm:text-sm leading-5 hover:text-blue-500 text-justify"
                                        >
                                            {{
                                                news.title
                                                    .split(" ")
                                                    .slice(0, 8)
                                                    .join(" ")
                                            }}
                                            {{
                                                news.title.split(" ").length > 9
                                                    ? "..."
                                                    : ""
                                            }}
                                        </p>
                                    </Link>
                                </div>
                            </div>

                            <!-- More -->
                            <div class="flex justify-end mt-2">
                                <Link
                                    :href="route('all.news', category.slug)"
                                    class="text-blue-400 text-sm"
                                >
                                    আরো সংবাদ...
                                    <i class="fa-solid fa-angles-right"></i>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
