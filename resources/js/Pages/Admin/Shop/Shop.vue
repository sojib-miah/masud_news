<script setup>
import { ref, computed } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";

const props = defineProps({
    shops: { type: Object, required: true },
});

const page = usePage();

// ==================== FORM STATE ====================
const form = ref({
    id: null,
    name: "",
    location: "",
});

const editMode = ref(false);
const processing = ref(false);
const errors = ref({});

// ==================== EDIT ====================
const editShop = (shop) => {
    form.value = {
        id: shop.id,
        name: shop.name,
        location: shop.location,
    };
    editMode.value = true;
    errors.value = {};
};

// ==================== RESET ====================
const resetForm = () => {
    form.value = { id: null, name: "", location: "" };
    editMode.value = false;
    errors.value = {};
};

// ==================== SUBMIT ====================
const submit = () => {
    errors.value = {};
    processing.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (editMode.value) {
        router.put(`/admin/shops/${form.value.id}`, form.value, options);
    } else {
        router.post("/admin/shops", form.value, options);
    }
};

// ==================== DELETE ====================
const deleteShop = (shop) => {
    if (!confirm(`"${shop.name}" — Are you sure you want to delete this shop?`))
        return;

    router.delete(`/admin/shops/${shop.id}`, {
        preserveScroll: true,
    });
};

// ==================== DISPLAY HELPERS ====================
const totalShops = computed(() => props.shops?.total ?? 0);

// Correct row number across pages
const rowNumber = (index) => {
    const from = props.shops?.from ?? 1;
    return from + index;
};
</script>

<template>
    <div>
        <Head title="Shop Management" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <Layout>
            <Breadcrumb />

            <div class="min-h-screen bg-slate-50">
                <div>
                    <!-- Page Header -->
                    <div
                        class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-lg px-5 sm:px-8 py-5 sm:py-6 mb-5 sm:mb-6"
                    >
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-white/20 backdrop-blur rounded-xl p-3"
                                >
                                    <i
                                        class="fa-solid fa-store text-white text-xl sm:text-2xl"
                                    ></i>
                                </div>
                                <div>
                                    <h1
                                        class="text-white text-xl sm:text-2xl font-bold"
                                    >
                                        Shop Management
                                    </h1>
                                    <p
                                        class="text-green-100 text-xs sm:text-sm mt-0.5"
                                    >
                                        দোকান যোগ, সম্পাদনা ও মুছে ফেলুন
                                    </p>
                                </div>
                            </div>

                            <div
                                class="bg-white/15 backdrop-blur rounded-xl px-4 py-2 text-center"
                            >
                                <p
                                    class="text-green-100 text-[10px] sm:text-xs uppercase tracking-wide"
                                >
                                    Total Shops
                                </p>
                                <p
                                    class="text-white text-lg sm:text-2xl font-bold leading-tight"
                                >
                                    {{ totalShops }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Grid: List + Form -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 sm:gap-6">
                        <!-- ==================== LEFT: LIST ==================== -->
                        <div
                            class="bg-white h-fit rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                        >
                            <div
                                class="px-4 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between"
                            >
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2"
                                >
                                    <i
                                        class="fa-solid fa-list text-green-600"
                                    ></i>
                                    Shop List
                                </h2>
                                <span
                                    class="text-xs font-semibold bg-green-50 text-green-700 px-3 py-1 rounded-full"
                                >
                                    {{ totalShops }} items
                                </span>
                            </div>

                            <!-- Empty state -->
                            <div
                                v-if="!shops.data || shops.data.length === 0"
                                class="p-10 sm:p-16 text-center"
                            >
                                <div
                                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-3"
                                >
                                    <i
                                        class="fa-solid fa-store-slash text-slate-400 text-2xl"
                                    ></i>
                                </div>
                                <p class="text-slate-600 font-semibold">
                                    কোনো দোকান নেই
                                </p>
                                <p class="text-sm text-slate-400 mt-1">
                                    ডান পাশের ফর্ম থেকে নতুন দোকান যোগ করুন
                                </p>
                            </div>

                            <!-- Table (responsive: cards on mobile) -->
                            <div v-else>
                                <!-- Desktop table -->
                                <div class="hidden md:block overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr
                                                class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wide"
                                            >
                                                <th
                                                    class="px-4 py-3 text-left font-semibold w-12"
                                                >
                                                    #
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                >
                                                    Name
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left font-semibold"
                                                >
                                                    Location
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-right font-semibold w-40"
                                                >
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-slate-100"
                                        >
                                            <tr
                                                v-for="(
                                                    shop, index
                                                ) in shops.data"
                                                :key="shop.id"
                                                class="hover:bg-green-50/40 transition-colors"
                                                :class="{
                                                    'bg-green-50':
                                                        editMode &&
                                                        form.id === shop.id,
                                                }"
                                            >
                                                <td
                                                    class="px-4 py-3 text-slate-500 font-medium"
                                                >
                                                    {{ rowNumber(index) }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 font-semibold text-slate-800"
                                                >
                                                    {{ shop.name }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-slate-600"
                                                >
                                                    <span
                                                        class="inline-flex items-center gap-1.5"
                                                    >
                                                        <i
                                                            class="fa-solid fa-location-dot text-green-500 text-xs"
                                                        ></i>
                                                        {{ shop.location }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div
                                                        class="flex items-center justify-end gap-2"
                                                    >
                                                        <button
                                                            @click="
                                                                editShop(shop)
                                                            "
                                                            class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                                                        >
                                                            <i
                                                                class="fa-solid fa-pen-to-square"
                                                            ></i>
                                                            Edit
                                                        </button>
                                                        <button
                                                            @click="
                                                                deleteShop(shop)
                                                            "
                                                            class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                                                        >
                                                            <i
                                                                class="fa-solid fa-trash"
                                                            ></i>
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile cards -->
                                <div
                                    class="md:hidden divide-y divide-slate-100"
                                >
                                    <div
                                        v-for="(shop, index) in shops.data"
                                        :key="shop.id"
                                        class="p-4"
                                        :class="{
                                            'bg-green-50':
                                                editMode && form.id === shop.id,
                                        }"
                                    >
                                        <div
                                            class="flex items-start justify-between gap-3 mb-2"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="bg-green-100 text-green-700 text-xs font-bold w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                                                >
                                                    {{ rowNumber(index) }}
                                                </span>
                                                <div>
                                                    <p
                                                        class="font-bold text-slate-800 text-sm"
                                                    >
                                                        {{ shop.name }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-slate-500 flex items-center gap-1 mt-0.5"
                                                    >
                                                        <i
                                                            class="fa-solid fa-location-dot text-green-500"
                                                        ></i>
                                                        {{ shop.location }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-2 mt-3"
                                        >
                                            <button
                                                @click="editShop(shop)"
                                                class="flex-1 inline-flex items-center justify-center gap-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-2 rounded-lg text-xs font-semibold transition"
                                            >
                                                <i
                                                    class="fa-solid fa-pen-to-square"
                                                ></i>
                                                Edit
                                            </button>
                                            <button
                                                @click="deleteShop(shop)"
                                                class="flex-1 inline-flex items-center justify-center gap-1.5 bg-red-50 text-red-600 hover:bg-red-100 px-3 py-2 rounded-lg text-xs font-semibold transition"
                                            >
                                                <i
                                                    class="fa-solid fa-trash"
                                                ></i>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pagination -->
                                <div
                                    v-if="shops.links && shops.links.length > 3"
                                    class="px-4 sm:px-6 py-4 border-t border-slate-200"
                                >
                                    <Pagination :links="shops.links" />
                                </div>
                            </div>
                        </div>

                        <!-- ==================== RIGHT: FORM ==================== -->
                        <div class="xl:sticky xl:top-5 self-start h-fit">
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                            >
                                <div
                                    class="px-4 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between"
                                    :class="
                                        editMode
                                            ? 'bg-gradient-to-r from-amber-50 to-orange-50'
                                            : 'bg-gradient-to-r from-green-50 to-emerald-50'
                                    "
                                >
                                    <h2
                                        class="text-base sm:text-lg font-bold flex items-center gap-2"
                                        :class="
                                            editMode
                                                ? 'text-amber-700'
                                                : 'text-green-700'
                                        "
                                    >
                                        <i
                                            :class="
                                                editMode
                                                    ? 'fa-solid fa-pen-to-square'
                                                    : 'fa-solid fa-circle-plus'
                                            "
                                        ></i>
                                        {{
                                            editMode
                                                ? "Edit Shop"
                                                : "Add New Shop"
                                        }}
                                    </h2>
                                    <span
                                        v-if="editMode"
                                        class="text-xs font-semibold bg-amber-100 text-amber-700 px-3 py-1 rounded-full"
                                    >
                                        #{{ form.id }}
                                    </span>
                                </div>

                                <form
                                    @submit.prevent="submit"
                                    class="p-4 sm:p-6 space-y-4"
                                >
                                    <!-- Name -->
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-slate-700 mb-1.5"
                                        >
                                            Shop Name
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i
                                                class="fa-solid fa-store absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                            ></i>
                                            <input
                                                v-model="form.name"
                                                type="text"
                                                placeholder="Enter shop name"
                                                class="w-full border border-slate-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition"
                                                :class="{
                                                    'border-red-400 focus:border-red-400 focus:ring-red-500/30':
                                                        errors.name,
                                                }"
                                                required
                                            />
                                        </div>
                                        <p
                                            v-if="errors.name"
                                            class="text-red-500 text-xs mt-1"
                                        >
                                            <i
                                                class="fa-solid fa-circle-exclamation mr-1"
                                            ></i>
                                            {{ errors.name }}
                                        </p>
                                    </div>

                                    <!-- Location -->
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-slate-700 mb-1.5"
                                        >
                                            Location
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i
                                                class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                            ></i>
                                            <input
                                                v-model="form.location"
                                                type="text"
                                                placeholder="Enter shop location"
                                                class="w-full border border-slate-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500 transition"
                                                :class="{
                                                    'border-red-400 focus:border-red-400 focus:ring-red-500/30':
                                                        errors.location,
                                                }"
                                                required
                                            />
                                        </div>
                                        <p
                                            v-if="errors.location"
                                            class="text-red-500 text-xs mt-1"
                                        >
                                            <i
                                                class="fa-solid fa-circle-exclamation mr-1"
                                            ></i>
                                            {{ errors.location }}
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div
                                        class="flex flex-col sm:flex-row gap-3 pt-3 border-t border-slate-100"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="processing"
                                            class="flex-1 inline-flex items-center justify-center gap-2 text-white font-bold py-2.5 rounded-lg shadow-md transition disabled:opacity-60 disabled:cursor-not-allowed"
                                            :class="
                                                editMode
                                                    ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600'
                                                    : 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800'
                                            "
                                        >
                                            <i
                                                v-if="processing"
                                                class="fa-solid fa-spinner fa-spin"
                                            ></i>
                                            <i
                                                v-else
                                                :class="
                                                    editMode
                                                        ? 'fa-solid fa-floppy-disk'
                                                        : 'fa-solid fa-plus'
                                                "
                                            ></i>
                                            {{
                                                processing
                                                    ? "Processing..."
                                                    : editMode
                                                      ? "Update Shop"
                                                      : "Save Shop"
                                            }}
                                        </button>

                                        <button
                                            type="button"
                                            @click="resetForm"
                                            class="sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 px-5 rounded-lg transition"
                                        >
                                            <i
                                                class="fa-solid fa-rotate-left"
                                            ></i>
                                            {{ editMode ? "Cancel" : "Reset" }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Hint card -->
                            <div
                                class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3"
                            >
                                <i
                                    class="fa-solid fa-circle-info text-blue-500 mt-0.5"
                                ></i>
                                <p
                                    class="text-xs text-blue-700 leading-relaxed"
                                >
                                    <strong>টিপস:</strong> কোনো দোকান সম্পাদনা
                                    করতে বাম পাশের তালিকা থেকে
                                    <em>Edit</em> বাটনে ক্লিক করুন। মুছে ফেললে
                                    সংশ্লিষ্ট অর্ডারগুলো থেকে শপ রিমুভ হয়ে
                                    যাবে।
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    </div>
</template>
