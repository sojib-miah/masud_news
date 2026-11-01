<script setup>
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { ref, watch, nextTick } from "vue";

const props = defineProps({
    carts: { type: Array, default: () => [] },
    shops: { type: Array, default: () => [] },
    totalBalance: { type: Number, default: 0 },
    stockMoney: { type: Number, default: 0 },
});

const page = usePage();

const form = useForm({
    name: page.props.auth.user?.name || "",
    email: page.props.auth.user?.email || "",
    phone: page.props.auth.user?.phone || "",
    delivery_type: "inside_dhaka",
    shop_id: "",
    payment_method: "",

    use_balance: false,
    use_stock_money: false,
});

// ==================== CALCULATIONS ====================
const subtotal = () => {
    return props.carts.reduce(
        (sum, c) => sum + (c.product?.discount_price || 0) * c.quantity,
        0,
    );
};

const deliveryCharge = () => {
    if (form.delivery_type === "outside_dhaka") return 100;
    return 0; // Inside Dhaka or Shop Pickup
};

const balanceUsed = () => {
    if (!form.use_balance) return 0;
    let currentTotal = subtotal() + deliveryCharge();
    const maxBalanceUse = currentTotal * 0.3; // Max 30% limit
    return Math.min(props.totalBalance, maxBalanceUse);
};

const stockMoneyUsed = () => {
    if (!form.use_stock_money) return 0;
    let currentTotal = subtotal() + deliveryCharge() - balanceUsed();
    return Math.min(props.stockMoney, currentTotal);
};

const total = () => {
    let finalTotal = subtotal() + deliveryCharge();
    finalTotal -= balanceUsed();
    finalTotal -= stockMoneyUsed();
    return Math.max(finalTotal, 0);
};

const submit = () => {
    form.post(route("order.store"), {
        preserveScroll: true,
    });
};

const paymentMethodSelect = ref(null);

watch(
    () => form.errors.payment_method,
    async (error) => {
        if (error) {
            await nextTick();
            paymentMethodSelect.value?.focus();
        }
    },
);
</script>

<template>
    <Head title="Checkout" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <div class="min-h-screen py-8 px-3 sm:px-6 lg:px-8 font-robo">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-slate-800 flex items-center gap-3"
                    >
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-green-100 text-green-600"
                        >
                            <i class="fa-solid fa-truck-fast"></i>
                        </span>
                        চেকআউট
                    </h1>
                    <p class="text-slate-500 mt-2 text-sm">
                        অর্ডার সম্পন্ন করতে নিচের তথ্যগুলো পূরণ করুন
                    </p>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- ==================== LEFT COLUMN: FORM ==================== -->
                    <div class="w-full lg:w-2/3 flex flex-col gap-6">
                        <form
                            @submit.prevent="submit"
                            class="flex flex-col gap-6"
                        >
                            <!-- 1. CUSTOMER INFO -->
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6"
                            >
                                <h2
                                    class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3"
                                >
                                    <i
                                        class="fa-regular fa-user text-green-600"
                                    ></i>
                                    গ্রাহকের তথ্য
                                </h2>

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                >
                                    <div class="sm:col-span-2">
                                        <label
                                            class="block text-sm font-semibold text-slate-700 mb-1"
                                            >পুরো নাম
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            placeholder="আপনার নাম"
                                            class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none"
                                        />
                                        <div
                                            v-if="form.errors.name"
                                            class="text-red-500 text-xs mt-1"
                                        >
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-slate-700 mb-1"
                                            >ফোন নম্বর
                                            <span class="text-red-500"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.phone"
                                            type="text"
                                            placeholder="01XXXXXXXXX"
                                            class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none"
                                        />
                                        <div
                                            v-if="form.errors.phone"
                                            class="text-red-500 text-xs mt-1"
                                        >
                                            {{ form.errors.phone }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-slate-700 mb-1"
                                            >ইমেইল
                                            <span
                                                class="text-slate-400 text-xs font-normal"
                                                >(ঐচ্ছিক)</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            placeholder="example@email.com"
                                            class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none"
                                        />
                                        <div
                                            v-if="form.errors.email"
                                            class="text-red-500 text-xs mt-1"
                                        >
                                            {{ form.errors.email }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. DELIVERY METHOD -->
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6"
                            >
                                <h2
                                    class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3"
                                >
                                    <i
                                        class="fa-solid fa-location-dot text-green-600"
                                    ></i>
                                    ডেলিভারি মাধ্যম
                                </h2>

                                <div class="flex flex-col gap-3">
                                    <!-- Inside Dhaka -->
                                    <label
                                        class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                        :class="
                                            form.delivery_type ===
                                            'inside_dhaka'
                                                ? 'border-green-500 bg-green-50 ring-1 ring-green-500'
                                                : 'border-slate-200 hover:bg-slate-50'
                                        "
                                    >
                                        <input
                                            type="radio"
                                            value="inside_dhaka"
                                            v-model="form.delivery_type"
                                            class="w-4 h-4 text-green-600 focus:ring-green-500 border-slate-300"
                                        />
                                        <div class="ml-3 flex-grow">
                                            <span
                                                class="block text-sm font-bold text-slate-800"
                                                >ঢাকার ভিতরে</span
                                            >
                                            <span
                                                class="block text-xs text-slate-500 mt-0.5"
                                                >ডেলিভারি চার্জ: ফ্রি</span
                                            >
                                        </div>
                                    </label>

                                    <!-- Outside Dhaka -->
                                    <label
                                        class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                        :class="
                                            form.delivery_type ===
                                            'outside_dhaka'
                                                ? 'border-green-500 bg-green-50 ring-1 ring-green-500'
                                                : 'border-slate-200 hover:bg-slate-50'
                                        "
                                    >
                                        <input
                                            type="radio"
                                            value="outside_dhaka"
                                            v-model="form.delivery_type"
                                            class="w-4 h-4 text-green-600 focus:ring-green-500 border-slate-300"
                                        />
                                        <div class="ml-3 flex-grow">
                                            <span
                                                class="block text-sm font-bold text-slate-800"
                                                >ঢাকার বাহিরে</span
                                            >
                                            <span
                                                class="block text-xs text-slate-500 mt-0.5"
                                                >ডেলিভারি চার্জ: ৳১০০</span
                                            >
                                        </div>
                                    </label>

                                    <!-- Pickup -->
                                    <label
                                        class="relative flex items-center p-4 border rounded-xl cursor-pointer transition-all"
                                        :class="
                                            form.delivery_type === 'shop'
                                                ? 'border-green-500 bg-green-50 ring-1 ring-green-500'
                                                : 'border-slate-200 hover:bg-slate-50'
                                        "
                                    >
                                        <input
                                            type="radio"
                                            value="shop"
                                            v-model="form.delivery_type"
                                            class="w-4 h-4 text-green-600 focus:ring-green-500 border-slate-300"
                                        />
                                        <div class="ml-3 flex-grow">
                                            <span
                                                class="block text-sm font-bold text-slate-800"
                                                >দোকান থেকে সংগ্রহ</span
                                            >
                                            <span
                                                class="block text-xs text-slate-500 mt-0.5"
                                                >নিজে এসে পণ্য নিয়ে যান (চার্জ
                                                ফ্রি)</span
                                            >
                                        </div>
                                    </label>
                                </div>
                                <div
                                    v-if="form.errors.delivery_type"
                                    class="text-red-500 text-xs mt-2"
                                >
                                    {{ form.errors.delivery_type }}
                                </div>

                                <!-- Shop Select -->
                                <div
                                    v-if="form.delivery_type === 'shop'"
                                    class="mt-4 animate-[fadeIn_0.3s_ease-in-out]"
                                >
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                        >দোকান নির্বাচন করুন
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        v-model="form.shop_id"
                                        class="w-full border border-slate-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none bg-white"
                                    >
                                        <option value="">
                                            -- একটি দোকান বেছে নিন --
                                        </option>
                                        <option
                                            v-for="s in shops"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.name }} - {{ s.location }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.shop_id"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.shop_id }}
                                    </div>
                                </div>
                            </div>

                            <!-- 3. PAYMENT & WALLET -->
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6"
                            >
                                <h2
                                    class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3"
                                >
                                    <i
                                        class="fa-solid fa-wallet text-green-600"
                                    ></i>
                                    পেমেন্ট ও ওয়ালেট
                                </h2>

                                <div class="mb-6">
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-2"
                                        >পেমেন্ট মাধ্যম
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        ref="paymentMethodSelect"
                                        v-model="form.payment_method"
                                        class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none bg-white font-medium"
                                    >
                                        <option value="">
                                            -- পেমেন্ট মাধ্যম নির্বাচন করুন --
                                        </option>
                                        <option value="cod">
                                            ক্যাশ অন ডেলিভারি (COD)
                                        </option>
                                        <option value="bkash">
                                            বিকাশ (Bkash)
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.payment_method"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.payment_method }}
                                    </div>
                                </div>

                                <!-- Wallet Uses -->
                                <div
                                    class="bg-slate-50 rounded-xl border border-slate-200 p-4 space-y-3"
                                >
                                    <label
                                        class="flex items-start gap-3 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="form.use_balance"
                                            class="w-4 h-4 mt-1 text-green-600 rounded focus:ring-green-500 border-slate-300"
                                        />
                                        <div>
                                            <span
                                                class="block text-sm font-bold text-slate-800"
                                                >ব্যালেন্স ব্যবহার করুন</span
                                            >
                                            <span
                                                class="block text-xs text-slate-500 mt-0.5"
                                                >আপনার ওয়ালেটে আছে: ৳{{
                                                    props.totalBalance
                                                }}
                                                <br /><span
                                                    class="text-[10px] text-orange-500 font-semibold italic"
                                                    >*সর্বোচ্চ বিলের ৩০% পর্যন্ত
                                                    ব্যবহার করা যাবে</span
                                                ></span
                                            >
                                        </div>
                                    </label>

                                    <div
                                        class="border-t border-slate-200"
                                    ></div>

                                    <label
                                        class="flex items-start gap-3 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="form.use_stock_money"
                                            class="w-4 h-4 mt-1 text-green-600 rounded focus:ring-green-500 border-slate-300"
                                        />
                                        <div>
                                            <span
                                                class="block text-sm font-bold text-slate-800"
                                                >স্টক মানি ব্যবহার করুন</span
                                            >
                                            <span
                                                class="block text-xs text-slate-500 mt-0.5"
                                                >আপনার স্টক মানি আছে: ৳{{
                                                    props.stockMoney
                                                }}</span
                                            >
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button (Mobile Only) -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="lg:hidden w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <i
                                    v-if="form.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i v-else class="fa-solid fa-check-circle"></i>
                                {{
                                    form.processing
                                        ? "অর্ডার হচ্ছে..."
                                        : "অর্ডার কনফার্ম করুন"
                                }}
                            </button>
                        </form>
                    </div>

                    <!-- ==================== RIGHT COLUMN: SUMMARY ==================== -->
                    <div class="w-full lg:w-1/3">
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 sticky top-24"
                        >
                            <h2
                                class="text-lg font-bold text-slate-800 mb-4 pb-4 border-b border-slate-100"
                            >
                                অর্ডার সামারি
                            </h2>

                            <!-- Items List -->
                            <div
                                class="max-h-[30vh] overflow-y-auto custom-scroll mb-4 pr-1 space-y-3"
                            >
                                <div
                                    v-for="c in props.carts"
                                    :key="c.id"
                                    class="flex items-center gap-3"
                                >
                                    <div
                                        class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200"
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
                                            alt="product image"
                                            class="w-full h-full object-cover"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div class="flex-grow">
                                        <p
                                            class="text-sm font-semibold text-slate-800 line-clamp-1"
                                        >
                                            {{ c.product?.name }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            ৳{{ c.product?.discount_price }} x
                                            {{ c.quantity }}
                                        </p>
                                    </div>
                                    <div
                                        class="text-sm font-bold text-slate-800"
                                    >
                                        ৳{{
                                            (c.product?.discount_price || 0) *
                                            c.quantity
                                        }}
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div
                                class="space-y-2 mb-4 text-sm border-t border-slate-100 pt-4"
                            >
                                <div
                                    class="flex justify-between text-slate-600"
                                >
                                    <span>সাবটোটাল</span>
                                    <span class="font-semibold text-slate-800"
                                        >৳{{ subtotal() }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-slate-600"
                                >
                                    <span>ডেলিভারি চার্জ</span>
                                    <span class="font-semibold text-slate-800"
                                        >৳{{ deliveryCharge() }}</span
                                    >
                                </div>

                                <!-- Deductions -->
                                <div
                                    v-if="balanceUsed() > 0"
                                    class="flex justify-between text-green-600"
                                >
                                    <span>ব্যালেন্স ছাড়</span>
                                    <span class="font-semibold"
                                        >- ৳{{
                                            Math.floor(balanceUsed())
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="stockMoneyUsed() > 0"
                                    class="flex justify-between text-green-600"
                                >
                                    <span>স্টক মানি ছাড়</span>
                                    <span class="font-semibold"
                                        >- ৳{{
                                            Math.floor(stockMoneyUsed())
                                        }}</span
                                    >
                                </div>
                            </div>

                            <!-- Total -->
                            <div
                                class="border-t border-slate-200 pt-4 mb-6 flex justify-between items-center"
                            >
                                <span class="font-bold text-slate-800"
                                    >সর্বমোট প্রদেয়</span
                                >
                                <span class="font-bold text-xl text-green-600"
                                    >৳{{ Math.floor(total()) }}</span
                                >
                            </div>

                            <!-- Submit Button (Desktop Only) -->
                            <button
                                @click="submit"
                                :disabled="form.processing"
                                class="hidden lg:flex w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition disabled:opacity-50 items-center justify-center gap-2"
                            >
                                <i
                                    v-if="form.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i v-else class="fa-solid fa-check-circle"></i>
                                {{
                                    form.processing
                                        ? "অর্ডার হচ্ছে..."
                                        : "অর্ডার কনফার্ম করুন"
                                }}
                            </button>

                            <p
                                class="text-center text-xs text-slate-400 mt-4 flex items-center justify-center gap-1"
                            >
                                <i class="fa-solid fa-lock text-[10px]"></i>
                                নিরাপদ এবং সুরক্ষিত পেমেন্ট
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* Custom scrollbar for items */
.custom-scroll::-webkit-scrollbar {
    width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
