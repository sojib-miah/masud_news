<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import { route } from "ziggy-js";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    videos: Object,
    search: { type: String, default: "" },
});

const selectedVideo = ref(null);
const search = ref(props.search || "");

const form = useForm({
    id: null,
    link: "",
});

function editVideo(video) {
    selectedVideo.value = video;
    form.id = video.id;
    form.link = video.link;

    // Scroll form into view on mobile
    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document.getElementById("video-form")?.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
}

function resetForm() {
    selectedVideo.value = null;
    form.reset();
}

function submit() {
    const method = form.id ? "put" : "post";
    const url = form.id
        ? route("admin.video.update", form.id)
        : route("admin.video.store");

    form[method](url, {
        preserveScroll: true,
        onSuccess: resetForm,
    });
}

function deleteVideo(video) {
    if (confirm("Are you sure you want to delete this video?")) {
        useForm({}).delete(route("admin.video.destroy", video.id), {
            preserveScroll: true,
        });
    }
}

// ==================== SEARCH ====================
function fetchVideos() {
    router.get(
        "/admin/video",
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/video", {}, { preserveState: false, replace: true });
}

function highlight(text) {
    const keyword = search.value.trim();
    if (!keyword) return text;

    const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return text.replace(
        regex,
        '<span class="bg-yellow-300 text-slate-900 rounded px-0.5">$1</span>',
    );
}

// ==================== ANIMATED PLACEHOLDER ====================
const animatedPlaceholder = ref("");
const placeholderText = "Search Video...";
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

// Row numbering across pages
function globalIndex(i) {
    const page = props.videos?.current_page ?? 1;
    const perPage = props.videos?.per_page ?? 10;
    return (page - 1) * perPage + i + 1;
}
</script>

<template>
    <div>
        <Head title="Create Video Link" />

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
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-red-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-rose-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-red-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-video mr-1"></i> Media
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Video Management
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Manage YouTube video links shown on the front-end.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <i class="fa-solid fa-database"></i>
                        Total: {{ props.videos?.total ?? 0 }}
                    </div>
                </div>
            </div>

            <!-- ==================== MAIN GRID ==================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- ==================== LIST ==================== -->
                <div
                    class="lg:col-span-2 order-2 lg:order-1 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden"
                >
                    <!-- Header with search -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 px-5 py-4"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-red-500 to-rose-600 text-white shadow"
                            >
                                <i class="fa-solid fa-list text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    All Videos
                                </h2>
                                <p class="text-xs text-slate-500">
                                    {{ props.videos?.total ?? 0 }} video(s)
                                    total
                                </p>
                            </div>
                        </div>

                        <div class="w-full sm:w-64">
                            <SearchFilter
                                v-model="search"
                                :placeholder="animatedPlaceholder"
                                @search="fetchVideos"
                                @reset="resetSearch"
                            />
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="!props.videos?.data?.length"
                        class="p-10 text-center"
                    >
                        <i
                            class="fa-solid fa-video-slash text-4xl text-slate-300 mb-3"
                        ></i>
                        <p class="text-slate-500 font-semibold">
                            No videos found
                        </p>
                        <p class="text-sm text-slate-400">
                            Try a different search or create a new one.
                        </p>
                    </div>

                    <!-- Desktop table -->
                    <div v-else class="hidden md:block overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr
                                    class="bg-slate-50 border-b border-slate-100"
                                >
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-16"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Video Link
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
                                    v-for="(video, i) in props.videos.data"
                                    :key="video.id"
                                    class="hover:bg-slate-50/80 transition-colors"
                                >
                                    <td
                                        class="px-4 py-3 text-sm font-semibold text-slate-600"
                                    >
                                        {{ globalIndex(i) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <a
                                            :href="video.link"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex items-center gap-2 text-sm text-slate-700 hover:text-red-600 transition-colors break-all"
                                        >
                                            <i
                                                class="fa-brands fa-youtube text-red-600 text-base shrink-0"
                                            ></i>
                                            <span
                                                v-html="highlight(video.link)"
                                            ></span>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="flex items-center justify-end gap-1.5"
                                        >
                                            <button
                                                @click="editVideo(video)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                                title="Edit"
                                            >
                                                <i
                                                    class="fa fa-edit text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="deleteVideo(video)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                                title="Delete"
                                            >
                                                <i
                                                    class="fa fa-trash text-xs"
                                                ></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile card list -->
                    <div
                        v-if="props.videos?.data?.length"
                        class="md:hidden divide-y divide-slate-100"
                    >
                        <div
                            v-for="(video, i) in props.videos.data"
                            :key="video.id"
                            class="p-4"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-500">
                                    #{{ globalIndex(i) }}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        @click="editVideo(video)"
                                        class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-50 text-blue-600 ring-1 ring-blue-200"
                                    >
                                        <i class="fa fa-edit text-[10px]"></i>
                                    </button>
                                    <button
                                        @click="deleteVideo(video)"
                                        class="flex h-7 w-7 items-center justify-center rounded-md bg-red-50 text-red-600 ring-1 ring-red-200"
                                    >
                                        <i class="fa fa-trash text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            <a
                                :href="video.link"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-start gap-2 text-sm text-slate-700 hover:text-red-600 break-all"
                            >
                                <i
                                    class="fa-brands fa-youtube text-red-600 text-base shrink-0 mt-0.5"
                                ></i>
                                <span v-html="highlight(video.link)"></span>
                            </a>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.videos?.links?.length > 3"
                        class="flex flex-wrap items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3"
                    >
                        <template
                            v-for="(link, i) in props.videos.links"
                            :key="i"
                        >
                            <button
                                v-if="link.url"
                                @click="
                                    router.visit(link.url, {
                                        preserveScroll: true,
                                        preserveState: true,
                                    })
                                "
                                class="min-w-[34px] h-9 flex items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                                :class="
                                    link.active
                                        ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow'
                                        : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'
                                "
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="min-w-[34px] h-9 flex items-center justify-center rounded-lg px-2 text-xs font-bold text-slate-300 bg-slate-50 ring-1 ring-slate-100"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>

                <!-- ==================== FORM ==================== -->
                <div
                    id="video-form"
                    class="lg:col-span-1 order-1 lg:order-2 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit lg:sticky lg:top-4"
                >
                    <div
                        class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-white shadow"
                            :class="
                                selectedVideo
                                    ? 'bg-gradient-to-br from-blue-500 to-indigo-600'
                                    : 'bg-gradient-to-br from-red-500 to-rose-600'
                            "
                        >
                            <i
                                :class="
                                    selectedVideo
                                        ? 'fa-solid fa-pen'
                                        : 'fa-solid fa-plus'
                                "
                                class="text-sm"
                            ></i>
                        </span>
                        <div class="min-w-0">
                            <h2
                                class="text-base sm:text-lg font-bold text-slate-800 truncate"
                            >
                                {{
                                    selectedVideo
                                        ? "Edit Video"
                                        : "Create Video"
                                }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{
                                    selectedVideo
                                        ? "Update the video link"
                                        : "Add a new video link"
                                }}
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-5 space-y-4">
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                            >
                                <i
                                    class="fa-brands fa-youtube text-red-600 mr-1"
                                ></i>
                                Video Link
                            </label>
                            <TextInput
                                id="link"
                                v-model="form.link"
                                :error="form.errors.link"
                                placeholder="https://youtube.com/watch?v=..."
                                @input="form.errors.link = null"
                            />
                        </div>

                        <!-- Selected preview -->
                        <div
                            v-if="selectedVideo"
                            class="rounded-lg bg-slate-50 p-3 ring-1 ring-slate-200"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1"
                            >
                                Currently Editing
                            </p>
                            <a
                                :href="selectedVideo.link"
                                target="_blank"
                                rel="noopener"
                                class="text-xs text-slate-700 break-all hover:text-red-600 inline-flex items-start gap-1.5"
                            >
                                <i
                                    class="fa-brands fa-youtube text-red-600 shrink-0 mt-0.5"
                                ></i>
                                <span>{{ selectedVideo.link }}</span>
                            </a>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                                :class="
                                    selectedVideo
                                        ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                        : 'from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700'
                                "
                            >
                                <i
                                    v-if="form.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i
                                    v-else
                                    :class="
                                        selectedVideo
                                            ? 'fa-solid fa-check'
                                            : 'fa-solid fa-plus'
                                    "
                                ></i>
                                {{
                                    form.processing
                                        ? "Processing..."
                                        : selectedVideo
                                          ? "Update Video"
                                          : "Create Video"
                                }}
                            </button>

                            <button
                                v-if="selectedVideo"
                                type="button"
                                @click="resetForm"
                                class="rounded-lg bg-slate-100 px-4 text-slate-700 font-semibold text-sm ring-1 ring-slate-200 hover:bg-slate-200 transition"
                                title="Cancel edit"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Layout>
    </div>
</template>

<style scoped>
@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
