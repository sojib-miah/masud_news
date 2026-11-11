<script setup>
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref } from "vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import FileInput from "@/Shared/FileInput.vue";
import TextInput from "@/Shared/TextInput.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import SelectInput from "@/Shared/SelectInput.vue";

const page = usePage();

// Added `currency` to form (it was missing from your original)
const form = useForm({
    logo: null,
    favaicon: null,
    tax: page.props.site?.tax || "",
    inv_prefix: page.props.site?.inv_prefix || "",
    inv_termes: page.props.site?.inv_termes || "",
    company_location: page.props.site?.company_location || "",
    company_contact: page.props.site?.company_contact || "",
    company_email: page.props.site?.company_email || "",
    company_web: page.props.site?.company_web || "",
    company_facebook: page.props.site?.company_facebook || "",
    company_youtube: page.props.site?.company_youtube || "",
    company_linkdin: page.props.site?.company_linkdin || "",
    site_termes: page.props.site?.site_termes || "",
    site_about: page.props.site?.site_about || "",
    currency: page.props.site?.currency || "BDT",
    news_view: page.props.site?.news_view || "0",
    create_news: page.props.site?.create_news || "0",
    register_status: page.props.site?.register_status || "0",
});

// Track active tab
const activeTab = ref("general");

function submit() {
    form.post(route("admin.sitesetting.update"), {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ["site"] });
        },
    });
}

const currencyOptions = [
    { value: "BDT", label: "BDT (৳)" },
    { value: "USD", label: "USD ($)" },
];

const tabs = [
    { id: "general", label: "General & Branding", icon: "fa-solid fa-sliders" },
    { id: "company", label: "Company Details", icon: "fa-solid fa-building" },
    { id: "config", label: "Configuration", icon: "fa-solid fa-gears" },
    { id: "legal", label: "Legal & Pages", icon: "fa-solid fa-file-contract" },
];
</script>

<template>
    <div>
        <Head title="Site Settings" />

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
                    class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
                ></div>
                <div
                    class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"
                ></div>
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                >
                    <div>
                        <p
                            class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-globe mr-1"></i> Global
                            Settings
                        </p>
                        <h1
                            class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                        >
                            Site Settings
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            Manage your website's branding, company information,
                            and configurations.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ==================== MAIN FORM ==================== -->
            <form
                @submit.prevent="submit"
                enctype="multipart/form-data"
                class="grid grid-cols-1 lg:grid-cols-4 gap-6"
            >
                <!-- ==================== SIDEBAR TABS ==================== -->
                <div class="lg:col-span-1">
                    <div
                        class="sticky top-4 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden"
                    >
                        <div class="p-3 space-y-1">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                type="button"
                                @click="activeTab = tab.id"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all text-left"
                                :class="
                                    activeTab === tab.id
                                        ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md'
                                        : 'text-slate-600 hover:bg-slate-50'
                                "
                            >
                                <i
                                    :class="tab.icon"
                                    class="w-5 text-center"
                                ></i>
                                {{ tab.label }}
                            </button>
                        </div>

                        <!-- Submit Button (Sticky at bottom of sidebar) -->
                        <div class="p-4 border-t border-slate-100 bg-slate-50">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full flex items-center justify-center gap-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <i
                                    v-if="form.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i v-else class="fa-solid fa-floppy-disk"></i>
                                {{
                                    form.processing
                                        ? "Saving..."
                                        : "Save All Changes"
                                }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ==================== FORM CONTENT ==================== -->
                <div
                    class="lg:col-span-3 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 p-5 sm:p-8"
                >
                    <!-- 1. GENERAL & BRANDING -->
                    <div
                        v-show="activeTab === 'general'"
                        class="space-y-6 animate-fade-in"
                    >
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-lg font-bold text-slate-800">
                                <i
                                    class="fa-solid fa-image text-emerald-500 mr-2"
                                ></i>
                                Branding
                            </h2>
                            <p class="text-xs text-slate-500">
                                Upload your site logo and favicon.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Logo -->
                            <div
                                class="p-4 rounded-xl border border-slate-200 bg-slate-50"
                            >
                                <FileInput
                                    v-model="form.logo"
                                    label="Site Logo"
                                    accept="image/*"
                                    :multiple="false"
                                    :error="form.errors.logo"
                                />
                                <div v-if="$page.props.site?.logo" class="mt-4">
                                    <p
                                        class="text-xs font-bold text-slate-500 mb-2 uppercase"
                                    >
                                        Current Logo
                                    </p>
                                    <img
                                        :src="`/storage/${$page.props.site?.logo}`"
                                        class="h-16 w-auto bg-white p-1 rounded border border-slate-200 shadow-sm"
                                        alt="Logo"
                                    />
                                </div>
                            </div>

                            <!-- Favicon -->
                            <div
                                class="p-4 rounded-xl border border-slate-200 bg-slate-50"
                            >
                                <FileInput
                                    v-model="form.favaicon"
                                    label="Site Favicon"
                                    accept="image/*"
                                    :multiple="false"
                                    :error="form.errors.favaicon"
                                />
                                <div
                                    v-if="$page.props.site?.favaicon"
                                    class="mt-4"
                                >
                                    <p
                                        class="text-xs font-bold text-slate-500 mb-2 uppercase"
                                    >
                                        Current Favicon
                                    </p>
                                    <img
                                        :src="`/storage/${$page.props.site?.favaicon}`"
                                        class="h-10 w-10 bg-white p-1 rounded border border-slate-200 shadow-sm object-contain"
                                        alt="Favicon"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. COMPANY DETAILS -->
                    <div
                        v-show="activeTab === 'company'"
                        class="space-y-6 animate-fade-in"
                    >
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-lg font-bold text-slate-800">
                                <i
                                    class="fa-solid fa-building text-indigo-500 mr-2"
                                ></i>
                                Company Information
                            </h2>
                            <p class="text-xs text-slate-500">
                                Details used on invoices and the contact page.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <TextInput
                                id="company_location"
                                label="Company Location / Address"
                                v-model="form.company_location"
                                :error="form.errors.company_location"
                                placeholder="e.g. Dhaka, Bangladesh"
                                @input="form.errors.company_location = null"
                            />
                            <TextInput
                                id="company_contact"
                                label="Contact Number"
                                v-model="form.company_contact"
                                :error="form.errors.company_contact"
                                placeholder="+880 1..."
                                @input="form.errors.company_contact = null"
                            />
                            <TextInput
                                id="company_email"
                                label="Contact Email"
                                v-model="form.company_email"
                                :error="form.errors.company_email"
                                placeholder="info@site.com"
                                @input="form.errors.company_email = null"
                            />
                            <TextInput
                                id="company_web"
                                label="Website URL"
                                v-model="form.company_web"
                                :error="form.errors.company_web"
                                placeholder="https://example.com"
                                @input="form.errors.company_web = null"
                            />

                            <div
                                class="sm:col-span-2 border-t border-slate-100 pt-3 mt-1"
                            >
                                <p
                                    class="text-xs font-bold text-slate-500 uppercase mb-3"
                                >
                                    Social Media Links
                                </p>
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-3 gap-4"
                                >
                                    <TextInput
                                        id="company_facebook"
                                        label="Facebook"
                                        v-model="form.company_facebook"
                                        :error="form.errors.company_facebook"
                                        placeholder="Facebook URL"
                                        @input="
                                            form.errors.company_facebook = null
                                        "
                                    />
                                    <TextInput
                                        id="company_youtube"
                                        label="YouTube"
                                        v-model="form.company_youtube"
                                        :error="form.errors.company_youtube"
                                        placeholder="YouTube URL"
                                        @input="
                                            form.errors.company_youtube = null
                                        "
                                    />
                                    <TextInput
                                        id="company_linkdin"
                                        label="LinkedIn"
                                        v-model="form.company_linkdin"
                                        :error="form.errors.company_linkdin"
                                        placeholder="LinkedIn URL"
                                        @input="
                                            form.errors.company_linkdin = null
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. CONFIGURATION -->
                    <div
                        v-show="activeTab === 'config'"
                        class="space-y-6 animate-fade-in"
                    >
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-lg font-bold text-slate-800">
                                <i
                                    class="fa-solid fa-gears text-amber-500 mr-2"
                                ></i>
                                System Configuration
                            </h2>
                            <p class="text-xs text-slate-500">
                                Pricing, currency, and registration toggles.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <TextInput
                                id="tax"
                                label="Tax (%)"
                                type="number"
                                v-model="form.tax"
                                :error="form.errors.tax"
                                placeholder="e.g. 5"
                                @input="form.errors.tax = null"
                            />
                            <TextInput
                                id="inv_prefix"
                                label="Invoice Prefix"
                                v-model="form.inv_prefix"
                                :error="form.errors.inv_prefix"
                                placeholder="e.g. INV-"
                                @input="form.errors.inv_prefix = null"
                            />
                            <SelectInput
                                v-model="form.currency"
                                :options="currencyOptions"
                                label="Site Currency"
                                placeholder="Select Currency"
                                :error="form.errors.currency"
                            />

                            <div class="relative">
                                <TextInput
                                    id="create_news"
                                    label="News Creation Fee"
                                    type="number"
                                    v-model="form.create_news"
                                    :error="form.errors.create_news"
                                    placeholder="e.g. 500"
                                    @input="form.errors.create_news = null"
                                />
                                <p class="text-[10px] text-slate-400 mt-1">
                                    <i
                                        class="fa-solid fa-circle-info text-amber-500"
                                    ></i>
                                    Set 0 for free. Use decimals for poisha
                                    (e.g. 500.00)
                                </p>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200"
                        >
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1"
                                    >Registration Status</label
                                >
                                <select
                                    v-model="form.register_status"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 bg-white"
                                >
                                    <option value="1">
                                        Open (Users can register)
                                    </option>
                                    <option value="0">Closed (Disabled)</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1"
                                    >Default News View Count</label
                                >
                                <TextInput
                                    id="news_view"
                                    type="number"
                                    v-model="form.news_view"
                                    :error="form.errors.news_view"
                                    placeholder="e.g. 0"
                                    @input="form.errors.news_view = null"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- 4. LEGAL PAGES -->
                    <div
                        v-show="activeTab === 'legal'"
                        class="space-y-6 animate-fade-in"
                    >
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-lg font-bold text-slate-800">
                                <i
                                    class="fa-solid fa-file-contract text-rose-500 mr-2"
                                ></i>
                                Pages & Terms
                            </h2>
                            <p class="text-xs text-slate-500">
                                Rich text content for public pages.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 mb-2"
                                    >About Us Page</label
                                >
                                <RichTextEditor
                                    v-model="form.site_about"
                                    placeholder="Write about your company..."
                                    :error="form.errors.site_about"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 mb-2"
                                    >Terms & Conditions Page</label
                                >
                                <RichTextEditor
                                    v-model="form.site_termes"
                                    placeholder="Enter terms and conditions..."
                                    :error="form.errors.site_termes"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-bold text-slate-700 mb-2"
                                    >Invoice Terms</label
                                >
                                <RichTextEditor
                                    v-model="form.inv_termes"
                                    placeholder="Terms printed on invoices..."
                                    :error="form.errors.inv_termes"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </Layout>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
