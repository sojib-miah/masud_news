<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed, watch, onBeforeUnmount } from "vue";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import Pagination from "@/Shared/Pagination.vue";
import { route } from "ziggy-js";

const props = defineProps({
    orders: { type: Object, default: () => ({ data: [], links: [] }) },
});

const showModal = ref(false);
const selectedOrder = ref(null);
const search = ref("");

// ==================== MODAL ====================
function openModal(order) {
    selectedOrder.value = order;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    // keep selectedOrder until transition finishes to avoid flicker
    setTimeout(() => {
        if (!showModal.value) selectedOrder.value = null;
    }, 200);
}

// Body scroll lock + Esc
watch(showModal, (open) => {
    document.body.style.overflow = open ? "hidden" : "";
});
function onKeydown(e) {
    if (e.key === "Escape" && showModal.value) closeModal();
}
if (typeof window !== "undefined") {
    window.addEventListener("keydown", onKeydown);
    onBeforeUnmount(() => {
        window.removeEventListener("keydown", onKeydown);
        document.body.style.overflow = "";
    });
}

// ==================== HELPERS ====================
const statusMeta = {
    processing: {
        label: "Processing",
        class: "bg-amber-100 text-amber-700 ring-amber-200",
        icon: "fa-solid fa-spinner",
    },
    pending: {
        label: "Pending",
        class: "bg-slate-100 text-slate-700 ring-slate-200",
        icon: "fa-solid fa-clock",
    },
    delivered: {
        label: "Delivered",
        class: "bg-green-100 text-green-700 ring-green-200",
        icon: "fa-solid fa-circle-check",
    },
    completed: {
        label: "Completed",
        class: "bg-green-100 text-green-700 ring-green-200",
        icon: "fa-solid fa-circle-check",
    },
    canceled: {
        label: "Canceled",
        class: "bg-red-100 text-red-700 ring-red-200",
        icon: "fa-solid fa-circle-xmark",
    },
    cancelled: {
        label: "Canceled",
        class: "bg-red-100 text-red-700 ring-red-200",
        icon: "fa-solid fa-circle-xmark",
    },
};
function statusFor(s) {
    return (
        statusMeta[s] ?? {
            label: s ?? "—",
            class: "bg-slate-100 text-slate-600 ring-slate-200",
            icon: "fa-solid fa-circle",
        }
    );
}

function formatMoney(v) {
    const n = Number(v ?? 0);
    if (Number.isNaN(n)) return v;
    return n.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}

// Summary
const totalOrders = computed(
    () => props.orders.total ?? props.orders.data?.length ?? 0,
);
const pageTotal = computed(() =>
    (props.orders.data ?? []).reduce((s, o) => s + Number(o.total ?? 0), 0),
);
const deliveredCount = computed(
    () =>
        (props.orders.data ?? []).filter(
            (o) => o.status === "delivered" || o.status === "completed",
        ).length,
);
const processingCount = computed(
    () =>
        (props.orders.data ?? []).filter((o) => o.status === "processing")
            .length,
);

// Filter (page-level)
const filteredOrders = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.orders.data ?? [];
    return (props.orders.data ?? []).filter((o) => {
        return (
            String(o.id).includes(q) ||
            String(o.phone ?? "")
                .toLowerCase()
                .includes(q) ||
            String(o.name ?? "")
                .toLowerCase()
                .includes(q)
        );
    });
});
</script>

<template>
    <Head title="My Orders" />

    <Layout>
        <div class="space-y-5 sm:space-y-6">
            <!-- ==================== HERO HEADER ==================== -->
            <div
                class="rounded-2xl bg-gradient-to-r from-green-600 to-green-700 p-5 sm:p-6 text-white shadow-lg relative overflow-hidden"
            >
                <div
                    class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10"
                ></div>
                <div
                    class="absolute -right-4 -bottom-16 w-56 h-56 rounded-full bg-white/5"
                ></div>

                <div
                    class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-green-100 text-xs sm:text-sm font-semibold uppercase tracking-wider"
                        >
                            <i class="fa-solid fa-box-open mr-1.5"></i>
                            Purchases
                        </p>
                        <h1 class="text-2xl sm:text-3xl font-extrabold mt-1">
                            My Orders
                        </h1>
                        <p class="text-green-100 text-sm mt-1">
                            Track your orders and download invoices.
                        </p>
                    </div>

                    <div
                        class="inline-flex items-center gap-2 bg-white/15 backdrop-blur rounded-xl px-4 py-2 self-start sm:self-auto"
                    >
                        <i class="fa-solid fa-receipt"></i>
                        <span class="text-sm font-semibold">
                            {{ totalOrders }} Total
                        </span>
                    </div>
                </div>
            </div>

            <!-- ==================== SUMMARY CARDS ==================== -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div
                    class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center gap-3"
                >
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shrink-0"
                    >
                        <i class="fa-solid fa-sack-dollar text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                        >
                            Page Total
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800 truncate"
                        >
                            ৳{{ formatMoney(pageTotal) }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center gap-3"
                >
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shrink-0"
                    >
                        <i class="fa-solid fa-circle-check text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                        >
                            Delivered
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800"
                        >
                            {{ deliveredCount }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center gap-3"
                >
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shrink-0"
                    >
                        <i class="fa-solid fa-spinner text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                        >
                            Processing
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800"
                        >
                            {{ processingCount }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ==================== SEARCH ==================== -->
            <div
                class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 sm:p-4"
            >
                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                    ></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by order #, phone or name..."
                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 outline-none text-sm transition"
                    />
                </div>
            </div>

            <!-- ==================== EMPTY STATE ==================== -->
            <div
                v-if="!filteredOrders.length"
                class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center"
            >
                <div
                    class="mx-auto h-16 w-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4"
                >
                    <i class="fa-solid fa-box-open text-2xl text-slate-400"></i>
                </div>
                <p class="text-slate-700 font-bold text-lg">
                    {{ search ? "No matching orders" : "No orders yet" }}
                </p>
                <p class="text-slate-500 text-sm mt-1">
                    {{
                        search
                            ? `No results for "${search}"`
                            : "Your placed orders will appear here."
                    }}
                </p>
            </div>

            <template v-else>
                <!-- ==================== DESKTOP TABLE (sm+) ==================== -->
                <div
                    class="hidden sm:block bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr
                                    class="bg-slate-50 border-b border-slate-200"
                                >
                                    <th
                                        class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-12"
                                    >
                                        SL
                                    </th>
                                    <th
                                        class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider"
                                    >
                                        Order ID
                                    </th>
                                    <th
                                        class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider"
                                    >
                                        Phone
                                    </th>
                                    <th
                                        class="p-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider"
                                    >
                                        Total
                                    </th>
                                    <th
                                        class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="p-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-40"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="(order, index) in filteredOrders"
                                    :key="order.id"
                                    class="border-b border-slate-100 last:border-0 hover:bg-green-50/40 transition"
                                >
                                    <td class="p-3 text-sm text-slate-500">
                                        {{ (orders.from ?? 0) + index }}
                                    </td>

                                    <td class="p-3">
                                        <span
                                            class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 text-xs font-mono font-semibold px-2 py-1 rounded"
                                        >
                                            #{{ order.id }}
                                        </span>
                                    </td>

                                    <td
                                        class="p-3 text-sm text-slate-700 whitespace-nowrap"
                                    >
                                        {{ order.phone }}
                                    </td>

                                    <td class="p-3 text-right">
                                        <span
                                            class="text-sm font-extrabold text-green-600"
                                        >
                                            ৳{{ formatMoney(order.total) }}
                                        </span>
                                    </td>

                                    <td class="p-3">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full ring-1"
                                            :class="
                                                statusFor(order.status).class
                                            "
                                        >
                                            <i
                                                :class="[
                                                    statusFor(order.status)
                                                        .icon,
                                                    'text-[10px]',
                                                ]"
                                            ></i>
                                            {{ statusFor(order.status).label }}
                                        </span>
                                    </td>

                                    <td class="p-3">
                                        <div class="flex gap-2 justify-end">
                                            <button
                                                @click="openModal(order)"
                                                class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-lg transition"
                                            >
                                                <i class="fa-solid fa-eye"></i>
                                                View
                                            </button>
                                            <a
                                                :href="
                                                    route(
                                                        'order.invoice',
                                                        order.id,
                                                    )
                                                "
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-lg transition"
                                            >
                                                <i
                                                    class="fa-solid fa-file-invoice"
                                                ></i>
                                                Invoice
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ==================== MOBILE CARDS (< sm) ==================== -->
                <div class="sm:hidden space-y-3">
                    <div
                        v-for="(order, index) in filteredOrders"
                        :key="order.id"
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-wide"
                                >
                                    Order #{{ order.id }}
                                </p>
                                <p
                                    class="text-xl font-extrabold text-slate-800 mt-0.5 truncate"
                                >
                                    ৳{{ formatMoney(order.total) }}
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    <i
                                        class="fa-solid fa-phone mr-1 text-[10px]"
                                    ></i>
                                    {{ order.phone }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full ring-1 shrink-0"
                                :class="statusFor(order.status).class"
                            >
                                <i
                                    :class="[
                                        statusFor(order.status).icon,
                                        'text-[9px]',
                                    ]"
                                ></i>
                                {{ statusFor(order.status).label }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <button
                                @click="openModal(order)"
                                class="inline-flex items-center justify-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-bold py-2 rounded-lg transition"
                            >
                                <i class="fa-solid fa-eye"></i>
                                View
                            </button>
                            <a
                                :href="route('order.invoice', order.id)"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-1.5 bg-green-50 hover:bg-green-100 text-green-700 text-sm font-bold py-2 rounded-lg transition"
                            >
                                <i class="fa-solid fa-file-invoice"></i>
                                Invoice
                            </a>
                        </div>
                    </div>
                </div>

                <Pagination :links="orders.links" class="mt-2" />
            </template>
        </div>

        <!-- ==================== ORDER DETAILS MODAL ==================== -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showModal && selectedOrder"
                    class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4"
                    @click.self="closeModal"
                >
                    <Transition
                        enter-active-class="transition-transform duration-200 ease-out"
                        enter-from-class="scale-95 opacity-0"
                        enter-to-class="scale-100 opacity-100"
                        leave-active-class="transition-transform duration-150 ease-in"
                        leave-from-class="scale-100 opacity-100"
                        leave-to-class="scale-95 opacity-0"
                        appear
                    >
                        <div
                            v-if="showModal && selectedOrder"
                            class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col"
                        >
                            <!-- Modal header -->
                            <div
                                class="bg-gradient-to-r from-green-600 to-green-700 px-5 py-4 text-white flex items-center justify-between shrink-0"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center ring-1 ring-white/30"
                                    >
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] uppercase tracking-wider text-green-100 font-bold"
                                        >
                                            Order #{{ selectedOrder.id }}
                                        </p>
                                        <h2
                                            class="text-lg font-extrabold leading-tight"
                                        >
                                            Order Details
                                        </h2>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="h-9 w-9 rounded-lg text-white/90 hover:text-white hover:bg-white/15 transition"
                                >
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>

                            <!-- Modal body -->
                            <div class="p-4 sm:p-5 overflow-y-auto">
                                <!-- Status strip -->
                                <div
                                    class="mb-4 flex items-center justify-between gap-2 rounded-xl p-3 bg-slate-50 border border-slate-200"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="text-[11px] uppercase font-semibold text-slate-500 tracking-wide"
                                        >
                                            Total
                                        </p>
                                        <p
                                            class="text-xl sm:text-2xl font-extrabold text-green-600 truncate"
                                        >
                                            ৳{{
                                                formatMoney(selectedOrder.total)
                                            }}
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full ring-1 shrink-0"
                                        :class="
                                            statusFor(selectedOrder.status)
                                                .class
                                        "
                                    >
                                        <i
                                            :class="[
                                                statusFor(selectedOrder.status)
                                                    .icon,
                                                'text-[10px]',
                                            ]"
                                        ></i>
                                        {{
                                            statusFor(selectedOrder.status)
                                                .label
                                        }}
                                    </span>
                                </div>

                                <!-- Customer info -->
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4"
                                >
                                    <div
                                        class="rounded-xl border border-slate-200 p-3.5 bg-white"
                                    >
                                        <p
                                            class="text-[11px] uppercase font-semibold text-slate-500 tracking-wide mb-2"
                                        >
                                            Customer
                                        </p>
                                        <p class="text-sm text-slate-800">
                                            <i
                                                class="fa-regular fa-user mr-1.5 text-slate-400"
                                            ></i>
                                            {{ selectedOrder.name }}
                                        </p>
                                        <p
                                            class="text-sm text-slate-700 mt-1 break-all"
                                        >
                                            <i
                                                class="fa-solid fa-phone mr-1.5 text-slate-400"
                                            ></i>
                                            {{ selectedOrder.phone }}
                                        </p>
                                        <p
                                            class="text-sm text-slate-700 mt-1 break-all"
                                        >
                                            <i
                                                class="fa-regular fa-envelope mr-1.5 text-slate-400"
                                            ></i>
                                            {{ selectedOrder.email ?? "N/A" }}
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 p-3.5 bg-white"
                                    >
                                        <p
                                            class="text-[11px] uppercase font-semibold text-slate-500 tracking-wide mb-2"
                                        >
                                            Delivery
                                        </p>
                                        <p class="text-sm text-slate-800">
                                            <i
                                                class="fa-solid fa-truck mr-1.5 text-slate-400"
                                            ></i>
                                            {{
                                                selectedOrder.delivery_type ??
                                                "—"
                                            }}
                                        </p>
                                        <p class="text-sm text-slate-700 mt-1">
                                            <i
                                                class="fa-regular fa-calendar mr-1.5 text-slate-400"
                                            ></i>
                                            {{
                                                selectedOrder.created_at ?? "—"
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Products -->
                                <h3
                                    class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-2 flex items-center gap-2"
                                >
                                    <span
                                        class="h-5 w-1 rounded-full bg-green-500"
                                    ></span>
                                    Products
                                    <span
                                        class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full"
                                    >
                                        {{ selectedOrder.items?.length ?? 0 }}
                                    </span>
                                </h3>

                                <!-- Desktop products table -->
                                <div
                                    class="hidden sm:block rounded-xl border border-slate-200 overflow-hidden"
                                >
                                    <table class="w-full">
                                        <thead>
                                            <tr
                                                class="bg-slate-50 border-b border-slate-200"
                                            >
                                                <th
                                                    class="p-2.5 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider w-16"
                                                >
                                                    Image
                                                </th>
                                                <th
                                                    class="p-2.5 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider"
                                                >
                                                    Product
                                                </th>
                                                <th
                                                    class="p-2.5 text-right text-[11px] font-bold text-slate-600 uppercase tracking-wider"
                                                >
                                                    Price
                                                </th>
                                                <th
                                                    class="p-2.5 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider"
                                                >
                                                    Qty
                                                </th>
                                                <th
                                                    class="p-2.5 text-right text-[11px] font-bold text-slate-600 uppercase tracking-wider"
                                                >
                                                    Subtotal
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="item in selectedOrder.items"
                                                :key="item.id"
                                                class="border-b border-slate-100 last:border-0"
                                            >
                                                <td class="p-2.5">
                                                    <img
                                                        v-if="
                                                            item.product?.image
                                                        "
                                                        :src="`/storage/${item.product.image}`"
                                                        :alt="
                                                            item.product?.name
                                                        "
                                                        class="w-12 h-12 object-cover rounded-lg border border-slate-200"
                                                    />
                                                    <div
                                                        v-else
                                                        class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400"
                                                    >
                                                        <i
                                                            class="fa-regular fa-image"
                                                        ></i>
                                                    </div>
                                                </td>
                                                <td
                                                    class="p-2.5 text-sm font-semibold text-slate-800"
                                                >
                                                    {{
                                                        item.product?.name ??
                                                        "—"
                                                    }}
                                                </td>
                                                <td
                                                    class="p-2.5 text-right text-sm text-slate-700 whitespace-nowrap"
                                                >
                                                    ৳{{
                                                        formatMoney(item.price)
                                                    }}
                                                </td>
                                                <td
                                                    class="p-2.5 text-center text-sm text-slate-700"
                                                >
                                                    {{ item.quantity }}
                                                </td>
                                                <td
                                                    class="p-2.5 text-right text-sm font-extrabold text-green-600 whitespace-nowrap"
                                                >
                                                    ৳{{
                                                        formatMoney(
                                                            item.price *
                                                                item.quantity,
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile products list -->
                                <div class="sm:hidden space-y-2">
                                    <div
                                        v-for="item in selectedOrder.items"
                                        :key="item.id"
                                        class="flex items-start gap-3 rounded-xl border border-slate-200 p-3"
                                    >
                                        <img
                                            v-if="item.product?.image"
                                            :src="`/storage/${item.product.image}`"
                                            :alt="item.product?.name"
                                            class="h-14 w-14 object-cover rounded-lg border border-slate-200 shrink-0"
                                        />
                                        <div
                                            v-else
                                            class="h-14 w-14 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0"
                                        >
                                            <i class="fa-regular fa-image"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="text-sm font-semibold text-slate-800 line-clamp-2"
                                            >
                                                {{ item.product?.name ?? "—" }}
                                            </p>
                                            <p
                                                class="text-xs text-slate-500 mt-0.5"
                                            >
                                                ৳{{ formatMoney(item.price) }} ×
                                                {{ item.quantity }}
                                            </p>
                                            <p
                                                class="text-sm font-extrabold text-green-600 mt-1"
                                            >
                                                ৳{{
                                                    formatMoney(
                                                        item.price *
                                                            item.quantity,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer actions -->
                                <div
                                    class="mt-4 flex flex-col sm:flex-row gap-2"
                                >
                                    <a
                                        :href="
                                            route(
                                                'order.invoice',
                                                selectedOrder.id,
                                            )
                                        "
                                        target="_blank"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2.5 rounded-lg shadow transition"
                                    >
                                        <i class="fa-solid fa-file-invoice"></i>
                                        Download Invoice
                                    </a>
                                    <button
                                        @click="closeModal"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold py-2.5 rounded-lg transition"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </Layout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
