<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";
import { ref, watch } from "vue";
import Pagination from "@/Shared/Pagination.vue";

const props = defineProps({
    orders: Object,
});

const form = useForm({});

const localOrders = ref([...props.orders.data]);

watch(
    () => props.orders.data,
    (newVal) => {
        localOrders.value = [...newVal];
    },
);

//  update status
const changeStatus = (order) => {
    form.transform(() => ({
        status: order.status,
    })).put(route("orders.updateStatus", order.id), {
        preserveScroll: true,
    });
};

// delete
const deleteOrder = (id) => {
    if (confirm("Delete this order?")) {
        form.delete(route("orders.destroy", id));
    }
};

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
    <Head title="Order List" />
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <Layout>
        <Breadcrumb />
        <div class="p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">All Orders</h1>
                <p class="text-lg">Total Orders : {{ orders.total }}</p>
            </div>

            <table class="w-full border text-center">
                <tr class="bg-gray-200">
                    <th class="p-2">SL</th>
                    <th class="p-2">Name</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Phone</th>
                    <th class="p-2">Shop</th>
                    <th class="p-2">Total</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">Date</th>
                    <th class="p-2">Action</th>
                </tr>

                <tr v-for="(o, i) in localOrders" :key="o.id">
                    <td class="p-2">{{ i + 1 }}</td>
                    <td class="p-2">{{ o.name }}</td>
                    <td class="p-2">{{ o.email ?? "N/A" }}</td>
                    <td class="p-2">{{ o.phone }}</td>
                    <td class="p-2">{{ o.shop?.name ?? "N/A" }}</td>
                    <td class="p-2">৳{{ o.total }}</td>

                    <!-- STATUS SELECT -->
                    <td class="p-2">
                        <select
                            v-model="o.status"
                            @change="changeStatus(o)"
                            class="border p-1 rounded"
                        >
                            <option value="processing">Processing</option>
                            <option value="delivered">Delivered</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </td>
                    <td class="p-2">
                        {{ new Date(o.created_at).toLocaleDateString() }}
                    </td>

                    <!--  DELETE -->
                    <td class="p-2">
                        <button
                            @click="openModal(o)"
                            class="bg-blue-500 text-white px-2 py-1 rounded mr-2"
                        >
                            <i class="fa fa-eye"></i>
                        </button>
                        <button
                            @click="deleteOrder(o.id)"
                            class="bg-red-500 text-white px-2 py-1 rounded"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </table>
            <pagination :links="orders.links" class="mt-4" />
        </div>
    </Layout>

    <!-- MODAL -->
    <div
        v-if="showModal"
        @click.self="closeModal"
        class="fixed inset-0 bg-black/50 bg-opacity-50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white w-11/12 md:w-3/4 rounded-lg p-6 max-h-[90vh] overflow-y-auto"
        >
            <!-- HEADER -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Order Details</h2>

                <button @click="closeModal" class="text-red-500 text-xl">
                    ✕
                </button>
            </div>

            <!-- ORDER INFO -->
            <div class="mb-6 border p-4 rounded">
                <h3 class="font-bold mb-2">Customer Info</h3>

                <p><strong>Name:</strong> {{ selectedOrder.name }}</p>
                <p><strong>Phone:</strong> {{ selectedOrder.phone }}</p>
                <p><strong>Email:</strong> {{ selectedOrder.email }}</p>
                <p><strong>Status:</strong> {{ selectedOrder.status }}</p>
                <p><strong>Total:</strong> ৳{{ selectedOrder.total }}</p>
                <p>
                    <strong>Delivery:</strong>
                    {{ selectedOrder.delivery_type }}
                </p>
            </div>

            <!-- PRODUCTS -->
            <div>
                <h3 class="font-bold text-lg mb-3">Ordered Products</h3>

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
                        <tr v-for="item in selectedOrder.items" :key="item.id">
                            <td class="p-2 border">
                                <img
                                    :src="`/storage/${item.product.image}`"
                                    class="w-16 h-16 object-cover"
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
</template>
