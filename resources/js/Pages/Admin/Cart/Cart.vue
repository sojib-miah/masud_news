<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { route } from "ziggy-js";

import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    carts: { type: Array, default: () => [] },
});

// ==================== SEARCH FILTER ====================
const searchQuery = ref("");

const filteredCarts = computed(() => {
    if (!searchQuery.value) return props.carts;
    const query = searchQuery.value.toLowerCase();
    return props.carts.filter((cart) =>
        cart.product?.name?.toLowerCase().includes(query),
    );
});

// ==================== LOCAL EDITABLE STATE ====================
const localCarts = ref([...props.carts]);

// Sync when backend updates
watch(
    () => props.carts,
    (newVal) => {
        localCarts.value = [...newVal];
    },
);

const form = useForm({
    quantity: 1,
});

const deleteForm = useForm({});
let timeout;

// ==================== UPDATE QUANTITY ====================
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
                cart.quantity = 1; // rollback on error
            },
        });
    }, 500);
};

// ==================== INCREASE / DECREASE ====================
const incrementQty = (cart) => {
    cart.quantity++;
    updateQty(cart);
};

const decrementQty = (cart) => {
    if (cart.quantity > 1) {
        cart.quantity--;
        updateQty(cart);
    }
};

// ==================== REMOVE ITEM ====================
const removeItem = (id) => {
    if (confirm("আপনি কি নিশ্চিত যে এই পণ্যটি কার্ট থেকে সরাতে চান?")) {
        deleteForm.delete(route("cart.destroy", id), {
            preserveScroll: true,
        });
    }
};

// ==================== TOTALS ====================
const grandTotal = computed(() => {
    return props.carts.reduce((total, c) => {
        const price = c.product?.discount_price || 0;
        return total + price * c.quantity;
    }, 0);
});
</script>

<template>
    <Head title="Shopping Cart" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <!-- <Breadcrumb /> -->
        <div class="min-h-screen py-8 px-3 sm:px-6 lg:px-8 font-robo">
            <div class="max-w-6xl mx-auto">
                <!-- Header & Search -->
                <div
                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8"
                >
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-slate-800 flex items-center gap-3"
                    >
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-green-100 text-green-600"
                        >
                            <i class="fa-solid fa-cart-shopping"></i>
                        </span>
                        আপনার কার্ট
                    </h1>

                    <!-- Search Filter -->
                    <div class="relative w-full sm:w-72">
                        <i
                            class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        ></i>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="পণ্য খুঁজুন..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all text-sm"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="props.carts.length === 0"
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center"
                >
                    <div
                        class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 text-slate-400 mb-4"
                    >
                        <i class="fa-solid fa-cart-arrow-down text-3xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-700 mb-2">
                        আপনার কার্ট খালি
                    </h2>
                    <p class="text-slate-500 mb-6">
                        কার্টে কোনো পণ্য নেই। কেনাকাটা শুরু করতে নিচের বাটনে
                        ক্লিক করুন।
                    </p>
                    <Link
                        :href="route('dashboard.pathock')"
                        class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg transition"
                    >
                        কেনাকাটা করুন
                    </Link>
                </div>

                <!-- No Search Results -->
                <div
                    v-else-if="filteredCarts.length === 0"
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center"
                >
                    <i
                        class="fa-solid fa-search text-4xl text-slate-300 mb-3 block"
                    ></i>
                    <h2 class="text-lg font-bold text-slate-700 mb-1">
                        কোনো ফলাফল পাওয়া যায়নি
                    </h2>
                    <p class="text-slate-500">
                        "{{ searchQuery }}" এর জন্য কোনো পণ্য খুঁজে পাওয়া
                        যায়নি।
                    </p>
                    <button
                        @click="searchQuery = ''"
                        class="mt-4 text-green-600 font-semibold hover:underline"
                    >
                        সব পণ্য দেখুন
                    </button>
                </div>

                <!-- Cart Content -->
                <div v-else class="flex flex-col lg:flex-row gap-8">
                    <!-- ==================== ITEMS LIST ==================== -->
                    <div class="w-full lg:w-2/3 flex flex-col gap-4">
                        <div
                            v-for="c in filteredCarts"
                            :key="c.id"
                            class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row items-center sm:items-start gap-4 transition-all hover:shadow-md"
                        >
                            <!-- Image -->
                            <Link
                                :href="
                                    route(
                                        'product.show',
                                        c.product?.slug || c.product?.id,
                                    )
                                "
                                class="w-full sm:w-24 h-48 sm:h-24 flex-shrink-0 rounded-lg overflow-hidden bg-slate-100"
                            >
                                <img
                                    :src="
                                        c.product?.image
                                            ? `/storage/${c.product.image}`
                                            : '/placeholder.png'
                                    "
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                    loading="lazy"
                                    :alt="c.product?.name"
                                    class="w-full h-full object-cover"
                                />
                            </Link>

                            <!-- Info & Controls -->
                            <div
                                class="flex-grow w-full flex flex-col justify-between h-full gap-4"
                            >
                                <div
                                    class="flex justify-between items-start gap-4"
                                >
                                    <div>
                                        <Link
                                            :href="
                                                route(
                                                    'product.show',
                                                    c.product?.slug ||
                                                        c.product?.id,
                                                )
                                            "
                                            class="font-bold text-slate-800 text-base sm:text-lg hover:text-green-600 transition-colors line-clamp-2"
                                        >
                                            {{ c.product?.name }}
                                        </Link>
                                        <p
                                            class="text-green-600 font-bold mt-1"
                                        >
                                            ৳{{ c.product?.discount_price }}
                                        </p>
                                    </div>

                                    <!-- Delete Button (Desktop) -->
                                    <button
                                        @click="removeItem(c.id)"
                                        class="text-red-400 hover:text-red-600 transition-colors p-2 hidden sm:block"
                                        title="সরিয়ে ফেলুন"
                                    >
                                        <i
                                            class="fa-regular fa-trash-can text-lg"
                                        ></i>
                                    </button>
                                </div>

                                <div
                                    class="flex items-center justify-between mt-auto"
                                >
                                    <!-- Quantity Controls -->
                                    <div
                                        class="flex items-center border border-slate-300 rounded-lg overflow-hidden bg-white"
                                    >
                                        <button
                                            @click="decrementQty(c)"
                                            class="px-3 py-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors disabled:opacity-50"
                                            :disabled="c.quantity <= 1"
                                        >
                                            <i
                                                class="fa-solid fa-minus text-xs"
                                            ></i>
                                        </button>
                                        <input
                                            type="number"
                                            v-model.number="c.quantity"
                                            @change="updateQty(c)"
                                            min="1"
                                            class="w-12 text-center py-1.5 border-none focus:ring-0 focus:outline-none text-sm font-semibold text-slate-700"
                                        />
                                        <button
                                            @click="incrementQty(c)"
                                            class="px-3 py-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                                        >
                                            <i
                                                class="fa-solid fa-plus text-xs"
                                            ></i>
                                        </button>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <!-- Subtotal -->
                                        <div class="text-right hidden sm:block">
                                            <p class="text-xs text-slate-400">
                                                সাবটোটাল
                                            </p>
                                            <p class="font-bold text-slate-800">
                                                ৳{{
                                                    (c.product
                                                        ?.discount_price || 0) *
                                                    c.quantity
                                                }}
                                            </p>
                                        </div>

                                        <!-- Delete Button (Mobile) -->
                                        <button
                                            @click="removeItem(c.id)"
                                            class="text-red-400 hover:text-red-600 transition-colors p-2 block sm:hidden"
                                        >
                                            <i
                                                class="fa-regular fa-trash-can text-lg"
                                            ></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== ORDER SUMMARY ==================== -->
                    <div class="w-full lg:w-1/3">
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sticky top-24"
                        >
                            <h2
                                class="text-lg font-bold text-slate-800 mb-4 pb-4 border-b border-slate-100"
                            >
                                অর্ডার সামারি
                            </h2>

                            <div class="space-y-3 mb-6">
                                <div
                                    class="flex justify-between text-slate-600 text-sm"
                                >
                                    <span>সাবটোটাল</span>
                                    <span class="font-semibold"
                                        >৳{{ grandTotal }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-slate-600 text-sm"
                                >
                                    <span>ডেলিভারি চার্জ</span>
                                    <span class="font-semibold text-green-600"
                                        >ফ্রি</span
                                    >
                                </div>
                                <div
                                    class="pt-3 border-t border-slate-100 flex justify-between items-center"
                                >
                                    <span class="font-bold text-slate-800"
                                        >সর্বমোট</span
                                    >
                                    <span
                                        class="font-bold text-xl text-green-600"
                                        >৳{{ grandTotal }}</span
                                    >
                                </div>
                            </div>

                            <Link
                                :href="route('order.create')"
                                class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-green-600/30"
                            >
                                চেকআউট করুন
                                <i class="fa-solid fa-arrow-right"></i>
                            </Link>

                            <Link
                                :href="route('dashboard.pathock')"
                                class="w-full flex items-center justify-center gap-2 text-green-600 font-semibold mt-4 py-2 hover:underline"
                            >
                                <i class="fa-solid fa-arrow-left text-sm"></i>
                                আরও কেনাকাটা করুন
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* Hide arrows from input number */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type="number"] {
    -moz-appearance: textfield;
}
</style>
