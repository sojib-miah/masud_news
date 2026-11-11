<script setup>
import { useForm, Head, router, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    permissions: Object,
});

const editing = ref(null);

const form = useForm({
    name: "",
});

// ==================== ACTIONS ====================
function submit() {
    if (editing.value) {
        form.put(`/admin/permissions/${editing.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                editing.value = null;
                form.reset();
                router.reload({ only: ["permissions"] });
            },
        });
    } else {
        form.post("/admin/permissions", {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                router.reload({ only: ["permissions"] });
            },
        });
    }
}

function edit(permission) {
    editing.value = permission;
    form.name = permission.name;

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document
            .getElementById("permission-form")
            ?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

function cancelEdit() {
    editing.value = null;
    form.reset();
}

function remove(permission) {
    if (confirm(`Delete permission "${permission.name}"?`)) {
        router.delete(`/admin/permissions/${permission.id}`, {
            preserveScroll: true,
        });
    }
}

// ==================== SEARCH ====================
const page = usePage();
const search = ref(page.props.search || "");

function fetchPermissions() {
    router.get(
        "/admin/permissions",
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get(
        "/admin/permissions",
        {},
        { preserveState: false, replace: true },
    );
}

function highlight(text) {
    const keyword = search.value.trim();
    if (!keyword) return text;

    const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return text.replace(
        regex,
        '<span class="bg-yellow-300 text-slate-900 rounded px-0.5">$1</span>',
    );
}

// ==================== ANIMATED PLACEHOLDER ====================
const animatedPlaceholder = ref("");
const placeholderText = "Search Permissions...";
let intervalId;

onMounted(() => {
    let i = 0;
    let typingForward = true;

    intervalId = setInterval(() => {
        if (typingForward) {
            animatedPlaceholder.value = placeholderText.slice(0, i + 1);
            i++;
            if (i === placeholderText.length) typingForward = false;
        } else {
            animatedPlaceholder.value = placeholderText.slice(0, i);
            i--;
            if (i === 0) typingForward = true;
        }
    }, 200);
});

onUnmounted(() => {
    clearInterval(intervalId);
});

// ==================== HELPERS ====================
function globalIndex(i) {
    const page = props.permissions?.current_page ?? 1;
    const perPage = props.permissions?.per_page ?? 20;
    return (page - 1) * perPage + i + 1;
}
</script>

<template>
    <div>
        <Head title="Permission Management" />

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
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-sky-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-key mr-1"></i> Access Control
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Permission Management
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Define granular capabilities to assign to roles.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <i class="fa-solid fa-key text-emerald-300"></i>
                        Total: {{ props.permissions?.total ?? 0 }} permissions
                    </div>
                </div>
            </div>

            <!-- ==================== MAIN GRID ==================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- ==================== LIST ==================== -->
                <div
                    class="lg:col-span-2 order-2 lg:order-1 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden"
                >
                    <!-- Header & Search -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 px-5 py-4"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow"
                            >
                                <i class="fa-solid fa-list text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    All Permissions
                                </h2>
                                <p class="text-xs text-slate-500">
                                    {{ props.permissions?.total ?? 0 }} total
                                    entries
                                </p>
                            </div>
                        </div>
                        <div class="w-full sm:w-64">
                            <SearchFilter
                                v-model="search"
                                :placeholder="animatedPlaceholder"
                                @search="fetchPermissions"
                                @reset="resetSearch"
                            />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="!props.permissions?.data?.length"
                        class="p-10 text-center"
                    >
                        <i
                            class="fa-solid fa-key text-4xl text-slate-300 mb-3"
                        ></i>
                        <p class="text-slate-500 font-semibold">
                            No permissions found
                        </p>
                        <p class="text-sm text-slate-400">
                            Try a different search or create a new one.
                        </p>
                    </div>

                    <!-- Card List -->
                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="(permission, i) in props.permissions.data"
                            :key="permission.id"
                            class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors flex items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200 font-bold text-xs"
                                >
                                    {{ globalIndex(i) }}
                                </span>
                                <div class="min-w-0">
                                    <h3
                                        class="text-sm font-bold text-slate-800 break-words"
                                        v-html="highlight(permission.name)"
                                    ></h3>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <button
                                    @click="edit(permission)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                    title="Edit"
                                >
                                    <i class="fa fa-edit text-xs"></i>
                                </button>
                                <button
                                    @click="remove(permission)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                    title="Delete"
                                >
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.permissions?.links?.length > 3"
                        class="flex flex-wrap items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3"
                    >
                        <template
                            v-for="(link, idx) in props.permissions.links"
                            :key="idx"
                        >
                            <button
                                v-if="link.url"
                                @click="
                                    router.visit(link.url, {
                                        preserveScroll: true,
                                        preserveState: true,
                                    })
                                "
                                class="min-w-[34px] h-9 flex items-center justify-center rounded-lg px-2 text-xs font-bold transition"
                                :class="
                                    link.active
                                        ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow'
                                        : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'
                                "
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="min-w-[34px] h-9 flex items-center justify-center rounded-lg px-2 text-xs font-bold text-slate-300 bg-slate-50 ring-1 ring-slate-100"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>

                <!-- ==================== FORM (SIDEBAR) ==================== -->
                <div
                    id="permission-form"
                    class="lg:col-span-1 order-1 lg:order-2 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit lg:sticky lg:top-4"
                >
                    <div
                        class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-white shadow"
                            :class="
                                editing
                                    ? 'bg-gradient-to-br from-blue-500 to-indigo-600'
                                    : 'bg-gradient-to-br from-emerald-500 to-teal-600'
                            "
                        >
                            <i
                                :class="
                                    editing
                                        ? 'fa-solid fa-pen'
                                        : 'fa-solid fa-plus'
                                "
                                class="text-sm"
                            ></i>
                        </span>
                        <div class="min-w-0">
                            <h2
                                class="text-base sm:text-lg font-bold text-slate-800 truncate"
                            >
                                {{
                                    editing
                                        ? "Edit Permission"
                                        : "Create Permission"
                                }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{
                                    editing
                                        ? "Update permission name"
                                        : "Add a new capability"
                                }}
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-5 space-y-4">
                        <TextInput
                            id="name"
                            label="Permission Name *"
                            v-model="form.name"
                            :error="form.errors.name"
                            placeholder="e.g. user.create, post.edit"
                            @input="form.errors.name = null"
                        />

                        <div class="text-xs text-slate-500">
                            <i
                                class="fa-solid fa-circle-info text-emerald-600 mr-1"
                            ></i>
                            Standard format is <code>module.action</code> (e.g.,
                            <code>user.view</code>). Keep it unique and
                            descriptive.
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-2 pt-4 border-t border-slate-100">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                                :class="
                                    editing
                                        ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                        : 'from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700'
                                "
                            >
                                <i
                                    v-if="form.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i
                                    v-else
                                    :class="
                                        editing
                                            ? 'fa-solid fa-check'
                                            : 'fa-solid fa-plus'
                                    "
                                ></i>
                                {{
                                    form.processing
                                        ? "Saving..."
                                        : editing
                                          ? "Update Permission"
                                          : "Create Permission"
                                }}
                            </button>

                            <button
                                v-if="editing"
                                type="button"
                                @click="cancelEdit"
                                class="rounded-lg bg-slate-100 px-4 text-slate-700 font-semibold text-sm ring-1 ring-slate-200 hover:bg-slate-200 transition"
                                title="Cancel edit"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Layout>
    </div>
</template>

<style scoped>
@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
