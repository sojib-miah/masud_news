<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";

const props = defineProps({
    orders: Object,
    search: { type: String, default: "" },
    status: { type: String, default: "" },
});

const form = useForm({});
const localOrders = ref([...props.orders.data]);

watch(
    () => props.orders.data,
    (newVal) => {
        localOrders.value = [...newVal];
    },
);

// ==================== FILTERS (client-side) ====================
const search = ref(props.search || "");
const statusFilter = ref(props.status || "");

const filteredOrders = computed(() => {
    let list = [...localOrders.value];

    if (search.value.trim()) {
        const q = search.value.trim().toLowerCase();
        list = list.filter(
            (o) =>
                o.name?.toLowerCase().includes(q) ||
                o.phone?.toLowerCase().includes(q) ||
                o.email?.toLowerCase().includes(q) ||
                String(o.id).includes(q),
        );
    }

    if (statusFilter.value) {
        list = list.filter((o) => o.status === statusFilter.value);
    }

    return list;
});

function resetFilters() {
    search.value = "";
    statusFilter.value = "";
}

// ==================== STATUS UPDATE ====================
const changeStatus = (order) => {
    form.transform(() => ({
        status: order.status,
    })).put(route("orders.updateStatus", order.id), {
        preserveScroll: true,
    });
};

// ==================== DELETE ====================
const deleteOrder = (order) => {
    if (confirm(`Delete order #${order.id}?`)) {
        form.delete(route("orders.destroy", order.id), {
            preserveScroll: true,
        });
    }
};

// ==================== VIEW MODAL ====================
const showModal = ref(false);
const selectedOrder = ref(null);

const openModal = (order) => {
    selectedOrder.value = order;
    showModal.value = true;
    document.body.style.overflow = "hidden";
};

const closeModal = () => {
    showModal.value = false;
    selectedOrder.value = null;
    document.body.style.overflow = "";
};

// ==================== HELPERS ====================
function globalIndex(i) {
    const page = props.orders?.current_page ?? 1;
    const perPage = props.orders?.per_page ?? 10;
    return (page - 1) * perPage + i + 1;
}

function formatMoney(n) {
    if (n === null || n === undefined || n === "") return "0";
    return Number(n).toLocaleString("en-US");
}

function formatDate(d) {
    if (!d) return "—";
    try {
        return new Date(d).toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    } catch {
        return d;
    }
}

function statusClasses(status) {
    switch (status) {
        case "delivered":
            return "bg-emerald-50 text-emerald-700 ring-emerald-200";
        case "canceled":
            return "bg-red-50 text-red-700 ring-red-200";
        case "processing":
        default:
            return "bg-amber-50 text-amber-700 ring-amber-200";
    }
}

function statusIcon(status) {
    switch (status) {
        case "delivered":
            return "fa-solid fa-circle-check";
        case "canceled":
            return "fa-solid fa-circle-xmark";
        case "processing":
        default:
            return "fa-solid fa-clock";
    }
}

function deliveryLabel(type) {
    switch (type) {
        case "inside_dhaka":
            return "Inside Dhaka";
        case "outside_dhaka":
            return "Outside Dhaka";
        case "shop":
            return "Shop Pickup";
        default:
            return type || "—";
    }
}

function visitPage(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
}

// Modal subtotal
const modalSubtotal = computed(() => {
    if (!selectedOrder.value?.items) return 0;
    return selectedOrder.value.items.reduce(
        (sum, it) => sum + Number(it.price) * Number(it.quantity),
        0,
    );
});
</script>

<template>
    <Head title="Order List" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <Breadcrumb />

        <!-- ==================== HERO ==================== -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
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
                        <i class="fa-solid fa-shopping-cart mr-1"></i> Sales
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        All Orders
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Review, update and manage customer orders.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                >
                    <i class="fa-solid fa-database text-emerald-300"></i>
                    Total: {{ props.orders?.total ?? 0 }}
                </div>
            </div>
        </div>

        <!-- ==================== FILTERS ==================== -->
        <div
            class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-4 mb-5"
        >
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-6 relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                    ></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by name, phone, email or ID..."
                        class="w-full rounded-lg border border-slate-300 pl-10 pr-9 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                    />
                    <button
                        v-if="search"
                        @click="search = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition"
                        title="Clear"
                    >
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
                <div class="sm:col-span-4">
                    <select
                        v-model="statusFilter"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white"
                    >
                        <option value="">All Statuses</option>
                        <option value="processing">Processing</option>
                        <option value="delivered">Delivered</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="sm:col-span-2 flex items-center justify-end">
                    <button
                        v-if="search || statusFilter"
                        @click="resetFilters"
                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 text-slate-700 ring-1 ring-slate-200 px-3 py-2.5 text-xs font-bold hover:bg-slate-200 transition"
                    >
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        Reset
                    </button>
                </div>
            </div>

            <!-- Result count -->
            <p
                v-if="filteredOrders.length !== localOrders.length"
                class="mt-3 pt-3 border-t border-slate-100 text-xs font-bold text-slate-500 flex items-center gap-2"
            >
                <i class="fa-solid fa-filter text-emerald-600"></i>
                Showing {{ filteredOrders.length }} of
                {{ localOrders.length }} on this page
            </p>
        </div>

        <!-- ==================== DESKTOP TABLE ==================== -->
        <div
            class="hidden md:block rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-14"
                            >
                                SL
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Customer
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Shop
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Total
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-40"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                            >
                                Date
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-32"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(o, i) in filteredOrders"
                            :key="o.id"
                            class="hover:bg-slate-50/80 transition-colors"
                        >
                            <td
                                class="px-4 py-3 text-sm font-semibold text-slate-600"
                            >
                                {{ globalIndex(i) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-xs shadow"
                                    >
                                        <img
                                            v-if="o.user.image"
                                            :src="`/storage/${o.user.image}`"
                                            alt="User Image"
                                            class="h-10 w-10 object-cover rounded-full"
                                            @error="
                                                (e) =>
                                                    (e.target.src =
                                                        '/images/default.jpg')
                                            "
                                        />
                                        <i class="fa-solid fa-user" v-else></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="text-sm font-bold text-slate-800 line-clamp-1"
                                        >
                                            {{ o.name || "Guest" }}
                                        </p>
                                        <p
                                            class="text-xs text-slate-500 line-clamp-1"
                                        >
                                            <i
                                                class="fa-solid fa-phone text-[10px] mr-1"
                                            ></i>
                                            {{ o.phone || "—" }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    v-if="o.shop"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-200"
                                >
                                    <i
                                        class="fa-solid fa-store text-[10px]"
                                    ></i>
                                    {{ o.shop.name }}
                                </span>
                                <span v-else class="text-xs text-slate-400"
                                    >N/A</span
                                >
                            </td>
                            <td
                                class="px-4 py-3 text-right text-sm font-extrabold text-emerald-600 whitespace-nowrap"
                            >
                                ৳{{ formatMoney(o.total) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="relative">
                                    <select
                                        v-model="o.status"
                                        @change="changeStatus(o)"
                                        class="w-full rounded-lg border px-2.5 py-1.5 text-xs font-bold outline-none transition appearance-none cursor-pointer pr-7 capitalize"
                                        :class="statusClasses(o.status)"
                                    >
                                        <option value="processing">
                                            Processing
                                        </option>
                                        <option value="delivered">
                                            Delivered
                                        </option>
                                        <option value="canceled">
                                            Canceled
                                        </option>
                                    </select>
                                    <i
                                        class="fa-solid fa-chevron-down absolute right-2 top-1/2 -translate-y-1/2 text-[9px] pointer-events-none opacity-60"
                                    ></i>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600"
                                >
                                    <i
                                        class="fa-regular fa-calendar text-slate-400"
                                    ></i>
                                    {{ formatDate(o.created_at) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <button
                                        @click="openModal(o)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 text-blue-700 ring-1 ring-blue-200 px-2.5 py-1.5 text-xs font-bold hover:bg-blue-100 hover:scale-105 transition"
                                        title="View"
                                    >
                                        <i class="fa fa-eye text-[10px]"></i>
                                        View
                                    </button>
                                    <button
                                        @click="deleteOrder(o)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                        title="Delete"
                                    >
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty -->
            <div v-if="!filteredOrders.length" class="py-16 px-6 text-center">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                >
                    <i
                        class="fa-solid fa-cart-shopping text-3xl text-slate-400"
                    ></i>
                </div>
                <p class="text-lg font-bold text-slate-700 mb-1">
                    {{
                        search || statusFilter
                            ? "No matching orders"
                            : "No orders yet"
                    }}
                </p>
                <p class="text-sm text-slate-500 mb-4">
                    {{
                        search || statusFilter
                            ? "Try adjusting your filters."
                            : "Orders will appear here when customers check out."
                    }}
                </p>
                <button
                    v-if="search || statusFilter"
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-800 text-white font-bold text-sm px-4 py-2 hover:bg-slate-900 transition"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    Clear filters
                </button>
            </div>
        </div>

        <!-- ==================== MOBILE CARDS ==================== -->
        <div class="md:hidden space-y-3">
            <div
                v-for="(o, i) in filteredOrders"
                :key="o.id"
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden"
            >
                <!-- Card header -->
                <div class="flex items-start gap-3 p-4">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold shadow"
                    >
                        <img
                            v-if="o.user.image"
                            :src="`/storage/${o.user.image}`"
                            alt="User Image"
                            class="h-10 w-10 object-cover rounded-full"
                            @error="
                                (e) => (e.target.src = '/images/default.jpg')
                            "
                        />
                        <i class="fa-solid fa-user" v-else></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 mb-1 text-[10px]">
                            <span
                                class="inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600 ring-1 ring-slate-200"
                            >
                                #{{ globalIndex(i) }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 capitalize"
                                :class="statusClasses(o.status)"
                            >
                                <i
                                    :class="statusIcon(o.status)"
                                    class="text-[8px]"
                                ></i>
                                {{ o.status }}
                            </span>
                        </div>
                        <p
                            class="text-sm font-bold text-slate-800 line-clamp-1"
                        >
                            {{ o.name || "Guest" }}
                        </p>
                        <p class="text-xs text-slate-500 line-clamp-1">
                            <i class="fa-solid fa-phone text-[10px] mr-1"></i>
                            {{ o.phone || "—" }}
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <p
                            class="text-[9px] uppercase tracking-wider text-slate-500 font-bold"
                        >
                            Total
                        </p>
                        <p class="text-base font-extrabold text-emerald-600">
                            ৳{{ formatMoney(o.total) }}
                        </p>
                    </div>
                </div>

                <!-- Meta strip -->
                <div
                    class="grid grid-cols-2 gap-2 border-t border-slate-100 px-4 py-2.5"
                >
                    <div class="flex items-center gap-1.5 text-xs min-w-0">
                        <i
                            class="fa-solid fa-store text-blue-500 text-[10px] shrink-0"
                        ></i>
                        <span class="text-slate-600 truncate">
                            {{ o.shop?.name ?? "No shop" }}
                        </span>
                    </div>
                    <div
                        class="flex items-center gap-1.5 text-xs justify-end min-w-0"
                    >
                        <i
                            class="fa-regular fa-calendar text-slate-400 text-[10px] shrink-0"
                        ></i>
                        <span class="text-slate-600 truncate">
                            {{ formatDate(o.created_at) }}
                        </span>
                    </div>
                </div>

                <!-- Status selector -->
                <div class="px-4 pb-3">
                    <label
                        class="block text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                        >Update Status</label
                    >
                    <div class="relative">
                        <select
                            v-model="o.status"
                            @change="changeStatus(o)"
                            class="w-full rounded-lg border px-3 py-2 text-xs font-bold outline-none transition appearance-none cursor-pointer pr-8 capitalize"
                            :class="statusClasses(o.status)"
                        >
                            <option value="processing">Processing</option>
                            <option value="delivered">Delivered</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <i
                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[9px] pointer-events-none opacity-60"
                        ></i>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="grid grid-cols-2 border-t border-slate-100 divide-x divide-slate-100"
                >
                    <button
                        @click="openModal(o)"
                        class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-blue-700 hover:bg-blue-50 transition"
                    >
                        <i class="fa fa-eye text-[10px]"></i>
                        View Details
                    </button>
                    <button
                        @click="deleteOrder(o)"
                        class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-red-700 hover:bg-red-50 transition"
                    >
                        <i class="fa fa-trash text-[10px]"></i>
                        Delete
                    </button>
                </div>
            </div>

            <!-- Empty mobile -->
            <div
                v-if="!filteredOrders.length"
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm py-14 px-6 text-center"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                >
                    <i
                        class="fa-solid fa-cart-shopping text-3xl text-slate-400"
                    ></i>
                </div>
                <p class="text-lg font-bold text-slate-700 mb-1">
                    {{
                        search || statusFilter
                            ? "No matching orders"
                            : "No orders yet"
                    }}
                </p>
                <p class="text-sm text-slate-500 mb-4">
                    {{
                        search || statusFilter
                            ? "Try adjusting your filters."
                            : "Orders will appear here when customers check out."
                    }}
                </p>
                <button
                    v-if="search || statusFilter"
                    @click="resetFilters"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-800 text-white font-bold text-sm px-4 py-2 hover:bg-slate-900 transition"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    Clear filters
                </button>
            </div>
        </div>

        <!-- ==================== PAGINATION ==================== -->
        <div
            v-if="props.orders?.links?.length > 3"
            class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
        >
            <template v-for="(link, i) in props.orders.links" :key="i">
                <button
                    v-if="link.url"
                    @click="visitPage(link.url)"
                    class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold transition"
                    :class="
                        link.active
                            ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow'
                            : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:ring-emerald-300'
                    "
                    v-html="link.label"
                />
                <span
                    v-else
                    class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold text-slate-300 bg-slate-50 ring-1 ring-slate-100"
                    v-html="link.label"
                />
            </template>
        </div>
    </Layout>

    <!-- ============================================= -->
    <!-- ================= VIEW MODAL ================ -->
    <!-- ============================================= -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="showModal && selectedOrder"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
        >
            <div
                @click="closeModal"
                class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
            ></div>

            <div
                class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200"
            >
                <!-- Header -->
                <div
                    class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-4 rounded-t-2xl"
                >
                    <div class="min-w-0">
                        <p
                            class="text-emerald-300 text-[10px] font-semibold uppercase tracking-wider mb-0.5"
                        >
                            <i class="fa-solid fa-receipt mr-1"></i> Order #{{
                                selectedOrder.id
                            }}
                        </p>
                        <h3
                            class="text-lg sm:text-xl font-extrabold text-white truncate"
                        >
                            {{ selectedOrder.name || "Guest" }}
                        </h3>
                    </div>
                    <button
                        @click="closeModal"
                        class="shrink-0 flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white hover:bg-red-500 transition ring-1 ring-white/20"
                        title="Close"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <!-- Status + delivery pills -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 capitalize"
                            :class="statusClasses(selectedOrder.status)"
                        >
                            <i :class="statusIcon(selectedOrder.status)"></i>
                            {{ selectedOrder.status }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-slate-200"
                        >
                            <i class="fa-solid fa-truck text-[10px]"></i>
                            {{ deliveryLabel(selectedOrder.delivery_type) }}
                        </span>
                        <span
                            v-if="selectedOrder.payment_method"
                            class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-200 capitalize"
                        >
                            <i class="fa-solid fa-credit-card text-[10px]"></i>
                            {{ selectedOrder.payment_method }}
                        </span>
                    </div>

                    <!-- Customer info -->
                    <div
                        class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-4"
                    >
                        <p
                            class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-3"
                        >
                            <i class="fa-solid fa-user mr-1"></i>
                            Customer Info
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Name
                                </p>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ selectedOrder.name || "Guest" }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Phone
                                </p>
                                <a
                                    v-if="selectedOrder.phone"
                                    :href="`tel:${selectedOrder.phone}`"
                                    class="text-sm font-semibold text-slate-800 hover:text-emerald-700"
                                >
                                    {{ selectedOrder.phone }}
                                </a>
                                <p v-else class="text-sm text-slate-400">—</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Email
                                </p>
                                <a
                                    v-if="selectedOrder.email"
                                    :href="`mailto:${selectedOrder.email}`"
                                    class="text-sm font-semibold text-slate-800 hover:text-emerald-700 break-all"
                                >
                                    {{ selectedOrder.email }}
                                </a>
                                <p v-else class="text-sm text-slate-400">—</p>
                            </div>
                            <div
                                v-if="selectedOrder.shop"
                                class="sm:col-span-2"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Shop
                                </p>
                                <p class="text-sm font-semibold text-slate-800">
                                    <i
                                        class="fa-solid fa-store text-blue-500 mr-1"
                                    ></i>
                                    {{ selectedOrder.shop.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div>
                        <p
                            class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                        >
                            <i class="fa-solid fa-boxes-stacked mr-1"></i>
                            Ordered Products ({{
                                selectedOrder.items?.length ?? 0
                            }})
                        </p>

                        <div
                            v-if="selectedOrder.items?.length"
                            class="space-y-2"
                        >
                            <div
                                v-for="item in selectedOrder.items"
                                :key="item.id"
                                class="flex items-center gap-3 rounded-xl bg-white ring-1 ring-slate-200 p-3"
                            >
                                <img
                                    v-if="item.product?.image"
                                    :src="`/storage/${item.product.image}`"
                                    class="h-14 w-14 shrink-0 object-cover rounded-lg ring-1 ring-slate-200"
                                    :alt="item.product?.name"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                />
                                <div
                                    v-else
                                    class="h-14 w-14 shrink-0 rounded-lg bg-slate-100 flex items-center justify-center ring-1 ring-slate-200"
                                >
                                    <i
                                        class="fa-regular fa-image text-slate-400"
                                    ></i>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm font-bold text-slate-800 line-clamp-1"
                                    >
                                        {{ item.product?.name || "Product" }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        ৳{{ formatMoney(item.price) }} ×
                                        {{ item.quantity }}
                                    </p>
                                </div>

                                <p
                                    class="text-sm font-extrabold text-emerald-600 whitespace-nowrap"
                                >
                                    ৳{{
                                        formatMoney(item.price * item.quantity)
                                    }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-slate-400 italic">
                            No items in this order.
                        </p>
                    </div>

                    <!-- Summary -->
                    <div
                        class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 p-4"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-semibold text-slate-600"
                                    >Subtotal</span
                                >
                                <span class="text-sm font-bold text-slate-800">
                                    ৳{{ formatMoney(selectedOrder.subtotal) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-semibold text-slate-600"
                                    >Delivery</span
                                >
                                <span class="text-sm font-bold text-slate-800">
                                    ৳{{
                                        formatMoney(
                                            selectedOrder.delivery_charge,
                                        )
                                    }}
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between pt-2 border-t border-emerald-300"
                            >
                                <span class="text-sm font-bold text-slate-800"
                                    >Total</span
                                >
                                <span
                                    class="text-xl font-extrabold text-emerald-700"
                                >
                                    ৳{{ formatMoney(selectedOrder.total) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="sticky bottom-0 border-t border-slate-100 bg-slate-50 px-5 py-3 grid grid-cols-2 gap-2"
                >
                    <button
                        @click="closeModal"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-100 font-semibold py-2.5 transition"
                    >
                        <i class="fa-solid fa-xmark text-xs"></i>
                        Close
                    </button>
                    <button
                        @click="
                            closeModal();
                            deleteOrder(selectedOrder);
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold py-2.5 shadow transition"
                    >
                        <i class="fa fa-trash text-xs"></i>
                        Delete Order
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
