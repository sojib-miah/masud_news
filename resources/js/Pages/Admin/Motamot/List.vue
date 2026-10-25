<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";

const props = defineProps({
    motamots: Object,
});
</script>

<template>
    <Head title="Motamot Users" />
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <Layout>
        <Breadcrumb />
        <div class="p-6">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold mb-4">Motamot Users</h1>
                <p class="text-gray-600">
                    Total Motamot Users: {{ motamots.total }}
                </p>
            </div>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">#</th>
                        <th class="p-2 text-left">Name</th>
                        <th class="p-2 text-left">Email</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(user, index) in motamots.data"
                        :key="user.id"
                        class="border-b"
                    >
                        <td class="p-2">{{ index + 1 }}</td>
                        <td class="p-2">{{ user.name }}</td>
                        <td class="p-2">{{ user.email }}</td>
                        <td class="p-2">
                            <Link
                                :href="route('motamot.user', user.id)"
                                class="bg-indigo-500 hover:bg-indigo-600 text-white py-1 px-3 rounded"
                            >
                                View Motamots
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <Pagination :links="motamots.links" class="mt-4" />
        </div>
    </Layout>
</template>
