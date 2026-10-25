<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({ subscriptions: Object });

function remove(subscription) {
    if (confirm("Delete this subscriptions")) {
        form.delete(`/admin/subscriptions/${subscription.id}`);
    }
}

// search
const page = usePage();
const search = ref(page.props.search || "");

function fetchRoles() {
    router.get(
        "/admin/subscriptions",
        {
            search: search.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function resetSearch() {
    search.value = "";
    selectedRole.value = "";
    router.get(
        "/admin/subscriptions",
        {},
        {
            preserveState: false,
            replace: true,
        },
    );
}
function highlight(text) {
    const keyword = search.value.trim();
    if (!keyword) return text;

    const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return text.replace(regex, '<span class="bg-yellow-500">$1</span>');
}
// search end

// search type
const animatedPlaceholder = ref("");

const placeholderText = "Search subscriptions...";
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
    }, 150);
});

onUnmounted(() => {
    clearInterval(intervalId);
});
</script>

<template>
    <div>
        <Head title="Subscription Page" />
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
                <!-- Role List -->
                <div
                    class="bg-white shadow rounded p-6 w-full h-fit border border-gray-300"
                >
                    <div class="flex justify-between">
                        <h2 class="text-lg font-semibold mb-4 w-1/3">
                            <i class="fa-solid fa-list"></i> All Subscriptions
                        </h2>
                        <div class="w-2/3">
                            <SearchFilter
                                v-model="search"
                                :placeholder="animatedPlaceholder"
                                @search="fetchRoles"
                                @reset="resetSearch"
                            />
                        </div>
                    </div>
                    <table class="min-w-full table-auto border border-gray-400">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(subscription, i) in subscriptions.data"
                                :key="subscription.id"
                                class="border-t"
                            >
                                <td class="px-4 py-2">{{ i + 1 }}</td>
                                <td
                                    class="px-4 py-2"
                                    v-html="highlight(subscription.email)"
                                ></td>
                                <td class="px-4 py-2">
                                    <button
                                        @click="remove(subscription)"
                                        class="text-red-600 hover:underline"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :links="subscriptions.links" />
                </div>
            </div>
        </Layout>
    </div>
</template>
