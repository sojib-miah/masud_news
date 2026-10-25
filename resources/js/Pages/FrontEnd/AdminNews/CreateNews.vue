<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import TextInput from "@/Shared/TextInput.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import FileInput from "@/Shared/FileInput.vue";
import { computed, ref } from "vue";
import FlashMessage from "@/Shared/FlashMessage.vue";

const languages = [
    { label: "Bangla", id: "1" },
    { label: "English", id: "2" },
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
    imagenote: "",
    category_id: "",
    subcategory_id: "",
    featured: 0,
    breaking: 0,
    schedule: "",
    expire: "",
    status: 0,
    audio: "",
    video: "",
});

const filteredSubcategories = computed(() => {
    if (!form.category_id) return [];

    return props.subcategories.filter(
        (sub) => Number(sub.category_id) === Number(form.category_id),
    );
});

const newTag = ref("");
const tags = ref([]);

const addTag = (event) => {
    event.preventDefault();
    if (newTag.value.trim() !== "") {
        tags.value.push(newTag.value.trim());
        newTag.value = "";
    }
};

const removeTag = (index) => {
    tags.value.splice(index, 1);
};

const submit = () => {
    form.tag = tags.value.join(",");

    form.post(route("admin.news.store"), {
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            tags.value = [];
        },
    });
};
</script>

<template>
    <Layout>
        <Head title="Add News" />
        <Breadcrumb />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <form @submit.prevent="submit">
            <div class="flex gap-5">
                <div
                    class="w-3/5 border border-slate-300 p-4 shadow-lg rounded bg-white h-fit"
                >
                    <div class="mb-4">
                        <select
                            v-model="form.language"
                            class="p-2 border border-slate-400 rounded w-full"
                        >
                            <option value="">ভাষা নির্বাচন করুন</option>
                            <option
                                v-for="lang in languages"
                                :key="lang.id"
                                :value="lang.id"
                            >
                                {{ lang.label }}
                            </option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <TextInput
                            v-model="form.title"
                            type="text"
                            label="Title"
                            placeholder="Title....."
                            :error="form.errors.title"
                            @update:modelValue="form.clearErrors('title')"
                        />
                    </div>

                    <div class="mb-4">
                        <input
                            type="text"
                            class="form-input"
                            placeholder="Tags..."
                            v-model="newTag"
                            @keydown.enter.prevent="addTag"
                        />
                        <div class="tags mt-2">
                            <span
                                v-for="(tag, index) in tags"
                                :key="index"
                                class="inline-block bg-gray-200 px-2 py-1 rounded mr-2 mb-2"
                            >
                                {{ tag }}
                                <button
                                    type="button"
                                    class="ml-1 text-red-500"
                                    @click="removeTag(index)"
                                >
                                    ×
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <RichTextEditor
                            v-model="form.description"
                            label="Description"
                            placeholder="Description"
                            :error="form.errors.description"
                        />
                    </div>
                </div>
                <div
                    class="w-2/5 border border-slate-300 p-4 shadow-lg rounded bg-white h-fit"
                >
                    <div class="mb-4">
                        <FileInput
                            v-model="form.image"
                            label="Image"
                            accept="image/*"
                            :multiple="false"
                            :error="form.errors.image"
                        />
                    </div>
                    <div class="mb-4">
                        <TextInput
                            v-model="form.imagenote"
                            type="text"
                            label="Image Note"
                            placeholder="Image Note....."
                            :error="form.errors.imagenote"
                            @update:modelValue="form.clearErrors('imageNote')"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="">Category</label>
                        <select
                            v-model="form.category_id"
                            class="p-2 border border-slate-400 rounded w-full"
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
                    </div>

                    <div class="mb-4">
                        <label class="form-label">SubCategory</label>
                        <select
                            v-model="form.subcategory_id"
                            class="p-2 border border-slate-400 rounded w-full"
                        >
                            <option value="">Select SubCategory</option>
                            <option
                                v-for="sub in filteredSubcategories"
                                :key="sub.id"
                                :value="sub.id"
                            >
                                {{ sub.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Add to Featured : </label>

                        <label class="text-lg font-bold mr-10 select-none">
                            Yes
                            <input
                                type="radio"
                                id="yes"
                                value="1"
                                v-model="form.featured"
                            />
                        </label>

                        <label class="text-lg font-bold select-none">
                            No
                            <input
                                type="radio"
                                id="no"
                                value="0"
                                v-model="form.featured"
                                checked
                            />
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Add to Breaking : </label>

                        <label class="text-lg font-bold mr-10 select-none">
                            Yes
                            <input
                                type="radio"
                                id="yes"
                                value="1"
                                v-model="form.breaking"
                            />
                        </label>

                        <label class="text-lg font-bold select-none">
                            No
                            <input
                                type="radio"
                                id="no"
                                value="0"
                                v-model="form.breaking"
                                checked
                            />
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Status : </label>

                        <label class="text-lg font-bold mr-10 select-none">
                            Yes
                            <input
                                type="radio"
                                id="yes"
                                value="1"
                                v-model="form.status"
                            />
                        </label>

                        <label class="text-lg font-bold select-none">
                            No
                            <input
                                type="radio"
                                id="no"
                                value="0"
                                v-model="form.status"
                                checked
                            />
                        </label>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn rounded w-full">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </Layout>
</template>
