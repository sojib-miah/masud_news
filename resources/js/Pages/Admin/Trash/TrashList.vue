<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import { route } from "ziggy-js";

const props = defineProps({
    trashedUsers: Object,
    search: String,
});

const search = ref(props.search || "");

// ==================== SEARCH ====================
function fetchUsers() {
    router.get(
        "/admin/trash",
        { search: search.value },
        { preserveState: true, preserveScroll: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/trash", {}, { preserveState: false });
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

// ==================== VIEW MODAL ====================
const viewModalOpen = ref(false);
const viewUser = ref(null);

function openView(user) {
    viewUser.value = user;
    viewModalOpen.value = true;
    document.body.style.overflow = "hidden";
}

function closeView() {
    viewModalOpen.value = false;
    viewUser.value = null;
    document.body.style.overflow = "";
}

// ==================== RESTORE ====================
function restoreUser(user) {
    if (confirm(`Restore "${user.name}"? They will become active again.`)) {
        router.post(
            route("pathock.restore", user.id),
            {},
            { preserveScroll: true },
        );
    }
}

// ==================== FORCE DELETE ====================
function forceDeleteUser(user) {
    if (
        confirm(
            `Permanently delete "${user.name}"?\n\nThis will remove ALL related data (profile, motamots, orders, comments, views, etc.) and CANNOT be undone.`,
        )
    ) {
        router.delete(route("pathock.forceDelete", user.id), {
            preserveScroll: true,
        });
    }
}

// ==================== HELPERS ====================
function globalIndex(i) {
    const page = props.trashedUsers?.current_page ?? 1;
    const perPage = props.trashedUsers?.per_page ?? 20;
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

function formatDateTime(d) {
    if (!d) return "—";
    try {
        return new Date(d).toLocaleString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    } catch {
        return d;
    }
}

// ==================== PROFILE HELPERS ====================
const profile = computed(() => {
    if (!viewUser.value) return null;
    return (
        viewUser.value.reporter_profile ??
        viewUser.value.pathock_profile ??
        null
    );
});

const profileType = computed(() => {
    if (!viewUser.value) return null;
    if (viewUser.value.reporter_profile) return "reporter";
    if (viewUser.value.pathock_profile) return "pathock";
    return null;
});

const locationChain = computed(() => {
    if (!profile.value) return [];
    const p = profile.value;
    const parts = [];
    const dn = (o) => o?.bn_name || o?.name;

    if (dn(p.division_data)) parts.push(dn(p.division_data));
    if (dn(p.district_data)) parts.push(dn(p.district_data));
    if (dn(p.upazila_data)) parts.push(dn(p.upazila_data));
    if (dn(p.union_data)) parts.push(dn(p.union_data));
    if (dn(p.city_corporation_data)) parts.push(dn(p.city_corporation_data));
    if (dn(p.ward_data)) parts.push(dn(p.ward_data));

    // pathock uses different relation names
    if (dn(p.division)) parts.push(dn(p.division));
    if (dn(p.district)) parts.push(dn(p.district));
    if (dn(p.upazilla)) parts.push(dn(p.upazilla));
    if (dn(p.union)) parts.push(dn(p.union));
    if (dn(p.cityCorporation)) parts.push(dn(p.cityCorporation));
    if (dn(p.ward)) parts.push(dn(p.ward));

    if (p.village) parts.push(p.village);

    // De-dupe while keeping order
    return [...new Set(parts)];
});

function roleColor(role) {
    const map = {
        pathock: "bg-blue-50 text-blue-700 ring-blue-200",
        reporter: "bg-emerald-50 text-emerald-700 ring-emerald-200",
        motamot: "bg-amber-50 text-amber-700 ring-amber-200",
        admin: "bg-violet-50 text-violet-700 ring-violet-200",
        user: "bg-slate-100 text-slate-700 ring-slate-200",
    };
    return map[role] || "bg-slate-100 text-slate-700 ring-slate-200";
}

function isPdf(path) {
    return /\.pdf$/i.test(path || "");
}

function fileUrl(path) {
    return `/storage/${path}`;
}
</script>

<template>
    <Head title="Trash — Deleted Users" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <Breadcrumb />

        <!-- ==================== PAGE HEADER ==================== -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-rose-950 via-slate-900 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
        >
            <div
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-rose-500/30 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-red-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <p
                        class="text-rose-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-trash-can mr-1"></i> Recycle Bin
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Trash — Deleted Users
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Restore accounts or permanently delete them with all
                        related data.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                >
                    <i class="fa-solid fa-database text-rose-300"></i>
                    Total: {{ props.trashedUsers?.total ?? 0 }}
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
                    {{ props.trashedUsers?.data?.length ?? 0 }} shown
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
                        <tr class="bg-rose-50/60 border-b border-rose-100">
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-rose-700 w-14"
                            >
                                #
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-rose-700"
                            >
                                User
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-rose-700"
                            >
                                Role
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-rose-700"
                            >
                                Deleted At
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-rose-700 w-64"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(user, index) in props.trashedUsers?.data"
                            :key="user.id"
                            class="hover:bg-rose-50/40 transition-colors"
                        >
                            <td
                                class="px-4 py-3 text-sm font-semibold text-slate-600"
                            >
                                {{ globalIndex(index) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-red-600 text-white font-bold text-xs shadow"
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
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold ring-1 capitalize"
                                    :class="roleColor(user.role)"
                                >
                                    <i
                                        class="fa-solid fa-user-tag text-[10px]"
                                    ></i>
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600"
                                >
                                    <i
                                        class="fa-regular fa-clock text-rose-500"
                                    ></i>
                                    {{ formatDateTime(user.deleted_at) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <button
                                        @click="openView(user)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 text-slate-700 ring-1 ring-slate-200 px-2.5 py-1.5 text-xs font-bold hover:bg-slate-200 hover:scale-105 transition"
                                        title="View Details"
                                    >
                                        <i class="fa fa-eye text-[10px]"></i>
                                        View
                                    </button>
                                    <button
                                        @click="restoreUser(user)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-2.5 py-1.5 text-xs font-bold hover:bg-emerald-100 hover:scale-105 transition"
                                        title="Restore user"
                                    >
                                        <i
                                            class="fa-solid fa-rotate-left text-[10px]"
                                        ></i>
                                        Restore
                                    </button>
                                    <button
                                        @click="forceDeleteUser(user)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-600 text-white ring-1 ring-red-700 hover:bg-red-700 hover:scale-110 transition"
                                        title="Delete permanently"
                                    >
                                        <i
                                            class="fa-solid fa-trash-can text-xs"
                                        ></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty -->
            <div
                v-if="!props.trashedUsers?.data?.length"
                class="py-16 px-6 text-center"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 ring-1 ring-emerald-200 mb-4"
                >
                    <i class="fa-solid fa-check text-3xl text-emerald-500"></i>
                </div>
                <p class="text-lg font-bold text-slate-700 mb-1">
                    {{
                        search ? "No matching trashed users" : "Trash is empty"
                    }}
                </p>
                <p class="text-sm text-slate-500">
                    {{
                        search
                            ? "Try a different search term."
                            : "Deleted users will appear here."
                    }}
                </p>
            </div>
        </div>

        <!-- ==================== MOBILE CARDS ==================== -->
        <div class="md:hidden space-y-3">
            <div
                v-for="(user, index) in props.trashedUsers?.data"
                :key="user.id"
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden"
            >
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-red-600 text-white font-bold shadow"
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
                                    :class="roleColor(user.role)"
                                >
                                    <i
                                        class="fa-solid fa-user-tag text-[8px]"
                                    ></i>
                                    {{ user.role }}
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

                    <div
                        class="mt-3 flex items-center gap-2 rounded-lg bg-rose-50 ring-1 ring-rose-100 px-3 py-2"
                    >
                        <i
                            class="fa-regular fa-clock text-rose-500 text-xs"
                        ></i>
                        <span class="text-xs font-semibold text-rose-700">
                            Deleted {{ formatDateTime(user.deleted_at) }}
                        </span>
                    </div>
                </div>

                <div
                    class="grid grid-cols-3 border-t border-slate-100 divide-x divide-slate-100"
                >
                    <button
                        @click="openView(user)"
                        class="inline-flex items-center justify-center gap-1 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                    >
                        <i class="fa fa-eye text-[10px]"></i>
                        View
                    </button>
                    <button
                        @click="restoreUser(user)"
                        class="inline-flex items-center justify-center gap-1 py-2.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition"
                    >
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        Restore
                    </button>
                    <button
                        @click="forceDeleteUser(user)"
                        class="inline-flex items-center justify-center gap-1 py-2.5 text-xs font-bold text-red-700 hover:bg-red-50 transition"
                    >
                        <i class="fa-solid fa-trash-can text-[10px]"></i>
                        Delete
                    </button>
                </div>
            </div>

            <!-- Empty mobile -->
            <div
                v-if="!props.trashedUsers?.data?.length"
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm py-16 px-6 text-center"
            >
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 ring-1 ring-emerald-200 mb-4"
                >
                    <i class="fa-solid fa-check text-3xl text-emerald-500"></i>
                </div>
                <p class="text-lg font-bold text-slate-700 mb-1">
                    {{
                        search ? "No matching trashed users" : "Trash is empty"
                    }}
                </p>
                <p class="text-sm text-slate-500">
                    {{
                        search
                            ? "Try a different search term."
                            : "Deleted users will appear here."
                    }}
                </p>
            </div>
        </div>

        <!-- ==================== PAGINATION ==================== -->
        <div
            v-if="props.trashedUsers?.links?.length > 3"
            class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
        >
            <template v-for="(link, i) in props.trashedUsers.links" :key="i">
                <button
                    v-if="link.url"
                    @click="visitPage(link.url)"
                    class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold transition"
                    :class="
                        link.active
                            ? 'bg-gradient-to-r from-rose-500 to-red-600 text-white shadow'
                            : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-rose-50 hover:text-rose-700 hover:ring-rose-300'
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
            v-if="viewModalOpen && viewUser"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
        >
            <!-- Backdrop -->
            <div
                @click="closeView"
                class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"
            ></div>

            <!-- Panel -->
            <div
                class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200"
            >
                <!-- Header -->
                <div
                    class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-rose-950 to-slate-900 px-5 py-4 rounded-t-2xl"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-red-600 text-white font-bold shadow ring-2 ring-white/20"
                        >
                            {{ initials(viewUser.name) }}
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-rose-300 text-[10px] font-semibold uppercase tracking-wider mb-0.5"
                            >
                                <i class="fa-solid fa-user-tag mr-1"></i>
                                {{ viewUser.role }}
                            </p>
                            <h3
                                class="text-lg sm:text-xl font-extrabold text-white truncate"
                            >
                                {{ viewUser.name }}
                            </h3>
                        </div>
                    </div>
                    <button
                        @click="closeView"
                        class="shrink-0 flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white hover:bg-red-500 transition ring-1 ring-white/20"
                        title="Close"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-5 sm:p-6 space-y-4">
                    <!-- Status banner -->
                    <div
                        class="flex flex-wrap items-center gap-2 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-3 py-2"
                    >
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-rose-600 text-white px-2.5 py-1 text-xs font-bold"
                        >
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                            In Trash
                        </span>
                        <span class="text-xs text-rose-800 font-semibold">
                            Deleted {{ formatDateTime(viewUser.deleted_at) }}
                        </span>
                        <span
                            class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-700 ring-1 ring-slate-200"
                        >
                            <i class="fa-solid fa-hashtag text-[10px]"></i>
                            ID: {{ viewUser.id }}
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
                                v-if="viewUser.email"
                                :href="`mailto:${viewUser.email}`"
                                class="text-sm font-semibold text-slate-800 break-all hover:text-sky-700"
                            >
                                {{ viewUser.email }}
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
                                v-if="viewUser.phone"
                                :href="`tel:${viewUser.phone}`"
                                class="text-sm font-semibold text-slate-800 hover:text-emerald-700"
                            >
                                {{ viewUser.phone }}
                            </a>
                            <p v-else class="text-sm text-slate-400">—</p>
                        </div>
                    </div>

                    <!-- Reporter Profile -->
                    <template v-if="profileType === 'reporter' && profile">
                        <div
                            class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden"
                        >
                            <div
                                class="bg-emerald-50 border-b border-emerald-100 px-4 py-2"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-emerald-700 font-bold"
                                >
                                    <i class="fa-solid fa-id-badge mr-1"></i>
                                    Reporter Profile
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
                                        {{ profile.dob || "—" }}
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
                                        {{ profile.blood_group || "—" }}
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
                                        {{ profile.father_name || "—" }}
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
                                        {{ profile.mother_name || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Education
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile.education || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Education Year
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile.education_year || "—" }}
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
                                        {{ profile.nid_number || "—" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Reporter types -->
                        <div
                            v-if="viewUser.reporter_type_names?.length"
                            class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-4"
                        >
                            <p
                                class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                            >
                                <i class="fa-solid fa-tags mr-1"></i>
                                Reporter Types
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="(
                                        type, i
                                    ) in viewUser.reporter_type_names"
                                    :key="i"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-200"
                                >
                                    <i class="fa-solid fa-tag text-[10px]"></i>
                                    {{ type }}
                                </span>
                            </div>
                        </div>

                        <!-- Reporter documents -->
                        <div
                            v-if="
                                profile.signature ||
                                profile.nid_file ||
                                profile.photo
                            "
                            class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden"
                        >
                            <div
                                class="bg-slate-50 border-b border-slate-100 px-4 py-2"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    <i class="fa-solid fa-paperclip mr-1"></i>
                                    Documents
                                </p>
                            </div>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4"
                            >
                                <div v-if="profile.photo">
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                                    >
                                        Photo
                                    </p>
                                    <img
                                        :src="fileUrl(profile.photo)"
                                        class="w-full max-h-40 object-contain rounded-lg ring-1 ring-slate-200 bg-slate-50"
                                        alt="Photo"
                                    />
                                </div>
                                <div v-if="profile.signature">
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                                    >
                                        Signature
                                    </p>
                                    <img
                                        :src="fileUrl(profile.signature)"
                                        class="w-full max-h-40 object-contain rounded-lg ring-1 ring-slate-200 bg-slate-50"
                                        alt="Signature"
                                    />
                                </div>
                                <div v-if="profile.nid_file">
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                                    >
                                        NID / Passport
                                    </p>
                                    <img
                                        v-if="!isPdf(profile.nid_file)"
                                        :src="fileUrl(profile.nid_file)"
                                        class="w-full max-h-40 object-contain rounded-lg ring-1 ring-slate-200 bg-slate-50"
                                        alt="NID"
                                    />
                                    <a
                                        v-else
                                        :href="fileUrl(profile.nid_file)"
                                        target="_blank"
                                        rel="noopener"
                                        class="flex flex-col items-center justify-center py-8 text-red-600 hover:bg-red-50 transition rounded-lg ring-1 ring-red-200 bg-red-50/40"
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
                            </div>
                        </div>
                    </template>

                    <!-- Pathock Profile -->
                    <template v-if="profileType === 'pathock' && profile">
                        <div
                            class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden"
                        >
                            <div
                                class="bg-blue-50 border-b border-blue-100 px-4 py-2"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-blue-700 font-bold"
                                >
                                    <i class="fa-solid fa-id-card mr-1"></i>
                                    Pathock Profile
                                </p>
                            </div>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4"
                            >
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Occupation
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile.occupation || "—" }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                    >
                                        Village
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ profile.village || "—" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Location -->
                    <div
                        v-if="locationChain.length"
                        class="rounded-xl bg-emerald-50/60 ring-1 ring-emerald-200 p-4"
                    >
                        <p
                            class="text-[10px] uppercase tracking-wider text-emerald-700 font-bold mb-2"
                        >
                            <i class="fa-solid fa-location-dot mr-1"></i>
                            Location
                        </p>
                        <div class="flex flex-wrap items-center gap-1.5">
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
                    </div>

                    <!-- Address (reporter) -->
                    <div
                        v-if="
                            profileType === 'reporter' &&
                            (profile?.address || profile?.reporter_area)
                        "
                        class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-4 space-y-1"
                    >
                        <p
                            v-if="profile.address"
                            class="text-xs text-slate-700"
                        >
                            <i
                                class="fa-solid fa-map-pin text-slate-500 mr-1"
                            ></i>
                            <strong>Address:</strong> {{ profile.address }}
                        </p>
                        <p
                            v-if="profile.reporter_area"
                            class="text-xs text-slate-700"
                        >
                            <i
                                class="fa-solid fa-bullseye text-slate-500 mr-1"
                            ></i>
                            <strong>Reporter Area:</strong>
                            {{ profile.reporter_area }}
                        </p>
                    </div>

                    <!-- No profile -->
                    <div
                        v-if="!profile"
                        class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-4 text-center"
                    >
                        <i
                            class="fa-solid fa-circle-info text-2xl text-slate-300 mb-2"
                        ></i>
                        <p class="text-sm text-slate-500">
                            No profile record found for this user.
                        </p>
                    </div>

                    <!-- Account meta -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div
                            class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                        >
                            <p
                                class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                            >
                                <i
                                    class="fa-regular fa-calendar text-slate-500 mr-1"
                                ></i>
                                Account Created
                            </p>
                            <p class="text-sm font-semibold text-slate-800">
                                {{ formatDateTime(viewUser.created_at) }}
                            </p>
                        </div>
                        <div
                            class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                        >
                            <p
                                class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                            >
                                <i
                                    class="fa-regular fa-clock text-rose-500 mr-1"
                                ></i>
                                Deleted At
                            </p>
                            <p class="text-sm font-semibold text-rose-700">
                                {{ formatDateTime(viewUser.deleted_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="sticky bottom-0 border-t border-slate-100 bg-slate-50 px-5 py-3 grid grid-cols-1 sm:grid-cols-3 gap-2"
                >
                    <button
                        @click="closeView"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-100 font-semibold py-2.5 transition"
                    >
                        <i class="fa-solid fa-xmark text-xs"></i>
                        Close
                    </button>
                    <button
                        @click="
                            closeView();
                            restoreUser(viewUser);
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-2.5 shadow transition"
                    >
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        Restore
                    </button>
                    <button
                        @click="
                            closeView();
                            forceDeleteUser(viewUser);
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold py-2.5 shadow transition"
                    >
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        Delete Forever
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
