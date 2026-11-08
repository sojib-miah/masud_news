<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Layout from "@/Shared/Layout.vue";
import ReferralNode from "./ReferralNode.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";

const props = defineProps({
    users: Object,
    search: String,
    totalUsers: { type: Number, default: 0 },
    totalRoots: { type: Number, default: 0 },
    totalReferrals: { type: Number, default: 0 },
});

const search = ref(props.search || "");

function fetchUsers() {
    router.get(
        "/admin/referral-tree",
        { search: search.value },
        { preserveState: true, preserveScroll: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/referral-tree", {}, { preserveState: false });
}

function visitPage(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Head title="Referral Tree" />

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
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-violet-500/25 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-blue-500/25 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <p
                        class="text-violet-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-sitemap mr-1"></i> Network
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Referral Tree
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Full multi-level referral hierarchy.
                    </p>
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap gap-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                    >
                        <i class="fa-solid fa-users text-blue-300"></i>
                        {{ props.totalUsers }} Users
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                    >
                        <i class="fa-solid fa-crown text-amber-300"></i>
                        {{ props.totalRoots }} Roots
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                    >
                        <i
                            class="fa-solid fa-diagram-project text-emerald-300"
                        ></i>
                        {{ props.totalReferrals }} Referrals
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== SEARCH ==================== -->
        <div
            class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-4 mb-5"
        >
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="flex-1 relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                    ></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search root users by name, email or phone..."
                        class="w-full rounded-lg border border-slate-300 pl-10 pr-24 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
                        @keydown.enter.prevent="fetchUsers"
                    />
                    <div
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 flex gap-1"
                    >
                        <button
                            v-if="search"
                            @click="resetSearch"
                            class="rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition"
                        >
                            Clear
                        </button>
                        <button
                            @click="fetchUsers"
                            class="rounded-md bg-violet-600 hover:bg-violet-700 px-3 py-1.5 text-xs font-bold text-white transition"
                        >
                            Search
                        </button>
                    </div>
                </div>
                <p
                    class="text-xs font-bold text-slate-500 whitespace-nowrap text-center sm:text-right"
                >
                    <i class="fa-solid fa-tree mr-1"></i>
                    {{ props.users?.data?.length ?? 0 }} root(s) shown
                </p>
            </div>
        </div>

        <!-- ==================== EMPTY ==================== -->
        <div
            v-if="!props.users?.data?.length"
            class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md py-16 px-6 text-center"
        >
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
            >
                <i class="fa-solid fa-sitemap text-3xl text-slate-400"></i>
            </div>
            <p class="text-lg font-bold text-slate-700 mb-1">
                {{ search ? "No matching root users" : "No referrals yet" }}
            </p>
            <p class="text-sm text-slate-500">
                {{
                    search
                        ? "Try a different search term."
                        : "Referral trees will appear here once users are added."
                }}
            </p>
        </div>

        <!-- ==================== TREES ==================== -->
        <div v-else class="space-y-4">
            <div
                v-for="(user, index) in props.users.data"
                :key="user.id"
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
            >
                <!-- Root user header -->
                <div
                    class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-violet-50 via-white to-blue-50 px-4 sm:px-5 py-3"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-blue-600 text-white font-black text-sm shadow"
                    >
                        #{{
                            (props.users.current_page - 1) *
                                props.users.per_page +
                            index +
                            1
                        }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-bold text-slate-800 line-clamp-1"
                        >
                            {{ user.name }}
                        </p>
                        <p class="text-xs text-slate-500 line-clamp-1">
                            {{ user.email || user.phone || "—" }}
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-violet-100 text-violet-700 ring-1 ring-violet-200 px-2.5 py-1 text-[10px] font-bold"
                    >
                        <i class="fa-solid fa-crown text-[9px]"></i>
                        Root
                    </span>
                </div>

                <!-- Tree node -->
                <div class="p-3 sm:p-4 bg-slate-50/40">
                    <ReferralNode :user="user" :depth="0" />
                </div>
            </div>
        </div>

        <!-- ==================== PAGINATION ==================== -->
        <div
            v-if="props.users?.links?.length > 3"
            class="mt-6 flex flex-wrap items-center justify-center gap-1.5"
        >
            <template v-for="(link, i) in props.users.links" :key="i">
                <button
                    v-if="link.url"
                    @click="visitPage(link.url)"
                    class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold transition"
                    :class="
                        link.active
                            ? 'bg-gradient-to-r from-violet-500 to-blue-600 text-white shadow'
                            : 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-violet-50 hover:text-violet-700 hover:ring-violet-300'
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
