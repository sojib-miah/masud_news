<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import { computed, ref } from "vue";

const props = defineProps({
    referrals: { type: Array, default: () => [] },
});

// ==================== SEARCH + SORT ====================
const search = ref("");
const sortKey = ref("created_at");
const sortDir = ref("desc");

function toggleSort(key) {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
    } else {
        sortKey.value = key;
        sortDir.value = "asc";
    }
}

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    let rows = props.referrals;

    if (q) {
        rows = rows.filter((u) => {
            return (
                String(u.name ?? "")
                    .toLowerCase()
                    .includes(q) ||
                String(u.phone ?? "")
                    .toLowerCase()
                    .includes(q) ||
                String(u.ref_id ?? "")
                    .toLowerCase()
                    .includes(q)
            );
        });
    }

    // sort copy
    const sorted = [...rows].sort((a, b) => {
        const av = a[sortKey.value];
        const bv = b[sortKey.value];

        if (sortKey.value === "total_earning") {
            return sortDir.value === "asc"
                ? Number(av ?? 0) - Number(bv ?? 0)
                : Number(bv ?? 0) - Number(av ?? 0);
        }

        const as = String(av ?? "").toLowerCase();
        const bs = String(bv ?? "").toLowerCase();
        if (as < bs) return sortDir.value === "asc" ? -1 : 1;
        if (as > bs) return sortDir.value === "asc" ? 1 : -1;
        return 0;
    });

    return sorted;
});

// ==================== SUMMARY STATS ====================
const totalReferrals = computed(() => props.referrals.length);

const totalEarning = computed(() =>
    props.referrals.reduce((sum, u) => sum + Number(u.total_earning ?? 0), 0),
);

const joinedThisMonth = computed(() => {
    const now = new Date();
    return props.referrals.filter((u) => {
        if (!u.created_at) return false;
        const d = new Date(u.created_at);
        return (
            d.getMonth() === now.getMonth() &&
            d.getFullYear() === now.getFullYear()
        );
    }).length;
});

// ==================== HELPERS ====================
function initials(name) {
    if (!name) return "?";
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((s) => s[0])
        .join("")
        .toUpperCase();
}

const avatarColors = [
    "from-indigo-500 to-indigo-700",
    "from-purple-500 to-purple-700",
    "from-teal-500 to-teal-700",
    "from-emerald-500 to-emerald-700",
    "from-sky-500 to-sky-700",
    "from-orange-500 to-orange-700",
    "from-pink-500 to-pink-700",
    "from-rose-500 to-rose-700",
];
function avatarColor(id) {
    const n = Number(id) || 0;
    return avatarColors[n % avatarColors.length];
}

function formatDate(v) {
    if (!v) return "-";
    const d = new Date(v);
    if (isNaN(d.getTime())) return v;
    return d.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function clearSearch() {
    search.value = "";
}
</script>

<template>
    <Layout>
        <Head title="My Referrals" />

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
                            <i class="fa-solid fa-user-group mr-1.5"></i>
                            Network
                        </p>
                        <h1 class="text-2xl sm:text-3xl font-extrabold mt-1">
                            My Referrals
                        </h1>
                        <p class="text-green-100 text-sm mt-1">
                            Everyone who registered using your link.
                        </p>
                    </div>

                    <div
                        class="inline-flex items-center gap-2 bg-white/15 backdrop-blur rounded-xl px-4 py-2 self-start sm:self-auto"
                    >
                        <i class="fa-solid fa-users text-white"></i>
                        <span class="text-sm font-semibold">
                            {{ totalReferrals }} Total
                        </span>
                    </div>
                </div>
            </div>

            <!-- ==================== SUMMARY STATS ==================== -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div
                    class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center gap-3"
                >
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shrink-0"
                    >
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                        >
                            Total Referrals
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800"
                        >
                            {{ totalReferrals }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center gap-3"
                >
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shrink-0"
                    >
                        <i class="fa-solid fa-coins text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                        >
                            Total Earning
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800 truncate"
                        >
                            ৳{{ totalEarning.toLocaleString() }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-2xl bg-white border border-slate-200 p-4 sm:p-5 shadow-sm flex items-center gap-3"
                >
                    <div
                        class="h-11 w-11 rounded-xl bg-gradient-to-br from-orange-500 to-orange-700 text-white flex items-center justify-center shrink-0"
                    >
                        <i class="fa-solid fa-calendar-check text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wide"
                        >
                            Joined This Month
                        </p>
                        <p
                            class="text-xl sm:text-2xl font-extrabold text-slate-800"
                        >
                            {{ joinedThisMonth }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ==================== SEARCH BAR ==================== -->
            <div
                class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 sm:p-4"
            >
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                        ></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, phone or referral ID..."
                            class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-slate-300 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 outline-none text-sm transition"
                        />
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSearch"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 h-6 w-6 rounded-full text-slate-400 hover:text-red-500 hover:bg-red-50 transition flex items-center justify-center"
                            title="Clear"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <div
                        class="inline-flex items-center gap-2 text-sm text-slate-500 sm:pl-2"
                    >
                        <i class="fa-solid fa-filter text-slate-400"></i>
                        <span>
                            Showing
                            <strong class="text-slate-800">{{
                                filtered.length
                            }}</strong>
                            of {{ totalReferrals }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- ==================== EMPTY STATE ==================== -->
            <div
                v-if="!totalReferrals"
                class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center"
            >
                <div
                    class="mx-auto h-16 w-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4"
                >
                    <i
                        class="fa-solid fa-user-plus text-2xl text-slate-400"
                    ></i>
                </div>
                <p class="text-slate-700 font-bold text-lg">No referrals yet</p>
                <p class="text-slate-500 text-sm mt-1">
                    Share your affiliate link to start growing your network.
                </p>
            </div>

            <!-- ==================== NO SEARCH RESULTS ==================== -->
            <div
                v-else-if="filtered.length === 0"
                class="rounded-2xl bg-white border border-dashed border-slate-300 p-10 text-center"
            >
                <div
                    class="mx-auto h-16 w-16 rounded-2xl bg-red-50 flex items-center justify-center mb-4"
                >
                    <i
                        class="fa-solid fa-magnifying-glass text-2xl text-red-400"
                    ></i>
                </div>
                <p class="text-slate-700 font-bold text-lg">
                    No matching referrals
                </p>
                <p class="text-slate-500 text-sm mt-1">
                    No results for
                    <span class="font-semibold">"{{ search }}"</span>
                </p>
                <button
                    @click="clearSearch"
                    class="mt-4 inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    Clear search
                </button>
            </div>

            <!-- ==================== DESKTOP TABLE (sm+) ==================== -->
            <div
                v-if="filtered.length"
                class="hidden sm:block bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th
                                    class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-12"
                                >
                                    #
                                </th>
                                <th
                                    @click="toggleSort('name')"
                                    class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider cursor-pointer select-none hover:text-green-700 transition"
                                >
                                    User
                                    <i
                                        :class="
                                            sortKey === 'name'
                                                ? sortDir === 'asc'
                                                    ? 'fa-solid fa-arrow-up'
                                                    : 'fa-solid fa-arrow-down'
                                                : 'fa-solid fa-sort opacity-40'
                                        "
                                        class="ml-1 text-[10px]"
                                    ></i>
                                </th>
                                <th
                                    @click="toggleSort('phone')"
                                    class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider cursor-pointer select-none hover:text-green-700 transition"
                                >
                                    Phone
                                    <i
                                        :class="
                                            sortKey === 'phone'
                                                ? sortDir === 'asc'
                                                    ? 'fa-solid fa-arrow-up'
                                                    : 'fa-solid fa-arrow-down'
                                                : 'fa-solid fa-sort opacity-40'
                                        "
                                        class="ml-1 text-[10px]"
                                    ></i>
                                </th>
                                <th
                                    @click="toggleSort('ref_id')"
                                    class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider cursor-pointer select-none hover:text-green-700 transition"
                                >
                                    Referral ID
                                    <i
                                        :class="
                                            sortKey === 'ref_id'
                                                ? sortDir === 'asc'
                                                    ? 'fa-solid fa-arrow-up'
                                                    : 'fa-solid fa-arrow-down'
                                                : 'fa-solid fa-sort opacity-40'
                                        "
                                        class="ml-1 text-[10px]"
                                    ></i>
                                </th>
                                <th
                                    @click="toggleSort('total_earning')"
                                    class="p-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider cursor-pointer select-none hover:text-green-700 transition"
                                >
                                    Total Earning
                                    <i
                                        :class="
                                            sortKey === 'total_earning'
                                                ? sortDir === 'asc'
                                                    ? 'fa-solid fa-arrow-up'
                                                    : 'fa-solid fa-arrow-down'
                                                : 'fa-solid fa-sort opacity-40'
                                        "
                                        class="ml-1 text-[10px]"
                                    ></i>
                                </th>
                                <th
                                    @click="toggleSort('created_at')"
                                    class="p-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider cursor-pointer select-none hover:text-green-700 transition"
                                >
                                    Joined
                                    <i
                                        :class="
                                            sortKey === 'created_at'
                                                ? sortDir === 'asc'
                                                    ? 'fa-solid fa-arrow-up'
                                                    : 'fa-solid fa-arrow-down'
                                                : 'fa-solid fa-sort opacity-40'
                                        "
                                        class="ml-1 text-[10px]"
                                    ></i>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(user, index) in filtered"
                                :key="user.id"
                                class="border-b border-slate-100 last:border-0 hover:bg-green-50/40 transition"
                            >
                                <td class="p-3 text-sm text-slate-500">
                                    {{ index + 1 }}
                                </td>

                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-9 w-9 rounded-full bg-gradient-to-br text-white text-xs font-bold flex items-center justify-center shrink-0"
                                            :class="avatarColor(user.id)"
                                        >
                                            {{ initials(user.name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p
                                                class="text-sm font-semibold text-slate-800 truncate"
                                            >
                                                {{ user.name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="p-3 text-sm text-slate-700 whitespace-nowrap"
                                >
                                    {{ user.phone }}
                                </td>

                                <td class="p-3">
                                    <span
                                        class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 text-xs font-mono font-semibold px-2 py-1 rounded"
                                    >
                                        {{ user.ref_id }}
                                    </span>
                                </td>

                                <td class="p-3 text-right">
                                    <span
                                        class="text-sm font-extrabold text-green-600"
                                    >
                                        ৳{{
                                            Number(
                                                user.total_earning ?? 0,
                                            ).toLocaleString()
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="p-3 text-sm text-slate-600 whitespace-nowrap"
                                >
                                    <i
                                        class="fa-regular fa-calendar mr-1 text-slate-400"
                                    ></i>
                                    {{ formatDate(user.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ==================== MOBILE CARDS (< sm) ==================== -->
            <div v-if="filtered.length" class="sm:hidden space-y-3">
                <div
                    v-for="(user, index) in filtered"
                    :key="user.id"
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="h-11 w-11 rounded-full bg-gradient-to-br text-white text-sm font-bold flex items-center justify-center shrink-0"
                            :class="avatarColor(user.id)"
                        >
                            {{ initials(user.name) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p
                                        class="font-bold text-slate-800 truncate"
                                    >
                                        {{ user.name }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <i
                                            class="fa-solid fa-phone mr-1 text-[10px]"
                                        ></i>
                                        {{ user.phone }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded"
                                >
                                    #{{ index + 1 }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                <div class="bg-slate-50 rounded-lg px-2.5 py-2">
                                    <p
                                        class="text-[10px] uppercase font-semibold text-slate-500 tracking-wide"
                                    >
                                        Referral ID
                                    </p>
                                    <p
                                        class="font-mono font-semibold text-slate-700 truncate"
                                    >
                                        {{ user.ref_id }}
                                    </p>
                                </div>
                                <div class="bg-green-50 rounded-lg px-2.5 py-2">
                                    <p
                                        class="text-[10px] uppercase font-semibold text-green-700 tracking-wide"
                                    >
                                        Earning
                                    </p>
                                    <p class="font-extrabold text-green-600">
                                        ৳{{
                                            Number(
                                                user.total_earning ?? 0,
                                            ).toLocaleString()
                                        }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs text-slate-500 mt-2.5">
                                <i
                                    class="fa-regular fa-calendar mr-1 text-slate-400"
                                ></i>
                                Joined {{ formatDate(user.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
