<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    pathocks: Object,
    search: String,
});

const search = ref(props.search || "");

// fetch
function fetchUsers() {
    router.get(
        "/admin/pathock",
        { search: search.value },
        { preserveState: true },
    );
}

function resetSearch() {
    router.get("/admin/pathock");
}

// highlight
function highlight(text) {
    if (!search.value) return text;

    const regex = new RegExp(`(${search.value})`, "gi");
    return text.replace(regex, '<span class="bg-yellow-300">$1</span>');
}

function deleteUser(id) {
    if (confirm("Are you sure you want to delete?")) {
        router.delete(`/admin/pathock/${id}`);
    }
}
</script>

<template>
    <div>
        <Head title="Pathock Page" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <Layout>
            <Breadcrumb />

            <!-- Search -->
            <div class="flex justify-between items-center mb-4 w-full m-auto">
                <h2 class="text-xl font-bold">Pathock List</h2>
                <div>
                    <SearchFilter
                        v-model="search"
                        @search="fetchUsers"
                        @reset="resetSearch"
                    />
                </div>
                <p class="text-lg">Total Pathocks: {{ pathocks.total }}</p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 border">#</th>
                            <th class="p-3 border">Name</th>
                            <th class="p-3 border">Email</th>
                            <th class="p-3 border">Role</th>
                            <th class="p-3 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(user, index) in pathocks.data"
                            :key="user.id"
                        >
                            <td class="p-3 border">
                                {{ index + 1 }}
                            </td>
                            <td
                                class="p-3 border"
                                v-html="highlight(user.name)"
                            ></td>
                            <td
                                class="p-3 border"
                                v-html="highlight(user.email)"
                            ></td>
                            <td class="p-3 border">
                                {{ user.role }}
                            </td>
                            <td class="p-3 border">
                                <button
                                    @click="deleteUser(user.id)"
                                    class="bg-red-500 text-white px-3 py-1 rounded"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :links="pathocks.links" class="mt-4" />
        </Layout>
    </div>
</template>
