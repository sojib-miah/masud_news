<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import SelectInput from "@/Shared/SelectInput.vue";
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
    language: null,
    title: "",
    tag: "",
    description: "",
    image: null,
    imageNote: "",
    category: "",
    subcategory: "",
    featured: "",
    breaking: "",
});

const filteredSubcategories = computed(() => {
    if (!form.category) return [];

    return props.subcategories.filter(
        (sub) => Number(sub.category_id) === Number(form.category),
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

    form.post(route("news.store"), {
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
        <!-- form.post(route('news.update', news.id), {
                _method: 'put'
            }); -->
        <form @submit.prevent="submit">
            <div class="flex gap-5">
                <div
                    class="w-3/5 border border-slate-300 p-4 shadow-lg rounded bg-white h-fit"
                >
                    <div class="mb-4">
                        <SelectInput
                            v-model="form.language"
                            label="ভাষা"
                            placeholder="ভাষা নির্বাচন করুন"
                            :options="
                                languages.map((lang) => ({
                                    label: lang.label,
                                    value: lang.id,
                                }))
                            "
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.language"
                        />
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
                            v-model="form.imageNote"
                            type="text"
                            label="Image Note"
                            placeholder="Image Note....."
                            :error="form.errors.imageNote"
                            @update:modelValue="form.clearErrors('imageNote')"
                        />
                    </div>
                    <div class="mb-4">
                        <SelectInput
                            v-model="form.category"
                            label="Category"
                            placeholder="Category..."
                            :options="
                                props.categories.map((cat) => ({
                                    label: cat.name,
                                    value: cat.id,
                                }))
                            "
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.category"
                        />
                    </div>

                    <div class="mb-4">
                        <SelectInput
                            v-model="form.subcategory"
                            label="Sub Category"
                            placeholder="Sub Category..."
                            :options="
                                filteredSubcategories.map((sub) => ({
                                    label: sub.name,
                                    value: sub.id,
                                }))
                            "
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.subcategory"
                        />
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Add to Featured</label>

                        <label for="yes" class="text-lg font-bold mr-10">
                            Yes
                            <input
                                type="radio"
                                id="yes"
                                value="yes"
                                v-model="form.featured"
                            />
                        </label>

                        <label for="no" class="text-lg font-bold">
                            No
                            <input
                                type="radio"
                                id="no"
                                value="no"
                                v-model="form.featured"
                                checked
                            />
                        </label>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Add to Breaking</label>

                        <label for="yes" class="text-lg font-bold mr-10">
                            Yes
                            <input
                                type="radio"
                                id="yes"
                                value="yes"
                                v-model="form.breaking"
                            />
                        </label>

                        <label for="no" class="text-lg font-bold">
                            No
                            <input
                                type="radio"
                                id="no"
                                value="no"
                                v-model="form.breaking"
                                checked
                            />
                        </label>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn rounded w-full">
                            Save Draft
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </Layout>
</template>
