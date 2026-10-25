<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import Pagination from "@/Shared/Pagination.vue";
import RichTextEditor from "@/Shared/RichTextEditor.vue";

const props = defineProps({
    products: Object,
});

// FORM
const form = useForm({
    id: null,
    name: "",
    description: "",
    price: "",
    discount_price: "",
    quantity: "",
    croy: "",
    dipo: "",
    dillar: "",
    sub_dillar: "",
    percentage: "",
    image: null,
});

const editMode = ref(false);
const preview = ref(null);

// EDIT
const editProduct = (product) => {
    form.clearErrors();
    form.id = product.id;
    form.name = product.name ?? "";
    form.description = product.description ?? "";
    form.price = product.price ?? "";
    form.discount_price = product.discount_price ?? "";
    form.quantity = product.quantity ?? "";
    form.croy = product.croy ?? "";
    form.dipo = product.dipo ?? "";
    form.dillar = product.dillar ?? "";
    form.sub_dillar = product.sub_dillar ?? "";
    form.percentage = product.percentage ?? "";
    form.image = null;

    preview.value = product.image ? `/storage/${product.image}` : null;

    editMode.value = true;
};

// RESET
const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.id = null;
    preview.value = null;
    editMode.value = false;
};

// IMAGE
const handleImage = (e) => {
    const file = e.target.files[0];
    form.image = file;

    if (file) {
        preview.value = URL.createObjectURL(file);
    }
};

// SUBMIT
const submit = () => {
    if (editMode.value) {
        form.transform((data) => ({
            ...data,
            _method: "put",
        })).post(route("products.update", form.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: resetForm,
        });
    } else {
        form.post(route("products.store"), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: resetForm,
        });
    }
};

// DELETE
const deleteProduct = (id) => {
    if (confirm("Delete?")) {
        form.delete(route("products.destroy", id));
    }
};
</script>

<template>
    <Head title="Product Page" />

    <Layout>
        <!-- FLASH -->
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <Breadcrumb />

        <div class="flex gap-6 p-6">
            <!-- LEFT TABLE -->
            <div class="w-1/2 bg-white p-4 shadow rounded h-fit">
                <div class="flex justify-between items-center">
                    <h2 class="font-bold text-lg">Product List</h2>
                    <p class="text-lg">Total Products: {{ products.total }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border text-center">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="p-2">#</th>
                                <th class="p-2">Name</th>
                                <th class="p-2">Price</th>
                                <th class="p-2">Discount Price</th>
                                <th class="p-2">Qty</th>
                                <th class="p-2">Croy</th>
                                <th class="p-2">Dipo</th>
                                <th class="p-2">Dillar</th>
                                <th class="p-2">Sub Dillar</th>
                                <th class="p-2">Percentage</th>
                                <th class="p-2">Image</th>
                                <th class="p-2">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(p, i) in products.data" :key="p.id">
                                <td class="p-2">{{ i + 1 }}</td>
                                <td class="p-2">{{ p.name }}</td>
                                <td class="p-2">{{ p.price }}</td>
                                <td class="p-2">{{ p.discount_price }}</td>
                                <td class="p-2">{{ p.quantity }}</td>
                                <td class="p-2">{{ p.croy }}</td>
                                <td class="p-2">{{ p.dipo }}</td>
                                <td class="p-2">{{ p.dillar }}</td>
                                <td class="p-2">{{ p.sub_dillar }}</td>
                                <td class="p-2">{{ p.percentage }}</td>

                                <td class="p-2">
                                    <img
                                        v-if="p.image"
                                        :src="`/storage/${p.image}`"
                                        class="w-10 h-10 mx-auto"
                                    />
                                </td>

                                <td class="p-2">
                                    <button
                                        @click="editProduct(p)"
                                        class="bg-blue-500 text-white px-2 py-1"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button
                                        @click="deleteProduct(p.id)"
                                        class="bg-red-500 text-white px-2 py-1 ml-2"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="products.links" />
            </div>

            <!-- RIGHT FORM -->
            <div class="w-1/2 bg-white p-4 shadow rounded h-fit">
                <h2 class="font-bold mb-4">
                    {{ editMode ? "Edit Product" : "Add Product" }}
                </h2>

                <form @submit.prevent="submit" enctype="multipart/form-data">
                    <!-- NAME -->
                    <div class="mb-3">
                        <label for="name" class="form-label"
                            >Product name</label
                        >
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Name"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="croy" class="form-label">Croy</label>
                        <input
                            type="text"
                            v-model="form.croy"
                            placeholder="Croy"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.croy"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.croy }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="dipo" class="form-label">Dipo</label>
                        <input
                            type="text"
                            v-model="form.dipo"
                            placeholder="Dipo"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.dipo"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.dipo }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="dillar" class="form-label">Dillar</label>
                        <input
                            type="text"
                            v-model="form.dillar"
                            placeholder="Dillar"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.dillar"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.dillar }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="sub_dillar" class="form-label"
                            >Sub Dillar</label
                        >
                        <input
                            type="text"
                            v-model="form.sub_dillar"
                            placeholder="Sub Dillar"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.sub_dillar"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.sub_dillar }}
                        </div>
                    </div>

                    <!-- PRICE -->
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input
                            v-model="form.price"
                            placeholder="Price"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.price"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.price }}
                        </div>
                    </div>

                    <!-- DISCOUNT -->
                    <div class="mb-3">
                        <label for="discount_price" class="form-label"
                            >Discount Price</label
                        >
                        <input
                            v-model="form.discount_price"
                            placeholder="Discount Price"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.discount_price"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.discount_price }}
                        </div>
                    </div>

                    <!-- QUANTITY -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label"
                            >Quantity</label
                        >
                        <input
                            v-model="form.quantity"
                            placeholder="Quantity"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.quantity"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.quantity }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="percentage" class="form-label"
                            >Percentage</label
                        >
                        <input
                            v-model="form.percentage"
                            placeholder="Percentage"
                            class="border rounded w-full p-2 mb-1"
                        />
                        <div
                            v-if="form.errors.percentage"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.percentage }}
                        </div>
                    </div>

                    <!-- IMAGE -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input
                            type="file"
                            id="image"
                            @change="handleImage"
                            class="mb-2 border rounded p-2 w-full"
                        />
                        <div
                            v-if="form.errors.image"
                            class="text-red-500 text-sm mb-2"
                        >
                            {{ form.errors.image }}
                        </div>

                        <!-- PREVIEW -->
                        <img
                            v-if="preview"
                            :src="preview"
                            class="w-24 h-24 mb-3 border rounded"
                        />
                    </div>

                    <RichTextEditor
                        v-model="form.description"
                        label="Description"
                        placeholder="Enter product description"
                        :error="form.errors.description"
                    />

                    <!-- BUTTON -->
                    <div class="mt-4">
                        <button
                            class="bg-green-500 text-white px-4 py-2 rounded"
                            :disabled="form.processing"
                        >
                            {{ editMode ? "Update" : "Save" }}
                        </button>

                        <button
                            type="button"
                            @click="resetForm"
                            class="ml-2 bg-gray-400 text-white px-4 rounded py-2"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
