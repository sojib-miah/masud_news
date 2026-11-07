<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import { route } from "ziggy-js";

const props = defineProps({
    motamots: Object,
    search: { type: String, default: "" },
});

const search = ref(props.search || "");

// ==================== SEARCH ====================
function fetchUsers() {
    router.get(
        "/admin/motamot/list",
        { search: search.value },
        { preserveState: true, preserveScroll: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/motamot/list", {}, { preserveState: false });
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
function deleteUser(user) {
    if (confirm(`Delete "${user.name}"?`)) {
        router.delete(route("pathock.destroy", user.id), {
            preserveScroll: true,
        });
    }
}

// ==================== HELPERS ====================
function globalIndex(i) {
    const page = props.motamots?.current_page ?? 1;
    const perPage = props.motamots?.per_page ?? 20;
    return (page - 1) * perPage + i + 1;
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

function visitPage(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Head title="Motamot User List" />

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
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-violet-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <p
                        class="text-indigo-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-comment-dots mr-1"></i> Users
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Motamot Users
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Browse motamot users and open their submitted opinions.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                >
                    <i class="fa-solid fa-database text-indigo-300"></i>
                    Total: {{ props.motamots?.total ?? 0 }}
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
                    {{ props.motamots?.data?.length ?? 0 }} shown
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
                                Email
                            </th>
                            <th
                                class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-56"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="(user, index) in props.motamots?.data"
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
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold text-xs shadow"
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
                                        <p class="text-xs text-slate-500">
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-indigo-200 capitalize"
                                            >
                                                <i
                                                    class="fa-solid fa-user-tag text-[8px]"
                                                ></i>
                                                {{ user.role }}
                                            </span>
                                        </p>
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
                                <a
                                    v-if="user.email"
                                    :href="`mailto:${user.email}`"
                                    class="text-sm font-medium text-slate-700 hover:text-indigo-700 line-clamp-1"
                                    v-html="highlight(user.email)"
                                ></a>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <Link
                                        :href="route('motamot.user', user.id)"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 px-2.5 py-1.5 text-xs font-bold hover:bg-indigo-100 hover:scale-105 transition"
                                        title="View Motamots"
                                    >
                                        <i class="fa fa-eye text-[10px]"></i>
                                        View
                                    </Link>
                                    <button
                                        @click="deleteUser(user)"
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
                v-if="!props.motamots?.data?.length"
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
                    {{ search ? "No matching users" : "No motamot users yet" }}
                </p>
                <p class="text-sm text-slate-500">
                    {{
                        search
                            ? "Try a different search term."
                            : "Motamot users will appear here."
                    }}
                </p>
            </div>
        </div>

        <!-- ==================== MOBILE CARDS ==================== -->
        <div class="md:hidden space-y-3">
            <div
                v-for="(user, index) in props.motamots?.data"
                :key="user.id"
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden"
            >
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold shadow"
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
                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-indigo-200 capitalize"
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

                    <!-- Phone strip -->
                    <div
                        class="mt-3 flex items-center gap-2 rounded-lg bg-slate-50 ring-1 ring-slate-100 px-3 py-2"
                    >
                        <i class="fa-solid fa-phone text-slate-400 text-xs"></i>
                        <span class="text-xs font-semibold text-slate-700">
                            {{ user.phone || "—" }}
                        </span>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 border-t border-slate-100 divide-x divide-slate-100"
                >
                    <Link
                        :href="route('motamot.user', user.id)"
                        class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-indigo-700 hover:bg-indigo-50 transition"
                    >
                        <i class="fa fa-eye text-[10px]"></i>
                        View Motamots
                    </Link>
                    <button
                        @click="deleteUser(user)"
                        class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-red-700 hover:bg-red-50 transition"
                    >
                        <i class="fa fa-trash text-[10px]"></i>
                        Delete
                    </button>
                </div>
            </div>

            <!-- Empty mobile -->
            <div
                v-if="!props.motamots?.data?.length"
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
                    {{ search ? "No matching users" : "No motamot users yet" }}
                </p>
                <p class="text-sm text-slate-500">
                    {{
                        search
                            ? "Try a different search term."
                            : "Motamot users will appear here."
                    }}
                </p>
            </div>
        </div>

        <!-- ==================== PAGINATION ==================== -->
        <div
            v-if="props.motamots?.links?.length > 3"
            class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
        >
            <template v-for="(link, i) in props.motamots.links" :key="i">
                <button
                    v-if="link.url"
                    @click="visitPage(link.url)"
                    class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold transition"
                    :class="
                        link.active
                            ? 'bg-gradient-to-r from-indigo-500 to-violet-600 text-white shadow'
                            : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-indigo-50 hover:text-indigo-700 hover:ring-indigo-300'
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
