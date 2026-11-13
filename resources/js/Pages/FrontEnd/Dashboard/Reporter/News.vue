<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import TextInput from "@/Shared/TextInput.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { computed, ref } from "vue";
import { route } from "ziggy-js";

const languages = [
    { label: "বাংলা (Bangla)", id: "1" },
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
    categories: { type: Array, default: () => [] },
    subcategories: { type: Array, default: () => [] },
});

const form = useForm({
    language: null,
    title: "",
    tag: "",
    description: "",
    image: null,
    division_name: "",
    imagenote: "",
    category: "",
    subcategory: "",
    featured: "no",
    breaking: "no",
});

const filteredSubcategories = computed(() => {
    if (!form.category) return [];
    return props.subcategories.filter(
        (sub) => Number(sub.category_id) === Number(form.category),
    );
});

// ==================== TAGS ====================
const newTag = ref("");
const tags = ref([]);

const addTag = (event) => {
    event.preventDefault();
    const val = newTag.value.trim();
    if (val && !tags.value.includes(val)) {
        tags.value.push(val);
    }
    newTag.value = "";
};

const removeTag = (index) => {
    tags.value.splice(index, 1);
};

// ==================== SUBMIT ====================
const submitting = ref(false);

const submit = () => {
    form.tag = tags.value.join(",");
    submitting.value = true;

    form.post(route("news.store"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            tags.value = [];
            imagePreview.value = null;
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
};
</script>

<template>
    <Layout>
        <Head title="Create News Page" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="min-h-screen">
            <div class="space-y-5">
                <!-- ==================== HEADER ==================== -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-xl overflow-hidden"
                >
                    <div
                        class="px-5 sm:px-8 py-5 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center text-white text-xl"
                            >
                                <i class="fa-solid fa-newspaper"></i>
                            </div>
                            <div>
                                <h1
                                    class="text-white text-lg sm:text-2xl font-bold leading-tight"
                                >
                                    নতুন সংবাদ যোগ করুন
                                </h1>
                                <p
                                    class="text-green-100 text-xs sm:text-sm mt-0.5"
                                >
                                    Add a new news article
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 text-white/90 text-xs bg-white/10 px-3 py-2 rounded-lg"
                        >
                            <i class="fa-solid fa-circle-info"></i>
                            <span>নিউজ সংরক্ষণ করা হবে ড্রাফট হিসেবে</span>
                        </div>
                    </div>
                </div>

                <!-- ==================== FORM ==================== -->
                <form
                    @submit.prevent="submit"
                    class="grid grid-cols-1 lg:grid-cols-3 gap-5"
                >
                    <!-- ==================== MAIN COLUMN (2/3) ==================== -->
                    <div class="lg:col-span-2 space-y-5">
                        <!-- ===== BASIC INFO ===== -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                        >
                            <div
                                class="px-5 py-3 border-b border-slate-100 bg-slate-50"
                            >
                                <h3
                                    class="text-sm font-bold text-slate-700 uppercase flex items-center gap-2"
                                >
                                    <i
                                        class="fa-solid fa-file-lines text-green-600"
                                    ></i>
                                    Main Information
                                </h3>
                            </div>

                            <div class="p-5 space-y-4">
                                <!-- Language -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        Language
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <SelectInput
                                        v-model="form.language"
                                        placeholder="Select Language"
                                        :options="
                                            languages.map((lang) => ({
                                                label: lang.label,
                                                value: lang.id,
                                            }))
                                        "
                                        :error="form.errors.language"
                                    />
                                </div>

                                <!-- Title -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        Title
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <TextInput
                                        v-model="form.title"
                                        type="text"
                                        placeholder="Enter news title..."
                                        :error="form.errors.title"
                                        @update:modelValue="
                                            form.clearErrors('title')
                                        "
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ===== TAGS ===== -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                        >
                            <div
                                class="px-5 py-3 border-b border-slate-100 bg-slate-50"
                            >
                                <h3
                                    class="text-sm font-bold text-slate-700 uppercase flex items-center gap-2"
                                >
                                    <i
                                        class="fa-solid fa-tags text-green-600"
                                    ></i>
                                    Tags
                                </h3>
                            </div>

                            <div class="p-5">
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                                        placeholder="Enter tags..."
                                        v-model="newTag"
                                        @keydown.enter.prevent="addTag"
                                    />
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 rounded-lg transition flex items-center gap-1.5"
                                    >
                                        <i class="fa-solid fa-plus text-xs"></i>
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
                                        class="inline-flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full"
                                    >
                                        <i
                                            class="fa-solid fa-hashtag text-green-500 text-[10px]"
                                        ></i>
                                        {{ tag }}
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="ml-0.5 text-green-500 hover:text-red-500 transition"
                                        >
                                            <i
                                                class="fa-solid fa-xmark text-[10px]"
                                            ></i>
                                        </button>
                                    </span>
                                </div>
                                <p
                                    v-else
                                    class="text-xs text-slate-400 mt-2 italic"
                                >
                                    Nothing added yet. Add tags to describe your
                                    news article.
                                </p>
                            </div>
                        </div>

                        <!-- ===== DESCRIPTION ===== -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                        >
                            <div
                                class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between"
                            >
                                <h3
                                    class="text-sm font-bold text-slate-700 uppercase flex items-center gap-2"
                                >
                                    <i
                                        class="fa-solid fa-align-left text-green-600"
                                    ></i>
                                    News Description
                                </h3>
                                <span
                                    class="text-[11px] text-slate-400 font-medium"
                                >
                                    <span class="text-red-500">*</span>
                                    Required
                                </span>
                            </div>

                            <div class="p-5">
                                <RichTextEditor
                                    v-model="form.description"
                                    :error="form.errors.description"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SIDEBAR (1/3) ==================== -->
                    <div class="space-y-5">
                        <!-- ===== FEATURED IMAGE ===== -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                        >
                            <div
                                class="px-5 py-3 border-b border-slate-100 bg-slate-50"
                            >
                                <h3
                                    class="text-sm font-bold text-slate-700 uppercase flex items-center gap-2"
                                >
                                    <i
                                        class="fa-regular fa-image text-green-600"
                                    ></i>
                                    Featured Image
                                </h3>
                            </div>

                            <div class="p-5 space-y-3">
                                <FileInput
                                    v-model="form.image"
                                    accept="image/*"
                                    :multiple="false"
                                    :error="form.errors.image"
                                />

                                <!-- Image Note -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        Image Note
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <TextInput
                                        v-model="form.imagenote"
                                        type="text"
                                        placeholder="Enter news imagenote..."
                                        :error="form.errors.imagenote"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ===== LOCATION & CATEGORY ===== -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200"
                        >
                            <div
                                class="px-5 py-3 border-b border-slate-100 bg-slate-50 rounded-t-2xl"
                            >
                                <h3
                                    class="text-sm font-bold text-slate-700 uppercase flex items-center gap-2"
                                >
                                    <i
                                        class="fa-solid fa-folder-tree text-green-600"
                                    ></i>
                                    Category
                                </h3>
                            </div>

                            <div class="p-5 space-y-4">
                                <!-- Division -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        Division
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.division_name"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition bg-white"
                                    >
                                        <option value="">
                                            Select Division
                                        </option>
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
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.division_name }}
                                    </p>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        Category
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <SelectInput
                                        v-model="form.category"
                                        placeholder="Select Category"
                                        :options="
                                            props.categories.map((cat) => ({
                                                label: cat.name,
                                                value: cat.id,
                                            }))
                                        "
                                        :error="form.errors.category"
                                    />
                                </div>

                                <!-- Subcategory -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        Subcategory
                                    </label>
                                    <SelectInput
                                        v-model="form.subcategory"
                                        :placeholder="
                                            form.category
                                                ? 'Select Subcategory'
                                                : 'First select a category'
                                        "
                                        :options="
                                            filteredSubcategories.map(
                                                (sub) => ({
                                                    label: sub.name,
                                                    value: sub.id,
                                                }),
                                            )
                                        "
                                        :error="form.errors.subcategory"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- ===== FLAGS ===== -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                        >
                            <div
                                class="px-5 py-3 border-b border-slate-100 bg-slate-50"
                            >
                                <h3
                                    class="text-sm font-bold text-slate-700 uppercase flex items-center gap-2"
                                >
                                    <i
                                        class="fa-solid fa-star text-green-600"
                                    ></i>
                                    Featured Flags
                                </h3>
                            </div>

                            <div class="p-5 space-y-4">
                                <!-- Featured -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-2"
                                    >
                                        Add to Featured?
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label
                                            class="flag-option"
                                            :class="{
                                                active: form.featured === 'yes',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="yes"
                                                v-model="form.featured"
                                                class="sr-only"
                                            />
                                            <i
                                                class="fa-solid fa-star text-xs"
                                            ></i>
                                            Yes
                                        </label>
                                        <label
                                            class="flag-option"
                                            :class="{
                                                active: form.featured === 'no',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="no"
                                                v-model="form.featured"
                                                class="sr-only"
                                            />
                                            <i
                                                class="fa-solid fa-xmark text-xs"
                                            ></i>
                                            No
                                        </label>
                                    </div>
                                </div>

                                <!-- Breaking -->
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-2"
                                    >
                                        Add to Breaking?
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label
                                            class="flag-option danger"
                                            :class="{
                                                active: form.breaking === 'yes',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="yes"
                                                v-model="form.breaking"
                                                class="sr-only"
                                            />
                                            <i
                                                class="fa-solid fa-bolt text-xs"
                                            ></i>
                                            Yes
                                        </label>
                                        <label
                                            class="flag-option"
                                            :class="{
                                                active: form.breaking === 'no',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="no"
                                                v-model="form.breaking"
                                                class="sr-only"
                                            />
                                            <i
                                                class="fa-solid fa-xmark text-xs"
                                            ></i>
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== SUBMIT ACTIONS ===== -->
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 space-y-2 lg:sticky lg:top-4"
                        >
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-2.5 rounded-lg shadow transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <i
                                    v-if="submitting"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i v-else class="fa-solid fa-floppy-disk"></i>
                                {{ submitting ? "Saving..." : "Save Draft" }}
                            </button>

                            <button
                                type="button"
                                @click="router.visit(route('news.all'))"
                                class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-lg transition flex items-center justify-center gap-2"
                            >
                                <i class="fa-solid fa-arrow-left text-xs"></i>
                                Cancel
                            </button>

                            <p
                                class="text-[11px] text-slate-400 text-center pt-2"
                            >
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                Saving will require admin approval
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* ===== FLAG RADIO BUTTONS ===== */
.flag-option {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 10px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    background: #f8fafc;
    color: #475569;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.15s,
        border-color 0.15s,
        color 0.15s,
        transform 0.15s;
    user-select: none;
}

.flag-option:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #00930c;
}

.flag-option.active {
    background: linear-gradient(135deg, #00930c, #00b515);
    border-color: #00930c;
    color: #fff;
    box-shadow: 0 3px 10px -2px rgba(0, 147, 12, 0.3);
}

.flag-option.danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.flag-option.danger.active {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    border-color: #dc2626;
    color: #fff;
    box-shadow: 0 3px 10px -2px rgba(220, 38, 38, 0.3);
}

/* Small icon alignment */
.flag-option i {
    line-height: 1;
}
</style>
