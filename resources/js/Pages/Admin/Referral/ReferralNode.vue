<script setup>
import ReferralNode from "./ReferralNode.vue";

defineOptions({
    name: "ReferralNode",
});

defineProps({
    user: Object,
});
</script>

<template>
    <div class="ml-5 mt-3 border-l-2 border-gray-300 pl-4">
        <!-- USER CARD -->
        <div class="bg-white shadow rounded-lg p-4 mb-3 border">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-lg text-gray-800">
                        {{ user.name }}
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ user.phone }}
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-sm text-gray-500">Total Earning</p>

                    <h2 class="text-xl font-bold text-green-600">
                        ৳{{ user.total_earning }}
                    </h2>
                </div>
            </div>
        </div>

        <!-- CHILD REFERRALS -->
        <div v-if="user.referrals && user.referrals.length">
            <ReferralNode
                v-for="child in user.referrals"
                :key="child.id"
                :user="child"
            />
        </div>
    </div>
</template>
