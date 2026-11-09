<script setup>
import { ref, watch, computed } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    stocks: { type: Object, required: true },
});

// Local copy so select updates instantly without waiting for reload
const localStocks = ref([...props.stocks.data]);

watch(
    () => props.stocks.data,
    (newVal) => {
        localStocks.value = [...newVal];
    },
);

const form = useForm({ status: "" });

// Track which row is currently saving
const savingId = ref(null);

// ==================== STATUS UPDATE ====================
const changeStatus = (item) => {
    const previous = item.status;
    savingId.value = item.id;
    form.status = item.status;

    form.post(`/admin/stock-money/update-status/${item.id}`, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            // Revert on validation error
            item.status = previous;
        },
        onFinish: () => {
            savingId.value = null;
        },
    });
};

// ==================== HELPERS ====================
const statusMeta = (status) => {
    switch (status) {
        case "approved":
            return {
                label: "Approved",
                icon: "fa-solid fa-circle-check",
                badge: "bg-green-100 text-green-700 border-green-200",
                select: "border-green-300 bg-green-50 text-green-700 focus:ring-green-500/30",
            };
        case "rejected":
            return {
                label: "Rejected",
                icon: "fa-solid fa-circle-xmark",
                badge: "bg-red-100 text-red-700 border-red-200",
                select: "border-red-300 bg-red-50 text-red-700 focus:ring-red-500/30",
            };
        default:
            return {
                label: "Pending",
                icon: "fa-solid fa-clock",
                badge: "bg-amber-100 text-amber-700 border-amber-200",
                select: "border-amber-300 bg-amber-50 text-amber-700 focus:ring-amber-500/30",
            };
    }
};

const formatAmount = (val) => {
    const n = Number(val ?? 0);
    return n.toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const initials = (name) => {
    if (!name) return "?";
    return name
        .split(" ")
        .map((w) => w[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();
};

// ==================== SUMMARY COUNTS ====================
const counts = computed(() => {
    const data = localStocks.value || [];
    return {
        total: props.stocks?.total ?? data.length,
        pending: data.filter((s) => s.status === "pending").length,
        approved: data.filter((s) => s.status === "approved").length,
        rejected: data.filter((s) => s.status === "rejected").length,
    };
});

const rowNumber = (index) => (props.stocks?.from ?? 1) + index;
</script>

<template>
    <Head title="Stock Money" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <Breadcrumb />

        <div class="min-h-screen bg-slate-50">
            <div>
                <!-- ==================== HEADER ==================== -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-lg px-5 sm:px-8 py-5 sm:py-6 mb-5 sm:mb-6"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="bg-white/20 backdrop-blur rounded-xl p-3"
                            >
                                <i
                                    class="fa-solid fa-money-bill-trend-up text-white text-xl sm:text-2xl"
                                ></i>
                            </div>
                            <div>
                                <h1
                                    class="text-white text-xl sm:text-2xl font-bold"
                                >
                                    Stock Money
                                </h1>
                                <p
                                    class="text-green-100 text-xs sm:text-sm mt-0.5"
                                >
                                    স্টক মানি রিকোয়েস্ট অনুমোদন ও ব্যবস্থাপনা
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-white/15 backdrop-blur rounded-xl px-4 py-2 text-center"
                        >
                            <p
                                class="text-green-100 text-[10px] sm:text-xs uppercase tracking-wide"
                            >
                                Total Requests
                            </p>
                            <p
                                class="text-white text-lg sm:text-2xl font-bold leading-tight"
                            >
                                {{ counts.total }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ==================== SUMMARY CARDS ==================== -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-5 sm:mb-6"
                >
                    <div
                        class="bg-white rounded-xl shadow-sm border border-amber-200 p-4 flex items-center gap-3"
                    >
                        <div
                            class="w-11 h-11 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"
                        >
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                        <div>
                            <p
                                class="text-xs text-slate-500 font-medium uppercase tracking-wide"
                            >
                                Pending
                            </p>
                            <p class="text-xl font-bold text-amber-600">
                                {{ counts.pending }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-xl shadow-sm border border-green-200 p-4 flex items-center gap-3"
                    >
                        <div
                            class="w-11 h-11 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0"
                        >
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                        <div>
                            <p
                                class="text-xs text-slate-500 font-medium uppercase tracking-wide"
                            >
                                Approved
                            </p>
                            <p class="text-xl font-bold text-green-600">
                                {{ counts.approved }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-xl shadow-sm border border-red-200 p-4 flex items-center gap-3"
                    >
                        <div
                            class="w-11 h-11 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0"
                        >
                            <i class="fa-solid fa-circle-xmark text-lg"></i>
                        </div>
                        <div>
                            <p
                                class="text-xs text-slate-500 font-medium uppercase tracking-wide"
                            >
                                Rejected
                            </p>
                            <p class="text-xl font-bold text-red-600">
                                {{ counts.rejected }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ==================== TABLE CARD ==================== -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                >
                    <div
                        class="px-4 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between"
                    >
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-list text-green-600"></i>
                            Stock Requests
                        </h2>
                        <span
                            class="text-xs font-semibold bg-green-50 text-green-700 px-3 py-1 rounded-full"
                        >
                            {{ counts.total }} total
                        </span>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="!localStocks || localStocks.length === 0"
                        class="p-10 sm:p-16 text-center"
                    >
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-3"
                        >
                            <i
                                class="fa-solid fa-inbox text-slate-400 text-2xl"
                            ></i>
                        </div>
                        <p class="text-slate-600 font-semibold">
                            কোনো স্টক রিকোয়েস্ট নেই
                        </p>
                        <p class="text-sm text-slate-400 mt-1">
                            নতুন রিকোয়েস্ট এলে এখানে দেখা যাবে
                        </p>
                    </div>

                    <div v-else>
                        <!-- ==================== DESKTOP TABLE ==================== -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wide"
                                    >
                                        <th
                                            class="px-4 py-3 text-left font-semibold w-16"
                                        >
                                            SL
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold"
                                        >
                                            User
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-semibold"
                                        >
                                            Amount
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left font-semibold w-56"
                                        >
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="(item, index) in localStocks"
                                        :key="item.id"
                                        class="hover:bg-green-50/40 transition-colors"
                                    >
                                        <td
                                            class="px-4 py-3 text-slate-500 font-medium"
                                        >
                                            {{ rowNumber(index) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="w-9 h-9 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0 shadow-sm"
                                                >
                                                    <img
                                                        v-if="item.user.image"
                                                        :src="`/storage/${item.user.image}`"
                                                        alt="user image"
                                                        class="w-9 h-9 rounded-full object-cover"
                                                        @error="
                                                            (e) =>
                                                                (e.target.src =
                                                                    '/images/default.jpg')
                                                        "
                                                    />
                                                    <span v-else>{{
                                                        initials(
                                                            item.user?.name,
                                                        )
                                                    }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <p
                                                        class="font-semibold text-slate-800 truncate"
                                                    >
                                                        {{
                                                            item.user?.name ??
                                                            "Unknown User"
                                                        }}
                                                    </p>
                                                    <p
                                                        v-if="item.user?.phone"
                                                        class="text-xs text-slate-500 truncate"
                                                    >
                                                        {{ item.user.phone }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-right font-bold text-slate-800 whitespace-nowrap"
                                        >
                                            ৳ {{ formatAmount(item.amount) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div
                                                class="relative flex items-center gap-2"
                                            >
                                                <select
                                                    v-model="item.status"
                                                    @change="changeStatus(item)"
                                                    :disabled="
                                                        savingId === item.id
                                                    "
                                                    class="w-full border rounded-lg pl-3 pr-9 py-2 text-sm font-semibold focus:outline-none focus:ring-2 transition disabled:opacity-60 disabled:cursor-wait"
                                                    :class="
                                                        statusMeta(item.status)
                                                            .select
                                                    "
                                                >
                                                    <option value="pending">
                                                        Pending
                                                    </option>
                                                    <option value="approved">
                                                        Approved
                                                    </option>
                                                    <option value="rejected">
                                                        Rejected
                                                    </option>
                                                </select>

                                                <i
                                                    v-if="savingId === item.id"
                                                    class="fa-solid fa-spinner fa-spin text-slate-500 absolute right-3 pointer-events-none"
                                                ></i>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- ==================== MOBILE CARDS ==================== -->
                        <div class="md:hidden divide-y divide-slate-100">
                            <div
                                v-for="(item, index) in localStocks"
                                :key="item.id"
                                class="p-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-3 mb-3"
                                >
                                    <div
                                        class="flex items-center gap-2 min-w-0"
                                    >
                                        <span
                                            class="bg-green-100 text-green-700 text-xs font-bold w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                                        >
                                            {{ rowNumber(index) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p
                                                class="font-bold text-slate-800 text-sm truncate"
                                            >
                                                {{
                                                    item.user?.name ??
                                                    "Unknown User"
                                                }}
                                            </p>
                                            <p
                                                v-if="item.user?.phone"
                                                class="text-xs text-slate-500 truncate"
                                            >
                                                {{ item.user.phone }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p
                                            class="text-xs text-slate-400 uppercase tracking-wide"
                                        >
                                            Amount
                                        </p>
                                        <p
                                            class="font-bold text-slate-800 whitespace-nowrap"
                                        >
                                            ৳ {{ formatAmount(item.amount) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-semibold px-2.5 py-1 rounded-full border flex items-center gap-1.5"
                                        :class="statusMeta(item.status).badge"
                                    >
                                        <i
                                            :class="
                                                statusMeta(item.status).icon
                                            "
                                        ></i>
                                        {{ statusMeta(item.status).label }}
                                    </span>
                                </div>

                                <div class="mt-3 relative">
                                    <select
                                        v-model="item.status"
                                        @change="changeStatus(item)"
                                        :disabled="savingId === item.id"
                                        class="w-full border rounded-lg pl-3 pr-9 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 transition disabled:opacity-60 disabled:cursor-wait"
                                        :class="statusMeta(item.status).select"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="approved">
                                            Approved
                                        </option>
                                        <option value="rejected">
                                            Rejected
                                        </option>
                                    </select>
                                    <i
                                        v-if="savingId === item.id"
                                        class="fa-solid fa-spinner fa-spin text-slate-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"
                                    ></i>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== PAGINATION ==================== -->
                        <div
                            v-if="stocks.links && stocks.links.length > 3"
                            class="px-4 sm:px-6 py-4 border-t border-slate-200"
                        >
                            <Pagination :links="stocks.links" />
                        </div>
                    </div>
                </div>

                <!-- Hint -->
                <div
                    class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3"
                >
                    <i
                        class="fa-solid fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"
                    ></i>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        <strong>টিপস:</strong> স্ট্যাটাস পরিবর্তন করলে সাথে
                        সাথেই সেভ হয়ে যাবে। Approved করলে ইউজারের স্টকে পরিমাণ
                        যোগ হবে, Rejected করলে অনুরোধ বাতিল হবে।
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>
