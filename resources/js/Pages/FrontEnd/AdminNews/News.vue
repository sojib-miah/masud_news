<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";

defineProps({
    news: Object,
});

function deleteNews(id) {
    if (confirm("Delete this news?")) {
        router.delete(route("admin.news.delete", id));
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

            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">All News</h1>
                <div class="flex items-center gap-4">
                    <p class="text-lg mb-0">Total News: {{ news.total }}</p>
                    <Link
                        :href="route('admin.news.create')"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        <i class="fa fa-plus"></i> Create News
                    </Link>
                </div>
            </div>

            <table
                class="min-w-full table-auto border border-gray-400 bg-white rounded shadow-lg"
            >
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-left">Image</th>
                        <th class="px-4 py-2 text-left">status</th>
                        <th class="px-4 py-2 text-left">Total View</th>
                        <th class="px-4 py-2 text-left">Created At</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, i) in news.data" :key="item.id">
                        <td class="px-4 py-2 text-left">{{ i + 1 }}</td>
                        <td class="px-4 py-2 text-left leading-7">
                            {{ item.title }}
                        </td>

                        <td class="px-4 py-2 text-left">
                            <img
                                v-if="item.image"
                                :src="'/storage/' + item.image"
                                width="60"
                            />
                        </td>

                        <td class="px-4 py-2 text-left">
                            {{ item.status == 1 ? "Published" : "Draft" }}
                        </td>
                        <td class="px-4 py-2 text-left">
                            {{ item.total_view }}
                        </td>
                        <td class="px-4 py-2 text-left">
                            {{ new Date(item.created_at).toLocaleDateString() }}
                        </td>

                        <td class="p-3">
                            <div class="flex gap-2">
                                <Link
                                    :href="route('admin.news.edit', item.id)"
                                    class="bg-green-600 text-white px-2 py-1 rounded"
                                >
                                    <i class="fa fa-edit"></i>
                                </Link>

                                <button
                                    @click="deleteNews(item.id)"
                                    class="bg-red-600 text-white px-2 py-1 rounded"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <Pagination :links="news.links" />
        </Layout>
    </div>
</template>
