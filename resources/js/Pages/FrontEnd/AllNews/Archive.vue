<script setup>
import Layout from "@/Components/Layout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref, computed } from "vue";

const props = defineProps({
    news: Object,
    date: String,
    sidebarLatest: Array,
    sidebarPopular: Array,
});

const selectedDate = ref(props.date);
const activeFilter = ref("latest");

const searchByDate = () => {
    if (!selectedDate.value) return;

    router.get(route("news.archive"), {
        date: selectedDate.value,
    });
};

const filteredNews = computed(() => {
    return activeFilter.value === "latest"
        ? props.sidebarLatest
        : props.sidebarPopular;
});
</script>

<template>
    <Layout>
        <Head title="Archive Page" />

        <div class="container mx-auto my-6 px-4">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Main Archive Section -->
                <div class="w-full md:w-2/3 mb-4">
                    <div
                        class="flex items-center gap-3 p-2 bg-slate-200 border-b border-black"
                    >
                        <Link :href="route('home')" class="text-blue-500">
                            <i class="fa-regular fa-house"></i>
                        </Link>
                        <i class="fa-solid fa-angle-right"></i>
                        <span>আর্কাইভ সংবাদ</span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 my-5">
                        <div class="w-full sm:w-2/3">
                            <input
                                type="date"
                                v-model="selectedDate"
                                class="border rounded p-2 border-slate-300 w-full"
                            />
                        </div>
                        <div class="w-full sm:w-1/3">
                            <button
                                @click="searchByDate"
                                class="border rounded bg-green-600 p-2 text-white w-full"
                            >
                                অনুসন্ধান করুন
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedDate" class="mb-4 text-gray-600">
                        <strong>{{ selectedDate }}</strong> তারিখের সংবাদ
                    </div>

                    <div
                        v-if="news.data.length"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
                    >
                        <div
                            v-for="item in news.data"
                            :key="item.id"
                            class="border rounded shadow-sm p-2"
                        >
                            <img
                                v-if="item.image"
                                class="w-full h-[150px] sm:h-[180px] md:h-[200px] object-cover rounded"
                                :src="`/storage/${item.image}`"
                                :alt="item.title"
                            />

                            <Link
                                :href="route('news.details', item.slug)"
                                class="block mt-3"
                            >
                                <p
                                    class="leading-6 font-medium hover:text-blue-600"
                                >
                                    {{
                                        item.title
                                            .split(" ")
                                            .slice(0, 10)
                                            .join(" ")
                                    }}{{
                                        item.title.split(" ").length > 10
                                            ? "..."
                                            : ""
                                    }}
                                </p>
                            </Link>
                        </div>
                    </div>

                    <div v-else class="text-center py-16 text-gray-500">
                        এই তারিখে কোনো সংবাদ পাওয়া যায়নি
                    </div>

                    <div
                        v-if="news.links.length > 3"
                        class="mt-8 flex flex-wrap gap-2"
                    >
                        <Link
                            v-for="link in news.links"
                            :key="link.label"
                            :href="link.url || ''"
                            v-html="link.label"
                            class="px-3 py-1 border rounded"
                            :class="{
                                'bg-green-600 text-white': link.active,
                                'text-gray-400': !link.url,
                            }"
                        />
                    </div>
                </div>

                <!-- Sidebar Section -->
                <div class="w-full md:w-1/3 mb-4 flex flex-col gap-3">
                    <div class="flex">
                        <button
                            @click="activeFilter = 'latest'"
                            class="w-1/2 py-3 text-white"
                            :class="
                                activeFilter === 'latest'
                                    ? 'bg-black'
                                    : 'bg-slate-400'
                            "
                        >
                            সর্বশেষ সংবাদ
                        </button>

                        <button
                            @click="activeFilter = 'popular'"
                            class="w-1/2 py-3 text-white"
                            :class="
                                activeFilter === 'popular'
                                    ? 'bg-black'
                                    : 'bg-slate-400'
                            "
                        >
                            জনপ্রিয় সংবাদ
                        </button>
                    </div>

                    <div class="p-3 bg-slate-300 flex-1">
                        <div
                            class="bg-white p-2 h-[400px] sm:h-[500px] overflow-y-auto"
                        >
                            <div
                                v-for="item in filteredNews"
                                :key="item.id"
                                class="flex gap-3 border-b border-dotted pb-2 mb-2"
                            >
                                <div class="w-[80px] sm:w-[100px]">
                                    <img
                                        v-if="item.image"
                                        class="w-full h-[50px] sm:h-[60px] object-cover rounded"
                                        :src="`/storage/${item.image}`"
                                    />
                                </div>

                                <div>
                                    <Link
                                        :href="route('news.details', item.slug)"
                                    >
                                        <p
                                            class="text-sm font-medium leading-5 hover:text-blue-600"
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
