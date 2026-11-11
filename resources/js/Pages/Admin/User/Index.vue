<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref, computed } from "vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import { route } from "ziggy-js";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    users: Object,
    roles: Array,
    permissions: Array,
    menuList: Array,
    search: String,
});

const selectedUser = ref(null);
const search = ref(props.search || "");

const form = useForm({
    id: null,
    name: "",
    email: "",
    password: "",
    roles: [],
    permissions: [],
    menus: [],
});

// NEW: Tracks which accordion menus are visually open
const expandedMenus = ref([]);

// ==================== ACTIONS ====================
function editUser(user) {
    selectedUser.value = user;
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = "";
    form.roles = user.roles.map((r) => r.name);
    form.permissions = user.permissions.map((p) => p.name);
    form.menus = user.menus.map((m) => m.id);

    // Open all menus that the user currently has access to
    expandedMenus.value = user.menus.map((m) => m.id);

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document
            .getElementById("user-form")
            ?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

function resetForm() {
    selectedUser.value = null;
    form.reset();
    form.id = null;
    expandedMenus.value = [];
}

function submit() {
    if (form.id) {
        // Update existing user
        form.transform((data) => ({
            ...data,
            _method: "put", // Inertia sometimes requires method spoofing for file uploads/FormData, but form.put() usually handles this.
        })).post(route("admin.users.update", form.id), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                router.reload({ only: ["users"] });
            },
        });
    } else {
        // Create new user
        form.post(route("admin.users.store"), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                router.reload({ only: ["users"] });
            },
        });
    }
}

function deleteUser(user) {
    if (confirm(`Are you sure you want to delete "${user.name}"?`)) {
        router.delete(route("admin.users.destroy", user.id), {
            preserveScroll: true,
        });
    }
}

// ==================== SEARCH ====================
function fetchUsers() {
    router.get(
        "/admin/users",
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/users", {}, { preserveState: false, replace: true });
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
const placeholderText = "Search Users...";
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
    const page = props.users?.current_page ?? 1;
    const perPage = props.users?.per_page ?? 20;
    return (page - 1) * perPage + i + 1;
}

// Check if role makes admin/sup_admin (to disable permissions)
const isAdminRole = computed(() => {
    return form.roles.includes("admin") || form.roles.includes("sup_admin");
});

// NEW: Toggle visual accordion AND checkbox automatically
function toggleMenu(menu) {
    const isExpanded = expandedMenus.value.includes(menu.id);
    const isChecked = form.menus.includes(menu.id);

    if (!isExpanded) {
        // Open it visually
        expandedMenus.value.push(menu.id);

        // If it wasn't checked, check it and auto-check its permissions
        if (!isChecked) {
            form.menus.push(menu.id);
            const permNames = menu.permissions.map((p) => p.name);
            const newPerms = new Set([...form.permissions, ...permNames]);
            form.permissions = Array.from(newPerms);
        }
    } else {
        // Close it visually
        expandedMenus.value = expandedMenus.value.filter(
            (id) => id !== menu.id,
        );

        // If it was checked, uncheck it and remove its permissions
        if (isChecked) {
            form.menus = form.menus.filter((id) => id !== menu.id);
            const permNames = menu.permissions.map((p) => p.name);
            form.permissions = form.permissions.filter(
                (p) => !permNames.includes(p),
            );
        }
    }
}

// Handle manual checkbox click (without closing accordion)
function handleMenuCheckbox(menu, event) {
    const isChecked = event.target.checked;

    // Ensure it's expanded visually if checked
    if (isChecked && !expandedMenus.value.includes(menu.id)) {
        expandedMenus.value.push(menu.id);
    }

    const permNames = menu.permissions.map((p) => p.name);

    if (isChecked) {
        // Add all its permissions
        const newPerms = new Set([...form.permissions, ...permNames]);
        form.permissions = Array.from(newPerms);
    } else {
        // Remove all its permissions
        form.permissions = form.permissions.filter(
            (p) => !permNames.includes(p),
        );
    }
}
</script>

<template>
    <div>
        <Head title="User Management" />

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
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-blue-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-users mr-1"></i> Team
                            Management
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            User Management
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Manage system users, assign roles, and restrict
                            access.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <i class="fa-solid fa-user text-blue-300"></i>
                        Total: {{ props.users?.total ?? 0 }} users
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
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow"
                            >
                                <i class="fa-solid fa-list text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    All Users
                                </h2>
                                <p class="text-xs text-slate-500">
                                    {{ props.users?.total ?? 0 }} registered
                                    users
                                </p>
                            </div>
                        </div>
                        <div class="w-full sm:w-64">
                            <SearchFilter
                                v-model="search"
                                :placeholder="animatedPlaceholder"
                                @search="fetchUsers"
                                @reset="resetSearch"
                            />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="!props.users?.data?.length"
                        class="p-10 text-center"
                    >
                        <i
                            class="fa-solid fa-users-slash text-4xl text-slate-300 mb-3"
                        ></i>
                        <p class="text-slate-500 font-semibold">
                            No users found
                        </p>
                        <p class="text-sm text-slate-400">
                            Try a different search or create a new user.
                        </p>
                    </div>

                    <!-- Card List -->
                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="(user, i) in props.users.data"
                            :key="user.id"
                            class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors"
                        >
                            <!-- Top Row: User Info & Actions -->
                            <div
                                class="flex items-start justify-between gap-3 mb-3"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-100 to-blue-100 text-indigo-600 font-bold ring-1 ring-indigo-200 uppercase"
                                    >
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3
                                            class="text-sm font-bold text-slate-800 break-words"
                                            v-html="highlight(user.name)"
                                        ></h3>
                                        <p
                                            class="text-xs text-slate-500 truncate"
                                            v-html="highlight(user.email)"
                                        ></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        @click="editUser(user)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                        title="Edit"
                                    >
                                        <i class="fa fa-edit text-xs"></i>
                                    </button>
                                    <button
                                        @click="deleteUser(user)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                        title="Delete"
                                    >
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Meta Row: Roles & Badges -->
                            <div class="pl-13 space-y-2">
                                <div class="flex flex-wrap gap-1.5">
                                    <!-- Roles -->
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id || role.name"
                                        class="inline-flex items-center gap-1 rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-indigo-200"
                                    >
                                        <i
                                            class="fa-solid fa-shield-halved text-[8px]"
                                        ></i>
                                        {{ role.name }}
                                    </span>
                                </div>

                                <!-- Menus / Permissions (only show if not admin) -->
                                <div
                                    v-if="
                                        !user.roles.some((r) =>
                                            ['admin', 'sup_admin'].includes(
                                                r.name,
                                            ),
                                        )
                                    "
                                    class="flex flex-wrap gap-1.5 mt-2"
                                >
                                    <span
                                        v-for="menu in user.menus"
                                        :key="menu.id"
                                        class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600"
                                    >
                                        <i
                                            class="fa-solid fa-bars text-[8px] text-slate-400"
                                        ></i>
                                        {{ menu.title }}
                                    </span>
                                    <span
                                        v-for="perm in user.permissions"
                                        :key="perm.id"
                                        class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200"
                                    >
                                        <i
                                            class="fa-solid fa-key text-[8px]"
                                        ></i>
                                        {{ perm.name }}
                                    </span>
                                </div>
                                <div
                                    v-else
                                    class="mt-2 text-[11px] text-slate-400 italic flex items-center gap-1"
                                >
                                    <i
                                        class="fa-solid fa-crown text-amber-400"
                                    ></i>
                                    Has full system access
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.users?.links?.length > 3"
                        class="flex flex-wrap items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3"
                    >
                        <template
                            v-for="(link, idx) in props.users.links"
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
                                        ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow'
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
                    id="user-form"
                    class="lg:col-span-1 order-1 lg:order-2 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit lg:sticky lg:top-4"
                >
                    <div
                        class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-white shadow"
                            :class="
                                selectedUser
                                    ? 'bg-gradient-to-br from-blue-500 to-indigo-600'
                                    : 'bg-gradient-to-br from-emerald-500 to-teal-600'
                            "
                        >
                            <i
                                :class="
                                    selectedUser
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
                                {{ selectedUser ? "Edit User" : "Create User" }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{
                                    selectedUser
                                        ? "Update user details"
                                        : "Add a new user"
                                }}
                            </p>
                        </div>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="p-5 space-y-4 max-h-[calc(100vh-220px)] overflow-y-auto"
                    >
                        <TextInput
                            id="name"
                            label="Name *"
                            v-model="form.name"
                            :error="form.errors.name"
                            placeholder="Full Name"
                            @input="form.errors.name = null"
                        />

                        <TextInput
                            id="email"
                            label="Email *"
                            type="email"
                            v-model="form.email"
                            :error="form.errors.email"
                            placeholder="Email Address"
                            @input="form.errors.email = null"
                        />

                        <TextInput
                            id="password"
                            label="Password"
                            type="password"
                            v-model="form.password"
                            :error="form.errors.password"
                            :placeholder="
                                selectedUser
                                    ? 'Leave blank to keep current'
                                    : 'Min 6 characters'
                            "
                            @input="form.errors.password = null"
                        />

                        <!-- Roles -->
                        <div class="border-t border-slate-100 pt-4">
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2"
                            >
                                <i
                                    class="fa-solid fa-shield-halved text-indigo-500 mr-1"
                                ></i>
                                Assign Roles
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <label
                                    v-for="role in roles"
                                    :key="role"
                                    class="flex items-center gap-2 cursor-pointer bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 transition"
                                >
                                    <input
                                        type="checkbox"
                                        :value="role"
                                        v-model="form.roles"
                                        class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                    />
                                    <span
                                        class="text-sm font-semibold text-slate-700 capitalize"
                                        >{{ role }}</span
                                    >
                                </label>
                            </div>
                        </div>

                        <!-- Admin Notice -->
                        <div
                            v-if="isAdminRole"
                            class="p-3 bg-amber-50 rounded-lg ring-1 ring-amber-200"
                        >
                            <p
                                class="text-xs font-bold text-amber-700 flex items-center gap-1.5"
                            >
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                Admin roles automatically get all permissions.
                            </p>
                        </div>

                        <!-- Menus & Permissions -->
                        <div v-else class="border-t border-slate-100 pt-4">
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2"
                            >
                                <i
                                    class="fa-solid fa-bars-staggered text-blue-500 mr-1"
                                ></i>
                                Menu Access & Permissions
                            </label>
                            <div
                                class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50/50 p-2 space-y-2"
                            >
                                <div
                                    v-if="!menuList.length"
                                    class="p-4 text-center text-xs text-slate-500"
                                >
                                    No menus available to configure.
                                </div>
                                <div
                                    v-for="menu in menuList"
                                    :key="menu.id"
                                    class="rounded-md bg-white border border-slate-200 overflow-hidden shadow-sm"
                                >
                                    <!-- Menu Header -->
                                    <div
                                        class="flex items-center justify-between p-2 cursor-pointer hover:bg-slate-50 transition"
                                        @click.prevent="toggleMenu(menu)"
                                    >
                                        <label
                                            class="flex items-center gap-2 flex-1 cursor-pointer"
                                            @click.stop
                                        >
                                            <input
                                                type="checkbox"
                                                :value="menu.id"
                                                v-model="form.menus"
                                                @change="
                                                    handleMenuCheckbox(
                                                        menu,
                                                        $event,
                                                    )
                                                "
                                                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                                            />
                                            <span
                                                class="text-xs font-bold text-slate-800"
                                                >{{ menu.title }}</span
                                            >
                                        </label>

                                        <!-- Expand Chevron -->
                                        <i
                                            :class="
                                                expandedMenus.includes(menu.id)
                                                    ? 'fa-solid fa-chevron-up text-slate-400'
                                                    : 'fa-solid fa-chevron-down text-slate-400'
                                            "
                                            class="text-[10px] pointer-events-none"
                                        ></i>
                                    </div>

                                    <!-- Nested Permissions (Uses expandedMenus instead of form.menus) -->
                                    <div
                                        v-if="
                                            expandedMenus.includes(menu.id) &&
                                            menu.permissions.length
                                        "
                                        class="bg-slate-50 px-3 pb-2 pt-1 border-t border-slate-100"
                                    >
                                        <label
                                            v-for="perm in menu.permissions"
                                            :key="perm.id"
                                            class="flex items-center gap-2 py-0.5 cursor-pointer hover:text-blue-600 transition"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="perm.name"
                                                v-model="form.permissions"
                                                class="w-3.5 h-3.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300"
                                            />
                                            <span
                                                class="text-[11px] font-medium text-slate-600"
                                                >{{ perm.name }}</span
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex gap-2 pt-2 border-t border-slate-100">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                                :class="
                                    selectedUser
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
                                        selectedUser
                                            ? 'fa-solid fa-check'
                                            : 'fa-solid fa-plus'
                                    "
                                ></i>
                                {{
                                    form.processing
                                        ? "Saving..."
                                        : selectedUser
                                          ? "Update User"
                                          : "Create User"
                                }}
                            </button>

                            <button
                                v-if="selectedUser"
                                type="button"
                                @click="resetForm"
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
