<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";

defineProps({
    referrals: Array,
});
</script>

<template>
    <Layout>
        <Head title="My Referrals" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">My Referrals</h1>

            <div
                v-if="referrals.length"
                class="overflow-x-auto bg-white shadow rounded-lg"
            >
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="p-3 border">#</th>
                            <th class="p-3 border">Name</th>
                            <th class="p-3 border">Phone</th>
                            <th class="p-3 border">Referral ID</th>
                            <th class="p-3 border">Total Earning</th>
                            <th class="p-3 border">Joined</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(user, index) in referrals"
                            :key="user.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="p-3 border">
                                {{ index + 1 }}
                            </td>

                            <td class="p-3 border">
                                {{ user.name }}
                            </td>

                            <td class="p-3 border">
                                {{ user.phone }}
                            </td>

                            <td class="p-3 border">
                                {{ user.ref_id }}
                            </td>

                            <td class="p-3 border">
                                ৳{{ user.total_earning }}
                            </td>

                            <td class="p-3 border">
                                {{ user.created_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-else
                class="bg-white p-6 rounded shadow text-center text-gray-500"
            >
                No referrals found
            </div>
        </div>
    </Layout>
</template>
