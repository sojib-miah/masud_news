<script setup>
import { useForm, Head, router, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref, computed } from "vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    roles: Object,
    permissions: Array,
});

const editing = ref(null);

const form = useForm({
    name: "",
    permissions: [],
});

// ==================== ACTIONS ====================
function submit() {
    if (editing.value) {
        form.put(`/admin/roles/${editing.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                editing.value = null;
                form.reset();
                router.reload({ only: ["roles"] });
            },
        });
    } else {
        form.post("/admin/roles", {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                router.reload({ only: ["roles"] });
            },
        });
    }
}

function edit(role) {
    editing.value = role;
    form.name = role.name;
    form.permissions = role.permissions.map((p) => p.name);

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document
            .getElementById("role-form")
            ?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

function cancelEdit() {
    editing.value = null;
    form.reset();
}

function remove(role) {
    if (confirm(`Delete role "${role.name}"?`)) {
        router.delete(`/admin/roles/${role.id}`, { preserveScroll: true });
    }
}

// ==================== SEARCH ====================
const page = usePage();
const search = ref(page.props.search || "");
const selectedRole = ref(page.props.role || "");
const roleOptions = page.props.roleOptions || [];

function fetchRoles() {
    router.get(
        "/admin/roles",
        {
            search: search.value,
            role: selectedRole.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function resetSearch() {
    search.value = "";
    selectedRole.value = "";
    router.get("/admin/roles", {}, { preserveState: false, replace: true });
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
const placeholderText = "Search Roles...";
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
    }, 150);
});

onUnmounted(() => {
    clearInterval(intervalId);
});

// ==================== HELPERS ====================
function globalIndex(i) {
    const page = props.roles?.current_page ?? 1;
    const perPage = props.roles?.per_page ?? 20;
    return (page - 1) * perPage + i + 1;
}

// Permission group search filter (inside the form)
const permissionSearch = ref("");
const filteredPermissions = computed(() => {
    if (!permissionSearch.value) return props.permissions;
    const q = permissionSearch.value.toLowerCase();
    return props.permissions.filter((p) => p.name.toLowerCase().includes(q));
});

// Toggle all filtered permissions
const allFilteredSelected = computed(() => {
    if (!filteredPermissions.value.length) return false;
    return filteredPermissions.value.every((p) =>
        form.permissions.includes(p.name),
    );
});

function toggleAllFiltered() {
    if (allFilteredSelected.value) {
        // Uncheck all filtered
        const filteredNames = filteredPermissions.value.map((p) => p.name);
        form.permissions = form.permissions.filter(
            (name) => !filteredNames.includes(name),
        );
    } else {
        // Check all filtered
        const newPerms = new Set([
            ...form.permissions,
            ...filteredPermissions.value.map((p) => p.name),
        ]);
        form.permissions = Array.from(newPerms);
    }
}
</script>

<template>
    <div>
        <Head title="Role Management" />

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
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-rose-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-rose-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-shield-halved mr-1"></i>
                            Access Control
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Role Management
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Define user roles and assign granular permissions.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <i class="fa-solid fa-users text-rose-300"></i>
                        Total: {{ props.roles?.total ?? 0 }} roles
                    </div>
                </div>
            </div>

            <!-- ==================== MAIN GRID ==================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- ==================== LIST ==================== -->
                <div
                    class="lg:col-span-2 order-2 lg:order-1 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit"
                >
                    <!-- Header & Search -->
                    <div
                        class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4"
                    >
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow"
                                >
                                    <i class="fa-solid fa-list text-sm"></i>
                                </span>
                                <div>
                                    <h2
                                        class="text-base sm:text-lg font-bold text-slate-800"
                                    >
                                        All Roles
                                    </h2>
                                    <p class="text-xs text-slate-500">
                                        {{ props.roles?.total ?? 0 }} total
                                        entries
                                    </p>
                                </div>
                            </div>

                            <!-- Role Filter -->
                            <div class="w-full sm:w-48">
                                <select
                                    v-model="selectedRole"
                                    @change="fetchRoles"
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm outline-none transition focus:border-rose-500 focus:ring-2 focus:ring-rose-200 bg-white font-semibold text-slate-700"
                                >
                                    <option value="">All Roles</option>
                                    <option
                                        v-for="role in roleOptions"
                                        :key="role"
                                        :value="role"
                                    >
                                        {{ role }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Search Bar -->
                        <div class="w-full">
                            <SearchFilter
                                v-model="search"
                                :placeholder="animatedPlaceholder"
                                @search="fetchRoles"
                                @reset="resetSearch"
                            />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="!props.roles?.data?.length"
                        class="p-10 text-center"
                    >
                        <i
                            class="fa-solid fa-user-shield text-4xl text-slate-300 mb-3"
                        ></i>
                        <p class="text-slate-500 font-semibold">
                            No roles found
                        </p>
                        <p class="text-sm text-slate-400">
                            Try a different search or create a new role.
                        </p>
                    </div>

                    <!-- Card List -->
                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="(role, i) in props.roles.data"
                            :key="role.id"
                            class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors"
                        >
                            <div
                                class="flex items-start justify-between gap-3 mb-3"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    <span
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 ring-1 ring-rose-200"
                                    >
                                        <i
                                            class="fa-solid fa-shield-halved text-sm"
                                        ></i>
                                    </span>
                                    <div class="min-w-0">
                                        <h3
                                            class="text-sm font-bold text-slate-800 break-words"
                                            v-html="highlight(role.name)"
                                        ></h3>
                                        <p class="text-xs text-slate-500">
                                            ID: #{{ role.id }} &bull;
                                            {{ role.permissions.length }}
                                            permissions
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        @click="edit(role)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                        title="Edit"
                                    >
                                        <i class="fa fa-edit text-xs"></i>
                                    </button>
                                    <button
                                        @click="remove(role)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                        title="Delete"
                                    >
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Permission Badges -->
                            <div class="flex flex-wrap gap-1.5 pl-10">
                                <template v-if="role.permissions.length">
                                    <span
                                        v-for="perm in role.permissions"
                                        :key="perm.id"
                                        class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200"
                                    >
                                        <i
                                            class="fa-solid fa-check text-[8px]"
                                        ></i>
                                        {{ perm.name }}
                                    </span>
                                </template>
                                <span
                                    v-else
                                    class="text-xs text-slate-400 italic pl-1"
                                    >No permissions assigned</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.roles?.links?.length > 3"
                        class="flex flex-wrap items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3"
                    >
                        <template
                            v-for="(link, idx) in props.roles.links"
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
                                        ? 'bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow'
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
                    id="role-form"
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
                                    : 'bg-gradient-to-br from-rose-500 to-pink-600'
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
                                {{ editing ? "Edit Role" : "Create Role" }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{
                                    editing
                                        ? "Update role & permissions"
                                        : "Define a new user role"
                                }}
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-5 space-y-4">
                        <TextInput
                            id="name"
                            label="Role Name *"
                            v-model="form.name"
                            :error="form.errors.name"
                            placeholder="e.g. Editor, Admin"
                            @input="form.errors.name = null"
                        />

                        <!-- Permissions Section -->
                        <div class="border-t border-slate-100 pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600"
                                >
                                    <i
                                        class="fa-solid fa-key text-rose-500 mr-1"
                                    ></i>
                                    Assign Permissions
                                </label>
                                <span
                                    class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full ring-1 ring-rose-200"
                                >
                                    {{ form.permissions.length }} selected
                                </span>
                            </div>

                            <!-- Permission Search -->
                            <div class="relative mb-3">
                                <i
                                    class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"
                                ></i>
                                <input
                                    v-model="permissionSearch"
                                    type="text"
                                    placeholder="Search permissions..."
                                    class="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-xs outline-none transition focus:border-rose-500 focus:ring-2 focus:ring-rose-200"
                                />
                            </div>

                            <!-- Select All Toggle -->
                            <div
                                class="flex items-center justify-between mb-2 px-1"
                            >
                                <button
                                    type="button"
                                    @click="toggleAllFiltered"
                                    class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1"
                                >
                                    <i
                                        :class="
                                            allFilteredSelected
                                                ? 'fa-solid fa-square-check'
                                                : 'fa-regular fa-square'
                                        "
                                    ></i>
                                    {{
                                        allFilteredSelected
                                            ? "Deselect All"
                                            : "Select All"
                                    }}
                                </button>
                                <span class="text-[10px] text-slate-400"
                                    >{{
                                        filteredPermissions.length
                                    }}
                                    shown</span
                                >
                            </div>

                            <!-- Scrollable Grid -->
                            <div
                                class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/50 p-2 space-y-1"
                            >
                                <div
                                    v-if="!filteredPermissions.length"
                                    class="p-4 text-center text-xs text-slate-500"
                                >
                                    No permissions match "{{
                                        permissionSearch
                                    }}"
                                </div>
                                <label
                                    v-for="permission in filteredPermissions"
                                    :key="permission.id"
                                    class="flex items-center gap-2.5 rounded-md px-2 py-1.5 hover:bg-white cursor-pointer transition-colors group"
                                >
                                    <input
                                        type="checkbox"
                                        :value="permission.name"
                                        v-model="form.permissions"
                                        class="w-3.5 h-3.5 rounded text-rose-600 focus:ring-rose-500 border-slate-300 cursor-pointer"
                                    />
                                    <span
                                        class="text-xs font-medium text-slate-700 group-hover:text-slate-900 truncate"
                                        :title="permission.name"
                                    >
                                        {{ permission.name }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-2 pt-2 border-t border-slate-100">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                                :class="
                                    editing
                                        ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                        : 'from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700'
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
                                          ? "Update Role"
                                          : "Create Role"
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
/* Custom Scrollbar for Permission List */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
