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
}

function resetForm() {
    selectedVideo.value = null;
    form.reset();
}

function submit() {
    const method = form.id ? "put" : "post";
    const url = form.id
        ? route("admin.video.update", form.id) // ✅ update route
        : route("admin.video.store"); // ✅ create route

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

// search start
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

    return text.replace(regex, '<span class="bg-yellow-500">$1</span>');
}

// type start
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
</script>

<template>
    <div>
        <Head title="Create Video Page" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <Layout>
            <Breadcrumb />
            <div
                class="flex justify-between gap-3 font-robo border border-gray-300 p-3 shadow-sm rounded"
            >
                <div
                    class="bg-white shadow rounded p-6 w-2/3 h-fit border border-gray-300"
                >
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold w-1/3">
                            <i class="fa-solid fa-list"></i> All Videos
                        </h2>
                        <div class="w-2/3">
                            <SearchFilter
                                v-model="search"
                                :placeholder="animatedPlaceholder"
                                @search="fetchVideos"
                                @reset="resetSearch"
                            />
                        </div>
                    </div>
                    <table class="min-w-full table-auto border border-gray-400">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Link</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(video, i) in videos.data"
                                :key="video.id"
                                class="border-t"
                            >
                                <td class="px-4 py-2">{{ i + 1 }}</td>
                                <td
                                    class="px-4 py-2"
                                    v-html="highlight(video.link)"
                                ></td>
                                <td class="px-4 py-2">
                                    <button
                                        @click="editVideo(video)"
                                        class="text-blue-600 hover:underline"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button
                                        @click="deleteVideo(video)"
                                        class="text-red-600 hover:underline ml-2"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right: Form -->
                <div
                    class="bg-white shadow rounded p-6 w-1/3 h-fit border border-gray-300"
                >
                    <h2 class="text-lg font-bold mb-4">
                        <i
                            :class="
                                selectedVideo ? 'fa fa-pencil' : 'fa fa-plus'
                            "
                        ></i>
                        {{ selectedVideo ? "Edit" : "Create" }} Video
                    </h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="mb-4">
                            <TextInput
                                id="link"
                                label="Link"
                                v-model="form.link"
                                :error="form.errors.link"
                                placeholder="Link..."
                                @input="form.errors.link = null"
                            />
                        </div>
                        <div class="flex gap-2 justify-end border-t pt-5">
                            <button
                                type="submit"
                                class="btn rounded-sm fw-normal flex justify-center items-center gap-4"
                                :class="{ 'btn-spinner': form.processing }"
                                :disabled="form.processing"
                            >
                                <span>{{
                                    form.processing ? "Processing..." : "Save"
                                }}</span>
                                <span
                                    v-if="form.processing"
                                    class="spinner"
                                ></span>
                            </button>
                            <button
                                type="button"
                                @click="resetForm"
                                class="btn-danger"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Layout>
    </div>
</template>
