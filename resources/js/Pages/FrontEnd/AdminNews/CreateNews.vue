<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import TextInput from "@/Shared/TextInput.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { computed, ref } from "vue";
import { route } from "ziggy-js";

const languages = [
    { label: "বাংলা", id: "1" },
    { label: "English", id: "2" },
];

const divisions = [
    { name: "ঢাকা" },
    { name: "চট্টগ্রাম" },
    { name: "রাজশাহী" },
    { name: "খুলনা" },
    { name: "বরিশাল" },
    { name: "সিলেট" },
    { name: "রংপুর" },
    { name: "ময়মনসিংহ" },
];

const props = defineProps({
    categories: Array,
    subcategories: Array,
});

const form = useForm({
    language: "",
    title: "",
    metatag: "",
    tag: "",
    description: "",
    image: null,
    division_name: "",
    category_id: "",
    subcategory_id: "",
    featured: 0,
    breaking: 0,
    status: 0,
});

const filteredSubcategories = computed(() => {
    if (!form.category_id) return [];
    return props.subcategories.filter(
        (sub) => Number(sub.category_id) === Number(form.category_id),
    );
});

// Reset subcategory if category changes
function onCategoryChange() {
    form.subcategory_id = "";
}

// ==================== TAGS ====================
const newTag = ref("");
const tags = ref([]);

const addTag = (event) => {
    event.preventDefault();
    const v = newTag.value.trim();
    if (v && !tags.value.includes(v)) {
        tags.value.push(v);
    }
    newTag.value = "";
};

const removeTag = (index) => {
    tags.value.splice(index, 1);
};

// ==================== SUBMIT ====================
const submit = () => {
    form.tag = tags.value.join(",");
    form.post(route("admin.news.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            tags.value = [];
        },
    });
};

// Toggle helper for radio groups
function setField(field, value) {
    form[field] = Number(value);
}
</script>

<template>
    <Layout>
        <Head title="Create News Page" />
        <Breadcrumb />

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
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Content
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Create News
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Write, tag, and publish a new article.
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold w-fit"
                >
                    <i class="fa-solid fa-lightbulb text-amber-300"></i>
                    Fill required fields marked *
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- ============================================= -->
                <!-- ================= LEFT COLUMN =============== -->
                <!-- ============================================= -->
                <div class="lg:col-span-2 order-2 lg:order-1 space-y-5">
                    <!-- CONTENT CARD -->
                    <div
                        class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow"
                            >
                                <i class="fa-solid fa-file-lines text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    Content
                                </h2>
                                <p class="text-xs text-slate-500">
                                    Title, language, tags & body
                                </p>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            <!-- Language -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    <i
                                        class="fa-solid fa-language text-blue-600 mr-1"
                                    ></i>
                                    Language <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.language"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white"
                                >
                                    <option value="">Select Language</option>
                                    <option
                                        v-for="lang in languages"
                                        :key="lang.id"
                                        :value="lang.id"
                                    >
                                        {{ lang.label }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.language"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.language }}
                                </p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    <i
                                        class="fa-solid fa-heading text-blue-600 mr-1"
                                    ></i>
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <TextInput
                                    v-model="form.title"
                                    type="text"
                                    placeholder="News Title..."
                                    :error="form.errors.title"
                                    @update:modelValue="
                                        form.clearErrors('title')
                                    "
                                />
                            </div>

                            <!-- Tags -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    <i
                                        class="fa-solid fa-tags text-violet-600 mr-1"
                                    ></i>
                                    Tag
                                    <span
                                        class="text-slate-400 text-[10px] font-normal normal-case"
                                        >(Enter চাপুন যোগ করতে)</span
                                    >
                                </label>

                                <div class="relative">
                                    <i
                                        class="fa-solid fa-hashtag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                    ></i>
                                    <input
                                        type="text"
                                        v-model="newTag"
                                        placeholder="Enter Tag..."
                                        class="w-full rounded-lg border border-slate-300 pl-9 pr-20 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
                                        @keydown.enter.prevent="addTag"
                                    />
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex items-center gap-1 rounded-md bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold px-3 py-1.5 transition"
                                    >
                                        <i
                                            class="fa-solid fa-plus text-[10px]"
                                        ></i>
                                        Add
                                    </button>
                                </div>

                                <div
                                    v-if="tags.length"
                                    class="flex flex-wrap gap-2 mt-3"
                                >
                                    <span
                                        v-for="(tag, index) in tags"
                                        :key="index"
                                        class="group inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700 ring-1 ring-violet-200"
                                    >
                                        <i
                                            class="fa-solid fa-hashtag text-[10px] opacity-70"
                                        ></i>
                                        {{ tag }}
                                        <button
                                            type="button"
                                            class="ml-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-violet-200 text-violet-700 hover:bg-red-500 hover:text-white transition"
                                            @click="removeTag(index)"
                                            title="Remove tag"
                                        >
                                            <i
                                                class="fa-solid fa-xmark text-[8px]"
                                            ></i>
                                        </button>
                                    </span>
                                </div>
                                <p
                                    v-else
                                    class="mt-2 text-xs text-slate-400 italic"
                                >
                                    No tags added yet
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    <i
                                        class="fa-solid fa-align-left text-blue-600 mr-1"
                                    ></i>
                                    Description
                                    <span class="text-red-500">*</span>
                                </label>
                                <RichTextEditor
                                    v-model="form.description"
                                    placeholder="News Description..."
                                    :error="form.errors.description"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================= -->
                <!-- ================= RIGHT COLUMN ============== -->
                <!-- ============================================= -->
                <div class="lg:col-span-1 order-1 lg:order-2 space-y-5">
                    <!-- MEDIA CARD -->
                    <div
                        class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow"
                            >
                                <i class="fa-solid fa-image text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    Media
                                </h2>
                                <p class="text-xs text-slate-500">
                                    Featured image
                                </p>
                            </div>
                        </div>

                        <div class="p-5">
                            <label
                                class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                            >
                                Image <span class="text-red-500">*</span>
                            </label>
                            <FileInput
                                v-model="form.image"
                                label="Upload image"
                                accept="image/*"
                                :multiple="false"
                                :error="form.errors.image"
                            />
                        </div>
                    </div>

                    <!-- TAXONOMY CARD -->
                    <div
                        class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow"
                            >
                                <i class="fa-solid fa-sitemap text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    Categorize
                                </h2>
                                <p class="text-xs text-slate-500">
                                    Category & location
                                </p>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            <!-- Category -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    Category <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.category_id"
                                    @change="onCategoryChange"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 bg-white"
                                >
                                    <option value="">Select Category</option>
                                    <option
                                        v-for="cat in props.categories"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.category_id"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <!-- Subcategory -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    Subcategory
                                </label>
                                <select
                                    v-model="form.subcategory_id"
                                    :disabled="!form.category_id"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 bg-white disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed"
                                >
                                    <option value="">
                                        {{
                                            form.category_id
                                                ? "Select Subcategory"
                                                : "Select Category first"
                                        }}
                                    </option>
                                    <option
                                        v-for="sub in filteredSubcategories"
                                        :key="sub.id"
                                        :value="sub.id"
                                    >
                                        {{ sub.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.subcategory_id"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.subcategory_id }}
                                </p>
                            </div>

                            <!-- Division -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    <i
                                        class="fa-solid fa-location-dot text-rose-600 mr-1"
                                    ></i>
                                    Division <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.division_name"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 bg-white"
                                >
                                    <option value="">Select Division</option>
                                    <option
                                        v-for="division in divisions"
                                        :key="division.name"
                                        :value="division.name"
                                    >
                                        {{ division.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.division_name"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.division_name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- FLAGS CARD -->
                    <div
                        class="rounded-2xl mb-8 bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 px-5 py-4"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow"
                            >
                                <i class="fa-solid fa-star text-sm"></i>
                            </span>
                            <div>
                                <h2
                                    class="text-base sm:text-lg font-bold text-slate-800"
                                >
                                    Highlights
                                </h2>
                                <p class="text-xs text-slate-500">
                                    Featured, breaking & status
                                </p>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            <!-- Featured -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    <i
                                        class="fa-solid fa-star text-amber-500 mr-1"
                                    ></i>
                                    Featured
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        @click="setField('featured', 1)"
                                        class="rounded-lg py-2 text-xs font-bold transition"
                                        :class="
                                            Number(form.featured) === 1
                                                ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 ring-1 ring-slate-200'
                                        "
                                    >
                                        <i class="fa-solid fa-check mr-1"></i>
                                        Yes
                                    </button>
                                    <button
                                        type="button"
                                        @click="setField('featured', 0)"
                                        class="rounded-lg py-2 text-xs font-bold transition"
                                        :class="
                                            Number(form.featured) === 0
                                                ? 'bg-slate-800 text-white shadow-md'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 ring-1 ring-slate-200'
                                        "
                                    >
                                        <i class="fa-solid fa-xmark mr-1"></i>
                                        No
                                    </button>
                                </div>
                            </div>

                            <!-- Breaking -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    <i
                                        class="fa-solid fa-bolt text-red-500 mr-1"
                                    ></i>
                                    Breaking News
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        @click="setField('breaking', 1)"
                                        class="rounded-lg py-2 text-xs font-bold transition"
                                        :class="
                                            Number(form.breaking) === 1
                                                ? 'bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-md'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 ring-1 ring-slate-200'
                                        "
                                    >
                                        <i class="fa-solid fa-check mr-1"></i>
                                        Yes
                                    </button>
                                    <button
                                        type="button"
                                        @click="setField('breaking', 0)"
                                        class="rounded-lg py-2 text-xs font-bold transition"
                                        :class="
                                            Number(form.breaking) === 0
                                                ? 'bg-slate-800 text-white shadow-md'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 ring-1 ring-slate-200'
                                        "
                                    >
                                        <i class="fa-solid fa-xmark mr-1"></i>
                                        No
                                    </button>
                                </div>
                            </div>
                            <!-- Status toggle -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5"
                                >
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        @click="setField('status', 1)"
                                        class="flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs font-bold transition"
                                        :class="
                                            Number(form.status) === 1
                                                ? 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-md'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 ring-1 ring-slate-200'
                                        "
                                    >
                                        <i class="fa-solid fa-circle-check"></i>
                                        Published
                                    </button>
                                    <button
                                        type="button"
                                        @click="setField('status', 0)"
                                        class="flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs font-bold transition"
                                        :class="
                                            Number(form.status) === 0
                                                ? 'bg-gradient-to-r from-amber-500 to-orange-600 text-white shadow-md'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 ring-1 ring-slate-200'
                                        "
                                    >
                                        <i
                                            class="fa-solid fa-pen-to-square"
                                        ></i>
                                        Draft
                                    </button>
                                </div>
                                <p
                                    v-if="form.errors.status"
                                    class="text-xs text-red-500 mt-1"
                                >
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <div
                        class="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-4 shadow-lg sticky bottom-4"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-3 shadow-lg hover:shadow-xl transition-all disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98]"
                        >
                            <i
                                v-if="form.processing"
                                class="fa-solid fa-spinner fa-spin"
                            ></i>
                            <i v-else class="fa-solid fa-paper-plane"></i>
                            {{ form.processing ? "Saving..." : "Save News" }}
                        </button>
                        <p
                            class="text-center text-[10px] text-slate-400 mt-2 uppercase tracking-wider font-semibold"
                        >
                            <i class="fa-solid fa-lock mr-1"></i>
                            All data saved securely
                        </p>
                    </div>
                </div>
            </div>
        </form>
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
