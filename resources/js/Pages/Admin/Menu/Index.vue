<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref, computed } from "vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import IconPicker from "@/Shared/IconPicker.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";

const props = defineProps({
    menuList: Object,
    allPermissions: Array,
    allMenus: Array,
    search: String,
});

const editing = ref(null);
const search = ref(props.search || "");

const form = useForm({
    title: "",
    parent_id: "",
    permission_class: [],
    route: "#",
    icon: "",
    status: true,
    top: false,
    left: true,
    footer: false,
    order_by: "",
});

// ==================== ACTIONS ====================
function submit() {
    if (editing.value) {
        form.put(`/admin/menus/${editing.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                editing.value = null;
                form.reset();
                router.reload({ only: ["menuList"] });
            },
        });
    } else {
        form.post("/admin/menus", {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                router.reload({ only: ["menuList"] });
            },
        });
    }
}

function edit(menu) {
    editing.value = menu;
    form.title = menu.title;
    form.route = menu.route;
    form.icon = menu.icon;
    form.parent_id = menu.parent_id || "";
    form.permission_class = Array.isArray(menu.permission_class)
        ? menu.permission_class
        : menu.permission_class
          ? [menu.permission_class]
          : [];
    form.status = !!menu.status;
    form.top = !!menu.top;
    form.left = !!menu.left;
    form.footer = !!menu.footer;
    form.order_by = menu.order_by;

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document
            .getElementById("menu-form")
            ?.scrollIntoView({ behavior: "smooth", block: "start" });
    }
}

function cancelEdit() {
    editing.value = null;
    form.reset();
}

function remove(menu) {
    if (confirm(`Delete menu "${menu.title}"?`)) {
        router.delete(`/admin/menus/${menu.id}`, { preserveScroll: true });
    }
}

// ==================== SEARCH ====================
function fetchMenus() {
    router.get(
        "/admin/menus",
        { search: search.value },
        { preserveState: true, replace: true },
    );
}

function resetSearch() {
    search.value = "";
    router.get("/admin/menus", {}, { preserveState: false, replace: true });
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
const placeholderText = "Search Menu...";
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
    const page = props.menuList?.current_page ?? 1;
    const perPage = props.menuList?.per_page ?? 20;
    return (page - 1) * perPage + i + 1;
}
</script>

<template>
    <div>
        <Head title="Menu Management" />

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
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-indigo-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-bars-staggered mr-1"></i>
                            Navigation
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Menu Management
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Configure sidebar, header, and footer navigation
                            links.
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                    >
                        <i class="fa-solid fa-list text-indigo-300"></i>
                        Total: {{ props.menuList?.total ?? 0 }} menus
                    </div>
                </div>
            </div>

            <!-- ==================== MAIN GRID ==================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- ==================== LIST ==================== -->
                <div
                    class="lg:col-span-2 order-2 lg:order-1 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden"
                >
                    <!-- Search Header -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 px-5 py-4"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 text-white shadow"
                            >
                                <i class="fa-solid fa-list text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    All Menus
                                </h2>
                                <p class="text-xs text-slate-500">
                                    {{ props.menuList?.total ?? 0 }} total
                                    entries
                                </p>
                            </div>
                        </div>
                        <div class="w-full sm:w-64">
                            <SearchFilter
                                v-model="search"
                                :placeholder="animatedPlaceholder"
                                @search="fetchMenus"
                                @reset="resetSearch"
                            />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="!props.menuList?.data?.length"
                        class="p-10 text-center"
                    >
                        <i
                            class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"
                        ></i>
                        <p class="text-slate-500 font-semibold">
                            No menus found
                        </p>
                        <p class="text-sm text-slate-400">
                            Try a different search or create a new menu.
                        </p>
                    </div>

                    <!-- Card List (Replaces unreadable 12-column table) -->
                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="(menu, i) in props.menuList.data"
                            :key="menu.id"
                            class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors"
                        >
                            <!-- Top Row: Title & Actions -->
                            <div
                                class="flex items-start justify-between gap-3 mb-2"
                            >
                                <div class="flex items-start gap-2 min-w-0">
                                    <span
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-indigo-200 mt-0.5"
                                    >
                                        <i
                                            :class="
                                                menu.icon || 'fa-solid fa-link'
                                            "
                                            class="text-sm"
                                        ></i>
                                    </span>
                                    <div class="min-w-0">
                                        <h3
                                            class="text-sm font-bold text-slate-800 break-words"
                                            v-html="highlight(menu.title)"
                                        ></h3>
                                        <p
                                            class="text-xs text-slate-500 truncate"
                                            :title="menu.route"
                                        >
                                            {{ menu.route }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button
                                        @click="edit(menu)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                        title="Edit"
                                    >
                                        <i class="fa fa-edit text-xs"></i>
                                    </button>
                                    <button
                                        @click="remove(menu)"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                        title="Delete"
                                    >
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Middle Row: Meta badges (Parent, Order, Permissions) -->
                            <div class="flex flex-wrap gap-2 mb-3 pl-10">
                                <span
                                    v-if="menu.parent"
                                    class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600"
                                >
                                    <i
                                        class="fa-solid fa-level-up-alt fa-rotate-90 text-slate-400"
                                    ></i>
                                    {{ menu.parent.title }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600"
                                >
                                    <i
                                        class="fa-solid fa-sort text-slate-400"
                                    ></i>
                                    Order: {{ menu.order_by || 0 }}
                                </span>

                                <!-- Permissions -->
                                <span
                                    v-if="
                                        Array.isArray(menu.permission_class)
                                            ? menu.permission_class.length
                                            : menu.permission_class
                                    "
                                    class="inline-flex flex-wrap gap-1"
                                >
                                    <template
                                        v-if="
                                            Array.isArray(menu.permission_class)
                                        "
                                    >
                                        <span
                                            v-for="(
                                                perm, idx
                                            ) in menu.permission_class"
                                            :key="idx"
                                            class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200"
                                        >
                                            {{ perm }}
                                        </span>
                                    </template>
                                    <span
                                        v-else
                                        class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200"
                                    >
                                        {{ menu.permission_class }}
                                    </span>
                                </span>
                            </div>

                            <!-- Bottom Row: Visibility Toggles -->
                            <div
                                class="flex items-center gap-3 pl-10 text-xs font-semibold"
                            >
                                <span
                                    class="flex items-center gap-1"
                                    :class="
                                        menu.status
                                            ? 'text-green-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    <i
                                        :class="
                                            menu.status
                                                ? 'fa-solid fa-circle-check'
                                                : 'fa-regular fa-circle'
                                        "
                                    ></i>
                                    Status
                                </span>
                                <span
                                    class="flex items-center gap-1"
                                    :class="
                                        menu.top
                                            ? 'text-green-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    <i
                                        :class="
                                            menu.top
                                                ? 'fa-solid fa-circle-check'
                                                : 'fa-regular fa-circle'
                                        "
                                    ></i>
                                    Top
                                </span>
                                <span
                                    class="flex items-center gap-1"
                                    :class="
                                        menu.left
                                            ? 'text-green-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    <i
                                        :class="
                                            menu.left
                                                ? 'fa-solid fa-circle-check'
                                                : 'fa-regular fa-circle'
                                        "
                                    ></i>
                                    Left
                                </span>
                                <span
                                    class="flex items-center gap-1"
                                    :class="
                                        menu.footer
                                            ? 'text-green-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    <i
                                        :class="
                                            menu.footer
                                                ? 'fa-solid fa-circle-check'
                                                : 'fa-regular fa-circle'
                                        "
                                    ></i>
                                    Footer
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="props.menuList?.links?.length > 3"
                        class="flex flex-wrap items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3"
                    >
                        <template
                            v-for="(link, idx) in props.menuList.links"
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
                                        ? 'bg-gradient-to-r from-indigo-500 to-blue-600 text-white shadow'
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
                    id="menu-form"
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
                                    : 'bg-gradient-to-br from-indigo-500 to-blue-600'
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
                                {{ editing ? "Edit Menu" : "Create Menu" }}
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{
                                    editing
                                        ? "Update existing link"
                                        : "Add a new link"
                                }}
                            </p>
                        </div>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="p-5 space-y-4 max-h-[calc(100vh-220px)] overflow-y-auto"
                    >
                        <TextInput
                            id="title"
                            label="Title *"
                            v-model="form.title"
                            :error="form.errors.title"
                            placeholder="e.g. Dashboard"
                            @input="form.errors.title = null"
                        />
                        <TextInput
                            id="route"
                            label="Route/URL"
                            v-model="form.route"
                            :error="form.errors.route"
                            placeholder="e.g. /admin/dashboard"
                            @input="form.errors.route = null"
                        />

                        <SelectInput
                            v-model="form.parent_id"
                            :options="allMenus"
                            label="Parent Menu"
                            placeholder="No Parent (Root)"
                            :error="form.errors.parent_id"
                        />

                        <SelectInput
                            v-model="form.permission_class"
                            :options="allPermissions"
                            label="Permissions"
                            multiple
                            placeholder="Select permissions..."
                            :error="form.errors.permission_class"
                        />

                        <!-- Toggles Grid -->
                        <div
                            class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-lg ring-1 ring-slate-200"
                        >
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.status"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                />
                                <span
                                    class="text-sm font-semibold text-slate-700"
                                    >Status</span
                                >
                            </label>
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.top"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                />
                                <span
                                    class="text-sm font-semibold text-slate-700"
                                    >Top Bar</span
                                >
                            </label>
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.left"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                />
                                <span
                                    class="text-sm font-semibold text-slate-700"
                                    >Left Side</span
                                >
                            </label>
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    v-model="form.footer"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                />
                                <span
                                    class="text-sm font-semibold text-slate-700"
                                    >Footer</span
                                >
                            </label>
                        </div>

                        <IconPicker
                            v-model="form.icon"
                            label="Icon"
                            placeholder="Select an icon..."
                            :error="form.errors.icon"
                        />

                        <TextInput
                            id="order_by"
                            label="Order Sequence *"
                            v-model="form.order_by"
                            :error="form.errors.order_by"
                            placeholder="e.g. 1"
                            @input="form.errors.order_by = null"
                        />

                        <!-- Submit Buttons -->
                        <div class="flex gap-2 pt-2 border-t border-slate-100">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                                :class="
                                    editing
                                        ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                        : 'from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700'
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
                                          ? "Update Menu"
                                          : "Create Menu"
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
