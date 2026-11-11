<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    pathocks: Object,
    search: String,
});

const search = ref(props.search || "");

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

// ==================== SEARCH ====================
function fetchUsers() {
    router.get(
        "/admin/pathock",
        { search: search.value },
        { preserveState: true, preserveScroll: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/pathock", {}, { preserveState: false });
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

// ==================== DELETE ====================
function deleteUser(id) {
    if (confirm("Are you sure you want to delete?")) {
        router.delete(`/admin/pathock/${id}`, { preserveScroll: true });
    }
}

// ==================== HELPERS ====================
function globalIndex(i) {
    const page = props.pathocks?.current_page ?? 1;
    const perPage = props.pathocks?.per_page ?? 20;
    return (page - 1) * perPage + i + 1;
}

function visitPage(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
}

// Pathock profile convenience
const profile = computed(() => viewUser.value?.pathock_profile ?? null);

// Build location breadcrumb from profile
const locationChain = computed(() => {
    if (!profile.value) return [];
    const p = profile.value;
    const parts = [];
    if (p.division?.bn_name || p.division?.name)
        parts.push(p.division.bn_name || p.division.name);
    if (p.district?.bn_name || p.district?.name)
        parts.push(p.district.bn_name || p.district.name);
    if (p.upazilla?.bn_name || p.upazilla?.name)
        parts.push(p.upazilla.bn_name || p.upazilla.name);
    if (p.union?.bn_name || p.union?.name)
        parts.push(p.union.bn_name || p.union.name);
    if (p.city_corporation?.bn_name || p.city_corporation?.name)
        parts.push(p.city_corporation.bn_name || p.city_corporation.name);
    if (p.ward?.bn_name || p.ward?.name)
        parts.push(p.ward.bn_name || p.ward.name);
    if (p.pourashava?.bn_name || p.pourashava?.name)
        parts.push(p.pourashava.bn_name || p.pourashava.name);
    if (p.pourashava_ward?.bn_name || p.pourashava_ward?.name)
        parts.push(p.pourashava_ward.bn_name || p.pourashava_ward.name);
    if (p.village) parts.push(p.village);
    return parts;
});

// Initials for avatar
function initials(name) {
    if (!name) return "?";
    return name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0])
        .join("")
        .toUpperCase();
}
</script>

<template>
    <div>
        <Head title="Pathock List Page" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <Layout>
            <Breadcrumb />

            <!-- ==================== PAGE HEADER ==================== -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
            >
                <div
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-cyan-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-users mr-1"></i> Users
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Pathock Users
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            View, search & manage Pathock role accounts.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <i class="fa-solid fa-database text-emerald-300"></i>
                        Total: {{ props.pathocks?.total ?? 0 }}
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
                        {{ props.pathocks?.data?.length ?? 0 }} shown
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
                                    User
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Phone
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Role
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-40"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(user, index) in props.pathocks?.data"
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
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold text-xs shadow"
                                        >
                                            <img
                                                v-if="user.image"
                                                :src="`/storage/${user.image}`"
                                                :alt="user.name"
                                                class="h-10 w-10 rounded-full object-cover"
                                            />
                                            <span v-else>
                                                {{ initials(user.name) }}
                                            </span>
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
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200 capitalize"
                                    >
                                        <i
                                            class="fa-solid fa-user-tag text-[10px]"
                                        ></i>
                                        {{ user.role }}
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
                    v-if="!props.pathocks?.data?.length"
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
                        {{ search ? "No matching users" : "No users yet" }}
                    </p>
                    <p class="text-sm text-slate-500">
                        {{
                            search
                                ? "Try a different search term."
                                : "Pathock users will appear here."
                        }}
                    </p>
                </div>
            </div>

            <!-- ==================== MOBILE CARDS ==================== -->
            <div class="md:hidden space-y-3">
                <div
                    v-for="(user, index) in props.pathocks?.data"
                    :key="user.id"
                    class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden"
                >
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold shadow"
                            >
                                <img
                                    v-if="user.image"
                                    :src="`/storage/${user.image}`"
                                    :alt="user.name"
                                    class="h-12 w-12 rounded-full object-cover"
                                />
                                <span v-else>
                                    {{ initials(user.name) }}
                                </span>
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
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200 capitalize"
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
                            class="mt-3 flex items-center gap-2 rounded-lg bg-slate-50 ring-1 ring-slate-100 px-3 py-2"
                        >
                            <i
                                class="fa-solid fa-phone text-slate-400 text-xs"
                            ></i>
                            <span class="text-xs font-semibold text-slate-700">
                                {{ user.phone || "—" }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 border-t border-slate-100 divide-x divide-slate-100"
                    >
                        <button
                            @click="openView(user)"
                            class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                        >
                            <i class="fa fa-eye text-[10px]"></i>
                            View
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
                    v-if="!props.pathocks?.data?.length"
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
                        {{ search ? "No matching users" : "No users yet" }}
                    </p>
                    <p class="text-sm text-slate-500">
                        {{
                            search
                                ? "Try a different search term."
                                : "Pathock users will appear here."
                        }}
                    </p>
                </div>
            </div>

            <!-- ==================== PAGINATION ==================== -->
            <div
                v-if="props.pathocks?.links?.length > 3"
                class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
            >
                <template v-for="(link, i) in props.pathocks.links" :key="i">
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
                v-if="viewModalOpen && viewUser"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
            >
                <!-- Backdrop -->
                <div
                    @click="closeView"
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                ></div>

                <!-- Modal panel -->
                <div
                    class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200"
                >
                    <!-- Header -->
                    <div
                        class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-4 rounded-t-2xl"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold shadow"
                            >
                                <img
                                    v-if="viewUser.image"
                                    :src="`/storage/${viewUser.image}`"
                                    :alt="viewUser.name"
                                    class="w-12 h-12 rounded-full object-cover"
                                />
                                <span v-else>
                                    {{ initials(viewUser.name) }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="text-emerald-300 text-[10px] font-semibold uppercase tracking-wider mb-0.5"
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
                        </div>

                        <!-- Occupation + village -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div
                                class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                                >
                                    <i
                                        class="fa-solid fa-briefcase text-violet-600 mr-1"
                                    ></i>
                                    Occupation
                                </p>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ profile?.occupation || "—" }}
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                                >
                                    <i
                                        class="fa-solid fa-house text-amber-600 mr-1"
                                    ></i>
                                    Village
                                </p>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ profile?.village || "—" }}
                                </p>
                            </div>
                        </div>

                        <!-- Meta -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div
                                class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                                >
                                    <i
                                        class="fa-solid fa-hashtag text-slate-500 mr-1"
                                    ></i>
                                    User ID
                                </p>
                                <p
                                    class="text-sm font-mono font-semibold text-slate-800"
                                >
                                    #{{ viewUser.id }}
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-slate-50 ring-1 ring-slate-200 p-3"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1"
                                >
                                    <i
                                        class="fa-regular fa-calendar text-slate-500 mr-1"
                                    ></i>
                                    Joined
                                </p>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{
                                        viewUser.created_at
                                            ? new Date(
                                                  viewUser.created_at,
                                              ).toLocaleDateString("en-GB", {
                                                  day: "2-digit",
                                                  month: "short",
                                                  year: "numeric",
                                              })
                                            : "—"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="sticky bottom-0 border-t border-slate-100 bg-slate-50 px-5 py-3 flex flex-col sm:flex-row gap-2"
                    >
                        <button
                            @click="closeView"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-2.5 shadow transition"
                        >
                            <i class="fa-solid fa-check text-xs"></i>
                            Close
                        </button>
                        <button
                            @click="
                                closeView();
                                deleteUser(viewUser.id);
                            "
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 font-semibold py-2.5 transition"
                        >
                            <i class="fa-solid fa-trash text-xs"></i>
                            Delete User
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
