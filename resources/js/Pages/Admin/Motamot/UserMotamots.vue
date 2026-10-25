<script setup>
import { Head } from "@inertiajs/vue3";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
const props = defineProps({
    user: Object,
    motamots: Array,
});
</script>

<template>
    <Head title="Menu Page" />
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <Layout>
        <Breadcrumb />
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-4">Motamots by {{ user.name }}</h1>
            <ul class="space-y-4">
                <li
                    v-for="motamot in motamots"
                    :key="motamot.id"
                    class="p-4 border rounded shadow-sm"
                >
                    <h2 class="text-xl font-semibold">{{ motamot.subject }}</h2>
                    <p
                        class="mt-2 text-gray-700"
                        v-html="motamot.description"
                    ></p>
                    <div
                        v-if="motamot.images && motamot.images.length"
                        class="mt-2 flex space-x-2"
                    >
                        <img
                            v-for="(img, index) in motamot.images"
                            :key="index"
                            :src="`/storage/${img}`"
                            alt="Motamot image"
                            class="w-32 h-32 object-cover rounded shadow"
                        />
                    </div>

                    <p class="text-sm text-gray-500 mt-1">
                        Created:
                        {{
                            new Date(motamot.created_at).toLocaleDateString(
                                "en-GB",
                            )
                        }}
                    </p>
                </li>
            </ul>
        </div>
    </Layout>
</template>
