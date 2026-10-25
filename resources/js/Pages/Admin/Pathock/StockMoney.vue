<script setup>
import { ref, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    stocks: Object,
});

const localOrders = ref([...props.stocks.data]);

watch(
    () => props.stocks.data,
    (newVal) => {
        localOrders.value = [...newVal];
    },
);

const form = useForm({
    status: "",
});

// update status
const changeStatus = (order) => {
    form.status = order.status;

    form.post(`/admin/stock-money/update-status/${order.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            console.log("Status updated");
        },
    });
};
</script>

<template>
    <Head title="Stock Money Page" />
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <Layout>
        <Breadcrumb />
        <div class="overflow-x-auto bg-white shadow rounded-lg p-4">
            <table class="min-w-full border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-2 border">SL</th>
                        <th class="p-2 border">User</th>
                        <th class="p-2 border">Amount</th>
                        <th class="p-2 border">Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(item, index) in stocks.data" :key="item.id">
                        <td class="p-2 border">{{ index + 1 }}</td>
                        <td class="p-2 border">{{ item.user.name }}</td>
                        <td class="p-2 border">{{ item.amount }}</td>

                        <td class="p-2 border">
                            <select
                                class="border rounded p-1"
                                v-model="item.status"
                                @change="changeStatus(item)"
                            >
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <Pagination :links="stocks.links" class="mt-4" />
        </div>
    </Layout>
</template>
