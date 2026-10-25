<script setup>
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import { onMounted, onUnmounted, ref } from "vue";
import SearchFilter from "@/Shared/SearchFilter.vue";

const props = defineProps({
    search: String,
});

const search = ref(props.search || "");
function fetchMenus() {
    router.get(
        "/admin/menus",
        { search: search.value },
        { preserveState: true, replace: true }
    );
}
function resetSearch() {
    search.value = "";
    router.get("/admin/menus", {}, { preserveState: false, replace: true });
}
function highlight(text) {
    const keyword = search.value.trim();
    if (!keyword) return text;

    const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return text.replace(regex, '<span class="bg-yellow-500">$1</span>');
}

// search place holder type
const animatedPlaceholder = ref("");

const placeholderText = "Search Payment Request...";
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
        <div
            class="bg-white shadow rounded p-6 w-full h-fit border border-gray-300"
        >
            <div class="flex gap-3 justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">
                    <i class="fa-solid fa-list"></i> All Menus
                </h2>
                <div class="w-[50%]">
                    <SearchFilter
                        v-model="search"
                        :placeholder="animatedPlaceholder"
                        @search="fetchMenus"
                        @reset="resetSearch"
                    />
                </div>
                <div>
                <button type="button" class="btn rounded"><i class="fa-solid fa-plus"></i> Add Payment Request</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table
                    class="min-w-full table-auto border border-gray-400 bg-white rounded shadow"
                >
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">Request Date</th>
                            <th class="px-4 py-2 text-left">Request Amount</th>
                            <th class="px-4 py-2 text-left">Account Details</th>
                            <th class="px-4 py-2 text-left">Approve Date</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Approve By</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </Layout>
</template>
