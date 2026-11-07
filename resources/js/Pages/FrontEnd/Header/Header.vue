<script setup>
import { useForm, Head, router, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Layout from "@/Shared/Layout.vue";
import { route } from "ziggy-js";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    headers: Object,
});

const editing = ref(false);
const currentId = ref(null);

const form = useForm({
    youtube: "",
    linkdin: "",
    instagram: "",
    facebook: "",
    twitter: "",
    ad: null,
});

const currentHeader = computed(() =>
    props.headers?.data?.find((h) => h.id === currentId.value),
);

// Social platforms config
const platforms = [
    {
        key: "youtube",
        label: "YouTube",
        icon: "fa-brands fa-youtube",
        color: "text-red-600",
        bg: "bg-red-50",
        ring: "ring-red-200",
    },
    {
        key: "facebook",
        label: "Facebook",
        icon: "fa-brands fa-facebook-f",
        color: "text-blue-600",
        bg: "bg-blue-50",
        ring: "ring-blue-200",
    },
    {
        key: "instagram",
        label: "Instagram",
        icon: "fa-brands fa-instagram",
        color: "text-pink-600",
        bg: "bg-pink-50",
        ring: "ring-pink-200",
    },
    {
        key: "linkdin",
        label: "LinkedIn",
        icon: "fa-brands fa-linkedin-in",
        color: "text-sky-700",
        bg: "bg-sky-50",
        ring: "ring-sky-200",
    },
    {
        key: "twitter",
        label: "Twitter",
        icon: "fa-brands fa-x-twitter",
        color: "text-slate-800",
        bg: "bg-slate-100",
        ring: "ring-slate-200",
    },
];

function submit() {
    if (editing.value) {
        form.transform((data) => ({
            ...data,
            _method: "put",
        })).post(route("headers.update", { header: currentId.value }), {
            forceFormData: true,
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route("headers.store"), {
            forceFormData: true,
            onSuccess: () => resetForm(),
        });
    }
}

function edit(header) {
    editing.value = true;
    currentId.value = header.id;
    form.youtube = header.youtube;
    form.linkdin = header.linkdin;
    form.instagram = header.instagram;
    form.facebook = header.facebook;
    form.twitter = header.twitter;
    form.ad = null;

    // Scroll form into view on mobile
    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        document.getElementById("header-form")?.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
}

function remove(header) {
    if (confirm("Are you sure you want to delete this header?")) {
        router.delete(route("headers.destroy", header.id));
    }
}

function resetForm() {
    editing.value = false;
    currentId.value = null;
    form.reset();
}
</script>

<template>
    <Layout>
        <Breadcrumb />
        <Head title="Header Management" />

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
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <p
                        class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-sliders mr-1"></i> Settings
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Header Management
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Manage social media links and advertise banner.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                >
                    <i class="fa-solid fa-database"></i>
                    Total: {{ props.headers?.total ?? 0 }}
                </div>
            </div>
        </div>

        <!-- ==================== MAIN LAYOUT ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- ==================== TABLE (LIST) ==================== -->
            <div
                class="lg:col-span-2 order-2 lg:order-1 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit"
            >
                <div
                    class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow"
                    >
                        <i class="fa-solid fa-list text-sm"></i>
                    </span>
                    <div>
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800"
                        >
                            Header List
                        </h2>
                        <p class="text-xs text-slate-500">
                            All configured social links & ad
                        </p>
                    </div>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!props.headers?.data?.length"
                    class="p-10 text-center"
                >
                    <i
                        class="fa-solid fa-inbox text-4xl text-slate-300 mb-3"
                    ></i>
                    <p class="text-slate-500 font-semibold">No headers yet</p>
                    <p class="text-sm text-slate-400">
                        Create your first one using the form.
                    </p>
                </div>

                <!-- Desktop table (sm+) -->
                <div v-else class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    #
                                </th>
                                <th
                                    v-for="p in platforms"
                                    :key="p.key"
                                    class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    {{ p.label }}
                                </th>
                                <th
                                    class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Ad
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(header, i) in props.headers.data"
                                :key="header.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td
                                    class="px-4 py-3 text-sm font-semibold text-slate-600"
                                >
                                    {{
                                        (props.headers.current_page - 1) *
                                            props.headers.per_page +
                                        i +
                                        1
                                    }}
                                </td>
                                <td
                                    v-for="p in platforms"
                                    :key="p.key"
                                    class="px-3 py-3"
                                >
                                    <a
                                        v-if="header[p.key]"
                                        :href="header[p.key]"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 transition hover:scale-105"
                                        :class="[p.color, p.bg, p.ring]"
                                        :title="header[p.key]"
                                    >
                                        <i :class="p.icon" class="text-sm"></i>
                                        <span>Open</span>
                                    </a>
                                    <span v-else class="text-xs text-slate-400"
                                        >—</span
                                    >
                                </td>
                                <td class="px-3 py-3">
                                    <img
                                        v-if="header.ad"
                                        :src="`/storage/${header.ad}`"
                                        alt="Ad"
                                        class="h-12 w-auto rounded-lg border border-slate-200 shadow-sm"
                                    />
                                    <span v-else class="text-xs text-slate-400"
                                        >—</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <button
                                            @click="edit(header)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-200 hover:bg-blue-100 hover:scale-110 transition"
                                            title="Edit"
                                        >
                                            <i class="fa fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            @click="remove(header)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                                            title="Delete"
                                        >
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile card list (< sm) -->
                <div
                    v-if="props.headers?.data?.length"
                    class="sm:hidden divide-y divide-slate-100"
                >
                    <div
                        v-for="(header, i) in props.headers.data"
                        :key="header.id"
                        class="p-4"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-500">
                                #{{
                                    (props.headers.current_page - 1) *
                                        props.headers.per_page +
                                    i +
                                    1
                                }}
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button
                                    @click="edit(header)"
                                    class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-50 text-blue-600 ring-1 ring-blue-200"
                                >
                                    <i class="fa fa-edit text-[10px]"></i>
                                </button>
                                <button
                                    @click="remove(header)"
                                    class="flex h-7 w-7 items-center justify-center rounded-md bg-red-50 text-red-600 ring-1 ring-red-200"
                                >
                                    <i class="fa fa-trash text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mb-2">
                            <a
                                v-for="p in platforms"
                                :key="p.key"
                                v-show="header[p.key]"
                                :href="header[p.key]"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1"
                                :class="[p.color, p.bg, p.ring]"
                            >
                                <i :class="p.icon"></i>
                                {{ p.label }}
                            </a>
                        </div>

                        <img
                            v-if="header.ad"
                            :src="`/storage/${header.ad}`"
                            alt="Ad"
                            class="h-14 w-auto rounded-lg border border-slate-200"
                        />
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="props.headers?.links?.length > 3"
                    class="flex flex-wrap items-center justify-center gap-1.5 border-t border-slate-100 px-4 py-3"
                >
                    <template v-for="(link, i) in props.headers.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
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

            <!-- ==================== FORM ==================== -->
            <div
                id="header-form"
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
                                editing ? 'fa-solid fa-pen' : 'fa-solid fa-plus'
                            "
                            class="text-sm"
                        ></i>
                    </span>
                    <div class="min-w-0">
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800 truncate"
                        >
                            {{ editing ? "Edit Header" : "Create Header" }}
                        </h2>
                        <p class="text-xs text-slate-500">
                            {{
                                editing
                                    ? "Update existing links"
                                    : "Add new social links"
                            }}
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-5 space-y-4">
                    <!-- Social inputs -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-brands fa-youtube text-red-500 mr-1"
                            ></i>
                            YouTube URL
                        </label>
                        <input
                            v-model="form.youtube"
                            placeholder="https://youtube.com/@channel"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                        <p
                            v-if="form.errors.youtube"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.youtube }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-brands fa-linkedin-in text-sky-700 mr-1"
                            ></i>
                            LinkedIn URL
                        </label>
                        <input
                            v-model="form.linkdin"
                            placeholder="https://linkedin.com/in/username"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                        <p
                            v-if="form.errors.linkdin"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.linkdin }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-brands fa-instagram text-pink-600 mr-1"
                            ></i>
                            Instagram URL
                        </label>
                        <input
                            v-model="form.instagram"
                            placeholder="https://instagram.com/username"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                        <p
                            v-if="form.errors.instagram"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.instagram }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-brands fa-facebook-f text-blue-600 mr-1"
                            ></i>
                            Facebook URL
                        </label>
                        <input
                            v-model="form.facebook"
                            placeholder="https://facebook.com/page"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                        <p
                            v-if="form.errors.facebook"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.facebook }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-brands fa-x-twitter text-slate-800 mr-1"
                            ></i>
                            Twitter / X URL
                        </label>
                        <input
                            v-model="form.twitter"
                            placeholder="https://twitter.com/username"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                        />
                        <p
                            v-if="form.errors.twitter"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.twitter }}
                        </p>
                    </div>

                    <!-- Current ad preview -->
                    <div
                        v-if="editing && currentHeader?.ad"
                        class="rounded-lg bg-slate-50 p-3 ring-1 ring-slate-200"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2"
                        >
                            Current Image
                        </p>
                        <img
                            :src="`/storage/${currentHeader.ad}`"
                            alt="Current Ad"
                            class="h-16 w-auto rounded-lg border border-slate-200 shadow-sm"
                        />
                    </div>

                    <!-- File input -->
                    <div>
                        <label
                            class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                        >
                            <i
                                class="fa-solid fa-image text-emerald-600 mr-1"
                            ></i>
                            Advertisement Banner
                        </label>
                        <FileInput
                            v-model="form.ad"
                            label="Upload New Image"
                            accept="image/*"
                            :multiple="false"
                            :error="form.errors.ad"
                        />
                    </div>

                    <!-- Submit -->
                    <div class="flex gap-2 pt-2">
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
                                    ? "Processing..."
                                    : editing
                                      ? "Update Header"
                                      : "Create Header"
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
