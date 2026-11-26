<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";
import { ref, computed, watch, onBeforeUnmount } from "vue";
import axios from "axios";

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
function formatDateTime(d) {
    if (!d) return "—";
    try {
        return new Date(d).toLocaleString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    } catch {
        return d;
    }
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

// inside <script setup>
function joinTags(tag) {
    if (!tag) return "";
    if (Array.isArray(tag)) return tag.join(", ");
    try {
        const parsed = JSON.parse(tag);
        if (Array.isArray(parsed)) return parsed.join(", ");
    } catch {}
    return tag;
}

// ==================== MODAL STATE ====================
const showModal = ref(false);
const loadingNews = ref(false);
const loadingAction = ref(false);
const newsDetails = ref(null);
const publishers = ref([]);
const currentNewsId = ref(null);

const isPublished = computed(() => Number(newsDetails.value?.status) === 1);

async function openNewsModal(item) {
    currentNewsId.value = item.id;
    showModal.value = true;
    loadingNews.value = true;
    newsDetails.value = null;

    // Lock body scroll
    document.body.style.overflow = "hidden";

    try {
        const { data } = await axios.get(route("admin.news.show", item.id));
        newsDetails.value = data.news;
        publishers.value = data.publishers ?? [];
    } catch (e) {
        console.error("Failed to load news details", e);
    } finally {
        loadingNews.value = false;
    }
}

function closeModal() {
    showModal.value = false;
    document.body.style.overflow = "";
    // Slight delay so animation can finish
    setTimeout(() => {
        newsDetails.value = null;
        currentNewsId.value = null;
        publishers.value = [];
    }, 200);
}

// Close on Escape
function onKeydown(e) {
    if (e.key === "Escape" && showModal.value) closeModal();
}

if (typeof window !== "undefined") {
    window.addEventListener("keydown", onKeydown);
}
onBeforeUnmount(() => {
    if (typeof window !== "undefined") {
        window.removeEventListener("keydown", onKeydown);
        document.body.style.overflow = "";
    }
});

// ==================== UPDATE STATUS ====================
async function updateStatus(newStatus) {
    if (!newsDetails.value) return;

    loadingAction.value = true;
    try {
        const { data } = await axios.patch(
            route("admin.news.updateStatus", newsDetails.value.id),
            { status: newStatus === 1 ? 1 : 0 },
        );

        // Update local modal state
        newsDetails.value.status = data.news.status;
        newsDetails.value.published_by = data.news.published_by;

        // Also update the table row so the row badge refreshes
        const row = (props.news?.data ?? []).find(
            (n) => n.id === newsDetails.value.id,
        );
        if (row) {
            row.status = data.news.status;
            row.published_by = data.news.published_by;
        }

        // Reload the page silently to refresh counts from server
        router.reload({ only: ["news", "publishedCount", "draftCount"] });
    } catch (e) {
        console.error("Failed to update status", e);
        alert("Failed to update status. Please try again.");
    } finally {
        loadingAction.value = false;
    }
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
                            <option value="schedule">Schedule</option>
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
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-24"
                                >
                                    Image
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Title
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
                                    Schedule
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
                                    <p
                                        class="text-sm font-semibold text-slate-800 leading-6 line-clamp-2 max-w-md"
                                    >
                                        {{ item.title }}
                                    </p>
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
                                        {{ formatDate(item.schedule) }}
                                    </p>
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
                                        <button
                                            @click="openNewsModal(item)"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 text-slate-700 ring-1 ring-slate-200 px-2.5 py-1.5 text-xs font-bold hover:bg-slate-200 transition"
                                        >
                                            <i
                                                class="fa fa-eye text-[10px]"
                                            ></i>
                                            View
                                        </button>
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
                            <span class="font-medium text-slate-500">
                                / Schedule - {{ formatDate(item.schedule) }}
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
        <!-- ============================================================ -->
        <!-- ==================== NEWS DETAILS MODAL ==================== -->
        <!-- ============================================================ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-start sm:items-center justify-center p-2 sm:p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto"
                    @click.self="closeModal"
                >
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="showModal"
                            class="w-full max-w-4xl my-4 sm:my-8 rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 overflow-hidden"
                        >
                            <!-- Modal header -->
                            <div
                                class="sticky top-0 z-10 flex items-center justify-between gap-3 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-4 sm:px-6 py-3 sm:py-4"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wider text-blue-300"
                                    >
                                        <i
                                            class="fa-solid fa-newspaper mr-1"
                                        ></i>
                                        News Details
                                    </p>
                                    <p
                                        class="text-white text-sm font-bold truncate"
                                    >
                                        #{{ newsDetails?.id ?? currentNewsId }}
                                    </p>
                                </div>
                                <button
                                    @click="closeModal"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/20 transition"
                                    title="Close"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <!-- Loading skeleton -->
                            <div v-if="loadingNews" class="p-8 space-y-4">
                                <div
                                    class="h-6 w-3/4 rounded bg-slate-200 animate-pulse"
                                ></div>
                                <div
                                    class="h-4 w-1/2 rounded bg-slate-200 animate-pulse"
                                ></div>
                                <div
                                    class="h-52 w-full rounded-xl bg-slate-200 animate-pulse"
                                ></div>
                                <div class="space-y-2">
                                    <div
                                        class="h-3 rounded bg-slate-200 animate-pulse"
                                    ></div>
                                    <div
                                        class="h-3 rounded bg-slate-200 animate-pulse"
                                    ></div>
                                    <div
                                        class="h-3 w-5/6 rounded bg-slate-200 animate-pulse"
                                    ></div>
                                </div>
                            </div>

                            <!-- Details -->
                            <div
                                v-else-if="newsDetails"
                                class="max-h-[calc(100vh-200px)] overflow-y-auto"
                            >
                                <!-- Hero image -->
                                <div v-if="newsDetails.image" class="relative">
                                    <img
                                        :src="`/storage/${newsDetails.image}`"
                                        :alt="newsDetails.title"
                                        class="w-full h-56 sm:h-72 object-cover"
                                        @error="
                                            (e) =>
                                                (e.target.src =
                                                    '/images/default.jpg')
                                        "
                                    />
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"
                                    ></div>
                                    <div
                                        class="absolute bottom-3 left-4 right-4"
                                    >
                                        <h2
                                            class="text-white text-lg sm:text-2xl font-extrabold leading-tight drop-shadow"
                                        >
                                            {{ newsDetails.imagenote }}
                                        </h2>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="p-5 sm:p-6 border-b border-slate-100"
                                >
                                    <h2
                                        class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-tight"
                                    >
                                        {{ newsDetails.title }}
                                    </h2>
                                </div>

                                <div class="p-4 sm:p-6 space-y-5">
                                    <!-- Status + breaking badges -->
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1"
                                            :class="
                                                Number(newsDetails.status) === 1
                                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                    : 'bg-amber-50 text-amber-700 ring-amber-200'
                                            "
                                        >
                                            <i
                                                :class="
                                                    Number(
                                                        newsDetails.status,
                                                    ) === 1
                                                        ? 'fa-solid fa-circle-check'
                                                        : 'fa-solid fa-pen-to-square'
                                                "
                                            ></i>
                                            {{
                                                Number(newsDetails.status) === 1
                                                    ? "Published"
                                                    : "Draft"
                                            }}
                                        </span>

                                        <span
                                            v-if="newsDetails.breaking"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-red-50 text-red-700 ring-1 ring-red-200 px-3 py-1 text-xs font-bold"
                                        >
                                            <i class="fa-solid fa-bolt"></i>
                                            Breaking
                                        </span>

                                        <span
                                            v-if="newsDetails.featured"
                                            class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 text-violet-700 ring-1 ring-violet-200 px-3 py-1 text-xs font-bold"
                                        >
                                            <i class="fa-solid fa-star"></i>
                                            Featured
                                        </span>

                                        <span
                                            class="ml-auto inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600"
                                        >
                                            <i class="fa-regular fa-eye"></i>
                                            {{ newsDetails.total_view }} views
                                        </span>
                                    </div>

                                    <!-- Meta grid -->
                                    <div
                                        class="grid grid-cols-2 sm:grid-cols-4 gap-3"
                                    >
                                        <!-- Categories & Subcategories -->
                                        <div
                                            class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3 col-span-full"
                                        >
                                            <!-- Categories -->
                                            <div class="mb-3">
                                                <p
                                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5"
                                                >
                                                    Categories
                                                </p>
                                                <div
                                                    class="flex flex-wrap gap-1.5"
                                                >
                                                    <template
                                                        v-if="
                                                            newsDetails?.categories &&
                                                            newsDetails
                                                                .categories
                                                                .length > 0
                                                        "
                                                    >
                                                        <span
                                                            v-for="cat in newsDetails.categories"
                                                            :key="cat.id"
                                                            class="inline-block px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-md border border-indigo-100"
                                                        >
                                                            {{ cat.name }}
                                                        </span>
                                                    </template>
                                                    <!-- Fallback to old single category if pivot is empty -->
                                                    <span
                                                        v-else-if="
                                                            newsDetails?.category
                                                        "
                                                        class="inline-block px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-md border border-indigo-100"
                                                    >
                                                        {{
                                                            newsDetails.category
                                                        }}
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-sm text-slate-400 italic"
                                                        >—</span
                                                    >
                                                </div>
                                            </div>

                                            <!-- Subcategories -->
                                            <div>
                                                <p
                                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5"
                                                >
                                                    Subcategories
                                                </p>
                                                <div
                                                    class="flex flex-wrap gap-1.5"
                                                >
                                                    <template
                                                        v-if="
                                                            newsDetails?.subcategories &&
                                                            newsDetails
                                                                .subcategories
                                                                .length > 0
                                                        "
                                                    >
                                                        <span
                                                            v-for="sub in newsDetails.subcategories"
                                                            :key="sub.id"
                                                            class="inline-block px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-md border border-emerald-100"
                                                        >
                                                            {{ sub.name }}
                                                        </span>
                                                    </template>
                                                    <!-- Fallback to old single subcategory if pivot is empty -->
                                                    <span
                                                        v-else-if="
                                                            newsDetails?.subcategory
                                                        "
                                                        class="inline-block px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-md border border-emerald-100"
                                                    >
                                                        {{
                                                            newsDetails.subcategory
                                                        }}
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-sm text-slate-400 italic"
                                                        >None</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-2.5"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                            >
                                                Schedule
                                            </p>
                                            <p
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                {{
                                                    formatDate(
                                                        newsDetails.schedule,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-2.5"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                            >
                                                Expire
                                            </p>
                                            <p
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                {{
                                                    formatDate(
                                                        newsDetails.expire,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- title -->
                                    <div>
                                        <p
                                            class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2"
                                        >
                                            <i
                                                class="fa-solid fa-heading mr-1"
                                            ></i>
                                            Title
                                        </p>
                                        <div
                                            class="prose prose-sm max-w-none rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3 sm:p-4 text-slate-700 leading-7"
                                        >
                                            {{ newsDetails.title }}
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <p
                                            class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2"
                                        >
                                            <i
                                                class="fa-solid fa-align-left mr-1"
                                            ></i>
                                            Description
                                        </p>
                                        <div
                                            class="prose prose-sm max-w-none rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3 sm:p-4 text-slate-700 leading-7"
                                            v-html="newsDetails.description"
                                        ></div>
                                    </div>

                                    <!-- Tags + metatag -->
                                    <div
                                        v-if="
                                            newsDetails.tag ||
                                            newsDetails.metatag
                                        "
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                                    >
                                        <div
                                            v-if="newsDetails.tag"
                                            class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-2.5"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                            >
                                                Tag
                                            </p>
                                            <p
                                                class="text-sm text-slate-700 break-words"
                                            >
                                                {{ joinTags(newsDetails.tag) }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="newsDetails.metatag"
                                            class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-2.5"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                            >
                                                Meta Tag
                                            </p>
                                            <p
                                                class="text-sm text-slate-700 break-words"
                                            >
                                                {{ newsDetails.metatag }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Created / published info -->
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                                    >
                                        <div
                                            class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-2.5"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                            >
                                                Created At
                                            </p>
                                            <p
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                {{
                                                    formatDateTime(
                                                        newsDetails.created_at,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-2.5"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-wider text-slate-500"
                                            >
                                                Last Updated
                                            </p>
                                            <p
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                {{
                                                    formatDateTime(
                                                        newsDetails.updated_at,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- ==================== UPDATE STATUS PANEL ==================== -->
                                    <div
                                        class="rounded-xl bg-gradient-to-br from-slate-50 to-slate-100 ring-1 ring-slate-200 p-4"
                                    >
                                        <p
                                            class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3"
                                        >
                                            <i
                                                class="fa-solid fa-sliders mr-1 text-emerald-600"
                                            ></i>
                                            Update Status
                                        </p>

                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                                        >
                                            <!-- Status toggle -->
                                            <div>
                                                <label
                                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                                >
                                                    Status
                                                </label>
                                                <div
                                                    class="flex rounded-lg ring-1 ring-slate-300 overflow-hidden"
                                                >
                                                    <button
                                                        type="button"
                                                        @click="updateStatus(1)"
                                                        :disabled="
                                                            loadingAction ||
                                                            isPublished
                                                        "
                                                        class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold transition disabled:cursor-not-allowed"
                                                        :class="
                                                            isPublished
                                                                ? 'bg-emerald-600 text-white'
                                                                : 'bg-white text-slate-600 hover:bg-emerald-50 hover:text-emerald-700'
                                                        "
                                                    >
                                                        <i
                                                            class="fa-solid fa-circle-check"
                                                        ></i>
                                                        Published
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="updateStatus(0)"
                                                        :disabled="
                                                            loadingAction ||
                                                            !isPublished
                                                        "
                                                        class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold transition disabled:cursor-not-allowed"
                                                        :class="
                                                            !isPublished
                                                                ? 'bg-amber-600 text-white'
                                                                : 'bg-white text-slate-600 hover:bg-amber-50 hover:text-amber-700'
                                                        "
                                                    >
                                                        <i
                                                            class="fa-solid fa-pen-to-square"
                                                        ></i>
                                                        Draft
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Publisher info -->
                                            <div>
                                                <label
                                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                                >
                                                    Published By
                                                </label>
                                                <div
                                                    class="flex items-center gap-2 rounded-lg bg-white ring-1 ring-slate-300 px-3 py-2 text-sm"
                                                >
                                                    <i
                                                        class="fa-solid fa-user-shield text-slate-400"
                                                    ></i>
                                                    <span
                                                        class="font-semibold text-slate-700 truncate"
                                                    >
                                                        {{
                                                            publishers.find(
                                                                (u) =>
                                                                    u.id ===
                                                                    newsDetails.published_by,
                                                            )?.name ??
                                                            (newsDetails.published_by
                                                                ? `User #${newsDetails.published_by}`
                                                                : "Not published yet")
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <p
                                            class="mt-2 text-[11px] text-slate-500 italic"
                                        >
                                            <i
                                                class="fa-solid fa-circle-info mr-1"
                                            ></i>
                                            Marking as Published will set you as
                                            the publisher. Reverting to Draft
                                            clears the publisher.
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div
                                        class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-slate-100"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.news.edit',
                                                    newsDetails.id,
                                                )
                                            "
                                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-4 py-2 text-sm font-bold hover:bg-emerald-100 transition"
                                        >
                                            <i class="fa fa-edit"></i>
                                            Edit News
                                        </Link>
                                        <button
                                            @click="closeModal"
                                            class="inline-flex items-center gap-2 rounded-lg bg-slate-100 text-slate-700 ring-1 ring-slate-200 px-4 py-2 text-sm font-bold hover:bg-slate-200 transition"
                                        >
                                            <i class="fa-solid fa-xmark"></i>
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Error state -->
                            <div v-else class="p-8 text-center">
                                <div
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-3"
                                >
                                    <i
                                        class="fa-solid fa-triangle-exclamation text-2xl text-red-500"
                                    ></i>
                                </div>
                                <p class="text-slate-700 font-bold mb-1">
                                    Failed to load news details
                                </p>
                                <p class="text-sm text-slate-500 mb-4">
                                    Please close and try again.
                                </p>
                                <button
                                    @click="closeModal"
                                    class="rounded-lg bg-slate-800 text-white font-bold text-sm px-4 py-2 hover:bg-slate-900 transition"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
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
