<script setup>
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import Pagination from "@/Shared/Pagination.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import { onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
    news: { type: Object, required: true },
    search: { type: String, default: "" },
});

// ==================== ACTIONS ====================
const deleteNews = (id) => {
    if (confirm("Are you sure you want to delete this news?")) {
        router.delete(route("news.destroy", id), {
            preserveScroll: true,
        });
    }
};

// ==================== SEARCH ====================
const search = ref(props.search || "");

function fetchInvoices() {
    router.get(
        route("breaking.news"),
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get(
        route("breaking.news"),
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
        '<span class="bg-yellow-200 text-slate-900 px-1 rounded">$1</span>',
    );
}

// ==================== ANIMATED PLACEHOLDER ====================
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
            if (i === placeholderText.length) {
                typingForward = false;
            }
        } else {
            animatedPlaceholder.value = placeholderText.slice(0, i);
            i--;
            if (i === 0) {
                typingForward = true;
            }
        }
    }, 200);
});

onUnmounted(() => {
    clearInterval(intervalId);
});
</script>

<template>
    <Layout>
        <Head title="Breaking News" />

        <div class="font-robo pb-10">
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

            <!-- ==================== HEADER ==================== -->
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-6"
            >
                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-slate-800 flex items-center gap-3"
                    >
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-100 text-red-600"
                        >
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                        Breaking News List
                    </h1>
                    <p class="text-slate-500 mt-2 text-sm">
                        আপনার তৈরি জরুরি (Breaking) সংবাদের তালিকা।
                    </p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <Link
                        :href="route('news.all')"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg transition-all"
                    >
                        <i class="fa-solid fa-list"></i> View All News
                    </Link>
                </div>
            </div>

            <!-- ==================== SEARCH & STATS BAR ==================== -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4"
            >
                <div class="w-full sm:w-1/2 lg:w-1/3">
                    <SearchFilter
                        v-model="search"
                        :placeholder="animatedPlaceholder"
                        @search="fetchInvoices"
                        @reset="resetSearch"
                    />
                </div>
                <div
                    class="flex items-center gap-2 text-sm font-semibold text-slate-600 bg-slate-50 px-4 py-2 rounded-lg border border-slate-200 w-full sm:w-auto justify-center"
                >
                    <i class="fa-solid fa-bolt text-red-500"></i>
                    Total Breaking News :
                    <span class="text-slate-900">{{ props.news.total }}</span>
                </div>
            </div>

            <!-- ==================== DESKTOP TABLE (hidden on mobile) ==================== -->
            <div
                class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
            >
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider border-b border-slate-200"
                        >
                            <th class="px-4 py-4 font-semibold">News</th>
                            <th class="px-4 py-4 font-semibold text-center">
                                Category
                            </th>
                            <th class="px-4 py-4 font-semibold text-center">
                                Language
                            </th>
                            <th class="px-4 py-4 font-semibold text-center">
                                Type
                            </th>
                            <th class="px-4 py-4 font-semibold text-center">
                                Status
                            </th>
                            <th class="px-4 py-4 font-semibold text-center">
                                Views
                            </th>
                            <th class="px-4 py-4 font-semibold text-center">
                                Date
                            </th>
                            <th class="px-4 py-4 font-semibold text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(item, index) in props.news.data"
                            :key="item.id"
                            class="hover:bg-slate-50/50 transition-colors group"
                        >
                            <!-- Title & Image -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-16 h-12 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200"
                                    >
                                        <img
                                            v-if="item.image"
                                            :src="`/storage/${item.image}`"
                                            class="w-full h-full object-cover"
                                        />
                                        <i
                                            v-else
                                            class="fa-regular fa-image text-slate-300 w-full h-full flex items-center justify-center"
                                        ></i>
                                    </div>
                                    <div>
                                        <p
                                            class="font-bold text-slate-800 text-sm line-clamp-2 leading-snug"
                                            v-html="highlight(item.title)"
                                        ></p>
                                        <p
                                            class="text-[10px] text-slate-400 mt-1"
                                        >
                                            #{{
                                                (news.current_page - 1) *
                                                    news.per_page +
                                                index +
                                                1
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-4 py-4 text-center">
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-semibold"
                                >
                                    {{ item.category?.name || "N/A" }}
                                </span>
                            </td>

                            <!-- Language -->
                            <td
                                class="px-4 py-4 text-center text-sm font-medium text-slate-600"
                            >
                                {{ item.language == 1 ? "বাংলা" : "English" }}
                            </td>

                            <!-- Post Type -->
                            <td class="px-4 py-4 text-center">
                                <span
                                    v-if="item.breaking"
                                    class="inline-flex px-2 py-0.5 rounded bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wide"
                                >
                                    Breaking
                                </span>
                                <span
                                    v-else-if="item.featured"
                                    class="inline-flex px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wide"
                                >
                                    Featured
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-slate-400 font-medium"
                                    >Normal</span
                                >
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4 text-center">
                                <span
                                    :class="
                                        item.status
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-orange-100 text-orange-700'
                                    "
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold"
                                >
                                    <span
                                        class="w-1.5 h-1.5 rounded-full"
                                        :class="
                                            item.status
                                                ? 'bg-green-500'
                                                : 'bg-orange-500'
                                        "
                                    ></span>
                                    {{ item.status ? "Published" : "Draft" }}
                                </span>
                            </td>

                            <!-- Views (Total & Current) -->
                            <td class="px-4 py-4 text-center">
                                <div
                                    class="flex flex-col items-center justify-center gap-1 text-[11px]"
                                >
                                    <span
                                        class="font-bold text-slate-700 flex items-center gap-1"
                                        title="Total Views"
                                    >
                                        <i
                                            class="fa-solid fa-chart-line text-slate-400"
                                        ></i>
                                        {{ item.total_view ?? 0 }}
                                    </span>
                                    <span
                                        class="text-slate-500 flex items-center gap-1"
                                        title="Current Views"
                                    >
                                        <i
                                            class="fa-solid fa-eye text-slate-300"
                                        ></i>
                                        {{ item.current_view ?? 0 }}
                                    </span>
                                </div>
                            </td>

                            <!-- Date -->
                            <td
                                class="px-4 py-4 text-center text-xs text-slate-500 font-medium whitespace-nowrap"
                            >
                                {{
                                    new Date(
                                        item.created_at,
                                    ).toLocaleDateString("en-GB", {
                                        day: "numeric",
                                        month: "short",
                                        year: "numeric",
                                    })
                                }}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-4 text-right">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <Link
                                        :href="route('news.edit', item.id)"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-colors"
                                        title="Edit"
                                    >
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </Link>
                                    <button
                                        @click="deleteNews(item.id)"
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white flex items-center justify-center transition-colors"
                                        title="Delete"
                                    >
                                        <i
                                            class="fa-solid fa-trash text-xs"
                                        ></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ==================== MOBILE CARDS (hidden on desktop) ==================== -->
            <div class="md:hidden space-y-4">
                <div
                    v-for="(item, index) in props.news.data"
                    :key="item.id"
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                >
                    <!-- Mobile Card Header -->
                    <div
                        class="p-4 flex gap-3 items-start border-b border-slate-100"
                    >
                        <div
                            class="w-20 h-16 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200"
                        >
                            <img
                                v-if="item.image"
                                :src="`/storage/${item.image}`"
                                class="w-full h-full object-cover"
                            />
                            <i
                                v-else
                                class="fa-regular fa-image text-slate-300 w-full h-full flex items-center justify-center"
                            ></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p
                                class="font-bold text-slate-800 text-sm leading-snug line-clamp-2"
                                v-html="highlight(item.title)"
                            ></p>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span
                                    v-if="item.breaking"
                                    class="text-[10px] font-bold text-red-600 bg-red-50 px-1.5 py-0.5 rounded"
                                    >Breaking</span
                                >
                                <span
                                    v-else-if="item.featured"
                                    class="text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded"
                                    >Featured</span
                                >
                                <span
                                    class="text-[10px] font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded"
                                    >{{ item.category?.name }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Card Body -->
                    <div
                        class="p-4 grid grid-cols-2 gap-y-3 text-sm bg-slate-50/50"
                    >
                        <div>
                            <span
                                class="block text-[11px] font-semibold text-slate-400 mb-0.5"
                                >Status</span
                            >
                            <span
                                :class="
                                    item.status
                                        ? 'text-green-600'
                                        : 'text-orange-600'
                                "
                                class="font-bold flex items-center gap-1"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full"
                                    :class="
                                        item.status
                                            ? 'bg-green-600'
                                            : 'bg-orange-600'
                                    "
                                ></span>
                                {{ item.status ? "Published" : "Draft" }}
                            </span>
                        </div>
                        <div>
                            <span
                                class="block text-[11px] font-semibold text-slate-400 mb-0.5"
                                >Language</span
                            >
                            <span class="font-bold text-slate-700">{{
                                item.language == 1 ? "বাংলা" : "English"
                            }}</span>
                        </div>
                        <div>
                            <span
                                class="block text-[11px] font-semibold text-slate-400 mb-0.5"
                                >Total Views</span
                            >
                            <span class="font-bold text-slate-700"
                                ><i
                                    class="fa-solid fa-chart-line text-[10px] text-slate-400 mr-1"
                                ></i
                                >{{ item.total_view ?? 0 }}</span
                            >
                        </div>
                        <div>
                            <span
                                class="block text-[11px] font-semibold text-slate-400 mb-0.5"
                                >Date</span
                            >
                            <span class="font-bold text-slate-700">{{
                                new Date(item.created_at).toLocaleDateString(
                                    "en-GB",
                                    {
                                        day: "numeric",
                                        month: "short",
                                        year: "numeric",
                                    },
                                )
                            }}</span>
                        </div>
                    </div>

                    <!-- Mobile Card Actions -->
                    <div
                        class="p-3 border-t border-slate-100 flex justify-end gap-2 bg-white"
                    >
                        <Link
                            :href="route('news.edit', item.id)"
                            class="flex-1 flex items-center justify-center gap-1.5 bg-blue-50 text-blue-600 font-semibold py-2 rounded-lg text-xs hover:bg-blue-100 transition-colors"
                        >
                            <i class="fa-solid fa-pen"></i> Edit
                        </Link>
                        <button
                            @click="deleteNews(item.id)"
                            class="flex-1 flex items-center justify-center gap-1.5 bg-red-50 text-red-600 font-semibold py-2 rounded-lg text-xs hover:bg-red-100 transition-colors"
                        >
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- ==================== EMPTY STATE ==================== -->
            <div
                v-if="props.news.data.length === 0"
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center mt-4"
            >
                <div
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 text-slate-300 mb-4"
                >
                    <i class="fa-solid fa-bolt text-4xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-1">
                    No Breaking News Available
                </h3>
                <p class="text-slate-500 text-sm">
                    No breaking news found matching your search criteria.
                </p>
                <button
                    @click="resetSearch"
                    class="mt-4 text-green-600 font-bold hover:underline"
                >
                    View All Breaking News
                </button>
            </div>

            <!-- ==================== PAGINATION ==================== -->
            <div
                v-if="props.news.links && props.news.data.length > 0"
                class="mt-8"
            >
                <Pagination :links="news.links" />
            </div>
        </div>
    </Layout>
</template>
