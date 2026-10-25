<script setup>
import Layout from "@/Components/Layout.vue";
import NewsBread from "./NewsBread.vue";
import { usePage, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const category = page.props.category;
const subcategory = page.props.subcategory;
const news = page.props.news;

// here sidebar
const latestNews = page.props.sidebarLatest ?? [];
const popularNews = page.props.sidebarPopular ?? [];
const activeFilter = ref("latest");
const filteredNews = computed(() => {
    return activeFilter.value === "latest" ? latestNews : popularNews;
});
const setFilter = (filter) => {
    activeFilter.value = filter;
};
</script>

<template>
    <Layout>
        <div class="container mx-auto px-4">
            <NewsBread :category="category" :subcategory="subcategory" />

            <div class="flex flex-col md:flex-row gap-4 my-5">
                <!-- Main News Section -->
                <div class="w-full md:w-2/3">
                    <div v-if="news.length">
                        <!-- Featured News -->
                        <div class="mb-6">
                            <img
                                :src="`/storage/${news[0].image}`"
                                class="border-b border-dotted pb-2 w-full object-cover h-[250px] md:h-[350px]"
                                alt="news"
                            />
                            <h2 class="text-xl mt-3">{{ news[0].title }}</h2>
                            <p
                                class="mt-1 leading-6"
                                v-html="news[0].description.substring(0, 200)"
                            ></p>
                            <Link
                                :href="`/news/details/${news[0].slug}`"
                                class="text-blue-400"
                            >
                                আরও পড়ুন
                            </Link>
                        </div>

                        <!-- Secondary News -->
                        <div class="flex flex-col sm:flex-row gap-4 mb-6">
                            <div
                                v-for="item in news.slice(1, 4)"
                                :key="item.id"
                                class="w-full sm:w-1/3"
                            >
                                <img
                                    :src="
                                        item.image
                                            ? `/storage/${item.image}`
                                            : '/storage/images/default.jpg'
                                    "
                                    class="w-full border-b border-dotted pb-2 h-[200px] md:h-[250px] object-cover"
                                    alt="news"
                                />
                                <Link :href="`/news/details/${item.slug}`">
                                    <p
                                        class="pt-2 leading-6 text-blue-500 hover:text-blue-600"
                                    >
                                        {{
                                            item.title
                                                .split(" ")
                                                .slice(0, 8)
                                                .join(" ")
                                        }}{{
                                            item.title.split(" ").length > 8
                                                ? "..."
                                                : ""
                                        }}
                                    </p>
                                </Link>
                            </div>
                        </div>

                        <!-- Grid News -->
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6"
                        >
                            <div
                                v-for="item in news.slice(0, 8)"
                                :key="item.id"
                                class="border-b border-dotted pb-2"
                            >
                                <img
                                    :src="
                                        item.image
                                            ? `/storage/${item.image}`
                                            : '/storage/images/default.jpg'
                                    "
                                    class="w-full object-cover h-[150px] sm:h-[180px] md:h-[200px]"
                                    alt="news"
                                />
                                <Link :href="`/news/details/${item.slug}`">
                                    <p
                                        class="pt-2 leading-6 text-blue-500 hover:text-blue-600"
                                    >
                                        {{
                                            item.title
                                                .split(" ")
                                                .slice(0, 8)
                                                .join(" ")
                                        }}{{
                                            item.title.split(" ").length > 8
                                                ? "..."
                                                : ""
                                        }}
                                    </p>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <p class="text-center text-gray-500">
                            No news available in this category.
                        </p>
                    </div>
                </div>

                <!-- Sidebar Section -->
                <div class="w-full md:w-1/3 flex flex-col gap-3 h-fit">
                    <!-- Filter Buttons -->
                    <div class="flex w-full mb-3">
                        <button
                            :class="[
                                'w-1/2 py-3 text-white',
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
                                'w-1/2 py-3 text-white',
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
                    <div class="p-3 bg-slate-300 flex-1">
                        <div
                            class="bg-white p-2 h-[400px] sm:h-[420px] overflow-y-auto"
                        >
                            <div
                                v-for="item in filteredNews.slice(0, 10)"
                                :key="item.id"
                                class="flex gap-3 border-b border-dotted pb-1 mt-1"
                            >
                                <div class="w-[80px] sm:w-[100px]">
                                    <img
                                        :src="
                                            item.image
                                                ? `/storage/${item.image}`
                                                : '/storage/images/default.jpg'
                                        "
                                        class="w-full h-[50px] object-cover rounded"
                                        alt="news"
                                    />
                                </div>
                                <div>
                                    <Link :href="`/news/details/${item.slug}`">
                                        <p
                                            class="leading-5 font-medium hover:text-blue-500"
                                        >
                                            {{
                                                item.title
                                                    .split(" ")
                                                    .slice(0, 8)
                                                    .join(" ")
                                            }}{{
                                                item.title.split(" ").length >
                                                10
                                                    ? "..."
                                                    : ""
                                            }}
                                        </p>
                                    </Link>
                                </div>
                            </div>

                            <div
                                v-if="filteredNews.length === 0"
                                class="text-center text-gray-500 py-5"
                            >
                                কোনো সংবাদ পাওয়া যায়নি।
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
