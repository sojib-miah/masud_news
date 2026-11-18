<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import { route } from "ziggy-js";
import axios from "axios";
import { ref, computed, watch, onMounted } from "vue";

/* ============================================================
 * TABLE REGISTRY — mirrors the backend registry
 * ============================================================ */
const TABLES = [
    {
        key: "divisions",
        label: "Divisions",
        icon: "fa-solid fa-map",
        manualId: true,
        fields: [
            { name: "id", label: "ID", type: "number", required: true },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: true,
            },
            { name: "url", label: "URL", type: "text", required: false },
        ],
    },
    {
        key: "districts",
        label: "Districts",
        icon: "fa-solid fa-map-location-dot",
        manualId: true,
        fields: [
            { name: "id", label: "ID", type: "number", required: true },
            {
                name: "division_id",
                label: "Division",
                type: "select",
                required: true,
                optionsFrom: "divisions",
            },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: true,
            },
            { name: "url", label: "URL", type: "text", required: false },
        ],
    },
    {
        key: "upazilas",
        label: "Upazilas",
        icon: "fa-solid fa-map-pin",
        manualId: true,
        fields: [
            { name: "id", label: "ID", type: "number", required: true },
            {
                name: "district_id",
                label: "District",
                type: "select",
                required: true,
                optionsFrom: "districts",
            },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: true,
            },
            { name: "url", label: "URL", type: "text", required: false },
        ],
    },
    {
        key: "unions",
        label: "Unions",
        icon: "fa-solid fa-people-group",
        manualId: true,
        fields: [
            { name: "id", label: "ID", type: "number", required: true },
            {
                name: "upazilla_id",
                label: "Upazila",
                type: "select",
                required: true,
                optionsFrom: "upazilas",
            },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: true,
            },
            { name: "url", label: "URL", type: "text", required: false },
        ],
    },
    {
        key: "city_corporations",
        label: "City Corporations",
        icon: "fa-solid fa-city",
        manualId: false,
        fields: [
            {
                name: "district_id",
                label: "District",
                type: "select",
                required: true,
                optionsFrom: "districts",
            },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: false,
            },
        ],
    },
    {
        key: "wards",
        label: "Wards",
        icon: "fa-solid fa-building",
        manualId: false,
        fields: [
            {
                name: "city_corporation_id",
                label: "City Corporation",
                type: "select",
                required: true,
                optionsFrom: "city_corporations",
            },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: false,
            },
        ],
    },
    {
        key: "pourashavas",
        label: "Pourashavas",
        icon: "fa-solid fa-house-chimney",
        manualId: false,
        fields: [
            {
                name: "district_id",
                label: "District",
                type: "select",
                required: true,
                optionsFrom: "districts",
            },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: false,
            },
            {
                name: "category",
                label: "Category",
                type: "select",
                required: false,
                options: ["A", "B", "C"],
            },
        ],
    },
    {
        key: "pourashava_wards",
        label: "Pourashava Wards",
        icon: "fa-solid fa-location-dot",
        manualId: false,
        fields: [
            {
                name: "pourashava_id",
                label: "Pourashava",
                type: "select",
                required: true,
                optionsFrom: "pourashavas",
            },
            { name: "name", label: "Name (EN)", type: "text", required: true },
            {
                name: "bn_name",
                label: "Name (BN)",
                type: "text",
                required: true,
            },
        ],
    },
    {
        key: "education",
        label: "Education",
        icon: "fa-solid fa-graduation-cap",
        manualId: false,
        fields: [{ name: "name", label: "Name", type: "text", required: true }],
    },
    {
        key: "education_years",
        label: "Education Years",
        icon: "fa-solid fa-calendar",
        manualId: false,
        fields: [{ name: "year", label: "Year", type: "text", required: true }],
    },
    {
        key: "reporter_types",
        label: "Reporter Types",
        icon: "fa-solid fa-id-badge",
        manualId: false,
        fields: [{ name: "name", label: "Name", type: "text", required: true }],
    },
    {
        key: "reporter_areas",
        label: "Reporter Areas",
        icon: "fa-solid fa-location-crosshairs",
        manualId: false,
        fields: [{ name: "name", label: "Name", type: "text", required: true }],
    },
];

/* ============================================================
 * STATE
 * ============================================================ */
const activeTab = ref(TABLES[0].key);
const activeMeta = computed(() =>
    TABLES.find((t) => t.key === activeTab.value),
);

// list state
const rows = ref([]);
const loading = ref(false);
const search = ref("");
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
let debounceTimer = null;

// options cache for selects
const optionsCache = ref({});

// modal state
const modal = ref({
    show: false,
    mode: "create", // 'create' | 'edit'
    row: {},
    errors: {},
    processing: false,
    serverMessage: "",
});

// delete confirm modal
const confirmDelete = ref({
    show: false,
    id: null,
    label: "",
});

/* ============================================================
 * FETCH LIST
 * ============================================================ */
async function fetchRows(page = 1) {
    loading.value = true;
    try {
        const { data } = await axios.get(
            route("admin.lookups.list", { table: activeTab.value }),
            { params: { search: search.value, page } },
        );
        rows.value = data.data;
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
        };
    } catch (e) {
        console.error("Load failed:", e);
    } finally {
        loading.value = false;
    }
}

async function fetchOptions(table) {
    if (optionsCache.value[table]) return optionsCache.value[table];
    const { data } = await axios.get(route("admin.lookups.options", { table }));
    optionsCache.value[table] = data;
    return data;
}

// Debounced search
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchRows(1), 350);
});

watch(activeTab, async () => {
    search.value = "";
    rows.value = [];
    await fetchRows(1);

    // Preload options for all select fields on this tab
    for (const f of activeMeta.value.fields) {
        if (f.type === "select" && f.optionsFrom) {
            fetchOptions(f.optionsFrom);
        }
    }
});

onMounted(async () => {
    await fetchRows(1);
    for (const f of activeMeta.value.fields) {
        if (f.type === "select" && f.optionsFrom) {
            fetchOptions(f.optionsFrom);
        }
    }
});

/* ============================================================
 * MODAL — open / close
 * ============================================================ */
function openCreate() {
    const blank = {};
    activeMeta.value.fields.forEach((f) => {
        blank[f.name] = f.type === "number" ? "" : "";
    });
    modal.value = {
        show: true,
        mode: "create",
        row: blank,
        errors: {},
        processing: false,
        serverMessage: "",
    };
}

function openEdit(row) {
    modal.value = {
        show: true,
        mode: "edit",
        row: { ...row },
        errors: {},
        processing: false,
        serverMessage: "",
    };
}

function closeModal() {
    if (modal.value.processing) return;
    modal.value.show = false;
}

/* ============================================================
 * SUBMIT (store / update)
 * ============================================================ */
async function submit() {
    modal.value.errors = {};
    modal.value.serverMessage = "";
    modal.value.processing = true;

    try {
        const isEdit = modal.value.mode === "edit";
        const url = isEdit
            ? route("admin.lookups.update", {
                  table: activeTab.value,
                  id: modal.value.row.id,
              })
            : route("admin.lookups.store", { table: activeTab.value });

        const method = isEdit ? "put" : "post";

        const { data } = await axios[method](url, modal.value.row);

        modal.value.serverMessage = data.message || "Saved!";
        await fetchRows(pagination.value.current_page);

        setTimeout(() => {
            modal.value.show = false;
        }, 500);
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors || {};
            Object.keys(errs).forEach((k) => {
                modal.value.errors[k] = errs[k][0];
            });
        } else {
            modal.value.serverMessage =
                e.response?.data?.message || "Something went wrong.";
        }
    } finally {
        modal.value.processing = false;
    }
}

/* ============================================================
 * DELETE
 * ============================================================ */
function askDelete(row) {
    confirmDelete.value = {
        show: true,
        id: row.id,
        label: row.name || row.bn_name || row.year || `#${row.id}`,
    };
}

async function doDelete() {
    try {
        await axios.delete(
            route("admin.lookups.destroy", {
                table: activeTab.value,
                id: confirmDelete.value.id,
            }),
        );
        confirmDelete.value.show = false;
        await fetchRows(pagination.value.current_page);
    } catch (e) {
        alert(e.response?.data?.message || "Delete failed.");
        confirmDelete.value.show = false;
    }
}

/* ============================================================
 * HELPERS
 * ============================================================ */
function cellValue(row, col) {
    return row[col] ?? "—";
}

function optionLabel(o) {
    return o.bn_name || o.name || o.year || `#${o.id}`;
}
</script>

<template>
    <Head title="Lookup CRUD" />
    <Layout>
        <div>
            <!-- HEADER -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                    <i class="fa-solid fa-database text-green-600 mr-2"></i>
                    Lookup Tables Management
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    সব লোকেশন ও রেফারেন্স টেবিল একই জায়গা থেকে ম্যানেজ করুন।
                </p>
            </div>

            <!-- TABS -->
            <div class="mb-4 -mx-2 overflow-x-auto">
                <div class="flex gap-2 px-2 pb-2">
                    <button
                        v-for="t in TABLES"
                        :key="t.key"
                        @click="activeTab = t.key"
                        class="flex-shrink-0 px-3.5 py-2 rounded-lg text-xs sm:text-sm font-semibold transition border whitespace-nowrap"
                        :class="
                            activeTab === t.key
                                ? 'bg-gradient-to-r from-green-600 to-green-700 text-white border-green-700 shadow'
                                : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50'
                        "
                    >
                        <i :class="t.icon" class="mr-1.5"></i>{{ t.label }}
                    </button>
                </div>
            </div>

            <!-- TOOLBAR -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 sm:p-4 mb-4 flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between"
            >
                <div class="relative flex-1">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                    ></i>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="`Search ${activeMeta.label}...`"
                        class="w-full border border-slate-300 rounded-lg pl-10 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    />
                </div>

                <button
                    @click="openCreate"
                    class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold text-sm px-4 py-2.5 rounded-lg shadow transition"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add {{ activeMeta.label }}
                </button>
            </div>

            <!-- TABLE -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    v-for="col in activeMeta.fields.map(
                                        (f) => f.name,
                                    )"
                                    :key="col"
                                    class="text-left px-4 py-3 font-semibold text-slate-600 capitalize"
                                >
                                    {{ col.replace("_", " ") }}
                                </th>
                                <th
                                    class="text-right px-4 py-3 font-semibold text-slate-600 w-32"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td
                                    :colspan="activeMeta.fields.length + 1"
                                    class="text-center py-10 text-slate-500"
                                >
                                    <i
                                        class="fa-solid fa-spinner fa-spin mr-2"
                                    ></i>
                                    Loading...
                                </td>
                            </tr>
                            <tr v-else-if="rows.length === 0">
                                <td
                                    :colspan="activeMeta.fields.length + 1"
                                    class="text-center py-10 text-slate-500"
                                >
                                    কোনো ডেটা পাওয়া যায়নি।
                                </td>
                            </tr>
                            <tr
                                v-else
                                v-for="row in rows"
                                :key="row.id"
                                class="border-b last:border-0 hover:bg-slate-50 transition"
                            >
                                <td
                                    v-for="col in activeMeta.fields.map(
                                        (f) => f.name,
                                    )"
                                    :key="col"
                                    class="px-4 py-3 text-slate-700"
                                >
                                    {{ cellValue(row, col) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right whitespace-nowrap"
                                >
                                    <button
                                        @click="openEdit(row)"
                                        class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-semibold mr-3"
                                    >
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <button
                                        @click="askDelete(row)"
                                        class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-xs font-semibold"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <div
                v-if="pagination.last_page > 1"
                class="flex justify-between items-center mt-4 text-sm text-slate-600"
            >
                <span>মোট {{ pagination.total }}</span>
                <div class="flex gap-2">
                    <button
                        :disabled="pagination.current_page <= 1"
                        @click="fetchRows(pagination.current_page - 1)"
                        class="px-3 py-1.5 border border-slate-300 rounded-lg disabled:opacity-50 hover:bg-slate-100"
                    >
                        Prev
                    </button>
                    <span class="px-3 py-1.5">
                        {{ pagination.current_page }} /
                        {{ pagination.last_page }}
                    </span>
                    <button
                        :disabled="
                            pagination.current_page >= pagination.last_page
                        "
                        @click="fetchRows(pagination.current_page + 1)"
                        class="px-3 py-1.5 border border-slate-300 rounded-lg disabled:opacity-50 hover:bg-slate-100"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== FORM MODAL ==================== -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="modal.show"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                        @click="closeModal"
                    ></div>

                    <div
                        class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col"
                    >
                        <div
                            class="h-1.5 w-full bg-gradient-to-r from-green-500 to-green-700 flex-shrink-0"
                        ></div>

                        <div class="p-6 overflow-y-auto">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-bold text-slate-800">
                                    <i
                                        :class="activeMeta.icon"
                                        class="text-green-600 mr-2"
                                    ></i>
                                    {{
                                        modal.mode === "create" ? "Add" : "Edit"
                                    }}
                                    {{ activeMeta.label }}
                                </h3>
                                <button
                                    @click="closeModal"
                                    class="text-slate-400 hover:text-slate-600"
                                >
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="f in activeMeta.fields"
                                    :key="f.name"
                                >
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        {{ f.label }}
                                        <span
                                            v-if="f.required"
                                            class="text-red-500"
                                            >*</span
                                        >
                                    </label>

                                    <!-- number / text -->
                                    <input
                                        v-if="
                                            f.type === 'text' ||
                                            f.type === 'number'
                                        "
                                        v-model="modal.row[f.name]"
                                        :type="f.type"
                                        :disabled="
                                            modal.mode === 'edit' &&
                                            f.name === 'id'
                                        "
                                        :placeholder="f.label"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-slate-100"
                                    />

                                    <!-- static select -->
                                    <select
                                        v-else-if="
                                            f.type === 'select' && f.options
                                        "
                                        v-model="modal.row[f.name]"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    >
                                        <option value="">— Select —</option>
                                        <option
                                            v-for="o in f.options"
                                            :key="o"
                                            :value="o"
                                        >
                                            {{ o }}
                                        </option>
                                    </select>

                                    <!-- dynamic select (optionsFrom) -->
                                    <select
                                        v-else-if="
                                            f.type === 'select' && f.optionsFrom
                                        "
                                        v-model="modal.row[f.name]"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    >
                                        <option value="">— Select —</option>
                                        <option
                                            v-for="o in optionsCache[
                                                f.optionsFrom
                                            ] || []"
                                            :key="o.id"
                                            :value="o.id"
                                        >
                                            {{ optionLabel(o) }}
                                        </option>
                                    </select>

                                    <p
                                        v-if="modal.errors[f.name]"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ modal.errors[f.name] }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-if="modal.serverMessage"
                                class="text-xs mt-3 text-slate-600"
                            >
                                {{ modal.serverMessage }}
                            </p>
                        </div>

                        <div
                            class="flex gap-3 px-6 pb-6 pt-2 border-t border-slate-100"
                        >
                            <button
                                @click="closeModal"
                                :disabled="modal.processing"
                                class="flex-1 px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                @click="submit"
                                :disabled="modal.processing"
                                class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <i
                                    v-if="modal.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                {{ modal.processing ? "Saving..." : "Save" }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ==================== DELETE CONFIRM ==================== -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
            >
                <div
                    v-if="confirmDelete.show"
                    class="fixed inset-0 z-[101] flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                        @click="confirmDelete.show = false"
                    ></div>

                    <div
                        class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
                    >
                        <div class="p-6 text-center">
                            <div
                                class="mx-auto w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-4"
                            >
                                <i
                                    class="fa-solid fa-triangle-exclamation text-2xl"
                                ></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800 mb-1">
                                Delete Confirmation
                            </h3>
                            <p class="text-sm text-slate-600">
                                আপনি কি
                                <strong>{{ confirmDelete.label }}</strong> ডিলিট
                                করতে চান?
                            </p>
                            <p class="text-xs text-red-500 mt-1">
                                এই কাজটি ফিরিয়ে আনা যাবে না।
                            </p>

                            <div class="flex gap-3 mt-6">
                                <button
                                    @click="confirmDelete.show = false"
                                    class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="doDelete"
                                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </Layout>
</template>
