<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import TextInput from "@/Shared/TextInput.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { computed, ref, watch, onMounted } from "vue";

const props = defineProps({
    news: Object,
    categories: Array,
    subcategories: Array,
});

const languages = [
    { label: "Bangla", id: "1" },
    { label: "English", id: "2" },
];

const tags = ref([]);

const form = useForm({
    language: props.news.language,
    title: props.news.title,
    metatag: props.news.metatag,
    tag: props.news.tag,
    description: props.news.description,
    image: null,
    imagenote: props.news.imagenote,
    category_id: props.news.category_id,
    subcategory_id: props.news.subcategory_id,
    featured: props.news.featured,
    breaking: props.news.breaking,
    schedule: props.news.schedule,
    expire: props.news.expire,
    status: props.news.status,
    audio: props.news.audio,
    video: props.news.video,
});

onMounted(() => {
    if (props.news.tag) {
        tags.value = JSON.parse(props.news.tag);
    }
});

const filteredSubcategories = computed(() => {
    if (!form.category_id) return [];

    return props.subcategories.filter(
        (sub) => Number(sub.category_id) === Number(form.category_id),
    );
});

watch(
    () => form.category_id,
    () => {
        form.subcategory_id = "";
    },
);

const newTag = ref("");

const addTag = () => {
    if (newTag.value.trim()) {
        tags.value.push(newTag.value.trim());
        newTag.value = "";
    }
};

const removeTag = (index) => {
    tags.value.splice(index, 1);
};

const submit = () => {
    form.tag = tags.value.join(",");

    form.post(route("admin.news.update", props.news.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Layout>
        <Head title="Edit News" />
        <Breadcrumb />

        <form @submit.prevent="submit">
            <div class="flex gap-5">
                <div class="w-3/5 border p-4 shadow-lg rounded bg-white">
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
                        <TextInput v-model="form.title" label="Title" />
                    </div>
                    <div class="mb-4">
                        <input
                            v-model="newTag"
                            placeholder="Add Tag"
                            @keydown.enter.prevent="addTag"
                            class="form-input"
                        />

                        <div class="mt-2">
                            <span
                                v-for="(tag, index) in tags"
                                :key="index"
                                class="bg-gray-200 px-2 py-1 rounded mr-2"
                            >
                                {{ tag }}
                                <button type="button" @click="removeTag(index)">
                                    ×
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <RichTextEditor v-model="form.description" />
                    </div>
                </div>

                <div class="w-2/5 border p-4 shadow-lg rounded bg-white">
                    <img
                        v-if="props.news.image"
                        :src="'/storage/' + props.news.image"
                        class="w-full mb-3 rounded"
                    />
                    <div class="mb-4">
                        <FileInput v-model="form.image" label="Change Image" />
                    </div>
                    <div class="mb-4">
                        <TextInput
                            v-model="form.imagenote"
                            label="Image Note"
                        />
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Category</label>
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
                        <label>Featured : </label>

                        <input type="radio" value="1" v-model="form.featured" />
                        Yes
                        <input type="radio" value="0" v-model="form.featured" />
                        No
                    </div>
                    <div class="mb-4">
                        <label>Breaking : </label>

                        <input type="radio" value="1" v-model="form.breaking" />
                        Yes
                        <input type="radio" value="0" v-model="form.breaking" />
                        No
                    </div>

                    <div class="mb-4">
                        <label>Status : </label>

                        <input type="radio" value="1" v-model="form.status" />
                        Publish
                        <input type="radio" value="0" v-model="form.status" />
                        Draft
                    </div>

                    <button class="btn mt-5 w-full">Update News</button>
                </div>
            </div>
        </form>
    </Layout>
</template>
