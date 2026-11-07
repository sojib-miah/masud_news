<script setup>
import { ref, computed } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";

const props = defineProps({
    products: Object,
});

// ==================== FORM ====================
const form = useForm({
    id: null,
    name: "",
    description: "",
    price: "",
    discount_price: "",
    quantity: "",
    croy: "",
    dipo: "",
    dillar: "",
    sub_dillar: "",
    percentage: "",
    image: null,
});

const editMode = ref(false);
const preview = ref(null);
const search = ref("");

// ==================== VIEW MODAL ====================
const viewModalOpen = ref(false);
const viewProduct = ref(null);

function openView(product) {
    viewProduct.value = product;
    viewModalOpen.value = true;
    document.body.style.overflow = "hidden";
}

function closeView() {
    viewModalOpen.value = false;
    viewProduct.value = null;
    document.body.style.overflow = "";
}

// ==================== FILTERED LIST ====================
const filteredProducts = computed(() => {
    const list = props.products?.data ?? [];
    const q = search.value.trim().toLowerCase();
    if (!q) return list;
    return list.filter(
        (p) => p.name?.toLowerCase().includes(q) || String(p.id).includes(q),
    );
});

// ==================== HELPERS ====================
function formatMoney(n) {
    if (n === null || n === undefined || n === "") return "—";
    return `৳${Number(n).toLocaleString("en-US")}`;
}

function globalIndex(i) {
    const page = props.products?.current_page ?? 1;
    const perPage = props.products?.per_page ?? 20;
    return (page - 1) * perPage + i + 1;
}

function stripHtml(html) {
    if (!html) return "—";
    return html.replace(/<[^>]*>/g, "").trim() || "—";
}

// ==================== EDIT ====================
const editProduct = (product) => {
    form.clearErrors();
    form.id = product.id;
    form.name = product.name ?? "";
    form.description = product.description ?? "";
    form.price = product.price ?? "";
    form.discount_price = product.discount_price ?? "";
    form.quantity = product.quantity ?? "";
    form.croy = product.croy ?? "";
    form.dipo = product.dipo ?? "";
    form.dillar = product.dillar ?? "";
    form.sub_dillar = product.sub_dillar ?? "";
    form.percentage = product.percentage ?? "";
    form.image = null;

    preview.value = product.image ? `/storage/${product.image}` : null;

    editMode.value = true;

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document.getElementById("product-form")?.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
};

// ==================== RESET ====================
const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    if (preview.value && preview.value.startsWith("blob:")) {
        URL.revokeObjectURL(preview.value);
    }
    preview.value = null;
    editMode.value = false;
};

// ==================== IMAGE ====================
const handleImage = (e) => {
    const file = e.target.files[0];
    form.image = file ?? null;

    if (file) {
        if (preview.value && preview.value.startsWith("blob:")) {
            URL.revokeObjectURL(preview.value);
        }
        preview.value = URL.createObjectURL(file);
    }
};

// ==================== SUBMIT ====================
const submit = () => {
    if (editMode.value) {
        form.transform((data) => ({
            ...data,
            _method: "put",
        })).post(route("products.update", form.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: resetForm,
        });
    } else {
        form.post(route("products.store"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: resetForm,
        });
    }
};

// ==================== DELETE ====================
const deleteProduct = (product) => {
    if (confirm(`Delete "${product.name}"?`)) {
        router.delete(route("products.destroy", product.id), {
            preserveScroll: true,
        });
    }
};

// ==================== PAGINATION ====================
function visitPage(url) {
    if (!url) return;
    router.visit(url, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Head title="Product Management Page" />

    <Layout>
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <Breadcrumb />

        <!-- ==================== PAGE HEADER ==================== -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
        >
            <div
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-violet-500/20 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <p
                        class="text-violet-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-box mr-1"></i> Inventory
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Product Management
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Manage products, pricing, stock & media.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                >
                    <i class="fa-solid fa-database text-violet-300"></i>
                    Total: {{ props.products?.total ?? 0 }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- ============================================= -->
            <!-- ================= LIST ====================== -->
            <!-- ============================================= -->
            <div class="lg:col-span-2 order-2 lg:order-1 space-y-5">
                <!-- Search -->
                <div
                    class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-4"
                >
                    <div class="relative">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                        ></i>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name or ID..."
                            class="w-full rounded-lg border border-slate-300 pl-10 pr-9 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
                        />
                        <button
                            v-if="search"
                            @click="search = ''"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition"
                            title="Clear search"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- DESKTOP TABLE -->
                <div
                    class="hidden md:block rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
                >
                    <div
                        class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                    >
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow"
                        >
                            <i class="fa-solid fa-list text-sm"></i>
                        </span>
                        <div>
                            <h2
                                class="text-base sm:text-lg font-bold text-slate-800"
                            >
                                Product List
                            </h2>
                            <p class="text-xs text-slate-500">
                                {{ filteredProducts.length }} of
                                {{ props.products?.data?.length ?? 0 }} shown
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr
                                    class="bg-slate-50 border-b border-slate-100"
                                >
                                    <th
                                        class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-12"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Image
                                    </th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Name
                                    </th>
                                    <th
                                        class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Price
                                    </th>
                                    <th
                                        class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Discount
                                    </th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        Qty
                                    </th>
                                    <th
                                        class="px-3 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        %
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-32"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="(p, i) in filteredProducts"
                                    :key="p.id"
                                    class="hover:bg-slate-50/80 transition-colors"
                                >
                                    <td
                                        class="px-3 py-3 text-sm font-semibold text-slate-600"
                                    >
                                        {{ globalIndex(i) }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <img
                                            v-if="p.image"
                                            :src="`/storage/${p.image}`"
                                            class="h-12 w-12 object-cover rounded-lg ring-1 ring-slate-200 shadow-sm"
                                            :alt="p.name"
                                        />
                                        <div
                                            v-else
                                            class="h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center ring-1 ring-slate-200"
                                        >
                                            <i
                                                class="fa-regular fa-image text-slate-400"
                                            ></i>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <p
                                            class="text-sm font-semibold text-slate-800 line-clamp-1 max-w-xs"
                                        >
                                            {{ p.name }}
                                        </p>
                                        <p
                                            v-if="p.dillar"
                                            class="text-xs text-slate-500"
                                        >
                                            {{ p.dillar }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-3 py-3 text-right text-sm font-semibold text-slate-700 whitespace-nowrap"
                                    >
                                        {{ formatMoney(p.price) }}
                                    </td>
                                    <td
                                        class="px-3 py-3 text-right text-sm font-bold text-emerald-600 whitespace-nowrap"
                                    >
                                        {{ formatMoney(p.discount_price) }}
                                    </td>
                                    <td
                                        class="px-3 py-3 text-center text-sm font-semibold text-slate-700"
                                    >
                                        {{ p.quantity ?? 0 }}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span
                                            v-if="
                                                p.percentage !== null &&
                                                p.percentage !== ''
                                            "
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-amber-200"
                                        >
                                            {{ p.percentage }}%
                                        </span>
                                        <span
                                            v-else
                                            class="text-slate-400 text-xs"
                                            >—</span
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="flex items-center justify-end gap-1.5"
                                        >
                                            <button
                                                @click="openView(p)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200 hover:scale-110 transition"
                                                title="View"
                                            >
                                                <i
                                                    class="fa fa-eye text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="editProduct(p)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                                title="Edit"
                                            >
                                                <i
                                                    class="fa fa-edit text-xs"
                                                ></i>
                                            </button>
                                            <button
                                                @click="deleteProduct(p)"
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                                title="Delete"
                                            >
                                                <i
                                                    class="fa fa-trash text-xs"
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
                        v-if="!filteredProducts.length"
                        class="py-14 px-6 text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                        >
                            <i
                                class="fa-solid fa-box-open text-3xl text-slate-400"
                            ></i>
                        </div>
                        <p class="text-lg font-bold text-slate-700 mb-1">
                            {{
                                search
                                    ? "No matching products"
                                    : "No products yet"
                            }}
                        </p>
                        <p class="text-sm text-slate-500">
                            {{
                                search
                                    ? "Try a different search term."
                                    : "Add your first product using the form."
                            }}
                        </p>
                    </div>
                </div>

                <!-- MOBILE CARDS -->
                <div class="md:hidden space-y-3">
                    <div
                        v-for="(p, i) in filteredProducts"
                        :key="p.id"
                        class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden"
                    >
                        <div class="flex gap-3 p-3">
                            <img
                                v-if="p.image"
                                :src="`/storage/${p.image}`"
                                class="h-24 w-24 shrink-0 object-cover rounded-xl ring-1 ring-slate-200"
                                :alt="p.name"
                            />
                            <div
                                v-else
                                class="h-24 w-24 shrink-0 rounded-xl bg-slate-100 flex items-center justify-center ring-1 ring-slate-200"
                            >
                                <i
                                    class="fa-regular fa-image text-slate-400 text-2xl"
                                ></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex items-center gap-1.5 mb-1 text-[10px]"
                                >
                                    <span
                                        class="inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-600 ring-1 ring-slate-200"
                                    >
                                        #{{ globalIndex(i) }}
                                    </span>
                                    <span
                                        v-if="p.dillar"
                                        class="truncate text-slate-500 font-medium"
                                    >
                                        {{ p.dillar }}
                                    </span>
                                </div>
                                <p
                                    class="text-sm font-bold text-slate-800 leading-5 line-clamp-2"
                                >
                                    {{ p.name }}
                                </p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span
                                        class="text-base font-extrabold text-emerald-600"
                                    >
                                        {{ formatMoney(p.discount_price) }}
                                    </span>
                                    <span
                                        v-if="
                                            Number(p.price) >
                                            Number(p.discount_price)
                                        "
                                        class="text-xs text-slate-400 line-through"
                                    >
                                        {{ formatMoney(p.price) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-3 gap-2 px-3 pb-3 text-center text-xs"
                        >
                            <div
                                class="rounded-lg bg-slate-50 py-1.5 ring-1 ring-slate-100"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Qty
                                </p>
                                <p class="font-bold text-slate-700">
                                    {{ p.quantity ?? 0 }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg bg-slate-50 py-1.5 ring-1 ring-slate-100"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Croy
                                </p>
                                <p class="font-bold text-slate-700">
                                    {{ p.croy ?? 0 }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg bg-amber-50 py-1.5 ring-1 ring-amber-100"
                            >
                                <p
                                    class="text-[10px] uppercase tracking-wider text-amber-700 font-bold"
                                >
                                    %
                                </p>
                                <p class="font-bold text-amber-700">
                                    {{ p.percentage ?? 0 }}%
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-3 border-t border-slate-100 divide-x divide-slate-100"
                        >
                            <button
                                @click="openView(p)"
                                class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                            >
                                <i class="fa fa-eye text-[10px]"></i>
                                View
                            </button>
                            <button
                                @click="editProduct(p)"
                                class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-blue-700 hover:bg-blue-50 transition"
                            >
                                <i class="fa fa-edit text-[10px]"></i>
                                Edit
                            </button>
                            <button
                                @click="deleteProduct(p)"
                                class="inline-flex items-center justify-center gap-1.5 py-2.5 text-xs font-bold text-red-700 hover:bg-red-50 transition"
                            >
                                <i class="fa fa-trash text-[10px]"></i>
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Empty mobile -->
                    <div
                        v-if="!filteredProducts.length"
                        class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm py-14 px-6 text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                        >
                            <i
                                class="fa-solid fa-box-open text-3xl text-slate-400"
                            ></i>
                        </div>
                        <p class="text-lg font-bold text-slate-700 mb-1">
                            {{
                                search
                                    ? "No matching products"
                                    : "No products yet"
                            }}
                        </p>
                        <p class="text-sm text-slate-500">
                            {{
                                search
                                    ? "Try a different search term."
                                    : "Add your first product using the form."
                            }}
                        </p>
                    </div>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="props.products?.links?.length > 3"
                    class="flex flex-wrap items-center justify-center gap-1.5"
                >
                    <template
                        v-for="(link, i) in props.products.links"
                        :key="i"
                    >
                        <button
                            v-if="link.url"
                            @click="visitPage(link.url)"
                            class="min-w-[38px] h-10 flex items-center justify-center rounded-lg px-2.5 text-sm font-bold transition"
                            :class="
                                link.active
                                    ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow'
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
            </div>

            <!-- ============================================= -->
            <!-- ================= FORM ====================== -->
            <!-- ============================================= -->
            <div
                id="product-form"
                class="lg:col-span-1 order-1 lg:order-2 rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-y-auto h-fit lg:sticky lg:top-4"
            >
                <div
                    class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-white shadow"
                        :class="
                            editMode
                                ? 'bg-gradient-to-br from-blue-500 to-indigo-600'
                                : 'bg-gradient-to-br from-violet-500 to-purple-600'
                        "
                    >
                        <i
                            :class="
                                editMode
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
                            {{ editMode ? "Edit Product" : "Add Product" }}
                        </h2>
                        <p class="text-xs text-slate-500">
                            {{
                                editMode
                                    ? "Update existing product"
                                    : "Create a new product"
                            }}
                        </p>
                    </div>
                </div>

                <form
                    @submit.prevent="submit"
                    enctype="multipart/form-data"
                    class="p-5 space-y-4 max-h-[calc(100vh-220px)] lg:overflow-y-auto"
                >
                    <!-- IMAGE -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i class="fa-solid fa-image text-pink-600 mr-1"></i>
                            Product Image
                        </label>
                        <div class="flex items-start gap-3">
                            <div
                                class="shrink-0 h-24 w-24 rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-50 flex items-center justify-center"
                            >
                                <img
                                    v-if="preview"
                                    :src="preview"
                                    class="h-full w-full object-cover"
                                    alt="Preview"
                                />
                                <i
                                    v-else
                                    class="fa-regular fa-image text-3xl text-slate-300"
                                ></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input
                                    type="file"
                                    id="image"
                                    @change="handleImage"
                                    accept="image/*"
                                    class="w-full text-xs rounded-lg border border-slate-300 p-2 file:mr-2 file:rounded-md file:border-0 file:bg-violet-50 file:text-violet-700 file:font-bold file:text-xs file:px-2 file:py-1 hover:file:bg-violet-100 cursor-pointer"
                                />
                                <p
                                    v-if="form.errors.image"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.image }}
                                </p>
                                <p
                                    v-if="editMode"
                                    class="text-[11px] text-slate-500 mt-1"
                                >
                                    <i
                                        class="fa-solid fa-info-circle text-blue-500 mr-1"
                                    ></i>
                                    Leave empty to keep current image.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- NAME -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            placeholder="Product name..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- PRICE -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-solid fa-tag text-emerald-600 mr-1"
                            ></i>
                            Pricing
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-bold"
                                        >৳</span
                                    >
                                    <input
                                        v-model="form.price"
                                        placeholder="0"
                                        class="w-full rounded-lg border border-slate-300 pl-7 pr-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    />
                                </div>
                                <p
                                    class="text-[10px] text-slate-500 mt-1 font-semibold uppercase tracking-wider"
                                >
                                    Price
                                </p>
                                <p
                                    v-if="form.errors.price"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.price }}
                                </p>
                            </div>
                            <div>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-emerald-600 text-sm font-bold"
                                        >৳</span
                                    >
                                    <input
                                        v-model="form.discount_price"
                                        placeholder="0"
                                        class="w-full rounded-lg border border-slate-300 pl-7 pr-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    />
                                </div>
                                <p
                                    class="text-[10px] text-slate-500 mt-1 font-semibold uppercase tracking-wider"
                                >
                                    Discount
                                </p>
                                <p
                                    v-if="form.errors.discount_price"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.discount_price }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- INVENTORY -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i class="fa-solid fa-cubes text-blue-600 mr-1"></i>
                            Inventory
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <input
                                    v-model="form.quantity"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-center outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                />
                                <p
                                    class="text-[10px] text-slate-500 mt-1 text-center font-semibold uppercase tracking-wider"
                                >
                                    Qty
                                </p>
                            </div>
                            <div>
                                <input
                                    v-model="form.croy"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-center outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                />
                                <p
                                    class="text-[10px] text-slate-500 mt-1 text-center font-semibold uppercase tracking-wider"
                                >
                                    Croy
                                </p>
                            </div>
                            <div>
                                <input
                                    v-model="form.dipo"
                                    placeholder="0"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-center outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                />
                                <p
                                    class="text-[10px] text-slate-500 mt-1 text-center font-semibold uppercase tracking-wider"
                                >
                                    Dipo
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- CLASSIFICATION -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-solid fa-sitemap text-indigo-600 mr-1"
                            ></i>
                            Classification
                        </label>
                        <div class="grid grid-cols-1 gap-2">
                            <input
                                v-model="form.dillar"
                                placeholder="Dillar..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />
                            <input
                                v-model="form.sub_dillar"
                                placeholder="Sub Dillar..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            />
                        </div>
                    </div>

                    <!-- PERCENTAGE -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-solid fa-percent text-amber-600 mr-1"
                            ></i>
                            Percentage
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.percentage"
                                placeholder="0"
                                class="w-full rounded-lg border border-slate-300 pl-3 pr-8 py-2 text-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200"
                            />
                            <span
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-amber-600 text-sm font-bold"
                                >%</span
                            >
                        </div>
                        <p
                            v-if="form.errors.percentage"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.percentage }}
                        </p>
                    </div>

                    <!-- DESCRIPTION -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-solid fa-align-left text-slate-500 mr-1"
                            ></i>
                            Description
                        </label>
                        <RichTextEditor
                            v-model="form.description"
                            placeholder="Enter product description"
                            :error="form.errors.description"
                        />
                    </div>

                    <!-- SUBMIT -->
                    <div class="flex gap-2 pt-2 border-t border-slate-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                            :class="
                                editMode
                                    ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                    : 'from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700'
                            "
                        >
                            <i
                                v-if="form.processing"
                                class="fa-solid fa-spinner fa-spin"
                            ></i>
                            <i
                                v-else
                                :class="
                                    editMode
                                        ? 'fa-solid fa-check'
                                        : 'fa-solid fa-plus'
                                "
                            ></i>
                            {{
                                form.processing
                                    ? "Processing..."
                                    : editMode
                                      ? "Update Product"
                                      : "Save Product"
                            }}
                        </button>
                        <button
                            v-if="editMode"
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
                v-if="viewModalOpen && viewProduct"
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
                    <!-- Modal header -->
                    <div
                        class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-slate-800 px-5 py-4 rounded-t-2xl"
                    >
                        <div class="min-w-0">
                            <p
                                class="text-violet-300 text-[10px] font-semibold uppercase tracking-wider mb-0.5"
                            >
                                <i class="fa-solid fa-box mr-1"></i> Product #{{
                                    viewProduct.id
                                }}
                            </p>
                            <h3
                                class="text-lg sm:text-xl font-extrabold text-white truncate"
                            >
                                {{ viewProduct.name }}
                            </h3>
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
                    <div class="p-5 sm:p-6 space-y-5">
                        <!-- Image -->
                        <div
                            class="rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-50"
                        >
                            <img
                                v-if="viewProduct.image"
                                :src="`/storage/${viewProduct.image}`"
                                class="w-full max-h-72 object-contain"
                                :alt="viewProduct.name"
                            />
                            <div
                                v-else
                                class="h-48 flex flex-col items-center justify-center text-slate-400"
                            >
                                <i
                                    class="fa-regular fa-image text-5xl mb-2"
                                ></i>
                                <p class="text-xs">No image available</p>
                            </div>
                        </div>

                        <!-- Price block -->
                        <div
                            class="rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 ring-1 ring-emerald-200 p-4"
                        >
                            <div
                                class="flex flex-wrap items-baseline justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-emerald-700 font-bold mb-0.5"
                                    >
                                        Discount Price
                                    </p>
                                    <p
                                        class="text-3xl font-extrabold text-emerald-700"
                                    >
                                        {{
                                            formatMoney(
                                                viewProduct.discount_price,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div
                                    v-if="
                                        Number(viewProduct.price) >
                                        Number(viewProduct.discount_price)
                                    "
                                    class="text-right"
                                >
                                    <p
                                        class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-0.5"
                                    >
                                        Regular
                                    </p>
                                    <p
                                        class="text-lg font-bold text-slate-400 line-through"
                                    >
                                        {{ formatMoney(viewProduct.price) }}
                                    </p>
                                </div>
                                <div
                                    v-if="
                                        viewProduct.percentage !== null &&
                                        viewProduct.percentage !== ''
                                    "
                                    class="ml-auto"
                                >
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-500 text-white px-3 py-1 text-xs font-bold shadow"
                                    >
                                        <i class="fa-solid fa-percent"></i>
                                        {{ viewProduct.percentage }}% OFF
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <div
                                class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3 text-center"
                            >
                                <i
                                    class="fa-solid fa-cubes text-blue-600 mb-1"
                                ></i>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Quantity
                                </p>
                                <p
                                    class="text-lg font-extrabold text-slate-800"
                                >
                                    {{ viewProduct.quantity ?? 0 }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3 text-center"
                            >
                                <i
                                    class="fa-solid fa-arrows-spin text-indigo-600 mb-1"
                                ></i>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Croy
                                </p>
                                <p
                                    class="text-lg font-extrabold text-slate-800"
                                >
                                    {{ viewProduct.croy ?? 0 }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3 text-center"
                            >
                                <i
                                    class="fa-solid fa-boxes-packing text-amber-600 mb-1"
                                ></i>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Dipo
                                </p>
                                <p
                                    class="text-lg font-extrabold text-slate-800"
                                >
                                    {{ viewProduct.dipo ?? 0 }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3 text-center"
                            >
                                <i
                                    class="fa-solid fa-tag text-emerald-600 mb-1"
                                ></i>
                                <p
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold"
                                >
                                    Price
                                </p>
                                <p
                                    class="text-sm font-extrabold text-slate-800 truncate"
                                >
                                    {{ formatMoney(viewProduct.price) }}
                                </p>
                            </div>
                        </div>

                        <!-- Classification -->
                        <div
                            v-if="viewProduct.dillar || viewProduct.sub_dillar"
                            class="rounded-xl bg-indigo-50/60 ring-1 ring-indigo-200 p-4"
                        >
                            <p
                                class="text-[10px] uppercase tracking-wider text-indigo-700 font-bold mb-2"
                            >
                                <i class="fa-solid fa-sitemap mr-1"></i>
                                Classification
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-if="viewProduct.dillar"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-800 ring-1 ring-indigo-200"
                                >
                                    <i
                                        class="fa-solid fa-folder text-indigo-600 text-[10px]"
                                    ></i>
                                    {{ viewProduct.dillar }}
                                </span>
                                <span
                                    v-if="viewProduct.sub_dillar"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-800 ring-1 ring-indigo-200"
                                >
                                    <i
                                        class="fa-solid fa-folder-tree text-indigo-600 text-[10px]"
                                    ></i>
                                    {{ viewProduct.sub_dillar }}
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <p
                                class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-2"
                            >
                                <i class="fa-solid fa-align-left mr-1"></i>
                                Description
                            </p>
                            <div
                                v-if="viewProduct.description"
                                class="prose prose-sm max-w-none text-sm text-slate-700 leading-relaxed rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3"
                                v-html="viewProduct.description"
                            ></div>
                            <p v-else class="text-sm text-slate-400 italic">
                                No description provided
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="sticky bottom-0 border-t border-slate-100 bg-slate-50 px-5 py-3 flex flex-col sm:flex-row gap-2"
                    >
                        <button
                            @click="
                                closeView();
                                editProduct(viewProduct);
                            "
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-2.5 shadow transition"
                        >
                            <i class="fa-solid fa-pen text-xs"></i>
                            Edit this product
                        </button>
                        <button
                            @click="closeView"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-100 font-semibold py-2.5 transition"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Layout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Rich text inside modal (v-html) */
.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
