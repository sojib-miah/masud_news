<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";
import { ref, computed, watch, onBeforeUnmount } from "vue";

const props = defineProps({
    news: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    publishedCount: {
        type: Number,
        default: 0,
    },
    draftCount: {
        type: Number,
        default: 0,
    },
    breakingCount: {
        type: Number,
        default: 0,
    },
});

// Filter state
const search = ref(props.filters?.search ?? "");
const statusFilter = ref(props.filters?.status ?? "");
const sortBy = ref(props.filters?.sort ?? "newest");

let filterTimeout = null;

// Server-side filtering and sorting
watch(
    [search, statusFilter, sortBy],
    ([searchValue, statusValue, sortValue]) => {
        clearTimeout(filterTimeout);

        filterTimeout = setTimeout(() => {
            router.get(
                route("admin.news"),
                {
                    search: searchValue.trim() || undefined,
                    status: statusValue || undefined,
                    sort: sortValue || "newest",
                    page: 1,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 350);
    },
);

onBeforeUnmount(() => {
    clearTimeout(filterTimeout);
});

// Reset all filters
const resetFilters = () => {
    search.value = "";
    statusFilter.value = "";
    sortBy.value = "newest";
};

// Active filters
const hasActiveFilters = computed(() => {
    return (
        search.value.trim() !== "" ||
        statusFilter.value !== "" ||
        sortBy.value !== "newest"
    );
});

// Server already filters and sorts the paginated results
const filteredNews = computed(() => props.news?.data ?? []);

// Delete news
function deleteNews(id) {
    if (confirm("Delete this news?")) {
        router.delete(route("admin.news.delete", id), {
            preserveScroll: true,
        });
    }
}

// Date formatting
function formatDate(date) {
    if (!date) return "—";

    return new Date(date).toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

// Global row number
function globalIndex(index) {
    const page = props.news?.current_page ?? 1;
    const perPage = props.news?.per_page ?? 20;

    return (page - 1) * perPage + index + 1;
}

// Pagination
function visitPage(url) {
    if (!url) return;

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <div>
        <Head title="News Page" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <Layout>
            <Breadcrumb />

            <!-- ==================== PAGE HEADER ==================== -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
            >
                <div
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-blue-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-newspaper mr-1"></i> Content
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            All News
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Manage, filter, and publish your news articles.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                        >
                            <i class="fa-solid fa-database text-blue-300"></i>
                            {{ props.news?.total ?? 0 }} Total
                        </div>
                        <Link
                            :href="route('admin.news.create')"
                            class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-bold px-4 py-2 shadow-lg hover:shadow-xl transition-all active:scale-95"
                        >
                            <i class="fa fa-plus"></i>
                            Create News
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ==================== STATS ROW ==================== -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-5">
                <div
                    class="flex items-center gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 shadow-sm"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 ring-1 ring-blue-200"
                    >
                        <i class="fa-solid fa-newspaper"></i>
                    </span>
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500"
                        >
                            Total News
                        </p>
                        <p class="text-xl font-extrabold text-slate-800">
                            {{ props.news?.total ?? 0 }}
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 shadow-sm"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 ring-1 ring-emerald-200"
                    >
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500"
                        >
                            Published
                        </p>
                        <p class="text-xl font-extrabold text-slate-800">
                            {{ publishedCount }}
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 shadow-sm"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 ring-1 ring-amber-200"
                    >
                        <i class="fa-solid fa-pen-to-square"></i>
                    </span>
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500"
                        >
                            Draft
                        </p>
                        <p class="text-xl font-extrabold text-slate-800">
                            {{ draftCount }}
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 rounded-xl bg-white p-4 ring-1 ring-slate-200 shadow-sm"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600 ring-1 ring-red-200"
                    >
                        <i class="fa-solid fa-fire"></i>
                    </span>
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500"
                        >
                            Breaking
                        </p>
                        <p class="text-xl font-extrabold text-slate-800">
                            {{ breakingCount }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ==================== FILTER BAR ==================== -->
            <div
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-4 mb-5"
            >
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <!-- Search (biggest) -->
                    <div class="md:col-span-6 relative">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                        ></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by title..."
                            class="w-full rounded-lg border border-slate-300 pl-10 pr-9 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        />
                        <button
                            v-if="search"
                            @click="search = ''"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition"
                            title="Clear search"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Status filter -->
                    <div class="md:col-span-3">
                        <select
                            v-model="statusFilter"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white"
                        >
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="breaking">Breaking</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="md:col-span-3">
                        <select
                            v-model="sortBy"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white"
                        >
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="views">Most Viewed</option>
                            <option value="title">Title (A–Z)</option>
                        </select>
                    </div>
                </div>

                <!-- Active filter row -->
                <div
                    v-if="hasActiveFilters"
                    class="mt-3 flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100"
                >
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-slate-500"
                    >
                        Filters:
                    </span>

                    <span
                        v-if="search"
                        class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-200"
                    >
                        "<span class="max-w-[120px] truncate">{{ search }}</span
                        >"
                        <button
                            @click="search = ''"
                            class="hover:text-blue-900"
                        >
                            <i class="fa-solid fa-xmark text-[10px]"></i>
                        </button>
                    </span>

                    <span
                        v-if="statusFilter !== ''"
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 capitalize"
                    >
                        {{ statusFilter }}
                        <button
                            @click="statusFilter = ''"
                            class="hover:text-emerald-900"
                        >
                            <i class="fa-solid fa-xmark text-[10px]"></i>
                        </button>
                    </span>

                    <span
                        v-if="sortBy !== 'newest'"
                        class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200"
                    >
                        Sort: {{ sortBy }}
                        <button
                            @click="sortBy = 'newest'"
                            class="hover:text-violet-900"
                        >
                            <i class="fa-solid fa-xmark text-[10px]"></i>
                        </button>
                    </span>

                    <button
                        @click="resetFilters"
                        class="ml-auto inline-flex items-center gap-1 text-xs font-bold text-red-600 hover:text-red-700"
                    >
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        Reset all
                    </button>
                </div>
            </div>

            <!-- ==================== RESULTS INFO ==================== -->
            <div
                v-if="filteredNews.length !== (props.news?.data?.length ?? 0)"
                class="mb-3 flex items-center gap-2 rounded-lg bg-blue-50 border border-blue-200 px-4 py-2.5 text-sm"
            >
                <i class="fa-solid fa-filter text-blue-600"></i>
                <span class="text-slate-700">
                    Showing
                    <strong class="text-blue-700">{{
                        filteredNews.length
                    }}</strong>
                    of {{ props.news?.data?.length ?? 0 }} on this page
                </span>
            </div>

            <!-- ==================== DESKTOP TABLE ==================== -->
            <div
                class="hidden md:block rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-14"
                                >
                                    #
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Title
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-24"
                                >
                                    Image
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-32"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-24"
                                >
                                    Views
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-24"
                                >
                                    Breaking
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-32"
                                >
                                    Created
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-32"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(item, i) in filteredNews"
                                :key="item.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td
                                    class="px-4 py-3 text-sm font-semibold text-slate-600"
                                >
                                    {{ globalIndex(i) }}
                                </td>
                                <td class="px-4 py-3">
                                    <p
                                        class="text-sm font-semibold text-slate-800 leading-6 line-clamp-2 max-w-md"
                                    >
                                        {{ item.title }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <img
                                        v-if="item.image"
                                        :src="`/storage/${item.image}`"
                                        class="h-12 w-16 object-cover rounded-lg ring-1 ring-slate-200 shadow-sm"
                                        :alt="item.title"
                                        @error="
                                            (e) =>
                                                (e.target.src =
                                                    '/images/default.jpg')
                                        "
                                        loading="lazy"
                                    />
                                    <div
                                        v-else
                                        class="h-12 w-16 rounded-lg bg-slate-100 flex items-center justify-center ring-1 ring-slate-200"
                                    >
                                        <i
                                            class="fa-regular fa-image text-slate-400"
                                        ></i>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold ring-1"
                                        :class="
                                            Number(item.status) === 1
                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                : 'bg-amber-50 text-amber-700 ring-amber-200'
                                        "
                                    >
                                        <i
                                            :class="
                                                Number(item.status) === 1
                                                    ? 'fa-solid fa-circle-check'
                                                    : 'fa-solid fa-pen-to-square'
                                            "
                                            class="text-[10px]"
                                        ></i>
                                        {{
                                            Number(item.status) === 1
                                                ? "Published"
                                                : "Draft"
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700"
                                    >
                                        <i
                                            class="fa-regular fa-eye text-slate-400 text-xs"
                                        ></i>
                                        {{ item.total_view ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold ring-1 bg-black/5 text-black/70 ring-black/10"
                                    >
                                        {{ item.breaking ? "Yes" : "No" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p
                                        class="text-sm font-medium text-slate-600"
                                    >
                                        {{ formatDate(item.created_at) }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.news.edit',
                                                    item.id,
                                                )
                                            "
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200 hover:bg-emerald-100 hover:scale-110 transition"
                                            title="Edit"
                                        >
                                            <i class="fa fa-edit text-xs"></i>
                                        </Link>
                                        <button
                                            @click="deleteNews(item.id)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                            title="Delete"
                                        >
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty (desktop) -->
                <div v-if="!filteredNews.length" class="py-14 px-6 text-center">
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                    >
                        <i
                            class="fa-regular fa-newspaper text-3xl text-slate-400"
                        ></i>
                    </div>
                    <p class="text-lg font-bold text-slate-700 mb-1">
                        {{
                            hasActiveFilters
                                ? "No matching news"
                                : "No news yet"
                        }}
                    </p>
                    <p class="text-sm text-slate-500 mb-4">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters."
                                : "Start by creating your first news article."
                        }}
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-800 text-white font-bold text-sm px-4 py-2 hover:bg-slate-900 transition"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                        Clear filters
                    </button>
                </div>
            </div>

            <!-- ==================== MOBILE CARDS ==================== -->
            <div class="md:hidden space-y-3">
                <div
                    v-for="(item, i) in filteredNews"
                    :key="item.id"
                    class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden"
                >
                    <!-- Image -->
                    <div class="relative">
                        <img
                            v-if="item.image"
                            :src="`/storage/${item.image}`"
                            class="w-full h-40 object-cover"
                            :alt="item.title"
                            @error="
                                (e) => (e.target.src = '/images/default.jpg')
                            "
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="w-full h-40 bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center"
                        >
                            <i
                                class="fa-regular fa-image text-4xl text-slate-400"
                            ></i>
                        </div>

                        <!-- Status badge overlay -->
                        <span
                            class="absolute top-2 right-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 shadow-sm backdrop-blur bg-white/90"
                            :class="
                                Number(item.status) === 1
                                    ? 'text-emerald-700 ring-emerald-200'
                                    : 'text-amber-700 ring-amber-200'
                            "
                        >
                            <i
                                :class="
                                    Number(item.status) === 1
                                        ? 'fa-solid fa-circle-check'
                                        : 'fa-solid fa-pen-to-square'
                                "
                            ></i>
                            {{
                                Number(item.status) === 1
                                    ? "Published"
                                    : "Draft"
                            }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2 text-xs">
                            <span
                                class="inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600 ring-1 ring-slate-200"
                            >
                                #{{ globalIndex(i) }}
                            </span>
                            <span class="text-slate-400">•</span>
                            <span class="font-medium text-slate-500">
                                {{ formatDate(item.created_at) }}
                            </span>
                        </div>

                        <p
                            class="text-sm font-bold text-slate-800 leading-6 line-clamp-3 mb-3"
                        >
                            {{ item.title }}
                        </p>

                        <div
                            class="flex items-center justify-between pt-3 border-t border-slate-100"
                        >
                            <span
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600"
                            >
                                <i class="fa-regular fa-eye text-slate-400"></i>
                                {{ item.total_view ?? 0 }} views
                            </span>

                            <div class="flex items-center gap-2">
                                <Link
                                    :href="route('admin.news.edit', item.id)"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-2.5 py-1.5 text-xs font-bold hover:bg-emerald-100 transition"
                                >
                                    <i class="fa fa-edit text-[10px]"></i>
                                    Edit
                                </Link>
                                <button
                                    @click="deleteNews(item.id)"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 text-red-700 ring-1 ring-red-200 px-2.5 py-1.5 text-xs font-bold hover:bg-red-100 transition"
                                >
                                    <i class="fa fa-trash text-[10px]"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty (mobile) -->
                <div
                    v-if="!filteredNews.length"
                    class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm py-14 px-6 text-center"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                    >
                        <i
                            class="fa-regular fa-newspaper text-3xl text-slate-400"
                        ></i>
                    </div>
                    <p class="text-lg font-bold text-slate-700 mb-1">
                        {{
                            hasActiveFilters
                                ? "No matching news"
                                : "No news yet"
                        }}
                    </p>
                    <p class="text-sm text-slate-500 mb-4">
                        {{
                            hasActiveFilters
                                ? "Try adjusting your filters."
                                : "Start by creating your first news article."
                        }}
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-800 text-white font-bold text-sm px-4 py-2 hover:bg-slate-900 transition"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                        Clear filters
                    </button>
                </div>
            </div>

            <!-- ==================== PAGINATION ==================== -->
            <div
                v-if="props.news?.links?.length > 3"
                class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
            >
                <template v-for="(link, i) in props.news.links" :key="i">
                    <button
                        v-if="link.url"
                        @click="visitPage(link.url)"
                        class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold transition"
                        :class="
                            link.active
                                ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow'
                                : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-blue-50 hover:text-blue-700 hover:ring-blue-300'
                        "
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold text-slate-300 bg-slate-50 ring-1 ring-slate-100"
                        v-html="link.label"
                    />
                </template>
            </div>
        </Layout>
    </div>
</template>

<style scoped>
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

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
