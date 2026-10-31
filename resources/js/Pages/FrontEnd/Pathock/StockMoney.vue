<script setup>
import { ref, computed, onBeforeUnmount, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Pagination from "@/Shared/Pagination.vue";
import { route } from "ziggy-js";

const props = defineProps({
    stocks: { type: Object, default: () => ({ data: [], links: [] }) },
});

const editId = ref(null);
const showModal = ref(false);
const deletingId = ref(null);

const form = useForm({
    amount: "",
});

// ==================== MODAL OPEN/CLOSE ====================
function openCreate() {
    editId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEdit(stock) {
    editId.value = stock.id;
    form.amount = stock.amount;
    form.clearErrors();
    showModal.value = true;
}

function closeModal() {
    if (form.processing) return;
    showModal.value = false;
    editId.value = null;
    form.reset();
    form.clearErrors();
}

// Lock body scroll while modal is open
watch(showModal, (open) => {
    document.body.style.overflow = open ? "hidden" : "";
});
onBeforeUnmount(() => {
    document.body.style.overflow = "";
});

// Esc key to close
function onKeydown(e) {
    if (e.key === "Escape" && showModal.value) closeModal();
}
if (typeof window !== "undefined") {
    window.addEventListener("keydown", onKeydown);
    onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));
}

// ==================== SUBMIT ====================
function submit() {
    if (editId.value) {
        form.put(route("stock-money.update", editId.value), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        form.post(route("stock-money.store"), {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        });
    }
}

// ==================== DELETE ====================
function deleteStock(id) {
    if (!confirm("Delete this item?")) return;
    deletingId.value = id;
    form.delete(route("stock-money.destroy", id), {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
}

// ==================== HELPERS ====================
const statusMeta = {
    approved: {
        label: "Approved",
        class: "bg-green-100 text-green-700 ring-green-200",
        dot: "bg-green-500",
        icon: "fa-solid fa-circle-check",
    },
    pending: {
        label: "Pending",
        class: "bg-amber-100 text-amber-700 ring-amber-200",
        dot: "bg-amber-500",
        icon: "fa-solid fa-clock",
    },
    rejected: {
        label: "Rejected",
        class: "bg-red-100 text-red-700 ring-red-200",
        dot: "bg-red-500",
        icon: "fa-solid fa-circle-xmark",
    },
};
function statusFor(s) {
    return (
        statusMeta[s] ?? {
            label: s ?? "—",
            class: "bg-slate-100 text-slate-600 ring-slate-200",
            dot: "bg-slate-400",
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
const totalAmount = computed(() =>
    (props.stocks.data ?? []).reduce((s, r) => s + Number(r.amount ?? 0), 0),
);
const approvedCount = computed(
    () =>
        (props.stocks.data ?? []).filter((r) => r.status === "approved").length,
);
const pendingCount = computed(
    () =>
        (props.stocks.data ?? []).filter((r) => r.status === "pending").length,
);
</script>

<template>
    <Layout>
        <Head title="Stock Money" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="space-y-5 sm:space-y-6">
            <!-- ==================== HEADER ==================== -->
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
                            <i class="fa-solid fa-arrow-trend-up mr-1.5"></i>
                            Inventory
                        </p>
                        <h1 class="text-2xl sm:text-3xl font-extrabold mt-1">
                            Stock Money
                        </h1>
                        <p class="text-green-100 text-sm mt-1">
                            Add, edit and track your stock money entries.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openCreate"
                        class="inline-flex items-center gap-2 bg-white text-green-700 hover:bg-green-50 font-bold text-sm px-4 py-2.5 rounded-xl shadow transition self-start sm:self-auto"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Add Stock Money
                    </button>
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
                            ৳{{ formatMoney(totalAmount) }}
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
                            Approved
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800"
                        >
                            {{ approvedCount }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center gap-3"
                >
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shrink-0"
                    >
                        <i class="fa-solid fa-clock text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                        >
                            Pending
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800"
                        >
                            {{ pendingCount }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ==================== EMPTY STATE ==================== -->
            <div
                v-if="!stocks.data?.length"
                class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center"
            >
                <div
                    class="mx-auto h-16 w-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4"
                >
                    <i class="fa-solid fa-box-open text-2xl text-slate-400"></i>
                </div>
                <p class="text-slate-700 font-bold text-lg">
                    No stock money entries yet
                </p>
                <p class="text-slate-500 text-sm mt-1">
                    Add your first entry to start tracking.
                </p>
                <button
                    @click="openCreate"
                    class="mt-4 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2.5 rounded-lg transition"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add Stock Money
                </button>
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
                                        #
                                    </th>
                                    <th
                                        class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider"
                                    >
                                        Amount
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
                                    v-for="(stock, index) in stocks.data"
                                    :key="stock.id"
                                    class="border-b border-slate-100 last:border-0 hover:bg-green-50/40 transition"
                                >
                                    <td class="p-3 text-sm text-slate-500">
                                        {{ (stocks.from ?? 0) + index }}
                                    </td>

                                    <td class="p-3">
                                        <span
                                            class="text-sm font-extrabold text-slate-800"
                                        >
                                            ৳ {{ formatMoney(stock.amount) }}
                                        </span>
                                    </td>

                                    <td class="p-3">
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full ring-1"
                                            :class="
                                                statusFor(stock.status).class
                                            "
                                        >
                                            <i
                                                :class="[
                                                    statusFor(stock.status)
                                                        .icon,
                                                    'text-[10px]',
                                                ]"
                                            ></i>
                                            {{ statusFor(stock.status).label }}
                                        </span>
                                    </td>

                                    <td class="p-3">
                                        <div class="flex gap-2 justify-end">
                                            <button
                                                @click="openEdit(stock)"
                                                class="inline-flex items-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1.5 rounded-lg transition"
                                            >
                                                <i
                                                    class="fa-solid fa-pen-to-square"
                                                ></i>
                                                Edit
                                            </button>

                                            <button
                                                @click="deleteStock(stock.id)"
                                                :disabled="
                                                    deletingId === stock.id
                                                "
                                                class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded-lg transition disabled:opacity-60"
                                            >
                                                <i
                                                    :class="
                                                        deletingId === stock.id
                                                            ? 'fa-solid fa-spinner fa-spin'
                                                            : 'fa-solid fa-trash'
                                                    "
                                                ></i>
                                                Delete
                                            </button>
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
                        v-for="(stock, index) in stocks.data"
                        :key="stock.id"
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p
                                    class="text-[11px] font-bold text-slate-400 uppercase tracking-wide"
                                >
                                    #{{ (stocks.from ?? 0) + index }}
                                </p>
                                <p
                                    class="text-xl font-extrabold text-slate-800 mt-0.5 truncate"
                                >
                                    ৳ {{ formatMoney(stock.amount) }}
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full ring-1 shrink-0"
                                :class="statusFor(stock.status).class"
                            >
                                <i
                                    :class="[
                                        statusFor(stock.status).icon,
                                        'text-[9px]',
                                    ]"
                                ></i>
                                {{ statusFor(stock.status).label }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <button
                                @click="openEdit(stock)"
                                class="inline-flex items-center justify-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-bold py-2 rounded-lg transition"
                            >
                                <i class="fa-solid fa-pen-to-square"></i>
                                Edit
                            </button>
                            <button
                                @click="deleteStock(stock.id)"
                                :disabled="deletingId === stock.id"
                                class="inline-flex items-center justify-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-bold py-2 rounded-lg transition disabled:opacity-60"
                            >
                                <i
                                    :class="
                                        deletingId === stock.id
                                            ? 'fa-solid fa-spinner fa-spin'
                                            : 'fa-solid fa-trash'
                                    "
                                ></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <Pagination :links="stocks.links" class="mt-2" />
            </template>
        </div>

        <!-- ==================== ADD/EDIT MODAL ==================== -->
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
                    v-if="showModal"
                    class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
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
                            v-if="showModal"
                            class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                        >
                            <!-- Modal header -->
                            <div
                                class="bg-gradient-to-r from-green-600 to-green-700 px-5 py-4 text-white flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center ring-1 ring-white/30"
                                    >
                                        <i
                                            :class="
                                                editId
                                                    ? 'fa-solid fa-pen-to-square'
                                                    : 'fa-solid fa-plus'
                                            "
                                        ></i>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[11px] uppercase tracking-wider text-green-100 font-bold"
                                        >
                                            {{
                                                editId
                                                    ? "Edit Entry"
                                                    : "New Entry"
                                            }}
                                        </p>
                                        <h2
                                            class="text-lg font-extrabold leading-tight"
                                        >
                                            {{
                                                editId
                                                    ? "Update Stock Money"
                                                    : "Add Stock Money"
                                            }}
                                        </h2>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="closeModal"
                                    :disabled="form.processing"
                                    class="h-9 w-9 rounded-lg text-white/90 hover:text-white hover:bg-white/15 transition disabled:opacity-50"
                                >
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>

                            <!-- Modal body -->
                            <form
                                @submit.prevent="submit"
                                class="p-5 space-y-4"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1.5"
                                    >
                                        Amount
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold"
                                        >
                                            ৳
                                        </span>
                                        <input
                                            v-model="form.amount"
                                            type="number"
                                            step="0.01"
                                            min="1"
                                            placeholder="0.00"
                                            autofocus
                                            class="w-full pl-8 pr-3 py-2.5 rounded-lg border border-slate-300 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 outline-none text-sm transition"
                                        />
                                    </div>

                                    <p
                                        v-if="form.errors.amount"
                                        class="text-red-500 text-xs mt-1.5"
                                    >
                                        <i
                                            class="fa-solid fa-circle-exclamation mr-1"
                                        ></i>
                                        {{ form.errors.amount }}
                                    </p>
                                </div>

                                <div
                                    class="flex flex-col sm:flex-row gap-2 pt-1"
                                >
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white text-sm font-bold py-2.5 rounded-lg shadow transition"
                                    >
                                        <i
                                            :class="
                                                form.processing
                                                    ? 'fa-solid fa-spinner fa-spin'
                                                    : 'fa-solid fa-check'
                                            "
                                        ></i>
                                        {{
                                            form.processing
                                                ? "Saving..."
                                                : editId
                                                  ? "Update"
                                                  : "Save"
                                        }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        :disabled="form.processing"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 disabled:opacity-60 text-slate-700 text-sm font-bold py-2.5 rounded-lg transition"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </Layout>
</template>
