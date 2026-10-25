<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { route } from "ziggy-js";

import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    carts: Array,
});

// local editable state (IMPORTANT)
const localCarts = ref([...props.carts]);

// sync when backend updates
watch(
    () => props.carts,
    (newVal) => {
        localCarts.value = [...newVal];
    },
);

const form = useForm({
    quantity: 1,
});

let timeout;

// update quantity (debounce + safe)
const updateQty = (cart) => {
    if (cart.quantity < 1) {
        cart.quantity = 1;
        return;
    }

    clearTimeout(timeout);

    timeout = setTimeout(() => {
        form.quantity = cart.quantity;

        form.put(route("cart.update", cart.id), {
            preserveScroll: true,

            onError: () => {
                // rollback (optional)
                cart.quantity = 1;
            },
        });
    }, 400);
};
</script>

<template>
    <Head title="Cart Page" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <Breadcrumb />
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6">Cart</h1>

            <table class="w-full border text-center">
                <tr class="bg-gray-200">
                    <th class="p-2">Name</th>
                    <th class="p-2">Image</th>
                    <th class="p-2">Qty</th>
                    <th class="p-2">Price</th>
                    <th class="p-2">Total</th>
                    <th class="p-2">Action</th>
                </tr>

                <tr v-for="c in carts" :key="c.id">
                    <td class="p-2">{{ c.product.name }}</td>
                    <td class="p-2">
                        <img
                            :src="`/storage/${c.product.image}`"
                            alt=""
                            class="w-16 h-16 object-cover"
                        />
                    </td>

                    <td class="p-2">
                        <input
                            type="number"
                            v-model="c.quantity"
                            @change="updateQty(c)"
                            min="1"
                            class="w-16 p-2 rounded border"
                        />
                    </td>

                    <td class="p-2">৳{{ c.product.discount_price }}</td>

                    <td class="p-2">
                        ৳{{ c.product.discount_price * c.quantity }}
                    </td>

                    <td class="p-2">
                        <button
                            @click="form.delete(route('cart.destroy', c.id))"
                            class="bg-red-500 text-white p-2 rounded"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </table>
            <div class="mt-10 text-end">
                <Link
                    :href="route('order.create')"
                    class="bg-green-500 text-white px-4 py-2 rounded"
                >
                    Checkout
                </Link>
            </div>
        </div>
    </Layout>
</template>
