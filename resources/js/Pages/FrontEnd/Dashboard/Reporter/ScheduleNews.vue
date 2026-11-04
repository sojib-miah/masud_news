<script setup>
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Pagination from "@/Shared/Pagination.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import { onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    news: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const deleteNews = (id) => {
    if (confirm("Are you sure you want to delete this news?")) {
        router.delete(route("news.destroy", id), {
            preserveScroll: true,
        });
    }
};

const search = ref(props.filters?.search || "");

function fetchInvoices() {
    router.get(
        route("schedule.news"),
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get(
        route("schedule.news"),
        {},
        { preserveState: false, replace: true },
    );
}

// Highlight search keywords
function highlight(text) {
    if (!text) return "";
    const keyword = search.value.trim();
    if (!keyword) return text;

    const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return text.replace(
        regex,
        '<span class="bg-yellow-300 px-0.5 rounded">$1</span>',
    );
}

// animated placeholder
const animatedPlaceholder = ref("");
const placeholderText = "Search by News Title...";
let intervalId;

onMounted(() => {
    let i = 0;
    let typingForward = true;

    intervalId = setInterval(() => {
        if (typingForward) {
            animatedPlaceholder.value = placeholderText.slice(0, i + 1);
            i++;
            if (i === placeholderText.length) typingForward = false;
        } else {
            animatedPlaceholder.value = placeholderText.slice(0, i);
            i--;
            if (i === 0) typingForward = true;
        }
    }, 200);
});

onUnmounted(() => {
    clearInterval(intervalId);
});

// Helper: post type label + style
function postType(item) {
    if (item.breaking)
        return {
            label: "Breaking",
            cls: "bg-red-50 text-red-600 border-red-200",
        };
    if (item.featured)
        return {
            label: "Featured",
            cls: "bg-green-50 text-green-600 border-green-200",
        };
    return {
        label: "Normal",
        cls: "bg-slate-50 text-slate-600 border-slate-200",
    };
}
</script>

<template>
    <Layout>
        <Head title="Scheduled News" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <FlashMessage
            v-if="$page.props.flash?.error"
            :message="$page.props.flash.error"
            type="error"
        />

        <div class="min-h-screen bg-slate-50">
            <div class="space-y-5">
                <!-- ==================== HEADER ==================== -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-xl overflow-hidden"
                >
                    <div
                        class="px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center text-white text-xl"
                            >
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div>
                                <h1
                                    class="text-white text-lg sm:text-2xl font-bold leading-tight"
                                >
                                    Schedule News
                                </h1>
                                <p
                                    class="text-green-100 text-xs sm:text-sm mt-0.5"
                                >
                                    Manage your scheduled news articles
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="route('news')"
                            class="inline-flex items-center justify-center gap-2 bg-white text-green-700 hover:bg-green-50 font-semibold px-4 py-2 rounded-lg shadow transition text-sm w-full sm:w-auto"
                        >
                            <i class="fa-solid fa-plus"></i>
                            Create News
                        </Link>
                    </div>
                </div>

                <!-- ==================== SEARCH ==================== -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 p-3 sm:p-4"
                >
                    <div class="max-w-md">
                        <SearchFilter
                            v-model="search"
                            :placeholder="animatedPlaceholder"
                            @search="fetchInvoices"
                            @reset="resetSearch"
                        />
                    </div>
                </div>

                <!-- ==================== EMPTY STATE ==================== -->
                <div
                    v-if="!props.news.data?.length"
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 p-10 sm:p-16 text-center"
                >
                    <i
                        class="fa-solid fa-newspaper text-4xl text-slate-300 mb-3"
                    ></i>
                    <p class="text-slate-600 font-semibold mb-1">
                        No scheduled news found
                    </p>
                    <p class="text-sm text-slate-500">
                        {{
                            search
                                ? "Try a different search term."
                                : "Start by creating your first news article."
                        }}
                    </p>
                </div>

                <!-- ==================== TABLE (DESKTOP) ==================== -->
                <div
                    v-if="props.news.data?.length"
                    class="hidden lg:block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead
                                class="bg-slate-50 border-b border-slate-200"
                            >
                                <tr
                                    class="text-left text-slate-600 uppercase text-[11px] tracking-wider"
                                >
                                    <th class="px-4 py-3 font-semibold">#</th>
                                    <th class="px-4 py-3 font-semibold">
                                        Image
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Title
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Category
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Language
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Type
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Status
                                    </th>
                                    <th
                                        class="px-4 py-3 font-semibold text-center"
                                    >
                                        Views
                                    </th>
                                    <th class="px-4 py-3 font-semibold">
                                        Created
                                    </th>
                                    <th
                                        class="px-4 py-3 font-semibold text-center"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="(item, index) in props.news.data"
                                    :key="item.id"
                                    class="hover:bg-green-50/40 transition-colors"
                                >
                                    <td class="px-4 py-3 text-slate-500">
                                        {{
                                            (props.news.current_page - 1) *
                                                props.news.per_page +
                                            index +
                                            1
                                        }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <img
                                            v-if="item.image"
                                            :src="`/storage/${item.image}`"
                                            class="w-16 h-12 object-cover rounded-lg border border-slate-200"
                                            alt=""
                                        />
                                        <div
                                            v-else
                                            class="w-16 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400"
                                        >
                                            <i class="fa-regular fa-image"></i>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="font-semibold text-slate-800 line-clamp-2 max-w-[280px]"
                                            v-html="highlight(item.title)"
                                        ></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-block text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded-md"
                                        >
                                            {{ item.category?.name || "—" }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-md',
                                                item.language == 1
                                                    ? 'bg-purple-50 text-purple-700'
                                                    : 'bg-blue-50 text-blue-700',
                                            ]"
                                        >
                                            <i
                                                :class="
                                                    item.language == 1
                                                        ? 'fa-solid fa-language'
                                                        : 'fa-solid fa-globe'
                                                "
                                            ></i>
                                            {{
                                                item.language == 1
                                                    ? "Bangla"
                                                    : "English"
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="[
                                                'inline-block text-xs font-semibold px-2 py-1 rounded-md border',
                                                postType(item).cls,
                                            ]"
                                        >
                                            {{ postType(item).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-md',
                                                item.status
                                                    ? 'bg-green-50 text-green-700'
                                                    : 'bg-amber-50 text-amber-700',
                                            ]"
                                        >
                                            <i
                                                :class="
                                                    item.status
                                                        ? 'fa-solid fa-circle-check'
                                                        : 'fa-solid fa-clock'
                                                "
                                            ></i>
                                            {{
                                                item.status
                                                    ? "Published"
                                                    : "Draft"
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center text-slate-700"
                                    >
                                        <div class="text-xs">
                                            <div>
                                                <span class="text-slate-400"
                                                    >Total:</span
                                                >
                                                {{ item.total_view ?? 0 }}
                                            </div>
                                            <div>
                                                <span class="text-slate-400"
                                                    >Now:</span
                                                >
                                                {{ item.current_view ?? 0 }}
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-slate-600 text-xs whitespace-nowrap"
                                    >
                                        {{
                                            new Date(
                                                item.created_at,
                                            ).toLocaleDateString("en-GB", {
                                                day: "2-digit",
                                                month: "short",
                                                year: "numeric",
                                            })
                                        }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2 justify-center">
                                            <Link
                                                :href="
                                                    route('news.edit', item.id)
                                                "
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition"
                                                title="Edit"
                                            >
                                                <i
                                                    class="fa-solid fa-pen-to-square"
                                                ></i>
                                            </Link>
                                            <button
                                                @click="deleteNews(item.id)"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition"
                                                title="Delete"
                                            >
                                                <i
                                                    class="fa-solid fa-trash"
                                                ></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== CARDS (MOBILE / TABLET) ==================== -->
                <div v-if="props.news.data?.length" class="lg:hidden space-y-3">
                    <div
                        v-for="(item, index) in props.news.data"
                        :key="item.id"
                        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 space-y-3"
                    >
                        <!-- top row -->
                        <div class="flex gap-3">
                            <img
                                v-if="item.image"
                                :src="`/storage/${item.image}`"
                                class="w-20 h-16 object-cover rounded-lg border border-slate-200 flex-shrink-0"
                                alt=""
                            />
                            <div
                                v-else
                                class="w-20 h-16 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 flex-shrink-0"
                            >
                                <i class="fa-regular fa-image"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <span
                                        class="text-xs text-slate-400 font-semibold"
                                    >
                                        #{{
                                            (props.news.current_page - 1) *
                                                props.news.per_page +
                                            index +
                                            1
                                        }}
                                    </span>
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-md',
                                            item.status
                                                ? 'bg-green-50 text-green-700'
                                                : 'bg-amber-50 text-amber-700',
                                        ]"
                                    >
                                        <i
                                            :class="
                                                item.status
                                                    ? 'fa-solid fa-circle-check'
                                                    : 'fa-solid fa-clock'
                                            "
                                        ></i>
                                        {{
                                            item.status ? "Published" : "Draft"
                                        }}
                                    </span>
                                </div>
                                <h3
                                    class="font-semibold text-slate-800 text-sm line-clamp-2 mt-1"
                                    v-html="highlight(item.title)"
                                ></h3>
                            </div>
                        </div>

                        <!-- badges -->
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                class="text-[11px] bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md"
                            >
                                {{ item.category?.name || "—" }}
                            </span>
                            <span
                                :class="[
                                    'text-[11px] font-semibold px-2 py-0.5 rounded-md',
                                    item.language == 1
                                        ? 'bg-purple-50 text-purple-700'
                                        : 'bg-blue-50 text-blue-700',
                                ]"
                            >
                                {{ item.language == 1 ? "Bangla" : "English" }}
                            </span>
                            <span
                                :class="[
                                    'text-[11px] font-semibold px-2 py-0.5 rounded-md border',
                                    postType(item).cls,
                                ]"
                            >
                                {{ postType(item).label }}
                            </span>
                        </div>

                        <!-- meta row -->
                        <div
                            class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100"
                        >
                            <div class="flex gap-3">
                                <span
                                    ><i
                                        class="fa-solid fa-eye text-slate-400 mr-1"
                                    ></i
                                    >{{ item.current_view ?? 0 }}</span
                                >
                                <span
                                    ><i
                                        class="fa-solid fa-chart-line text-slate-400 mr-1"
                                    ></i
                                    >{{ item.total_view ?? 0 }}</span
                                >
                            </div>
                            <span class="whitespace-nowrap">
                                {{
                                    new Date(
                                        item.created_at,
                                    ).toLocaleDateString("en-GB", {
                                        day: "2-digit",
                                        month: "short",
                                        year: "numeric",
                                    })
                                }}
                            </span>
                        </div>

                        <!-- actions -->
                        <div class="flex gap-2 pt-1">
                            <Link
                                :href="route('news.edit', item.id)"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold text-xs py-2 rounded-lg transition"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                                Edit
                            </Link>
                            <button
                                @click="deleteNews(item.id)"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-semibold text-xs py-2 rounded-lg transition"
                            >
                                <i class="fa-solid fa-trash"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ==================== PAGINATION ==================== -->
                <div
                    v-if="props.news.data?.length"
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 p-3 sm:p-4"
                >
                    <Pagination :links="props.news.links" />
                </div>
            </div>
        </div>
    </Layout>
</template>
