<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import ReferralNode from "./ReferralNode.vue";
import Pagination from "@/Shared/Pagination.vue";

const props = defineProps({
    users: Object,
});
</script>

<template>
    <Head title="Referral Tree" />

    <Layout>
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-6">Referral Tree</h1>

            <div v-for="user in users.data" :key="user.id" class="mb-6">
                <ReferralNode :user="user" />
            </div>

            <Pagination :links="users.links" />
        </div>
    </Layout>
</template>
