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
    news: Object,
});

const deleteNews = (id) => {
    if (confirm("Are you sure?")) {
        router.delete(route("news.destroy", id));
    }
};

const search = ref(props.search || "");

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
    const keyword = search.value.trim();
    if (!keyword) return text;

    const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return text.replace(regex, '<span class="bg-yellow-300">$1</span>');
}

// search place holder type
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
        <Head title="Add News" />
        <Breadcrumb />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <div>
            <div class="w-[40%] m-auto my-3">
                <SearchFilter
                    v-model="search"
                    :placeholder="animatedPlaceholder"
                    @search="fetchInvoices"
                    @reset="resetSearch"
                />
            </div>
            <table class="min-w-full table-auto border border-gray-400">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Image</th>
                        <th class="px-4 py-2">Title</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2">Language</th>
                        <th class="px-4 py-2">Post Type</th>
                        <th class="px-4 py-2">Post Status</th>
                        <th class="px-4 py-2">Total View</th>
                        <th class="px-4 py-2">Current View</th>
                        <th class="px-4 py-2">Created At</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(item, index) in props.news.data"
                        :key="item.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2 text-center">
                            {{ index + 1 }}
                        </td>
                        <td class="px-4 py-2">
                            <img
                                v-if="item.image"
                                :src="`/storage/${item.image}`"
                                class="w-16 h-12 object-cover rounded"
                            />
                        </td>
                        <td
                            class="px-4 py-2"
                            v-html="highlight(item.title)"
                        ></td>
                        <td class="px-4 py-2">
                            {{ item.category?.name }}
                        </td>
                        <td class="px-4 py-2">
                            {{ item.language == 1 ? "Bangla" : "English" }}
                        </td>
                        <td class="px-4 py-2">
                            <span
                                v-if="item.breaking"
                                class="text-red-600 font-bold"
                            >
                                Breaking
                            </span>
                            <span
                                v-else-if="item.featured"
                                class="text-green-600 font-bold"
                            >
                                Featured
                            </span>
                            <span v-else> Normal </span>
                        </td>
                        <td class="px-4 py-2">
                            <span
                                :class="
                                    item.status
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                {{ item.status ? "Published" : "Draft" }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ item.total_view ?? 0 }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ item.current_view ?? 0 }}
                        </td>
                        <td class="px-4 py-2">
                            {{ new Date(item.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-4 py-2 flex gap-2">
                            <Link
                                :href="route('news.edit', item.id)"
                                class="bg-blue-500 text-white px-3 py-1 rounded"
                            >
                                <i class="fa-solid fa-edit"></i>
                            </Link>

                            <button
                                @click="deleteNews(item.id)"
                                class="bg-red-500 text-white px-3 py-1 rounded"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <Pagination :links="news.links" />
        </div>
    </Layout>
</template>
