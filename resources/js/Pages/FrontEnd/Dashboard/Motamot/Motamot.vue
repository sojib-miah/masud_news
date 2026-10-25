<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Pagination from "@/Shared/Pagination.vue";
import { route } from "ziggy-js";
import Layout from "@/Components/FrontEnd/Motamot/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    motamots: Object,
});

const editMode = ref(false);

const previewImages = ref([]);

const imageInput = ref(null);

const form = useForm({
    id: null,
    subject: "",
    description: "",
    images: [],
});

const handleImages = (e) => {
    form.images = Array.from(e.target.files);

    previewImages.value = [];

    form.images.forEach((file) => {
        previewImages.value.push(URL.createObjectURL(file));
    });
};

const submit = () => {
    const formData = new FormData();

    formData.append("subject", form.subject);
    formData.append("description", form.description);

    if (form.images.length > 0) {
        form.images.forEach((image, index) => {
            formData.append(`images[${index}]`, image);
        });
    }

    // UPDATE
    if (editMode.value) {
        formData.append("_method", "PUT");

        router.post(`/motamot/update/${form.id}`, formData, {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                resetForm();
            },
        });
    }

    // CREATE
    else {
        router.post("/motamot/store", formData, {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                resetForm();
            },
        });
    }
};

const editMotamot = (motamot) => {
    editMode.value = true;

    form.id = motamot.id;
    form.subject = motamot.subject;
    form.description = motamot.description;

    previewImages.value = motamot.images.map((img) => `/storage/${img}`);
};

const resetForm = () => {
    editMode.value = false;

    form.reset();

    previewImages.value = [];

    if (imageInput.value) {
        imageInput.value.value = "";
    }
};

const deleteMotamot = (id) => {
    if (confirm("Are you sure?")) {
        router.delete(route("motamot.destroy", id));
    }
};
</script>

<template>
    <Head title="Motamot Page" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <Breadcrumb />

        <div class="max-w-7xl mx-auto p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- LEFT -->
                <div class="bg-white shadow rounded h-fit p-6">
                    <h2 class="text-2xl font-bold mb-5">All Motamot</h2>

                    <div
                        v-for="motamot in motamots.data"
                        :key="motamot.id"
                        class="border rounded-lg p-4 mb-4"
                    >
                        <div class="flex justify-between mb-3">
                            <h3 class="font-bold text-lg">
                                {{ motamot.subject }}
                            </h3>

                            <div class="space-x-2">
                                <button
                                    @click="editMotamot(motamot)"
                                    class="bg-blue-500 text-white px-3 py-1 rounded"
                                >
                                    Edit
                                </button>

                                <button
                                    @click="deleteMotamot(motamot.id)"
                                    class="bg-red-500 text-white px-3 py-1 rounded"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- HTML RENDER -->
                        <div class="mb-3" v-html="motamot.description"></div>

                        <div class="flex flex-wrap gap-2">
                            <img
                                v-for="(img, i) in motamot.images"
                                :key="i"
                                :src="`/storage/${img}`"
                                class="w-24 h-24 rounded object-cover border"
                            />
                        </div>
                    </div>

                    <Pagination :links="motamots.links" />
                </div>

                <!-- RIGHT -->
                <div class="bg-white shadow rounded p-6 h-fit">
                    <h2 class="text-2xl font-bold mb-5">
                        {{ editMode ? "Edit Motamot" : "Add Motamot" }}
                    </h2>

                    <form @submit.prevent="submit">
                        <!-- SUBJECT -->
                        <div class="mb-4">
                            <label class="block mb-2 font-medium">
                                Subject
                            </label>

                            <input
                                v-model="form.subject"
                                type="text"
                                class="w-full border rounded-lg p-3"
                            />

                            <div
                                v-if="form.errors.subject"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ form.errors.subject }}
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mb-4">
                            <RichTextEditor
                                v-model="form.description"
                                label="Description"
                                placeholder="Description"
                                :error="form.errors.description"
                            />
                        </div>

                        <!-- IMAGE -->
                        <div class="mb-4">
                            <label class="block mb-2 font-medium">
                                Images
                            </label>

                            <input
                                ref="imageInput"
                                type="file"
                                multiple
                                @change="handleImages"
                                class="w-full border rounded-lg p-2"
                            />
                        </div>

                        <!-- PREVIEW -->
                        <div
                            v-if="previewImages.length"
                            class="flex flex-wrap gap-3 mb-5"
                        >
                            <img
                                v-for="(img, i) in previewImages"
                                :key="i"
                                :src="img"
                                class="w-24 h-24 rounded object-cover border"
                            />
                        </div>

                        <!-- BUTTON -->
                        <div class="flex gap-3">
                            <button
                                type="submit"
                                class="bg-indigo-500 text-white px-6 py-3 rounded-lg"
                            >
                                {{ editMode ? "Update" : "Submit" }}
                            </button>

                            <button
                                v-if="editMode"
                                type="button"
                                @click="resetForm"
                                class="bg-gray-500 text-white px-6 py-3 rounded-lg"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>
