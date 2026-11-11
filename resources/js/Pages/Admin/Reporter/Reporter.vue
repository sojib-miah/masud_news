<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";

const props = defineProps({
    reporters: Object,
    search: String,
});

const search = ref(props.search || "");

// ==================== STATUS UPDATE ====================
const form = useForm({});
const localreporters = ref([...props.reporters.data]);

watch(
    () => props.reporters.data,
    (newVal) => {
        localreporters.value = [...newVal];
    },
);

const changeStatus = (reporter) => {
    form.transform(() => ({
        status: reporter.status,
    })).put(route("reporters.updateStatus", reporter.id), {
        preserveScroll: true,
        preserveState: true,
    });
};

// ==================== SEARCH ====================
function fetchUsers() {
    router.get(
        "/admin/reporter",
        { search: search.value },
        { preserveState: true, preserveScroll: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/reporter", {}, { preserveState: false });
}

function highlight(text) {
    if (!text || !search.value) return text;
    const escaped = search.value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escaped})`, "gi");
    return text.replace(
        regex,
        '<span class="bg-yellow-300 rounded px-0.5">$1</span>',
    );
}

function deleteUser(id) {
    if (confirm("Are you sure you want to delete this reporter?")) {
        router.delete(`/admin/pathock/${id}`, { preserveScroll: true });
    }
}

// ==================== VIEW MODAL ====================
const showModal = ref(false);
const selectedReporter = ref(null);

const openModal = (user) => {
    selectedReporter.value = user;
    showModal.value = true;
    document.body.style.overflow = "hidden";
};

const closeModal = () => {
    showModal.value = false;
    selectedReporter.value = null;
    document.body.style.overflow = "";
};

// ==================== HELPERS ====================
function globalIndex(i) {
    const page = props.reporters?.current_page ?? 1;
    const perPage = props.reporters?.per_page ?? 10;
    return (page - 1) * perPage + i + 1;
}

function visitPage(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
}

function initials(name) {
    if (!name) return "?";
    return name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0])
        .join("")
        .toUpperCase();
}

// Profile convenience
const profile = computed(
    () => selectedReporter.value?.reporter_profile ?? null,
);

// Location chain for modal
const locationChain = computed(() => {
    if (!profile.value) return [];
    const p = profile.value;
    const parts = [];
    if (p.division_data?.bn_name || p.division_data?.name)
        parts.push(p.division_data.bn_name || p.division_data.name);
    if (p.district_data?.bn_name || p.district_data?.name)
        parts.push(p.district_data.bn_name || p.district_data.name);
    if (p.upazila_data?.bn_name || p.upazila_data?.name)
        parts.push(p.upazila_data.bn_name || p.upazila_data.name);
    if (p.union_data?.bn_name || p.union_data?.name)
        parts.push(p.union_data.bn_name || p.union_data.name);
    if (p.city_corporation_data?.bn_name || p.city_corporation_data?.name)
        parts.push(
            p.city_corporation_data.bn_name || p.city_corporation_data?.name,
        );
    if (p.ward_data?.bn_name || p.ward_data?.name)
        parts.push(p.ward_data.bn_name || p.ward_data.name);
    if (p.pourashava_data?.bn_name || p.pourashava_data?.name)
        parts.push(p.pourashava_data.bn_name || p.pourashava_data.name);
    if (p.pourashava_ward_data?.bn_name || p.pourashava_ward_data?.name)
        parts.push(
            p.pourashava_ward_data.bn_name || p.pourashava_ward_data.name,
        );
    if (p.village) parts.push(p.village);
    return parts;
});

// Status color
function statusClasses(status) {
    return status === "approved"
        ? "bg-emerald-100 text-emerald-700 ring-emerald-200"
        : "bg-amber-100 text-amber-700 ring-amber-200";
}

// File preview detection
function isPdf(path) {
    return /\.pdf$/i.test(path || "");
}

function fileUrl(path) {
    return `/storage/${path}`;
}
</script>

<template>
    <div>
        <Head title="Reporter List Page" />

        <Layout>
            <Breadcrumb />

            <FlashMessage
                v-if="$page.props.flash?.status"
                :message="$page.props.flash.status"
                type="success"
            />

            <!-- ==================== PAGE HEADER ==================== -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
            >
                <div
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-violet-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-blue-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-id-badge mr-1"></i> Users
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Reporter Management
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Review, approve & manage reporter accounts.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <i class="fa-solid fa-database text-blue-300"></i>
                        Total: {{ props.reporters?.total ?? 0 }}
                    </div>
                </div>
            </div>

            <!-- ==================== SEARCH ==================== -->
            <div
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-4 mb-5"
            >
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <SearchFilter
                            v-model="search"
                            placeholder="Search by name, email or phone..."
                            @search="fetchUsers"
                            @reset="resetSearch"
                        />
                    </div>
                    <p
                        class="text-xs font-bold text-slate-500 whitespace-nowrap text-center sm:text-right"
                    >
                        <i class="fa-solid fa-filter mr-1"></i>
                        {{ props.reporters?.data?.length ?? 0 }} shown
                    </p>
                </div>
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
                                    #
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Reporter
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Phone
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Types
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-40"
                                >
                                    Status
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
                                v-for="(user, index) in props.reporters?.data"
                                :key="user.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td
                                    class="px-4 py-3 text-sm font-semibold text-slate-600"
                                >
                                    {{ globalIndex(index) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img
                                            v-if="user.image"
                                            :src="`/storage/${user.image}`"
                                            class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-white shadow"
                                            :alt="user.name"
                                        />
                                        <div
                                            v-else
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold text-xs shadow"
                                        >
                                            {{ initials(user.name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-sm font-bold text-slate-800 line-clamp-1"
                                                v-html="highlight(user.name)"
                                            ></p>
                                            <p
                                                class="text-xs text-slate-500 line-clamp-1"
                                                v-html="
                                                    highlight(user.email || '—')
                                                "
                                            ></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700"
                                    >
                                        <i
                                            class="fa-solid fa-phone text-slate-400 text-xs"
                                        ></i>
                                        {{ user.phone || "—" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        <span
                                            v-for="(
                                                type, i
                                            ) in user.reporter_type_names"
                                            :key="i"
                                            class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700 ring-1 ring-blue-200"
                                        >
                                            {{ type }}
                                        </span>
                                        <span
                                            v-if="
                                                !user.reporter_type_names
                                                    ?.length
                                            "
                                            class="text-xs text-slate-400"
                                            >—</span
                                        >
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <select
                                        v-model="user.status"
                                        @change="changeStatus(user)"
                                        class="w-full rounded-lg border px-2.5 py-1.5 text-xs font-bold outline-none transition focus:ring-2 capitalize"
                                        :class="[
                                            statusClasses(user.status),
                                            'focus:ring-blue-200',
                                        ]"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="approved">
                                            Approved
                                        </option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <button
                                            @click="openModal(user)"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 text-blue-700 ring-1 ring-blue-200 px-2.5 py-1.5 text-xs font-bold hover:bg-blue-100 hover:scale-105 transition"
                                            title="View Details"
                                        >
                                            <i
                                                class="fa fa-eye text-[10px]"
                                            ></i>
                                            View
                                        </button>
                                        <button
                                            @click="deleteUser(user.id)"
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
                <div
                    v-if="!props.reporters?.data?.length"
                    class="py-14 px-6 text-center"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                    >
                        <i
                            class="fa-solid fa-user-slash text-3xl text-slate-400"
                        ></i>
                    </div>
                    <p class="text-lg font-bold text-slate-700 mb-1">
                        {{
                            search
                                ? "No matching reporters"
                                : "No reporters yet"
                        }}
                    </p>
                    <p class="text-sm text-slate-500">
                        {{
                            search
                                ? "Try a different search term."
                                : "Reporter applications will appear here."
                        }}
                    </p>
                </div>
            </div>

            <!-- ==================== MOBILE CARDS ==================== -->
            <div class="md:hidden space-y-3">
                <div
                    v-for="(user, index) in props.reporters?.data"
                    :key="user.id"
                    class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden"
                >
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <img
                                v-if="user.image"
                                :src="`/storage/${user.image}`"
                                class="h-12 w-12 shrink-0 rounded-full object-cover ring-2 ring-white shadow"
                                :alt="user.name"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold shadow"
                            >
                                {{ initials(user.name) }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div
                                    class="flex items-center gap-1.5 mb-1 text-[10px]"
                                >
                                    <span
                                        class="inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600 ring-1 ring-slate-200"
                                    >
                                        #{{ globalIndex(index) }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 capitalize"
                                        :class="statusClasses(user.status)"
                                    >
                                        <i
                                            :class="
                                                user.status === 'approved'
                                                    ? 'fa-solid fa-circle-check'
                                                    : 'fa-solid fa-clock'
                                            "
                                            class="text-[8px]"
                                        ></i>
                                        {{ user.status }}
                                    </span>
                                </div>
                                <p
                                    class="text-sm font-bold text-slate-800 line-clamp-1"
                                    v-html="highlight(user.name)"
                                ></p>
                                <p
                                    class="text-xs text-slate-500 line-clamp-1"
                                    v-html="highlight(user.email || '—')"
                                ></p>
                            </div>
                        </div>

                        <!-- Reporter types -->
                        <div
                            v-if="user.reporter_type_names?.length"
                            class="mt-3 flex flex-wrap gap-1"
                        >
                            <span
                                v-for="(type, i) in user.reporter_type_names"
                                :key="i"
                                class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 ring-1 ring-blue-200"
                            >
                                {{ type }}
                            </span>
                        </div>

                        <!-- Phone + status -->
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div
                                class="flex items-center gap-2 rounded-lg bg-slate-50 ring-1 ring-slate-100 px-2.5 py-1.5 min-w-0"
                            >
                                <i
                                    class="fa-solid fa-phone text-slate-400 text-xs shrink-0"
                                ></i>
                                <span
                                    class="text-xs font-semibold text-slate-700 truncate"
                                >
                                    {{ user.phone || "—" }}
                                </span>
                            </div>

                            <select
                                v-model="user.status"
                                @change="changeStatus(user)"
                                class="w-full rounded-lg border px-2 py-1.5 text-xs font-bold outline-none transition capitalize"
                                :class="statusClasses(user.status)"
                            >
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                            </select>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 border-t border-slate-100 divide-x divide-slate-100"
                    >
                        <button
                            @click="openModal(user)"
                            class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-blue-700 hover:bg-blue-50 transition"
                        >
                            <i class="fa fa-eye text-[10px]"></i>
                            View Details
                        </button>
                        <button
                            @click="deleteUser(user.id)"
                            class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-red-700 hover:bg-red-50 transition"
                        >
                            <i class="fa fa-trash text-[10px]"></i>
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Empty mobile -->
                <div
                    v-if="!props.reporters?.data?.length"
                    class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm py-14 px-6 text-center"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                    >
                        <i
                            class="fa-solid fa-user-slash text-3xl text-slate-400"
                        ></i>
                    </div>
                    <p class="text-lg font-bold text-slate-700 mb-1">
                        {{
                            search
                                ? "No matching reporters"
                                : "No reporters yet"
                        }}
                    </p>
                    <p class="text-sm text-slate-500">
                        {{
                            search
                                ? "Try a different search term."
                                : "Reporter applications will appear here."
                        }}
                    </p>
                </div>
            </div>

            <!-- ==================== PAGINATION ==================== -->
            <div
                v-if="props.reporters?.links?.length > 3"
                class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
            >
                <template v-for="(link, i) in props.reporters.links" :key="i">
                    <button
                        v-if="link.url"
                        @click="visitPage(link.url)"
                        class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold transition"
                        :class="
                            link.active
                                ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow'
                                : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-blue-50 hover:text-blue-700 hover:ring-blue-300'
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
                v-if="showModal && selectedReporter"
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
                        <div class="flex items-center gap-3 min-w-0">
                            <img
                                v-if="selectedReporter.image"
                                :src="`/storage/${selectedReporter.image}`"
                                class="h-12 w-12 rounded-full object-cover ring-2 ring-white/30"
                                :alt="selectedReporter.name"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold shadow ring-2 ring-white/20"
                            >
                                {{ initials(selectedReporter.name) }}
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="text-blue-300 text-[10px] font-semibold uppercase tracking-wider mb-0.5"
                                >
                                    <i class="fa-solid fa-id-badge mr-1"></i>
                                    Reporter
                                </p>
                                <h3
                                    class="text-lg sm:text-xl font-extrabold text-white truncate"
                                >
                                    {{ selectedReporter.name }}
                                </h3>
                            </div>
                        </div>
                        <button
                            @click="closeModal"
                            class="shrink-0 flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white hover:bg-red-500 transition ring-1 ring-white/20"
                            title="Close"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-5 sm:p-6 space-y-4">
                        <!-- Status pill -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold ring-1 capitalize"
                                :class="statusClasses(selectedReporter.status)"
                            >
                                <i
                                    :class="
                                        selectedReporter.status === 'approved'
                                            ? 'fa-solid fa-circle-check'
                                            : 'fa-solid fa-clock'
                                    "
                                ></i>
                                Status: {{ selectedReporter.status }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 ring-1 ring-slate-200"
                            >
                                <i class="fa-solid fa-hashtag text-[10px]"></i>
                                ID: {{ selectedReporter.id }}
                            </span>
                        </div>

                        <!-- Contact -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div
                                class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                                >
                                    <i
                                        class="fa-solid fa-envelope text-sky-600 mr-1"
                                    ></i>
                                    Email
                                </p>
                                <a
                                    v-if="selectedReporter.email"
                                    :href="`mailto:${selectedReporter.email}`"
                                    class="text-sm font-semibold text-slate-800 break-all hover:text-sky-700"
                                >
                                    {{ selectedReporter.email }}
                                </a>
                                <p v-else class="text-sm text-slate-400">—</p>
                            </div>
                            <div
                                class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                                >
                                    <i
                                        class="fa-solid fa-phone text-emerald-600 mr-1"
                                    ></i>
                                    Phone
                                </p>
                                <a
                                    v-if="selectedReporter.phone"
                                    :href="`tel:${selectedReporter.phone}`"
                                    class="text-sm font-semibold text-slate-800 hover:text-emerald-700"
                                >
                                    {{ selectedReporter.phone }}
                                </a>
                                <p v-else class="text-sm text-slate-400">—</p>
                            </div>
                        </div>

                        <!-- Personal Info -->
                        <div
                            class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden"
                        >
                            <div
                                class="bg-slate-50 border-b border-slate-100 px-4 py-2"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    <i
                                        class="fa-solid fa-user text-slate-600 mr-1"
                                    ></i>
                                    Personal Information
                                </p>
                            </div>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4"
                            >
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Date of Birth
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile?.dob || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Blood Group
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile?.blood_group || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Father's Name
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile?.father_name || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Mother's Name
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile?.mother_name || "—" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Education -->
                        <div
                            class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden"
                        >
                            <div
                                class="bg-slate-50 border-b border-slate-100 px-4 py-2"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    <i
                                        class="fa-solid fa-graduation-cap text-slate-600 mr-1"
                                    ></i>
                                    Education
                                </p>
                            </div>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4"
                            >
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Qualification
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile?.education || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Year
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile?.education_year || "—" }}
                                    </p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        NID Number
                                    </p>
                                    <p
                                        class="text-sm font-mono font-semibold text-slate-800"
                                    >
                                        {{ profile?.nid_number || "—" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div
                            class="rounded-xl bg-emerald-50/60 ring-1 ring-emerald-200 p-4"
                        >
                            <p
                                class="text-[10px] uppercase tracking-wider text-emerald-700 font-bold mb-2"
                            >
                                <i class="fa-solid fa-location-dot mr-1"></i>
                                Location
                            </p>
                            <div
                                v-if="locationChain.length"
                                class="flex flex-wrap items-center gap-1.5"
                            >
                                <template
                                    v-for="(part, i) in locationChain"
                                    :key="i"
                                >
                                    <span
                                        class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200"
                                    >
                                        {{ part }}
                                    </span>
                                    <i
                                        v-if="i < locationChain.length - 1"
                                        class="fa-solid fa-angle-right text-emerald-400 text-[10px]"
                                    ></i>
                                </template>
                            </div>
                            <p v-else class="text-sm text-slate-400 italic">
                                No location info provided
                            </p>

                            <div
                                v-if="
                                    profile?.address || profile?.reporter_area
                                "
                                class="mt-3 pt-3 border-t border-emerald-200 space-y-1"
                            >
                                <p
                                    v-if="profile.address"
                                    class="text-xs text-slate-700"
                                >
                                    <i
                                        class="fa-solid fa-map-pin text-emerald-600 mr-1"
                                    ></i>
                                    <strong>Address:</strong>
                                    {{ profile.address }}
                                </p>
                                <p
                                    v-if="profile.reporter_area"
                                    class="text-xs text-slate-700"
                                >
                                    <i
                                        class="fa-solid fa-bullseye text-emerald-600 mr-1"
                                    ></i>
                                    <strong>Reporter Area:</strong>
                                    {{ profile.reporter_area }}
                                </p>
                            </div>
                        </div>

                        <!-- Reporter Types -->
                        <div>
                            <p
                                class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                            >
                                <i class="fa-solid fa-id-badge mr-1"></i>
                                Reporter Types
                            </p>
                            <div
                                v-if="
                                    selectedReporter.reporter_type_names?.length
                                "
                                class="flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="(
                                        type, i
                                    ) in selectedReporter.reporter_type_names"
                                    :key="i"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-200"
                                >
                                    <i class="fa-solid fa-tag text-[10px]"></i>
                                    {{ type }}
                                </span>
                            </div>
                            <p v-else class="text-sm text-slate-400 italic">
                                No reporter types assigned
                            </p>
                        </div>

                        <!-- Files -->
                        <div
                            class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden"
                        >
                            <div
                                class="bg-slate-50 border-b border-slate-100 px-4 py-2"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    <i
                                        class="fa-solid fa-paperclip text-slate-600 mr-1"
                                    ></i>
                                    Documents
                                </p>
                            </div>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4"
                            >
                                <!-- Signature -->
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                                    >
                                        Signature
                                    </p>
                                    <div
                                        v-if="profile?.signature"
                                        class="rounded-lg ring-1 ring-slate-200 overflow-hidden bg-slate-50"
                                    >
                                        <img
                                            :src="fileUrl(profile.signature)"
                                            class="w-full max-h-40 object-contain"
                                            alt="Signature"
                                            @error="
                                                (e) =>
                                                    (e.target.src =
                                                        '/images/default.jpg')
                                            "
                                        />
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm text-slate-400 italic"
                                    >
                                        Not uploaded
                                    </p>
                                </div>

                                <!-- NID File -->
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                                    >
                                        NID / Passport
                                    </p>
                                    <div
                                        v-if="profile?.nid_file"
                                        class="rounded-lg ring-1 ring-slate-200 overflow-hidden bg-slate-50"
                                    >
                                        <img
                                            v-if="!isPdf(profile.nid_file)"
                                            :src="fileUrl(profile.nid_file)"
                                            class="w-full max-h-40 object-contain"
                                            alt="NID File"
                                            @error="
                                                (e) =>
                                                    (e.target.src =
                                                        '/images/default.jpg')
                                            "
                                        />
                                        <a
                                            v-else
                                            :href="fileUrl(profile.nid_file)"
                                            target="_blank"
                                            rel="noopener"
                                            class="flex flex-col items-center justify-center py-8 text-red-600 hover:bg-red-50 transition"
                                        >
                                            <i
                                                class="fa-solid fa-file-pdf text-4xl mb-2"
                                            ></i>
                                            <span
                                                class="text-xs font-bold underline"
                                            >
                                                View PDF
                                            </span>
                                        </a>
                                    </div>
                                    <p
                                        v-else
                                        class="text-sm text-slate-400 italic"
                                    >
                                        Not uploaded
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="sticky bottom-0 border-t border-slate-100 bg-slate-50 px-5 py-3 flex flex-col sm:flex-row gap-2"
                    >
                        <button
                            @click="closeModal"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-2.5 shadow transition"
                        >
                            <i class="fa-solid fa-check text-xs"></i>
                            Close
                        </button>
                        <button
                            @click="
                                closeModal();
                                deleteUser(selectedReporter.id);
                            "
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 font-semibold py-2.5 transition"
                        >
                            <i class="fa fa-trash text-xs"></i>
                            Delete Reporter
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
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
