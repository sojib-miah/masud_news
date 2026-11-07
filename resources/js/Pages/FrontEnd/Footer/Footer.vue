<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Layout from "@/Shared/Layout.vue";
import { route } from "ziggy-js";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    footers: Object,
});

const editing = ref(false);
const currentId = ref(null);

const form = useForm({
    som: "",
    pro: "",
    bab: "",
    nir: "",
    ban: "",
    phone1: "",
    phone2: "",
    email: "",
    web1: "",
    web2: "",
    copy: "",
});

// Grouped display fields — cleaner "at-a-glance" list
const contentFields = [
    {
        key: "som",
        label: "সম্পাদক ও প্রকাশক",
        icon: "fa-solid fa-user-pen",
        color: "text-emerald-600",
        bg: "bg-emerald-50",
        ring: "ring-emerald-200",
    },
    {
        key: "bab",
        label: "ব্যবস্থাপনা সম্পাদক",
        icon: "fa-solid fa-user-tie",
        color: "text-blue-600",
        bg: "bg-blue-50",
        ring: "ring-blue-200",
    },
    {
        key: "nir",
        label: "নির্বাহী সম্পাদক",
        icon: "fa-solid fa-user-gear",
        color: "text-violet-600",
        bg: "bg-violet-50",
        ring: "ring-violet-200",
    },
    {
        key: "ban",
        label: "বাণিজ্যিক কার্যালয়",
        icon: "fa-solid fa-building",
        color: "text-amber-600",
        bg: "bg-amber-50",
        ring: "ring-amber-200",
    },
];

function submit() {
    if (editing.value) {
        form.put(route("footers.update", currentId.value), {
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route("footers.store"), {
            onSuccess: () => resetForm(),
        });
    }
}

function edit(footer) {
    editing.value = true;
    currentId.value = footer.id;
    Object.assign(form, footer);

    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document.getElementById("footer-form")?.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
}

function remove(footer) {
    if (confirm("Are you sure you want to delete this footer?")) {
        router.delete(route("footers.destroy", footer.id));
    }
}

function resetForm() {
    editing.value = false;
    currentId.value = null;
    form.reset();
}

function globalIndex(i) {
    const page = props.footers?.current_page ?? 1;
    const perPage = props.footers?.per_page ?? 10;
    return (page - 1) * perPage + i + 1;
}

// Human-friendly website display
function cleanWebsite(url) {
    if (!url) return "";
    return url.replace(/^https?:\/\//, "").replace(/\/$/, "");
}
</script>

<template>
    <Layout>
        <Breadcrumb />
        <Head title="Footer Management" />

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
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <p
                        class="text-amber-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-shoe-prints mr-1"></i> Site Footer
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Footer Management
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Manage editorial panel, contact info & copyright.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                >
                    <i class="fa-solid fa-database"></i>
                    Total: {{ props.footers?.total ?? 0 }}
                </div>
            </div>
        </div>

        <!-- ==================== MAIN GRID ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- ==================== LIST ==================== -->
            <div
                class="lg:col-span-2 order-2 lg:order-1 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit"
            >
                <div
                    class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow"
                    >
                        <i class="fa-solid fa-list text-sm"></i>
                    </span>
                    <div>
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800"
                        >
                            Footer Entries
                        </h2>
                        <p class="text-xs text-slate-500">
                            {{ props.footers?.total ?? 0 }} entr(y/ies) total
                        </p>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!props.footers?.data?.length"
                    class="p-10 text-center"
                >
                    <i
                        class="fa-solid fa-inbox text-4xl text-slate-300 mb-3"
                    ></i>
                    <p class="text-slate-500 font-semibold">No footers yet</p>
                    <p class="text-sm text-slate-400">
                        Create your first footer using the form.
                    </p>
                </div>

                <!-- Card list (all breakpoints — better than wide table for 10+ columns) -->
                <div
                    v-if="props.footers?.data?.length"
                    class="divide-y divide-slate-100"
                >
                    <div
                        v-for="(footer, i) in props.footers.data"
                        :key="footer.id"
                        class="p-4 sm:p-5 hover:bg-slate-50/70 transition-colors"
                    >
                        <!-- Top row: number + actions -->
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="inline-flex items-center justify-center h-7 min-w-[28px] px-2 rounded-full bg-slate-100 text-xs font-bold text-slate-600 ring-1 ring-slate-200"
                            >
                                #{{ globalIndex(i) }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button
                                    @click="edit(footer)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                    title="Edit"
                                >
                                    <i class="fa fa-edit text-xs"></i>
                                </button>
                                <button
                                    @click="remove(footer)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                    title="Delete"
                                >
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Editorial panel grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3">
                            <div
                                v-for="f in contentFields"
                                :key="f.key"
                                class="flex items-start gap-2 rounded-lg p-2 bg-white ring-1 ring-slate-100"
                            >
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-xs"
                                    :class="[f.color, f.bg, 'ring-1', f.ring]"
                                >
                                    <i :class="f.icon"></i>
                                </span>
                                <div class="min-w-0">
                                    <p
                                        class="text-[10px] pb-1 font-bold uppercase tracking-wider text-slate-500"
                                    >
                                        {{ f.label }}
                                    </p>
                                    <p
                                        class="text-sm leading-6 font-semibold text-slate-800 break-words"
                                    >
                                        {{ footer[f.key] || "—" }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact strip -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <!-- Phones -->
                            <div
                                class="flex items-start gap-2 rounded-lg bg-emerald-50/60 px-2.5 py-2 ring-1 ring-emerald-100"
                            >
                                <i
                                    class="fa-solid fa-phone text-emerald-600 text-xs mt-0.5"
                                ></i>
                                <div class="min-w-0">
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-wider text-emerald-700"
                                    >
                                        Phone
                                    </p>
                                    <p
                                        class="text-xs leading-6 font-semibold text-slate-700 break-words"
                                    >
                                        {{
                                            [footer.phone1, footer.phone2]
                                                .filter(Boolean)
                                                .join(", ") || "—"
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div
                                class="flex items-start gap-2 rounded-lg bg-sky-50/60 px-2.5 py-2 ring-1 ring-sky-100 min-w-0"
                            >
                                <i
                                    class="fa-solid fa-envelope text-sky-600 text-xs mt-0.5"
                                ></i>
                                <div class="min-w-0">
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-wider text-sky-700"
                                    >
                                        Email
                                    </p>
                                    <a
                                        v-if="footer.email"
                                        :href="`mailto:${footer.email}`"
                                        class="text-xs leading-6 font-semibold text-slate-700 hover:text-sky-700 break-all"
                                    >
                                        {{ footer.email }}
                                    </a>
                                    <p v-else class="text-xs text-slate-400">
                                        —
                                    </p>
                                </div>
                            </div>

                            <!-- Websites -->
                            <div
                                class="flex items-start gap-2 rounded-lg bg-violet-50/60 px-2.5 py-2 ring-1 ring-violet-100 min-w-0"
                            >
                                <i
                                    class="fa-solid fa-globe text-violet-600 text-xs mt-0.5"
                                ></i>
                                <div class="min-w-0">
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-wider text-violet-700"
                                    >
                                        Website
                                    </p>
                                    <div
                                        v-if="footer.web1 || footer.web2"
                                        class="space-y-0.5"
                                    >
                                        <a
                                            v-if="footer.web1"
                                            :href="
                                                footer.web1.startsWith('http')
                                                    ? footer.web1
                                                    : `https://${footer.web1}`
                                            "
                                            target="_blank"
                                            rel="noopener"
                                            class="block text-xs leading-6 font-semibold text-slate-700 hover:text-violet-700 break-all"
                                        >
                                            {{ cleanWebsite(footer.web1) }}
                                        </a>
                                        <a
                                            v-if="footer.web2"
                                            :href="
                                                footer.web2.startsWith('http')
                                                    ? footer.web2
                                                    : `https://${footer.web2}`
                                            "
                                            target="_blank"
                                            rel="noopener"
                                            class="block text-xs leading-6 font-semibold text-slate-700 hover:text-violet-700 break-all"
                                        >
                                            {{ cleanWebsite(footer.web2) }}
                                        </a>
                                    </div>
                                    <p v-else class="text-xs text-slate-400">
                                        —
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Copyright -->
                        <div
                            class="mt-3 flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 ring-1 ring-slate-200"
                        >
                            <i
                                class="fa-regular fa-copyright text-slate-500 text-xs"
                            ></i>
                            <p
                                class="text-xs font-medium text-slate-600 break-words"
                            >
                                {{ footer.copy || "No copyright set" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="props.footers?.links?.length > 3"
                    class="flex flex-wrap items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3"
                >
                    <template v-for="(link, i) in props.footers.links" :key="i">
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
                                    ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow'
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

            <!-- ==================== FORM ==================== -->
            <div
                id="footer-form"
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
                                : 'bg-gradient-to-br from-amber-500 to-orange-600'
                        "
                    >
                        <i
                            :class="
                                editing ? 'fa-solid fa-pen' : 'fa-solid fa-plus'
                            "
                            class="text-sm"
                        ></i>
                    </span>
                    <div class="min-w-0">
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800 truncate"
                        >
                            {{ editing ? "Edit Footer" : "Create Footer" }}
                        </h2>
                        <p class="text-xs text-slate-500">
                            {{
                                editing
                                    ? "Update existing footer"
                                    : "Add a new footer entry"
                            }}
                        </p>
                    </div>
                </div>

                <form
                    @submit.prevent="submit"
                    class="p-5 space-y-4 max-h-[calc(100vh-220px)] overflow-y-auto"
                >
                    <!-- Editorial panel group -->
                    <div>
                        <p
                            class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2 pb-1 border-b border-slate-100"
                        >
                            <i
                                class="fa-solid fa-users mr-1 text-emerald-600"
                            ></i>
                            Editorial Panel
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    সম্পাদক ও প্রকাশক
                                </label>
                                <input
                                    v-model="form.som"
                                    placeholder="সম্পাদক ও প্রকাশক..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                />
                                <p
                                    v-if="form.errors.som"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.som }}
                                </p>
                            </div>

                            <!-- Hidden 'pro' field (kept for controller compatibility) -->
                            <div class="hidden">
                                <label class="form-label">প্রকাশক</label>
                                <input
                                    v-model="form.pro"
                                    placeholder="প্রকাশক..."
                                    class="form-input"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    ব্যবস্থাপনা সম্পাদক
                                </label>
                                <input
                                    v-model="form.bab"
                                    placeholder="ব্যবস্থাপনা সম্পাদক..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                />
                                <p
                                    v-if="form.errors.bab"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.bab }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    নির্বাহী সম্পাদক
                                </label>
                                <input
                                    v-model="form.nir"
                                    placeholder="নির্বাহী সম্পাদক..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                />
                                <p
                                    v-if="form.errors.nir"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.nir }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    বাণিজ্যিক কার্যালয়
                                </label>
                                <input
                                    v-model="form.ban"
                                    placeholder="বাণিজ্যিক কার্যালয়..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                />
                                <p
                                    v-if="form.errors.ban"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.ban }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact group -->
                    <div>
                        <p
                            class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2 pb-1 border-b border-slate-100"
                        >
                            <i
                                class="fa-solid fa-address-book mr-1 text-sky-600"
                            ></i>
                            Contact
                        </p>

                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                    >
                                        Phone 1
                                    </label>
                                    <input
                                        v-model="form.phone1"
                                        placeholder="Phone one..."
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                    >
                                        Phone 2
                                    </label>
                                    <input
                                        v-model="form.phone2"
                                        placeholder="Phone two..."
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="Email..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    Website 1
                                </label>
                                <input
                                    v-model="form.web1"
                                    placeholder="https://..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    Website 2
                                </label>
                                <input
                                    v-model="form.web2"
                                    placeholder="https://..."
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Copyright -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-regular fa-copyright mr-1 text-slate-500"
                            ></i>
                            Copyright
                        </label>
                        <input
                            v-model="form.copy"
                            placeholder="© 2025 Your Company"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                    </div>

                    <!-- Submit -->
                    <div class="flex gap-2 pt-2 border-t border-slate-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r text-white font-bold py-2.5 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                            :class="
                                editing
                                    ? 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'
                                    : 'from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700'
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
                                    ? "Processing..."
                                    : editing
                                      ? "Update Footer"
                                      : "Create Footer"
                            }}
                        </button>

                        <button
                            v-if="editing"
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
</template>

<style scoped>
@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
