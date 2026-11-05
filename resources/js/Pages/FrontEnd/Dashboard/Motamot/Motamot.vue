<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Pagination from "@/Shared/Pagination.vue";
import { route } from "ziggy-js";
import Layout from "@/Components/FrontEnd/Motamot/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";

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

    // Smooth scroll to form on mobile / small screens
    if (typeof window !== "undefined" && window.innerWidth < 1024) {
        setTimeout(() => {
            document
                .getElementById("motamot-form")
                ?.scrollIntoView({ behavior: "smooth", block: "start" });
        }, 50);
    }
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
    <Head title="Motamot Management Page" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <!-- ==================== PAGE HEADER ==================== -->
        <div
            class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
        >
            <div>
                <h1
                    class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800"
                >
                    Motamot Management
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Create, edit, and manage your motamot entries
                </p>
            </div>

            <div
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-50 text-teal-700 text-xs sm:text-sm font-semibold w-fit"
            >
                <i class="fa-solid fa-layer-group"></i>
                <span>Total: {{ props.motamots?.total ?? 0 }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
            <!-- ==================== LEFT: ALL MOTAMOT ==================== -->
            <div
                class="bg-white shadow-sm border border-slate-200 rounded-2xl h-fit overflow-hidden"
            >
                <div
                    class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between"
                >
                    <h2
                        class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2"
                    >
                        <i class="fa-solid fa-list-ul text-teal-600"></i>
                        <span>All Motamot</span>
                    </h2>

                    <span
                        class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full"
                    >
                        {{ props.motamots?.data?.length ?? 0 }} items
                    </span>
                </div>

                <div class="p-4 sm:p-6 space-y-4">
                    <!-- Empty state -->
                    <div
                        v-if="!props.motamots?.data?.length"
                        class="text-center py-10"
                    >
                        <div
                            class="w-16 h-16 mx-auto rounded-full bg-teal-50 text-teal-500 flex items-center justify-center mb-3"
                        >
                            <i class="fa-solid fa-inbox text-2xl"></i>
                        </div>
                        <p class="text-slate-700 font-semibold">
                            No motamots yet
                        </p>
                        <p class="text-sm text-slate-500 mt-1">
                            Add your first motamot using the form on the right.
                        </p>
                    </div>

                    <!-- List items -->
                    <div
                        v-for="motamot in motamots.data"
                        :key="motamot.id"
                        class="border border-slate-200 rounded-xl p-4 sm:p-5 hover:shadow-md hover:border-teal-200 transition"
                    >
                        <!-- Header row -->
                        <div
                            class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3"
                        >
                            <h3
                                class="font-bold text-base sm:text-lg text-slate-800 break-words pr-2"
                            >
                                {{ motamot.subject }}
                            </h3>

                            <div class="flex gap-2 shrink-0">
                                <button
                                    @click="editMotamot(motamot)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold hover:bg-blue-100 transition"
                                    title="Edit"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>

                                <button
                                    @click="deleteMotamot(motamot.id)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition"
                                    title="Delete"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>

                        <!-- Description (rendered HTML) -->
                        <div
                            class="prose prose-sm max-w-none text-slate-600 mb-3 break-words"
                            v-html="motamot.description"
                        ></div>

                        <!-- Images -->
                        <div
                            v-if="motamot.images?.length"
                            class="flex flex-wrap gap-2"
                        >
                            <img
                                v-for="(img, i) in motamot.images"
                                :key="i"
                                :src="`/storage/${img}`"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg object-cover border border-slate-200"
                                alt="Motamot image"
                            />
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.motamots?.links?.length > 3" class="pt-2">
                        <Pagination :links="motamots.links" />
                    </div>
                </div>
            </div>

            <!-- ==================== RIGHT: FORM ==================== -->
            <div
                id="motamot-form"
                class="bg-white shadow-sm border border-slate-200 rounded-2xl h-fit lg:sticky lg:top-20 overflow-hidden"
            >
                <!-- Form header -->
                <div
                    class="px-4 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between"
                >
                    <h2
                        class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2"
                    >
                        <i
                            :class="
                                editMode
                                    ? 'fa-solid fa-pen-to-square'
                                    : 'fa-solid fa-plus'
                            "
                            class="text-teal-600"
                        ></i>
                        <span>{{
                            editMode ? "Edit Motamot" : "Add Motamot"
                        }}</span>
                    </h2>

                    <button
                        v-if="editMode"
                        type="button"
                        @click="resetForm"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        <span>New</span>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-4 sm:p-6 space-y-4">
                    <!-- SUBJECT -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-1.5"
                        >
                            Subject <span class="text-red-500">*</span>
                        </label>

                        <input
                            v-model="form.subject"
                            type="text"
                            placeholder="Enter motamot subject"
                            class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                        />

                        <div
                            v-if="form.errors.subject"
                            class="text-red-500 text-xs mt-1.5"
                        >
                            {{ form.errors.subject }}
                        </div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div>
                        <RichTextEditor
                            v-model="form.description"
                            label="Description"
                            placeholder="Enter description"
                            :error="form.errors.description"
                        />
                    </div>

                    <!-- IMAGE INPUT -->
                    <div>
                        <label
                            class="block text-sm font-semibold text-slate-700 mb-1.5"
                        >
                            Images
                        </label>

                        <label
                            class="flex items-center justify-center gap-2 w-full border-2 border-dashed border-slate-300 rounded-lg px-4 py-6 cursor-pointer hover:border-teal-400 hover:bg-teal-50/30 transition text-slate-500"
                        >
                            <i
                                class="fa-solid fa-cloud-arrow-up text-teal-600 text-lg"
                            ></i>
                            <span class="text-sm font-medium">
                                Click to select images
                            </span>

                            <input
                                ref="imageInput"
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleImages"
                                class="hidden"
                            />
                        </label>
                    </div>

                    <!-- PREVIEW -->
                    <div v-if="previewImages.length" class="space-y-2">
                        <p
                            class="text-xs font-semibold text-slate-500 uppercase tracking-wider"
                        >
                            Preview ({{ previewImages.length }})
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <img
                                v-for="(img, i) in previewImages"
                                :key="i"
                                :src="img"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg object-cover border border-slate-200"
                                alt="Preview"
                            />
                        </div>
                    </div>

                    <!-- BUTTONS -->
                    <div
                        class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-2 border-t border-slate-100"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed w-full sm:w-auto"
                        >
                            <i
                                :class="
                                    form.processing
                                        ? 'fa-solid fa-spinner fa-spin'
                                        : editMode
                                          ? 'fa-solid fa-check'
                                          : 'fa-solid fa-paper-plane'
                                "
                            ></i>
                            <span>
                                {{
                                    form.processing
                                        ? "Saving..."
                                        : editMode
                                          ? "Update"
                                          : "Submit"
                                }}
                            </span>
                        </button>

                        <button
                            v-if="editMode"
                            type="button"
                            @click="resetForm"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold transition w-full sm:w-auto"
                        >
                            <i class="fa-solid fa-xmark"></i>
                            <span>Cancel</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
