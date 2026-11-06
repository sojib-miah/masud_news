<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";

const props = defineProps({
    categories: Array,
    subcategories: Array,
});

// ==================== CATEGORY FORM ====================
const categoryForm = useForm({
    id: null,
    name: "",
});

const editingCategory = computed(() => !!categoryForm.id);

const editCategory = (cat) => {
    categoryForm.id = cat.id;
    categoryForm.name = cat.name;

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document.getElementById("category-form")?.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
};

const resetCategoryForm = () => {
    categoryForm.reset();
    categoryForm.id = null;
};

const updateCategory = () => {
    categoryForm.put(route("categories.update", categoryForm.id), {
        preserveScroll: true,
        onSuccess: () => resetCategoryForm(),
    });
};

const submitCategory = () => {
    if (categoryForm.id) {
        updateCategory();
    } else {
        categoryForm.post(route("categories.store"), {
            preserveScroll: true,
            onSuccess: () => resetCategoryForm(),
        });
    }
};

const deleteCategory = (cat) => {
    if (
        confirm(
            `Delete category "${cat.name}"? All its subcategories will also be deleted.`,
        )
    ) {
        categoryForm.delete(route("categories.destroy", cat.id), {
            preserveScroll: true,
        });
    }
};

// ==================== SUBCATEGORY FORM ====================
const subCategoryForm = useForm({
    id: null,
    category_id: "",
    name: "",
});

const editingSubCategory = computed(() => !!subCategoryForm.id);

const editSubCategory = (sub) => {
    subCategoryForm.id = sub.id;
    subCategoryForm.name = sub.name;
    subCategoryForm.category_id = sub.category_id;

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document.getElementById("subcategory-form")?.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
};

const resetSubCategoryForm = () => {
    subCategoryForm.reset();
    subCategoryForm.id = null;
};

const updateSubCategory = () => {
    subCategoryForm.put(route("subcategories.update", subCategoryForm.id), {
        preserveScroll: true,
        onSuccess: () => resetSubCategoryForm(),
    });
};

const submitSubCategory = () => {
    if (subCategoryForm.id) {
        updateSubCategory();
    } else {
        subCategoryForm.post(route("subcategories.store"), {
            preserveScroll: true,
            onSuccess: () => resetSubCategoryForm(),
        });
    }
};

const deleteSubCategory = (sub) => {
    if (confirm(`Delete subcategory "${sub.name}"?`)) {
        subCategoryForm.delete(route("subcategories.destroy", sub.id), {
            preserveScroll: true,
        });
    }
};

// Filter subcategories by search
const subSearch = ref("");
const filteredSubcategories = computed(() => {
    const q = subSearch.value.trim().toLowerCase();
    if (!q) return props.subcategories ?? [];
    return (props.subcategories ?? []).filter(
        (s) =>
            s.name?.toLowerCase().includes(q) ||
            s.category?.name?.toLowerCase().includes(q),
    );
});

// Group subcategories by category for nice display
const groupedSubcategories = computed(() => {
    const groups = {};
    filteredSubcategories.value.forEach((sub) => {
        const key = sub.category?.name ?? "Uncategorized";
        if (!groups[key]) groups[key] = [];
        groups[key].push(sub);
    });
    return groups;
});
</script>

<template>
    <Layout>
        <Breadcrumb />
        <Head title="Categories & Subcategories" />

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
                        <i class="fa-solid fa-sitemap mr-1"></i> Taxonomy
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Categories & Subcategories
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Organize your news content with a clean two-level
                        hierarchy.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                    >
                        <i class="fa-solid fa-folder text-indigo-300"></i>
                        {{ props.categories?.length ?? 0 }} Categories
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                    >
                        <i class="fa-solid fa-folder-tree text-emerald-300"></i>
                        {{ props.subcategories?.length ?? 0 }} Subcategories
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== MAIN GRID ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- ============================================================ -->
            <!-- ==================== CATEGORY PANEL ========================= -->
            <!-- ============================================================ -->
            <div
                class="rounded-2xl h-fit bg-white shadow-md ring-1 ring-slate-100 overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 text-white shadow"
                    >
                        <i class="fa-solid fa-folder text-sm"></i>
                    </span>
                    <div>
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800"
                        >
                            Categories
                        </h2>
                        <p class="text-xs text-slate-500">
                            Top-level grouping for news
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <div
                    id="category-form"
                    class="p-5 border-b border-slate-100 bg-slate-50/50"
                >
                    <form @submit.prevent="submitCategory" class="space-y-3">
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                            >
                                <i
                                    class="fa-solid mr-1"
                                    :class="
                                        editingCategory
                                            ? 'fa-pen text-blue-600'
                                            : 'fa-plus text-indigo-600'
                                    "
                                ></i>
                                {{
                                    editingCategory
                                        ? "Edit Category"
                                        : "New Category"
                                }}
                            </label>
                            <input
                                v-model="categoryForm.name"
                                type="text"
                                placeholder="e.g. Sports, Politics, Technology..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                                @input="categoryForm.errors.name = null"
                            />
                            <p
                                v-if="categoryForm.errors.name"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ categoryForm.errors.name }}
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="submit"
                                :disabled="categoryForm.processing"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                                :class="
                                    editingCategory
                                        ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                        : 'from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700'
                                "
                            >
                                <i
                                    v-if="categoryForm.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i
                                    v-else
                                    :class="
                                        editingCategory
                                            ? 'fa-solid fa-check'
                                            : 'fa-solid fa-plus'
                                    "
                                ></i>
                                {{
                                    categoryForm.processing
                                        ? "Processing..."
                                        : editingCategory
                                          ? "Update Category"
                                          : "Create Category"
                                }}
                            </button>

                            <button
                                v-if="editingCategory"
                                type="button"
                                @click="resetCategoryForm"
                                class="rounded-lg bg-white px-4 text-slate-700 font-semibold text-sm ring-1 ring-slate-200 hover:bg-slate-100 transition"
                                title="Cancel edit"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- List -->
                <div class="p-3">
                    <!-- Empty state -->
                    <div
                        v-if="!props.categories?.length"
                        class="p-8 text-center"
                    >
                        <i
                            class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2"
                        ></i>
                        <p class="text-slate-500 font-semibold text-sm">
                            No categories yet
                        </p>
                        <p class="text-xs text-slate-400">
                            Create one using the form above.
                        </p>
                    </div>

                    <!-- Category cards -->
                    <div v-else class="space-y-2">
                        <div
                            v-for="cat in props.categories"
                            :key="cat.id"
                            class="group flex items-start gap-3 rounded-lg border border-slate-100 bg-white px-3 py-2.5 hover:border-indigo-200 hover:bg-indigo-50/40 transition"
                        >
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 ring-1 ring-indigo-200"
                            >
                                <i class="fa-solid fa-folder text-sm"></i>
                            </span>

                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-bold text-slate-800 break-words"
                                >
                                    {{ cat.name }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    <i
                                        class="fa-solid fa-folder-tree mr-1 text-emerald-500"
                                    ></i>
                                    {{ cat.subcategories?.length ?? 0 }}
                                    subcategories
                                </p>
                            </div>

                            <div
                                class="flex items-center gap-1.5 shrink-0 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <button
                                    @click="editCategory(cat)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 hover:bg-yellow-100 hover:scale-110 transition"
                                    title="Edit"
                                >
                                    <i class="fa fa-edit text-xs"></i>
                                </button>
                                <button
                                    @click="deleteCategory(cat)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                    title="Delete"
                                >
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- ================= SUBCATEGORY PANEL ======================== -->
            <!-- ============================================================ -->
            <div
                class="rounded-2xl h-fit bg-white shadow-md ring-1 ring-slate-100 overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow"
                    >
                        <i class="fa-solid fa-folder-tree text-sm"></i>
                    </span>
                    <div>
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800"
                        >
                            Subcategories
                        </h2>
                        <p class="text-xs text-slate-500">
                            Detailed grouping under each category
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <div
                    id="subcategory-form"
                    class="p-5 border-b border-slate-100 bg-slate-50/50"
                >
                    <form @submit.prevent="submitSubCategory" class="space-y-3">
                        <div>
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                            >
                                <i
                                    class="fa-solid mr-1"
                                    :class="
                                        editingSubCategory
                                            ? 'fa-pen text-blue-600'
                                            : 'fa-plus text-emerald-600'
                                    "
                                ></i>
                                {{
                                    editingSubCategory
                                        ? "Edit Subcategory"
                                        : "New Subcategory"
                                }}
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <select
                                        v-model="subCategoryForm.category_id"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white"
                                        @change="
                                            subCategoryForm.errors.category_id =
                                                null
                                        "
                                    >
                                        <option disabled value="">
                                            Select Category
                                        </option>
                                        <option
                                            v-for="cat in props.categories"
                                            :key="cat.id"
                                            :value="cat.id"
                                        >
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="
                                            subCategoryForm.errors.category_id
                                        "
                                        class="text-xs text-red-500 mt-1"
                                    >
                                        {{ subCategoryForm.errors.category_id }}
                                    </p>
                                </div>

                                <div>
                                    <input
                                        v-model="subCategoryForm.name"
                                        type="text"
                                        placeholder="Subcategory name..."
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                        @input="
                                            subCategoryForm.errors.name = null
                                        "
                                    />
                                    <p
                                        v-if="subCategoryForm.errors.name"
                                        class="text-xs text-red-500 mt-1"
                                    >
                                        {{ subCategoryForm.errors.name }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="submit"
                                :disabled="subCategoryForm.processing"
                                class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                                :class="
                                    editingSubCategory
                                        ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                        : 'from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700'
                                "
                            >
                                <i
                                    v-if="subCategoryForm.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i
                                    v-else
                                    :class="
                                        editingSubCategory
                                            ? 'fa-solid fa-check'
                                            : 'fa-solid fa-plus'
                                    "
                                ></i>
                                {{
                                    subCategoryForm.processing
                                        ? "Processing..."
                                        : editingSubCategory
                                          ? "Update Subcategory"
                                          : "Create Subcategory"
                                }}
                            </button>

                            <button
                                v-if="editingSubCategory"
                                type="button"
                                @click="resetSubCategoryForm"
                                class="rounded-lg bg-white px-4 text-slate-700 font-semibold text-sm ring-1 ring-slate-200 hover:bg-slate-100 transition"
                                title="Cancel edit"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Search + List -->
                <div class="p-3">
                    <!-- Search -->
                    <div
                        v-if="props.subcategories?.length"
                        class="relative mb-3"
                    >
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"
                        ></i>
                        <input
                            v-model="subSearch"
                            type="text"
                            placeholder="Search subcategories..."
                            class="w-full rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                        <button
                            v-if="subSearch"
                            @click="subSearch = ''"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <!-- Empty states -->
                    <div
                        v-if="!props.subcategories?.length"
                        class="p-8 text-center"
                    >
                        <i
                            class="fa-solid fa-folder-tree text-3xl text-slate-300 mb-2"
                        ></i>
                        <p class="text-slate-500 font-semibold text-sm">
                            No subcategories yet
                        </p>
                        <p class="text-xs text-slate-400">
                            Create one using the form above.
                        </p>
                    </div>

                    <div
                        v-else-if="!filteredSubcategories.length"
                        class="p-6 text-center"
                    >
                        <i
                            class="fa-solid fa-magnifying-glass text-2xl text-slate-300 mb-2"
                        ></i>
                        <p class="text-sm text-slate-500">
                            No results for "{{ subSearch }}"
                        </p>
                    </div>

                    <!-- Grouped list -->
                    <div v-else class="space-y-3">
                        <div
                            v-for="(
                                items, categoryName
                            ) in groupedSubcategories"
                            :key="categoryName"
                            class="rounded-lg border border-slate-100 overflow-hidden"
                        >
                            <!-- Category header -->
                            <div
                                class="flex items-center gap-2 bg-emerald-50/60 px-3 py-1.5 border-b border-emerald-100"
                            >
                                <i
                                    class="fa-solid fa-folder text-emerald-600 text-xs"
                                ></i>
                                <p
                                    class="text-xs font-bold text-emerald-800 truncate"
                                >
                                    {{ categoryName }}
                                </p>
                                <span
                                    class="ml-auto inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-200"
                                >
                                    {{ items.length }}
                                </span>
                            </div>

                            <!-- Subcategory items -->
                            <div class="divide-y divide-slate-100">
                                <div
                                    v-for="sub in items"
                                    :key="sub.id"
                                    class="group flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition"
                                >
                                    <i
                                        class="fa-solid fa-angle-right text-slate-300 text-xs"
                                    ></i>
                                    <p
                                        class="flex-1 text-sm font-medium text-slate-700 break-words min-w-0"
                                    >
                                        {{ sub.name }}
                                    </p>

                                    <div
                                        class="flex items-center gap-1.5 shrink-0 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <button
                                            @click="editSubCategory(sub)"
                                            class="flex h-7 w-7 items-center justify-center rounded-md bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 hover:bg-yellow-100 hover:scale-110 transition"
                                            title="Edit"
                                        >
                                            <i
                                                class="fa fa-edit text-[10px]"
                                            ></i>
                                        </button>
                                        <button
                                            @click="deleteSubCategory(sub)"
                                            class="flex h-7 w-7 items-center justify-center rounded-md bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                            title="Delete"
                                        >
                                            <i
                                                class="fa fa-trash text-[10px]"
                                            ></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
