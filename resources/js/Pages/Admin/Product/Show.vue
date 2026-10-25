<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    product: Object,
});

const qty = ref(1);

const form = useForm({
    product_id: props.product.id,
    quantity: 1,
});

const addToCart = () => {
    form.quantity = qty.value;

    form.post(route("cart.store"));
};
</script>

<template>
    <Head title="Product Details Page" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <Layout>
        <Breadcrumb />
        <div class="p-8 max-w-5xl mx-auto">
            <div class="grid grid-cols-2 gap-8">
                <!-- IMAGE -->
                <div>
                    <img
                        :src="`/storage/${product.image}`"
                        class="rounded-xl"
                    />
                    <p
                        class="text-gray-600 my-10 leading-7"
                        v-if="product.description"
                    >
                        <span v-html="product.description"></span>
                    </p>
                </div>
                <!-- DETAILS -->
                <div>
                    <h1 class="text-2xl font-bold mb-3">{{ product.name }}</h1>

                    <!-- PRICE -->
                    <div class="mb-4">
                        <span class="text-green-600 text-2xl font-bold">
                            ৳{{ product.discount_price }}
                        </span>

                        <span class="line-through text-gray-400 ml-2">
                            ৳{{ product.price }}
                        </span>
                    </div>

                    <!-- QUANTITY -->
                    <div class="flex items-center gap-3 mb-4">
                        <button
                            @click="qty--"
                            :disabled="qty <= 1"
                            class="px-3 bg-gray-300"
                        >
                            -
                        </button>
                        <span>{{ qty }}</span>
                        <button @click="qty++" class="px-3 bg-gray-300">
                            +
                        </button>
                    </div>

                    <!-- ADD TO CART -->
                    <button
                        @click="addToCart"
                        class="bg-blue-500 text-white px-6 py-2 rounded"
                    >
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>
