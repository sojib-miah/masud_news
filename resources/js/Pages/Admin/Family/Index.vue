<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";
import { ref, computed, watch } from "vue";

const props = defineProps({
    families: { type: Array, default: () => [] },
    editing: { type: Object, default: null },
});

// ==================== STATE ====================
const editingId = ref(props.editing?.id ?? null);
const search = ref("");

// ==================== FORM ====================
function blankForm() {
    return {
        name: "",
        email: "",
        designation: "",
        phone: "",
        location: "",
        image: null,
        short_desc: "",
        long_desc: "",
    };
}

function fromEditing(f) {
    return {
        name: f.name ?? "",
        email: f.email ?? "",
        designation: f.designation ?? "",
        phone: f.phone ?? "",
        location: f.location ?? "",
        image: null,
        short_desc: f.short_desc ?? "",
        long_desc: f.long_desc ?? "",
    };
}

const form = useForm(props.editing ? fromEditing(props.editing) : blankForm());

// Sync when server sends fresh editing prop (e.g. refresh with ?edit=ID)
watch(
    () => props.editing,
    (val) => {
        editingId.value = val?.id ?? null;
        form.defaults(val ? fromEditing(val) : blankForm());
        form.reset();
        form.clearErrors();
    },
);

// ==================== COMPUTED ====================
const filteredFamilies = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.families;
    return props.families.filter(
        (f) =>
            f.name?.toLowerCase().includes(q) ||
            f.email?.toLowerCase().includes(q) ||
            f.phone?.toLowerCase().includes(q) ||
            f.location?.toLowerCase().includes(q) ||
            f.designation?.toLowerCase().includes(q),
    );
});

// ==================== ACTIONS ====================
function submit() {
    if (editingId.value) {
        form.transform((data) => ({
            ...data,
            _method: "put",
        })).post(route("admin.families.update", editingId.value), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                editingId.value = null;
                form.defaults(blankForm());
                form.reset();
            },
        });
    } else {
        form.post(route("admin.families.store"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.defaults(blankForm());
                form.reset();
            },
        });
    }
}

function editFamily(f) {
    editingId.value = f.id;
    form.defaults(fromEditing(f));
    form.reset();
    form.clearErrors();

    if (window.innerWidth < 1024) {
        document
            .getElementById("family-form")
            ?.scrollIntoView({ behavior: "smooth" });
    }
}

function cancelEdit() {
    editingId.value = null;
    form.defaults(blankForm());
    form.reset();
    form.clearErrors();
}

function deleteFamily(f) {
    if (!confirm(`Delete "${f.name}"?`)) return;
    router.delete(route("admin.families.destroy", f.id), {
        preserveScroll: true,
    });
}

const currentEditingImage = computed(() => {
    if (!editingId.value) return null;
    const item = props.families.find((f) => f.id === editingId.value);
    return item?.image_url ?? null;
});
</script>

<template>
    <Layout>
        <Head title="Family Management" />
        <Breadcrumb />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="bg-slate-50">
            <div class="mx-auto">
                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl px-6 py-4 mb-5 text-white shadow-md"
                >
                    <h1 class="text-xl sm:text-2xl font-bold">
                        <i class="fa-solid fa-users mr-2"></i>Family Management
                    </h1>
                    <p class="text-green-100 text-xs sm:text-sm mt-1">
                        Create, edit and manage family records
                    </p>
                </div>

                <!-- Two-column: list (left) + form (right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    <!-- ==================== LEFT: LIST ==================== -->
                    <div
                        class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden h-fit"
                    >
                        <div
                            class="px-4 py-3 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between"
                        >
                            <h2
                                class="text-sm font-bold text-slate-700 uppercase"
                            >
                                Family List
                                <span
                                    class="text-xs text-slate-400 normal-case"
                                >
                                    ({{ filteredFamilies.length }})
                                </span>
                            </h2>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search..."
                                class="border border-slate-300 rounded-md px-3 py-1.5 text-sm w-full sm:w-56 focus:outline-none focus:ring-1 focus:ring-green-500"
                            />
                        </div>

                        <div
                            v-if="filteredFamilies.length === 0"
                            class="p-10 text-center text-slate-500"
                        >
                            <i
                                class="fa-regular fa-folder-open text-4xl text-slate-300 mb-2"
                            ></i>
                            <p class="text-sm">No family records yet</p>
                        </div>

                        <ul
                            class="divide-y divide-slate-100 max-h-[640px] overflow-y-auto"
                        >
                            <li
                                v-for="f in filteredFamilies"
                                :key="f.id"
                                class="px-4 py-3 hover:bg-green-50/60 transition flex gap-3"
                            >
                                <!-- Avatar / image -->
                                <div class="flex-shrink-0">
                                    <img
                                        v-if="f.image_url"
                                        :src="f.image_url"
                                        alt=""
                                        class="w-14 h-14 rounded-lg object-cover border border-slate-200"
                                    />
                                    <div
                                        v-else
                                        class="w-14 h-14 rounded-lg bg-green-100 text-green-700 flex items-center justify-center text-lg font-bold border border-green-200"
                                    >
                                        {{ (f.name || "?").charAt(0) }}
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div
                                        class="flex items-center gap-2 flex-wrap"
                                    >
                                        <p
                                            class="font-semibold text-slate-800 truncate"
                                        >
                                            {{ f.name }}
                                        </p>
                                        <span
                                            v-if="f.designation"
                                            class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full"
                                        >
                                            {{ f.designation }}
                                        </span>
                                    </div>

                                    <p
                                        class="text-xs text-slate-500 mt-1 truncate"
                                    >
                                        <span v-if="f.email">
                                            <i
                                                class="fa-solid fa-envelope mr-1"
                                            ></i
                                            >{{ f.email }}
                                        </span>
                                        <span v-if="f.phone" class="ml-3">
                                            <i
                                                class="fa-solid fa-phone mr-1"
                                            ></i
                                            >{{ f.phone }}
                                        </span>
                                    </p>

                                    <p
                                        v-if="f.location"
                                        class="text-xs text-slate-500 mt-0.5 truncate"
                                    >
                                        <i
                                            class="fa-solid fa-location-dot mr-1"
                                        ></i
                                        >{{ f.location }}
                                    </p>

                                    <p
                                        v-if="f.short_desc"
                                        class="text-xs text-slate-400 mt-1 line-clamp-1"
                                    >
                                        {{ f.short_desc }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="flex-shrink-0 flex items-start gap-1"
                                >
                                    <button
                                        type="button"
                                        class="text-blue-600 hover:bg-blue-50 p-2 rounded"
                                        title="Edit"
                                        @click="editFamily(f)"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="text-red-600 hover:bg-red-50 p-2 rounded"
                                        title="Delete"
                                        @click="deleteFamily(f)"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- ==================== RIGHT: FORM ==================== -->
                    <div
                        id="family-form"
                        class="lg:col-span-5 bg-white border border-green-600 rounded-2xl shadow-sm overflow-hidden self-start h-fit"
                    >
                        <div
                            class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3 text-white"
                        >
                            <h2 class="font-bold text-sm">
                                <i
                                    :class="
                                        editingId
                                            ? 'fa-solid fa-pen-to-square mr-2'
                                            : 'fa-solid fa-plus mr-2'
                                    "
                                ></i>
                                {{ editingId ? "Edit Family" : "Add Family" }}
                            </h2>
                        </div>

                        <form class="p-4 space-y-3" @submit.prevent="submit">
                            <!-- Name -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 mb-1"
                                >
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <TextInput
                                    v-model="form.name"
                                    placeholder="Full name"
                                    :error="form.errors.name"
                                />
                            </div>

                            <!-- Email + Phone -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-700 mb-1"
                                    >
                                        Email
                                    </label>
                                    <TextInput
                                        v-model="form.email"
                                        type="email"
                                        placeholder="example@mail.com"
                                        :error="form.errors.email"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-700 mb-1"
                                    >
                                        Phone
                                    </label>
                                    <TextInput
                                        v-model="form.phone"
                                        placeholder="01XXXXXXXXX"
                                        :error="form.errors.phone"
                                    />
                                </div>
                            </div>

                            <!-- Designation + Location -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-700 mb-1"
                                    >
                                        Designation
                                    </label>
                                    <TextInput
                                        v-model="form.designation"
                                        placeholder="e.g. Engineer"
                                        :error="form.errors.designation"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-700 mb-1"
                                    >
                                        Location
                                    </label>
                                    <TextInput
                                        v-model="form.location"
                                        placeholder="City / Address"
                                        :error="form.errors.location"
                                    />
                                </div>
                            </div>

                            <!-- Image -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 mb-1"
                                >
                                    Image
                                </label>
                                <FileInput
                                    v-model="form.image"
                                    accept="image/*"
                                    :multiple="false"
                                    :error="form.errors.image"
                                />
                                <img
                                    v-if="currentEditingImage"
                                    :src="currentEditingImage"
                                    class="w-20 h-20 mt-2 rounded border border-slate-300 object-cover"
                                    alt="Current image"
                                />
                            </div>

                            <!-- Short Desc -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 mb-1"
                                >
                                    Short Description
                                </label>
                                <textarea
                                    v-model="form.short_desc"
                                    rows="2"
                                    placeholder="Short description..."
                                    class="w-full border border-gray-400 shadow-sm rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                                ></textarea>
                                <span
                                    v-if="form.errors.short_desc"
                                    class="text-red-500 text-xs"
                                >
                                    {{ form.errors.short_desc }}
                                </span>
                            </div>

                            <!-- Long Desc -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-700 mb-1"
                                >
                                    Long Description
                                </label>
                                <textarea
                                    v-model="form.long_desc"
                                    rows="5"
                                    placeholder="Full description..."
                                    class="w-full border border-gray-400 shadow-sm rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
                                ></textarea>
                                <span
                                    v-if="form.errors.long_desc"
                                    class="text-red-500 text-xs"
                                >
                                    {{ form.errors.long_desc }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 pt-2">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-2.5 rounded-lg shadow transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm"
                                >
                                    <i
                                        v-if="form.processing"
                                        class="fa-solid fa-spinner fa-spin"
                                    ></i>
                                    {{
                                        form.processing
                                            ? "Saving..."
                                            : editingId
                                              ? "Update"
                                              : "Create"
                                    }}
                                </button>
                                <button
                                    v-if="editingId"
                                    type="button"
                                    @click="cancelEdit"
                                    class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm hover:bg-slate-50"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
</style>
