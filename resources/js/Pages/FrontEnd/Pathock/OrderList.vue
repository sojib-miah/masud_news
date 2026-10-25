<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import Pagination from "@/Shared/Pagination.vue";

const props = defineProps({
    orders: Object,
});

const showModal = ref(false);
const selectedOrder = ref(null);

const openModal = (order) => {
    selectedOrder.value = order;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <Head title="My Orders" />

    <Layout>
        <div class="max-w-7xl mx-auto p-6">
            <h1 class="text-3xl font-bold mb-6">My Orders</h1>

            <!-- ORDER TABLE -->
            <div class="overflow-x-auto bg-white shadow rounded-lg p-6">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 border">SL</th>
                            <th class="p-3 border">Order ID</th>
                            <th class="p-3 border">Phone</th>
                            <th class="p-3 border">Total</th>
                            <th class="p-3 border">Status</th>
                            <th class="p-3 border">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(order, index) in orders.data"
                            :key="order.id"
                        >
                            <td class="p-3 border">
                                {{ index + 1 }}
                            </td>

                            <td class="p-3 border">#{{ order.id }}</td>

                            <td class="p-3 border">
                                {{ order.phone }}
                            </td>

                            <td class="p-3 border">৳{{ order.total }}</td>

                            <td class="p-3 border">
                                <span
                                    class="px-2 py-1 rounded text-white"
                                    :class="{
                                        'bg-yellow-500':
                                            order.status === 'processing',
                                        'bg-green-500':
                                            order.status === 'delivered',
                                        'bg-red-500':
                                            order.status === 'canceled',
                                    }"
                                >
                                    {{ order.status }}
                                </span>
                            </td>

                            <td class="p-3 border">
                                <button
                                    @click="openModal(order)"
                                    class="bg-blue-500 mr-2 hover:bg-blue-600 text-white px-3 py-1 rounded"
                                >
                                    <i class="fa fa-eye"></i>
                                </button>
                                <Link
                                    :href="route('order.invoice', order.id)"
                                    target="_blank"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded inline-block"
                                >
                                    <i class="fa fa-file-invoice"></i>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination :links="orders.links" class="mt-4" />
            </div>

            <!-- MODAL -->
            <div
                v-if="showModal"
                @click.self="closeModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white w-11/12 md:w-3/4 rounded-xl p-6 max-h-[90vh] overflow-y-auto"
                >
                    <!-- HEADER -->
                    <div class="flex justify-between items-center mb-5">
                        <h2 class="text-2xl font-bold">Order Details</h2>

                        <button
                            @click="closeModal"
                            class="text-red-500 text-xl"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- ORDER INFO -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="border p-4 rounded">
                            <p>
                                <strong>Name:</strong>
                                {{ selectedOrder.name }}
                            </p>

                            <p>
                                <strong>Phone:</strong>
                                {{ selectedOrder.phone }}
                            </p>

                            <p>
                                <strong>Email:</strong>
                                {{ selectedOrder.email ?? "N/A" }}
                            </p>
                        </div>

                        <div class="border p-4 rounded">
                            <p>
                                <strong>Total:</strong>
                                ৳{{ selectedOrder.total }}
                            </p>

                            <p>
                                <strong>Status:</strong>
                                {{ selectedOrder.status }}
                            </p>

                            <p>
                                <strong>Delivery:</strong>
                                {{ selectedOrder.delivery_type }}
                            </p>
                        </div>
                    </div>

                    <!-- PRODUCTS -->
                    <h3 class="text-xl font-bold mb-3">Products</h3>

                    <table class="w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 border">Image</th>
                                <th class="p-2 border">Product</th>
                                <th class="p-2 border">Price</th>
                                <th class="p-2 border">Qty</th>
                                <th class="p-2 border">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="item in selectedOrder.items"
                                :key="item.id"
                            >
                                <td class="p-2 border">
                                    <img
                                        :src="`/storage/${item.product.image}`"
                                        class="w-16 h-16 object-cover rounded"
                                    />
                                </td>

                                <td class="p-2 border">
                                    {{ item.product.name }}
                                </td>

                                <td class="p-2 border">৳{{ item.price }}</td>

                                <td class="p-2 border">
                                    {{ item.quantity }}
                                </td>

                                <td class="p-2 border">
                                    ৳{{ item.price * item.quantity }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </Layout>
</template>
