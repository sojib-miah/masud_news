<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { onMounted, ref } from "vue";
import Chart from "chart.js/auto";

const page = usePage();
const props = defineProps({
    avatar: String,
    auth: Object,
    todayNews: Number,
    monthNews: Number,
    totalNews: Number,
    todaySell: Number,
    monthSell: Number,
    totalSell: Number,
    months: Array,
    sales: Array,
});

const t = (key) => page.props.translations.messages[key] ?? key;

const chartRef = ref(null);

onMounted(() => {
    new Chart(chartRef.value, {
        type: "bar",

        data: {
            labels: props.months,

            datasets: [
                {
                    label: "Monthly Sales",
                    data: props.sales,
                    borderWidth: 1,
                },
            ],
        },

        options: {
            responsive: true,

            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
});
</script>

<template>
    <Head title="Admin Dashboard" />
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <FlashMessage
        v-if="$page.props.flash?.error"
        :message="$page.props.flash.error"
        type="error"
    />
    <FlashMessage
        v-if="$page.props.flash?.warning"
        :message="$page.props.flash.warning"
        type="warning"
    />
    <div>
        <Layout>
            <Breadcrumb />

            <div class="border border-gray-300 p-3 shadow-sm rounded">
                <h1 class="text-2xl text-black">News Details</h1>

                <div
                    class="flex gap-3 w-full border-b pb-3 mb-3 border-b-gray-400"
                >
                    <!-- Today News -->
                    <div
                        class="shadow p-5 w-1/3 rounded text-white bg-gradient-to-r from-green-400 to-blue-500"
                    >
                        <h2 class="text-3xl font-bold">
                            <Link :href="route('admin.news')">{{
                                todayNews
                            }}</Link>
                        </h2>
                        <p>Today News</p>
                    </div>

                    <!-- This Month News -->
                    <div
                        class="shadow p-5 rounded w-1/3 text-white bg-gradient-to-r from-purple-400 to-pink-500"
                    >
                        <h2 class="text-3xl font-bold">
                            <Link :href="route('admin.news')">{{
                                monthNews
                            }}</Link>
                        </h2>
                        <p>This Month News</p>
                    </div>

                    <!-- Total News -->
                    <div
                        class="shadow p-5 rounded w-1/3 text-white bg-gradient-to-r from-yellow-400 to-red-500"
                    >
                        <h2 class="text-3xl font-bold">
                            <Link :href="route('admin.news')">{{
                                totalNews
                            }}</Link>
                        </h2>
                        <p>Total News</p>
                    </div>
                </div>

                <!-- sales -->
                <div class="flex gap-3 w-full pb-3 mb-3">
                    <!-- Today Sell -->
                    <div
                        class="shadow p-5 w-1/3 rounded text-white bg-gradient-to-r from-green-400 to-blue-500"
                    >
                        <h2 class="text-3xl font-bold">
                            <Link :href="route('orders.index')">
                                ৳{{ todaySell }}
                            </Link>
                        </h2>
                        <p>Today Sell</p>
                    </div>

                    <!-- This Month Sell -->
                    <div
                        class="shadow p-5 rounded w-1/3 text-white bg-gradient-to-r from-purple-400 to-pink-500"
                    >
                        <h2 class="text-3xl font-bold">
                            <Link :href="route('orders.index')">
                                ৳{{ monthSell }}
                            </Link>
                        </h2>
                        <p>This Month Sell</p>
                    </div>

                    <!-- Total Sell -->
                    <div
                        class="shadow p-5 rounded w-1/3 text-white bg-gradient-to-r from-yellow-400 to-red-500"
                    >
                        <h2 class="text-3xl font-bold">
                            <Link :href="route('orders.index')">
                                ৳{{ totalSell }}
                            </Link>
                        </h2>
                        <p>Total Sell</p>
                    </div>
                </div>

                <div class="bg-white shadow rounded p-5 mt-5">
                    <h2 class="text-xl font-bold mb-4">Monthly Sales Chart</h2>

                    <canvas ref="chartRef"></canvas>
                </div>
                <!-- <img :src="$page.props.auth.avatar" alt="Avatar" /> -->
            </div>
        </Layout>
    </div>
</template>
