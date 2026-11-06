<script setup>
import Layout from "@/Components/Layout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref, computed, watch } from "vue";

const props = defineProps({
    news: Object,
    date: String,
    sidebarLatest: Array,
    sidebarPopular: Array,
});

const selectedDate = ref(props.date);
const activeFilter = ref("latest");

watch(
    () => props.date,
    (newDate) => {
        selectedDate.value = newDate;
    },
);

const searchByDate = () => {
    if (!selectedDate.value) return;

    router.get(
        route("news.archive"),
        { date: selectedDate.value },
        {
            preserveScroll: true,
            preserveState: false,
            replace: true,
        },
    );
};

const filteredNews = computed(() => {
    return activeFilter.value === "latest"
        ? (props.sidebarLatest ?? [])
        : (props.sidebarPopular ?? []);
});

// Short title
function shortTitle(title, words = 10) {
    if (!title) return "";
    const parts = title.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : title;
}

// Format date to Bengali-friendly display
function formatDate(d) {
    if (!d) return "";
    try {
        return new Date(d).toLocaleDateString("en-GB", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric",
        });
    } catch {
        return d;
    }
}

// Pagination helper
function visitPage(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Layout>
        <Head title="Archive Page" />

        <div class="max-w-7xl mx-auto my-6 px-3 sm:px-4">
            <!-- ==================== BREADCRUMB ==================== -->
            <nav
                class="flex items-center gap-2 px-3 py-2.5 mb-5 rounded-xl bg-gradient-to-r from-slate-100 to-slate-50 border border-slate-200 text-sm"
            >
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-1.5 text-slate-600 hover:text-emerald-600 font-semibold transition-colors"
                >
                    <i class="fa-regular fa-house text-xs"></i>
                    <span class="hidden sm:inline">হোম</span>
                </Link>
                <i
                    class="fa-solid fa-angle-right text-slate-400 text-[10px]"
                ></i>
                <span class="text-slate-800 font-semibold">আর্কাইভ সংবাদ</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">
                <!-- ============================================ -->
                <!-- ================= MAIN COLUMN =============== -->
                <!-- ============================================ -->
                <div class="lg:col-span-2 order-2 lg:order-1">
                    <!-- Header card with date search -->
                    <div
                        class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 px-5 py-6 sm:px-6 sm:py-7 shadow-xl mb-5"
                    >
                        <div
                            class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald-500/30 blur-3xl"
                        ></div>
                        <div
                            class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
                        ></div>

                        <div class="relative">
                            <p
                                class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1"
                            >
                                <i class="fa-solid fa-box-archive mr-1"></i>
                                Archive
                            </p>
                            <h1
                                class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white tracking-tight mb-4"
                            >
                                আর্কাইভ সংবাদ
                            </h1>

                            <!-- Date picker row -->
                            <div
                                class="flex flex-col sm:flex-row gap-2 sm:gap-3"
                            >
                                <div class="flex-1 relative">
                                    <i
                                        class="fa-regular fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                    ></i>
                                    <input
                                        type="date"
                                        v-model="selectedDate"
                                        class="w-full rounded-lg border border-white/20 bg-white/10 backdrop-blur pl-10 pr-3 py-2.5 text-sm text-white placeholder-slate-300 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30"
                                    />
                                </div>
                                <button
                                    @click="searchByDate"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-2.5 px-6 shadow-lg transition-all hover:shadow-xl active:scale-95"
                                >
                                    <i
                                        class="fa-solid fa-magnifying-glass text-sm"
                                    ></i>
                                    অনুসন্ধান করুন
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Selected date info bar -->
                    <div
                        v-if="selectedDate"
                        class="flex flex-wrap items-center gap-2 mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2.5"
                    >
                        <span
                            class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 text-white text-xs font-bold px-2 py-1"
                        >
                            <i class="fa-solid fa-calendar-day"></i>
                            তারিখ
                        </span>
                        <span
                            class="text-sm font-semibold text-slate-800 break-words"
                        >
                            {{ formatDate(selectedDate) }}
                        </span>
                        <span
                            class="ml-auto text-xs font-bold text-emerald-700"
                        >
                            {{ props.news?.total ?? 0 }} সংবাদ
                        </span>
                    </div>

                    <!-- News grid -->
                    <div
                        v-if="props.news?.data?.length"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5"
                    >
                        <Link
                            v-for="item in props.news.data"
                            :key="item.id"
                            :href="route('news.details', item.slug)"
                            class="group relative overflow-hidden rounded-xl bg-white ring-1 ring-slate-200 shadow-sm hover:shadow-xl hover:ring-emerald-300 transition-all duration-300 hover:-translate-y-1 flex flex-col"
                        >
                            <!-- Image -->
                            <div class="relative overflow-hidden">
                                <img
                                    v-if="item.image"
                                    class="w-full h-[160px] sm:h-[180px] object-cover transition-transform duration-500 group-hover:scale-105"
                                    :src="`/storage/${item.image}`"
                                    :alt="item.title"
                                    loading="lazy"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                />
                                <div
                                    v-else
                                    class="w-full h-[160px] sm:h-[180px] bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center"
                                >
                                    <i
                                        class="fa-regular fa-image text-4xl text-slate-400"
                                    ></i>
                                </div>

                                <!-- Overlay gradient on hover -->
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"
                                ></div>
                            </div>

                            <!-- Content -->
                            <div class="p-3 sm:p-4 flex-1 flex flex-col">
                                <p
                                    class="leading-6 font-semibold text-slate-800 group-hover:text-emerald-700 transition-colors line-clamp-3 flex-1"
                                >
                                    {{ shortTitle(item.title, 12) }}
                                </p>
                                <div
                                    class="mt-3 flex items-center gap-1 text-xs font-bold text-emerald-600"
                                >
                                    <span>বিস্তারিত পড়ুন</span>
                                    <i
                                        class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"
                                    ></i>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-else
                        class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm py-16 px-6 text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                        >
                            <i
                                class="fa-regular fa-newspaper text-3xl text-slate-400"
                            ></i>
                        </div>
                        <p class="text-lg font-bold text-slate-700 mb-1">
                            এই তারিখে কোনো সংবাদ পাওয়া যায়নি
                        </p>
                        <p class="text-sm text-slate-500">
                            অন্য তারিখ বেছে নিন অথবা পরে আবার চেষ্টা করুন
                        </p>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.news?.links?.length > 3"
                        class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
                    >
                        <template
                            v-for="(link, i) in props.news.links"
                            :key="i"
                        >
                            <button
                                v-if="link.url"
                                @click="visitPage(link.url)"
                                class="min-w-[36px] h-9 flex items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                                :class="
                                    link.active
                                        ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow'
                                        : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:ring-emerald-300'
                                "
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="min-w-[36px] h-9 flex items-center justify-center rounded-lg px-2 text-xs font-bold text-slate-300 bg-slate-50 ring-1 ring-slate-100"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- ================= SIDEBAR =================== -->
                <!-- ============================================ -->
                <div class="lg:col-span-1 order-1 lg:order-2">
                    <div
                        class="sticky top-4 rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
                    >
                        <!-- Tabs -->
                        <div class="grid grid-cols-2">
                            <button
                                @click="activeFilter = 'latest'"
                                class="relative py-3 text-sm font-bold transition-all"
                                :class="
                                    activeFilter === 'latest'
                                        ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                "
                            >
                                <i class="fa-solid fa-clock mr-1.5 text-xs"></i>
                                সর্বশেষ সংবাদ
                            </button>
                            <button
                                @click="activeFilter = 'popular'"
                                class="relative py-3 text-sm font-bold transition-all"
                                :class="
                                    activeFilter === 'popular'
                                        ? 'bg-gradient-to-r from-orange-500 to-rose-600 text-white'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                "
                            >
                                <i class="fa-solid fa-fire mr-1.5 text-xs"></i>
                                জনপ্রিয় সংবাদ
                            </button>
                        </div>

                        <!-- List header -->
                        <div
                            class="flex items-center gap-2 px-4 py-2.5 border-b border-slate-100 bg-slate-50"
                        >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-md text-[10px] font-bold text-white"
                                :class="
                                    activeFilter === 'latest'
                                        ? 'bg-emerald-600'
                                        : 'bg-orange-600'
                                "
                            >
                                <i
                                    :class="
                                        activeFilter === 'latest'
                                            ? 'fa-solid fa-clock'
                                            : 'fa-solid fa-fire'
                                    "
                                ></i>
                            </span>
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-600"
                            >
                                {{
                                    activeFilter === "latest"
                                        ? "সর্বশেষ"
                                        : "জনপ্রিয়"
                                }}
                            </p>
                            <span
                                class="ml-auto text-xs font-bold text-slate-500"
                            >
                                {{ filteredNews.length }}
                            </span>
                        </div>

                        <!-- List -->
                        <div
                            class="p-2 max-h-[500px] sm:max-h-[600px] lg:max-h-[calc(100vh-200px)] overflow-y-auto"
                        >
                            <div
                                v-if="filteredNews.length"
                                class="divide-y divide-slate-100"
                            >
                                <Link
                                    v-for="(item, i) in filteredNews"
                                    :key="item.id"
                                    :href="route('news.details', item.slug)"
                                    class="group flex gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors"
                                >
                                    <!-- Number badge -->
                                    <div
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-xs font-black text-white mt-0.5"
                                        :class="
                                            activeFilter === 'latest'
                                                ? 'bg-gradient-to-br from-emerald-500 to-teal-600'
                                                : 'bg-gradient-to-br from-orange-500 to-rose-600'
                                        "
                                    >
                                        {{ i + 1 }}
                                    </div>

                                    <!-- Thumbnail -->
                                    <div class="shrink-0">
                                        <img
                                            v-if="item.image"
                                            class="w-16 sm:w-20 h-12 sm:h-14 object-cover rounded-md ring-1 ring-slate-200"
                                            :src="`/storage/${item.image}`"
                                            :alt="item.title"
                                            loading="lazy"
                                            @error="
                                                (e) =>
                                                    (e.target.src =
                                                        '/images/default.jpg')
                                            "
                                        />
                                        <div
                                            v-else
                                            class="w-16 sm:w-20 h-12 sm:h-14 rounded-md bg-slate-100 flex items-center justify-center ring-1 ring-slate-200"
                                        >
                                            <i
                                                class="fa-regular fa-image text-slate-400"
                                            ></i>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-xs sm:text-sm font-semibold text-slate-700 leading-5 group-hover:text-emerald-700 transition-colors line-clamp-2"
                                        >
                                            {{ shortTitle(item.title, 9) }}
                                        </p>
                                    </div>
                                </Link>
                            </div>

                            <!-- Empty state -->
                            <div
                                v-else
                                class="flex flex-col items-center justify-center py-12 px-4 text-center"
                            >
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 mb-3"
                                >
                                    <i
                                        class="fa-regular fa-newspaper text-xl text-slate-400"
                                    ></i>
                                </div>
                                <p
                                    class="text-sm font-bold text-slate-700 mb-1"
                                >
                                    কোনো সংবাদ পাওয়া যায়নি।
                                </p>
                                <p class="text-xs text-slate-500">
                                    অন্য বিভাগ দেখুন
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* Line clamp utility (Tailwind plugin may not be present) */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom scrollbar for sidebar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
