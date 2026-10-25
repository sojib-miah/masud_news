<script setup>
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    products: Array,
    wallets: Object,
    stockMoney: String,
});

const page = usePage();

const stats = computed(() => page.props.stats);
const affiliateLink = computed(() => page.props.affiliate_link);

function copyLink() {
    navigator.clipboard
        .writeText(affiliateLink.value)
        .then(() => alert("Link copied to clipboard!"))
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

const form = useForm({
    product_id: null,
});

const addToCart = (product) => {
    form.product_id = product.id;

    form.post(route("cart.store"), {
        preserveScroll: true,
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="relative rounded-lg shadow-lg overflow-hidden text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #6a5cff 50%,
                        #2f2b6d 50%
                    );
                "
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Available Wallet</p>
                        <h2 class="text-4xl font-bold mt-2">
                            ৳{{ wallets.balance }}
                        </h2>
                    </div>
                    <i
                        class="fa-solid fa-magnifying-glass text-3xl opacity-90"
                    ></i>
                </div>
            </div>

            <div
                class="relative rounded-lg shadow-lg overflow-hidden text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #6a5cff 50%,
                        #2f2b6d 50%
                    );
                "
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Income</p>
                        <h2 class="text-4xl font-bold mt-2">
                            ৳{{ wallets.total_income }}
                        </h2>
                    </div>
                    <i class="fa-solid fa-money-bill text-3xl opacity-90"></i>
                </div>
            </div>

            <div
                class="relative rounded-lg shadow-lg overflow-hidden text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #14b8a6 50%,
                        #0f9f95 50%
                    );
                "
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Daily Return Wallet</p>
                        <h2 class="text-4xl font-bold mt-2">
                            ৳{{ wallets.daily_return }}
                        </h2>
                    </div>
                    <i
                        class="fa-solid fa-money-bill-wave text-3xl opacity-90"
                    ></i>
                </div>
            </div>

            <div
                class="relative rounded-lg shadow-lg overflow-hidden text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #14b8a6 50%,
                        #0f9f95 50%
                    );
                "
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Gift Wallet</p>
                        <h2 class="text-4xl font-bold mt-2">
                            ৳{{ wallets.gift_wallet }}
                        </h2>
                    </div>
                    <i
                        class="fa-solid fa-money-bill-wave text-3xl opacity-90"
                    ></i>
                </div>
            </div>

            <div
                class="relative rounded-lg shadow-lg overflow-hidden text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #0ea5e9 50%,
                        #0284c7 50%
                    );
                "
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Marketing Wallet</p>
                        <h2 class="text-4xl font-bold mt-2">
                            ৳{{ wallets.marketing_wallet }}
                        </h2>
                    </div>
                    <i class="fa-solid fa-wallet text-3xl opacity-90"></i>
                </div>
            </div>

            <div
                class="relative rounded-lg shadow-lg overflow-hidden text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #fb923c 50%,
                        #f97316 50%
                    );
                "
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">
                            রিসেলার ডিলার স্টক ভেলু
                        </p>
                        <h2 class="text-4xl font-bold mt-2">
                            {{ stockMoney }}
                        </h2>
                    </div>
                    <i class="fa-solid fa-money-bill text-3xl opacity-90"></i>
                </div>
            </div>
        </div>

        <!-- product -->
        <!-- product -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-6"
        >
            <div
                v-for="product in products"
                :key="product.id"
                class="bg-white rounded-2xl shadow hover:shadow-lg transition duration-300 overflow-hidden"
            >
                <!-- IMAGE -->
                <div class="h-48 bg-gray-100 flex items-center justify-center">
                    <img
                        v-if="product.image"
                        :src="`/storage/${product.image}`"
                        class="h-full object-cover"
                    />
                    <span v-else class="text-gray-400">No Image</span>
                </div>

                <!-- CONTENT -->
                <div class="p-4">
                    <!-- NAME -->
                    <h3 class="font-semibold text-lg mb-2">
                        {{ product.name }}
                    </h3>

                    <!-- PRICE -->
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-green-600 font-bold text-lg">
                            ৳{{ product.discount_price }}
                        </span>

                        <span
                            v-if="product.discount_price < product.price"
                            class="text-gray-400 line-through text-sm"
                        >
                            ৳{{ product.price }}
                        </span>
                    </div>

                    <!-- BUTTON -->
                    <button
                        @click="addToCart(product)"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg transition"
                    >
                        Add to Cart
                    </button>
                    <Link
                        :href="route('product.show', product.id)"
                        class="w-full bg-indigo-500 block mt-3 text-center hover:bg-indigo-600 text-white py-2 rounded-lg transition"
                    >
                        View Details
                    </Link>
                </div>
            </div>
        </div>

        <div
            class="my-5 flex flex-col gap-3 w-full h-fit p-4 rounded"
            style="
                background: linear-gradient(135deg, #fb923c 50%, #f97316 50%);
            "
        >
            <span class="text-sm font-robo text-white">
                এই অ্যাফিলিয়েট লিঙ্কটি শেয়ার করুন এবং যারা আপনার লিঙ্ক ব্যবহার
                করে রেজিস্ট্রেশন করবে তাদের ভিউ থেকে কমিশন পান
            </span>

            <input
                type="text"
                :value="affiliateLink"
                readonly
                class="form-input border-white"
            />

            <div class="flex items-center gap-3">
                <button
                    @click="copyLink"
                    class="border bg-green-600 rounded p-2 text-white border-green-600"
                >
                    <i class="fa-regular fa-copy"></i> Copy
                </button>

                <button
                    @click="shareWhatsApp"
                    class="border bg-blue-600 rounded p-2 text-white border-blue-600"
                >
                    <i class="fa-brands fa-whatsapp"></i> Share on WhatsApp
                </button>

                <button
                    @click="shareFacebook"
                    class="border bg-fuchsia-600 rounded p-2 text-white border-fuchsia-600"
                >
                    <i class="fa-brands fa-facebook"></i> Share on Facebook
                </button>
            </div>
        </div>
    </Layout>
</template>
