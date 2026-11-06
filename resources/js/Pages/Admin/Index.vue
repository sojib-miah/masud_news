<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { onMounted, ref, onBeforeUnmount } from "vue";
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

const t = (key) => page.props.translations?.messages?.[key] ?? key;

const chartRef = ref(null);
let chartInstance = null;

// Format number with thousands separator
const fmt = (n) => Number(n || 0).toLocaleString("en-US");

onMounted(() => {
    if (!chartRef.value) return;

    const ctx = chartRef.value.getContext("2d");

    // Gradient fill for bars
    const gradient = ctx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, "rgba(34, 197, 94, 0.95)"); // green-500
    gradient.addColorStop(0.5, "rgba(59, 130, 246, 0.85)"); // blue-500
    gradient.addColorStop(1, "rgba(59, 130, 246, 0.25)");

    chartInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels: props.months,
            datasets: [
                {
                    label: "Monthly Sales (৳)",
                    data: props.sales,
                    backgroundColor: gradient,
                    hoverBackgroundColor: "rgba(34, 197, 94, 1)",
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 42,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "index",
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    align: "end",
                    labels: {
                        usePointStyle: true,
                        pointStyle: "circle",
                        boxWidth: 8,
                        boxHeight: 8,
                        font: { size: 12, weight: "600" },
                        color: "#475569",
                    },
                },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.92)",
                    titleColor: "#fff",
                    bodyColor: "#e2e8f0",
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (ctx) =>
                            `৳ ${Number(ctx.parsed.y).toLocaleString("en-US")}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: "#64748b",
                        font: { size: 11, weight: "600" },
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgba(148, 163, 184, 0.15)",
                        drawBorder: false,
                    },
                    ticks: {
                        color: "#64748b",
                        font: { size: 11 },
                        callback: (v) =>
                            `৳${Number(v).toLocaleString("en-US")}`,
                    },
                },
            },
            animation: {
                duration: 900,
                easing: "easeOutQuart",
            },
        },
    });
});

onBeforeUnmount(() => {
    if (chartInstance) chartInstance.destroy();
});

// Stat cards config
const newsCards = [
    {
        label: "Today News",
        value: () => props.todayNews,
        href: () => route("admin.news"),
        icon: "fa-solid fa-newspaper",
        gradient: "from-emerald-500 to-teal-500",
        glow: "shadow-emerald-500/30",
        ring: "ring-emerald-500/20",
    },
    {
        label: "This Month News",
        value: () => props.monthNews,
        href: () => route("admin.news"),
        icon: "fa-solid fa-calendar-days",
        gradient: "from-violet-500 to-fuchsia-500",
        glow: "shadow-violet-500/30",
        ring: "ring-violet-500/20",
    },
    {
        label: "Total News",
        value: () => props.totalNews,
        href: () => route("admin.news"),
        icon: "fa-solid fa-layer-group",
        gradient: "from-amber-500 to-orange-500",
        glow: "shadow-amber-500/30",
        ring: "ring-amber-500/20",
    },
];

const salesCards = [
    {
        label: "Today Sell",
        value: () => `৳${fmt(props.todaySell)}`,
        href: () => route("orders.index"),
        icon: "fa-solid fa-cash-register",
        gradient: "from-sky-500 to-blue-600",
        glow: "shadow-sky-500/30",
        ring: "ring-sky-500/20",
    },
    {
        label: "This Month Sell",
        value: () => `৳${fmt(props.monthSell)}`,
        href: () => route("orders.index"),
        icon: "fa-solid fa-chart-line",
        gradient: "from-rose-500 to-pink-600",
        glow: "shadow-rose-500/30",
        ring: "ring-rose-500/20",
    },
    {
        label: "Total Sell",
        value: () => `৳${fmt(props.totalSell)}`,
        href: () => route("orders.index"),
        icon: "fa-solid fa-sack-dollar",
        gradient: "from-lime-500 to-green-600",
        glow: "shadow-lime-500/30",
        ring: "ring-lime-500/20",
    },
];
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

    <Layout>
        <Breadcrumb />

        <div class="space-y-6">
            <!-- ==================== PAGE HEADER ==================== -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 sm:py-8 shadow-xl"
            >
                <div
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
                ></div>

                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-gauge-high mr-1"></i>
                            Dashboard
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Welcome back 👋
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Here's what's happening with your news and sales
                            today.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"
                            ></span>
                        </span>
                        Live Overview
                    </div>
                </div>
            </div>

            <!-- ==================== NEWS SECTION ==================== -->
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"
                    >
                        <i class="fa-solid fa-newspaper text-sm"></i>
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800">
                        News Overview
                    </h2>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <Link
                        v-for="card in newsCards"
                        :key="card.label"
                        :href="card.href()"
                        class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md hover:shadow-2xl transition-all duration-300 ring-1 ring-slate-100 hover:-translate-y-1"
                    >
                        <!-- Colored glow -->
                        <div
                            class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-gradient-to-br opacity-10 group-hover:opacity-20 transition-opacity"
                            :class="card.gradient"
                        ></div>

                        <div class="relative flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1"
                                >
                                    {{ card.label }}
                                </p>
                                <p
                                    class="text-3xl sm:text-4xl font-extrabold text-slate-900"
                                >
                                    {{ fmt(card.value()) }}
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg ring-4 transition-transform group-hover:scale-110"
                                :class="[card.gradient, card.glow, card.ring]"
                            >
                                <i :class="card.icon" class="text-lg"></i>
                            </div>
                        </div>

                        <div
                            class="relative mt-4 flex items-center gap-1 text-xs font-semibold text-slate-500 group-hover:text-emerald-600 transition-colors"
                        >
                            <span>View details</span>
                            <i
                                class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"
                            ></i>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- ==================== SALES SECTION ==================== -->
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
                    >
                        <i class="fa-solid fa-dollar-sign text-sm"></i>
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800">
                        Sales Overview
                    </h2>
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <Link
                        v-for="card in salesCards"
                        :key="card.label"
                        :href="card.href()"
                        class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-md hover:shadow-2xl transition-all duration-300 ring-1 ring-slate-100 hover:-translate-y-1"
                    >
                        <div
                            class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-gradient-to-br opacity-10 group-hover:opacity-20 transition-opacity"
                            :class="card.gradient"
                        ></div>

                        <div class="relative flex items-start justify-between">
                            <div class="min-w-0">
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1"
                                >
                                    {{ card.label }}
                                </p>
                                <p
                                    class="text-2xl sm:text-3xl font-extrabold text-slate-900 break-words"
                                >
                                    {{ card.value() }}
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg ring-4 transition-transform group-hover:scale-110"
                                :class="[card.gradient, card.glow, card.ring]"
                            >
                                <i :class="card.icon" class="text-lg"></i>
                            </div>
                        </div>

                        <div
                            class="relative mt-4 flex items-center gap-1 text-xs font-semibold text-slate-500 group-hover:text-blue-600 transition-colors"
                        >
                            <span>View orders</span>
                            <i
                                class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"
                            ></i>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- ==================== SALES CHART ==================== -->
            <section
                class="rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-blue-600 text-white shadow"
                        >
                            <i class="fa-solid fa-chart-column text-sm"></i>
                        </span>
                        <div>
                            <h2
                                class="text-base sm:text-lg font-bold text-slate-800"
                            >
                                Monthly Sales Chart
                            </h2>
                            <p class="text-xs text-slate-500">
                                Total sales performance for the current year
                            </p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 w-fit"
                    >
                        <i class="fa-solid fa-calendar"></i>
                        {{ new Date().getFullYear() }}
                    </span>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="h-64 sm:h-80 md:h-96 w-full">
                        <canvas ref="chartRef"></canvas>
                    </div>
                </div>
            </section>
        </div>
    </Layout>
</template>

<style scoped>
/* Smooth transitions on all interactive cards */
a {
    -webkit-tap-highlight-color: transparent;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
