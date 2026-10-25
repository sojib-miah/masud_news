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
    reporters: Object,
});

const search = ref(props.search || "");

function fetchInvoices() {
    router.get(
        route("all.reporter"),
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get(
        route("all.reporter"),
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

const placeholderText = "Search by Name, Email, Phone...";
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
        <Head title="Pending News" />
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
            <table
                class="min-w-full table-auto border border-gray-400 bg-white"
            >
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Desination</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">Total Views</th>
                        <th class="px-4 py-2">Total Income</th>
                        <th class="px-4 py-2">Current Views</th>
                        <th class="px-4 py-2">Current Income</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(item, index) in props.reporters.data"
                        :key="item.id"
                        class="border-t"
                    >
                        <td class="px-4 py-2 text-center">{{ index + 1 }}</td>
                        <td
                            class="px-4 py-2 text-center"
                            v-html="highlight(item.name)"
                        ></td>
                        <td class="px-4 py-2 text-center">
                            {{
                                item.reporter_profile?.reporter_types_names ??
                                ""
                            }}
                        </td>
                        <td
                            class="px-4 py-2 text-center"
                            v-html="highlight(item.email)"
                        ></td>
                        <td
                            class="px-4 py-2 text-center"
                            v-html="highlight(item.phone)"
                        ></td>
                        <td class="px-4 py-2 text-center">
                            {{ item.total_views ?? "0" }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ item.total_income ?? "0" }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ item.current_view ?? "0" }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            {{ item.current_income ?? "0" }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <Pagination :links="reporters.links" />
        </div>
    </Layout>
</template>
