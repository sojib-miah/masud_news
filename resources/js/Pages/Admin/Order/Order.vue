<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    carts: Array,
    shops: Array,
    totalBalance: Number,
    stockMoney: Number,
});

const page = usePage();

const form = useForm({
    name: page.props.auth.user?.name || "",
    email: "",
    phone: page.props.auth.user?.phone || "",
    delivery_type: "inside_dhaka",
    shop_id: null,
    payment_method: "",

    use_balance: false,
    use_stock_money: false,
});

const subtotal = () => {
    return props.carts.reduce(
        (sum, c) => sum + c.product.discount_price * c.quantity,
        0,
    );
};

const deliveryCharge = () => {
    if (form.delivery_type === "outside_dhaka") return 100;
    return 0;
};

const total = () => {
    let finalTotal = subtotal() + deliveryCharge();

    // total balance max 30%
    if (form.use_balance) {
        const maxBalanceUse = finalTotal * 0.3;

        const balanceUsed = Math.min(props.totalBalance, maxBalanceUse);

        finalTotal -= balanceUsed;
    }

    // stock money
    if (form.use_stock_money) {
        const stockUsed = Math.min(props.stockMoney, finalTotal);

        finalTotal -= stockUsed;
    }

    return Math.max(finalTotal, 0);
};

const submit = () => {
    form.post(route("order.store"));
};
</script>

<template>
    <Head title="Order Page" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <Breadcrumb />
        <div class="p-8 max-w-4xl mx-auto border rounded shadow-lg bg-white">
            <h1 class="text-2xl font-bold mb-6">Checkout</h1>
            <!-- CUSTOMER -->
            <div class="mb-4">
                <label for="name" class="form-label">Name</label>
                <input
                    v-model="form.name"
                    placeholder="Name"
                    class="border rounded p-2 w-full mb-2"
                />
            </div>
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input
                    v-model="form.email"
                    placeholder="Email"
                    class="border rounded p-2 w-full mb-2"
                />
                <div v-if="form.errors.email" class="text-red-500 text-sm">
                    {{ form.errors.email }}
                </div>
            </div>
            <div class="mb-4">
                <label for="phone" class="form-label">Phone</label>
                <input
                    v-model="form.phone"
                    placeholder="Phone"
                    class="border rounded p-2 w-full mb-4"
                />
            </div>

            <!-- DELIVERY -->
            <div class="mb-4">
                <label class="form-label"
                    ><input
                        type="radio"
                        value="inside_dhaka"
                        v-model="form.delivery_type"
                    />
                    Inside Dhaka (0tk)</label
                >
                <label class="form-label"
                    ><input
                        type="radio"
                        value="outside_dhaka"
                        v-model="form.delivery_type"
                    />
                    Outside Dhaka (100tk)</label
                >
                <label class="form-label"
                    ><input
                        type="radio"
                        value="shop"
                        v-model="form.delivery_type"
                    />
                    Pickup from Shop</label
                >
                <div
                    v-if="form.errors.delivery_type"
                    class="text-red-500 text-sm"
                >
                    {{ form.errors.delivery_type }}
                </div>
            </div>

            <!-- SHOP SELECT -->
            <div v-if="form.delivery_type === 'shop'" class="mb-4">
                <label for="shop_id" class="form-label">Select Shop</label>
                <select
                    v-model="form.shop_id"
                    class="border rounded p-2 w-full"
                >
                    <option value="" selected>Select Shop</option>
                    <option v-for="s in shops" :key="s.id" :value="s.id">
                        {{ s.name }} - {{ s.location }}
                    </option>
                </select>
                <div v-if="form.errors.shop_id" class="text-red-500 text-sm">
                    {{ form.errors.shop_id }}
                </div>
            </div>

            <!-- PAYMENT -->
            <div class="mb-4">
                <label class="form-label">Select Payment Method</label>
                <select
                    v-model="form.payment_method"
                    class="border rounded p-2 w-full"
                >
                    <option value="">Select Payment</option>
                    <option value="cod">Cash on Delivery</option>
                    <option value="bkash">Bkash</option>
                </select>
                <div
                    v-if="form.errors.payment_method"
                    class="text-red-500 text-sm"
                >
                    {{ form.errors.payment_method }}
                </div>
            </div>

            <!-- WALLET OPTIONS -->
            <div class="mb-4 border rounded p-4">
                <label class="flex items-center gap-2 mb-2">
                    <input type="checkbox" v-model="form.use_balance" />

                    Use Balance (Available: ৳{{ totalBalance }})
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" v-model="form.use_stock_money" />

                    Use Stock Money (Available: ৳{{ stockMoney }})
                </label>
            </div>

            <!-- SUMMARY -->
            <div class="bg-gray-100 p-4 rounded mb-4">
                <p>Subtotal: ৳{{ subtotal() }}</p>
                <p>Delivery: ৳{{ deliveryCharge() }}</p>
                <h2 class="font-bold">Total: ৳{{ Math.floor(total()) }}</h2>
            </div>

            <!-- SUBMIT -->
            <button
                @click="submit"
                class="bg-blue-500 text-white px-6 py-2 rounded"
            >
                Place Order
            </button>
        </div>
    </Layout>
</template>
