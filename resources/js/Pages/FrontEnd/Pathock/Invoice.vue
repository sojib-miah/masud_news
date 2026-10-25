<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";

const props = defineProps({
    order: Object,
    footer: Object,
});

const printInvoice = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Invoice #${order.id}`" />

    <Layout>
        <div class="max-w-5xl mx-auto p-6">
            <!-- TOP -->
            <div class="flex justify-between items-center mb-6 print-hidden">
                <Link
                    href="/pathock/orders"
                    class="bg-gray-500 text-white px-4 py-2 rounded"
                >
                    Back
                </Link>

                <div class="space-x-2">
                    <button
                        @click="printInvoice"
                        class="bg-blue-500 text-white px-4 py-2 rounded"
                    >
                        Print
                    </button>

                    <a
                        :href="route('order.invoice.pdf', order.id)"
                        target="_blank"
                        class="bg-green-500 text-white px-4 py-2 rounded"
                    >
                        Download PDF
                    </a>
                </div>
            </div>

            <!-- INVOICE -->
            <div id="invoice" class="bg-white shadow-lg rounded-xl p-8">
                <!-- HEADER -->
                <div class="flex justify-between border-b pb-6 mb-6">
                    <div>
                        <h1 class="text-4xl font-bold">INVOICE</h1>

                        <p class="text-gray-500 mt-2">
                            Invoice #: #{{ order.id }}
                        </p>

                        <p class="text-gray-500">
                            Date:
                            {{
                                new Date(order.created_at).toLocaleDateString()
                            }}
                        </p>
                    </div>

                    <div class="text-right">
                        <img
                            :src="`/storage/${$page.props.site?.logo}`"
                            alt="logo"
                            class="h-12 mb-5 w-full object-contain"
                        />

                        <p>{{ footer?.ban || "" }}</p>

                        <p>{{ footer?.email || "" }}</p>
                    </div>
                </div>

                <!-- CUSTOMER -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <h3 class="font-bold text-lg mb-2">Billing To</h3>

                        <p>{{ order.name }}</p>

                        <p>{{ order.phone }}</p>

                        <p>{{ order.email }}</p>

                        <p>{{ order.address }}</p>
                    </div>

                    <div class="text-right">
                        <h3 class="font-bold text-lg mb-2">Order Details</h3>

                        <p>
                            <strong>Status:</strong>
                            {{ order.status }}
                        </p>

                        <p>
                            <strong>Payment:</strong>
                            {{ order.payment_method }}
                        </p>

                        <p>
                            <strong>Delivery:</strong>
                            {{ order.delivery_type }}
                        </p>
                    </div>
                </div>

                <!-- PRODUCT TABLE -->
                <div class="overflow-x-auto">
                    <table class="w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-3 text-left">Product</th>

                                <th class="border p-3 text-center">Image</th>

                                <th class="border p-3 text-center">Price</th>

                                <th class="border p-3 text-center">Qty</th>

                                <th class="border p-3 text-right">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="border p-3">
                                    {{ item.product.name }}
                                </td>

                                <td class="border p-3 text-center">
                                    <img
                                        :src="`/storage/${item.product.image}`"
                                        class="w-16 h-16 object-cover rounded mx-auto"
                                    />
                                </td>

                                <td class="border p-3 text-center">
                                    ৳{{ item.price }}
                                </td>

                                <td class="border p-3 text-center">
                                    {{ item.quantity }}
                                </td>

                                <td class="border p-3 text-right">
                                    ৳{{ item.price * item.quantity }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL -->
                <div class="flex justify-end mt-8">
                    <div class="w-80">
                        <div class="flex justify-between border-b py-2">
                            <span>Subtotal</span>

                            <span> ৳{{ order.total }} </span>
                        </div>

                        <div class="flex justify-between border-b py-2">
                            <span>Delivery Charge</span>

                            <span>৳0</span>
                        </div>

                        <div
                            class="flex justify-between py-3 text-xl font-bold"
                        >
                            <span>Grand Total</span>

                            <span> ৳{{ order.total }} </span>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="mt-12 border-t pt-6 text-center text-gray-500">
                    Thank you for your order.
                </div>
            </div>
        </div>
    </Layout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #invoice,
    #invoice * {
        visibility: visible;
    }

    #invoice {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .print-hidden {
        display: none !important;
    }
}
</style>
