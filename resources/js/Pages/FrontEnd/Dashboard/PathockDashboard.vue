<script setup>
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    products: { type: Array, default: () => [] },
    wallets: { type: Object, default: () => ({}) },
    stockMoney: { type: [String, Number], default: "0" },
});

const page = usePage();

const affiliateLink = computed(() => page.props.affiliate_link ?? "");

const copied = ref(false);
function copyLink() {
    navigator.clipboard
        .writeText(affiliateLink.value)
        .then(() => {
            copied.value = true;
            setTimeout(() => (copied.value = false), 1800);
        })
        .catch(() => alert("Failed to copy link"));
}

function shareWhatsApp() {
    const url = `https://wa.me/?text=${encodeURIComponent(affiliateLink.value)}`;
    window.open(url, "_blank");
}

function shareFacebook() {
    const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(affiliateLink.value)}`;
    window.open(url, "_blank");
}

// Wallet cards data-driven
const walletCards = computed(() => [
    {
        label: "Available Wallet",
        value: props.wallets.balance ?? 0,
        icon: "fa-solid fa-wallet",
        gradient: "from-indigo-500 to-indigo-700",
        ring: "ring-indigo-300",
    },
    {
        label: "Total Income",
        value: props.wallets.total_income ?? 0,
        icon: "fa-solid fa-money-bill-trend-up",
        gradient: "from-purple-500 to-purple-700",
        ring: "ring-purple-300",
    },
    {
        label: "Daily Return Wallet",
        value: props.wallets.daily_return ?? 0,
        icon: "fa-solid fa-money-bill-wave",
        gradient: "from-teal-500 to-teal-700",
        ring: "ring-teal-300",
    },
    {
        label: "Gift Wallet",
        value: props.wallets.gift_wallet ?? 0,
        icon: "fa-solid fa-gift",
        gradient: "from-emerald-500 to-emerald-700",
        ring: "ring-emerald-300",
    },
    {
        label: "Marketing Wallet",
        value: props.wallets.marketing_wallet ?? 0,
        icon: "fa-solid fa-bullhorn",
        gradient: "from-sky-500 to-sky-700",
        ring: "ring-sky-300",
    },
    {
        label: "রিসেলার ডিলার স্টক ভেলু",
        value: props.stockMoney,
        icon: "fa-solid fa-warehouse",
        gradient: "from-orange-500 to-orange-700",
        ring: "ring-orange-300",
    },
]);

const form = useForm({ product_id: null });

const addingId = ref(null);
const addToCart = (product) => {
    form.product_id = product.id;
    addingId.value = product.id;

    form.post(route("cart.store"), {
        preserveScroll: true,
        onFinish: () => {
            addingId.value = null;
        },
    });
};
</script>

<template>
    <Head title="Pathock Dashboard" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <div class="space-y-6 sm:space-y-8">
            <!-- ==================== WELCOME HEADER ==================== -->
            <div
                class="rounded-2xl bg-gradient-to-r from-green-600 to-green-700 p-5 sm:p-6 lg:p-8 text-white shadow-lg relative overflow-hidden"
            >
                <div
                    class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"
                ></div>
                <div
                    class="absolute -right-4 -bottom-16 w-56 h-56 rounded-full bg-white/5"
                ></div>

                <div
                    class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-green-100 text-xs sm:text-sm font-semibold uppercase tracking-wider"
                        >
                            Welcome Back
                        </p>
                        <h1 class="text-2xl sm:text-3xl font-extrabold mt-1">
                            {{
                                $page.props.auth?.user?.name ??
                                "Pathock Dashboard"
                            }}
                        </h1>
                        <p class="text-green-100 text-sm mt-1">
                            Here's your wallet overview at a glance.
                        </p>
                    </div>

                    <div
                        class="inline-flex items-center gap-2 bg-white/15 backdrop-blur rounded-xl px-4 py-2 self-start sm:self-auto"
                    >
                        <i class="fa-solid fa-bolt text-yellow-300"></i>
                        <span class="text-sm font-semibold">Dashboard</span>
                    </div>
                </div>
            </div>

            <!-- ==================== WALLET STATS ==================== -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 sm:gap-5"
            >
                <div
                    v-for="(card, i) in walletCards"
                    :key="i"
                    class="group relative rounded-2xl overflow-hidden text-white shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                    :class="`bg-gradient-to-br ${card.gradient}`"
                >
                    <!-- decorative circles -->
                    <div
                        class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10 group-hover:bg-white/20 transition"
                    ></div>
                    <div
                        class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full bg-white/5"
                    ></div>

                    <div
                        class="relative p-4 sm:p-5 flex items-start justify-between gap-3"
                    >
                        <div class="min-w-0">
                            <p
                                class="text-xs sm:text-sm font-medium text-white/90 truncate"
                            >
                                {{ card.label }}
                            </p>
                            <h2
                                class="text-2xl sm:text-3xl font-extrabold mt-1.5 tracking-tight truncate"
                            >
                                ৳{{ card.value }}
                            </h2>
                        </div>

                        <div
                            class="shrink-0 h-11 w-11 sm:h-12 sm:w-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center ring-1 ring-white/30"
                        >
                            <i :class="[card.icon, 'text-lg sm:text-xl']"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== PRODUCTS SECTION ==================== -->
            <section>
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="flex items-center gap-2">
                        <span
                            class="h-8 w-1.5 rounded-full bg-gradient-to-b from-green-500 to-green-700"
                        ></span>
                        <h2
                            class="text-lg sm:text-xl font-extrabold text-slate-800"
                        >
                            Products
                        </h2>
                        <span
                            class="ml-1 text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full"
                        >
                            {{ products.length }}
                        </span>
                    </div>
                </div>

                <div
                    v-if="products.length === 0"
                    class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center"
                >
                    <i
                        class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"
                    ></i>
                    <p class="text-slate-500 font-semibold">
                        No products available
                    </p>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5"
                >
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-slate-100 hover:border-green-200 transition-all duration-300 overflow-hidden flex flex-col"
                    >
                        <!-- IMAGE -->
                        <div
                            class="relative aspect-[4/3] bg-slate-100 overflow-hidden"
                        >
                            <img
                                v-if="product.image"
                                :src="`/storage/${product.image}`"
                                :alt="product.name"
                                @error="
                                    (e) =>
                                        (e.target.src = '/images/default.jpg')
                                "
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            />

                            <!-- Discount badge -->
                            <span
                                v-if="
                                    product.price &&
                                    product.discount_price &&
                                    Number(product.discount_price) <
                                        Number(product.price)
                                "
                                class="absolute top-2 left-2 bg-red-500 text-white text-[11px] font-bold px-2 py-1 rounded-full shadow"
                            >
                                -
                                {{
                                    Math.round(
                                        (1 -
                                            Number(product.discount_price) /
                                                Number(product.price)) *
                                            100,
                                    )
                                }}%
                            </span>
                        </div>

                        <!-- CONTENT -->
                        <div class="p-3.5 sm:p-4 flex flex-col flex-1">
                            <h3
                                class="font-bold text-sm sm:text-base text-slate-800 line-clamp-2 min-h-[2.5rem]"
                            >
                                {{ product.name }}
                            </h3>

                            <div
                                class="flex items-center gap-2 mt-2 mb-3 flex-wrap"
                            >
                                <span
                                    class="text-green-600 font-extrabold text-base sm:text-lg"
                                >
                                    ৳{{
                                        product.discount_price ?? product.price
                                    }}
                                </span>
                                <span
                                    v-if="
                                        product.discount_price &&
                                        Number(product.discount_price) <
                                            Number(product.price)
                                    "
                                    class="text-slate-400 line-through text-xs sm:text-sm"
                                >
                                    ৳{{ product.price }}
                                </span>
                            </div>

                            <div class="mt-auto flex flex-col gap-2">
                                <button
                                    @click="addToCart(product)"
                                    :disabled="addingId === product.id"
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-bold py-2 rounded-lg transition inline-flex items-center justify-center gap-2"
                                >
                                    <i
                                        v-if="addingId === product.id"
                                        class="fa-solid fa-spinner fa-spin"
                                    ></i>
                                    <i v-else class="fa-solid fa-cart-plus"></i>
                                    {{
                                        addingId === product.id
                                            ? "Adding..."
                                            : "Add to Cart"
                                    }}
                                </button>
                                <Link
                                    :href="route('product.show', product.id)"
                                    class="w-full bg-slate-800 hover:bg-slate-900 text-center text-white text-sm font-bold py-2 rounded-lg transition inline-flex items-center justify-center gap-2"
                                >
                                    <i class="fa-solid fa-circle-info"></i>
                                    View Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== AFFILIATE SECTION ==================== -->
            <section
                class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-orange-500 via-orange-500 to-red-500 p-5 sm:p-6 lg:p-8 text-white shadow-lg"
            >
                <div
                    class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-white/10"
                ></div>
                <div
                    class="absolute -left-6 -bottom-16 w-52 h-52 rounded-full bg-white/5"
                ></div>

                <div class="relative flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="h-11 w-11 sm:h-12 sm:w-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center ring-1 ring-white/30 shrink-0"
                        >
                            <i
                                class="fa-solid fa-share-nodes text-lg sm:text-xl"
                            ></i>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-lg sm:text-xl font-extrabold">
                                Share &amp; Earn Commission
                            </h2>
                            <p class="text-xs sm:text-sm text-white/90">
                                Share your affiliate link and earn commission
                                when people register and view.
                            </p>
                        </div>
                    </div>

                    <p
                        class="text-sm font-medium text-white/95 leading-relaxed"
                    >
                        এই অ্যাফিলিয়েট লিঙ্কটি শেয়ার করুন এবং যারা আপনার লিঙ্ক
                        ব্যবহার করে রেজিস্ট্রেশন করবে তাদের ভিউ থেকে কমিশন পান।
                    </p>

                    <!-- Link input -->
                    <div
                        class="flex items-center gap-2 bg-white/15 backdrop-blur rounded-xl p-2 ring-1 ring-white/30"
                    >
                        <i
                            class="fa-solid fa-link text-white/80 ml-2 shrink-0"
                        ></i>
                        <input
                            type="text"
                            :value="affiliateLink"
                            readonly
                            class="flex-1 min-w-0 bg-transparent border-0 text-white text-xs sm:text-sm font-mono outline-none placeholder-white/60"
                        />
                        <button
                            @click="copyLink"
                            class="shrink-0 bg-white text-orange-600 hover:bg-orange-50 font-bold text-xs sm:text-sm px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5"
                        >
                            <i
                                :class="
                                    copied
                                        ? 'fa-solid fa-check'
                                        : 'fa-regular fa-copy'
                                "
                            ></i>
                            {{ copied ? "Copied" : "Copy" }}
                        </button>
                    </div>

                    <!-- Share buttons -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <button
                            @click="shareWhatsApp"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2.5 rounded-lg transition inline-flex items-center justify-center gap-2 shadow"
                        >
                            <i class="fa-brands fa-whatsapp text-base"></i>
                            Share on WhatsApp
                        </button>
                        <button
                            @click="shareFacebook"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 rounded-lg transition inline-flex items-center justify-center gap-2 shadow"
                        >
                            <i class="fa-brands fa-facebook text-base"></i>
                            Share on Facebook
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </Layout>
</template>

<style scoped>
/* Multi-line truncation */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Extra small breakpoint (below sm) for a 2-col grid on very small phones */
@media (min-width: 400px) {
    .xs\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Thin custom scrollbar for the affiliate input if it overflows */
input[type="text"] {
    scrollbar-width: thin;
}
</style>
