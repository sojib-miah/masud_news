<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    categories: Array,
    subcategories: Array,
});

const categoryForm = useForm({
    id: null,
    name: "",
});

const subCategoryForm = useForm({
    id: null,
    category_id: "",
    name: "",
});

const editCategory = (cat) => {
    categoryForm.id = cat.id;
    categoryForm.name = cat.name;
};

const updateCategory = () => {
    categoryForm.put(route("categories.update", categoryForm.id), {
        onSuccess: () => categoryForm.reset(),
    });
};

const editSubCategory = (sub) => {
    subCategoryForm.id = sub.id;
    subCategoryForm.name = sub.name;
    subCategoryForm.category_id = sub.category_id;
};

const updateSubCategory = () => {
    subCategoryForm.put(route("subcategories.update", subCategoryForm.id), {
        onSuccess: () => subCategoryForm.reset(),
    });
};
</script>

<template>
    <Layout>
        <Head title="Categories & Subcategories" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="flex gap-8 p-6">
            <div class="w-1/2 bg-white p-6 rounded-lg shadow h-fit">
                <h2 class="text-xl font-bold mb-4">Category</h2>

                <form
                    @submit.prevent="
                        categoryForm.id
                            ? updateCategory()
                            : categoryForm.post(route('categories.store'), {
                                  onSuccess: () => categoryForm.reset(),
                              })
                    "
                    class="flex gap-3 mb-6"
                >
                    <input
                        v-model="categoryForm.name"
                        type="text"
                        placeholder="Category Name"
                        class="border p-2 rounded w-full"
                    />
                    <span
                        class="text-red-600"
                        v-if="categoryForm.errors.name"
                        >{{ categoryForm.errors.name }}</span
                    >
                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded"
                    >
                        {{ categoryForm.id ? "Update" : "Save" }}
                    </button>
                </form>

                <table class="w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">Name</th>
                            <th class="p-2 border text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cat in categories" :key="cat.id">
                            <td class="p-2 border">{{ cat.name }}</td>
                            <td class="p-2 border text-center space-x-2">
                                <button
                                    @click="editCategory(cat)"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>

                                <button
                                    @click="
                                        categoryForm.delete(
                                            route('categories.destroy', cat.id),
                                        )
                                    "
                                    class="bg-red-600 text-white px-3 py-1 rounded"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="w-1/2 bg-white p-6 rounded-lg shadow h-fit">
                <h2 class="text-xl font-bold mb-4">Subcategory</h2>

                <form
                    @submit.prevent="
                        subCategoryForm.id
                            ? updateSubCategory()
                            : subCategoryForm.post(
                                  route('subcategories.store'),
                                  {
                                      onSuccess: () => subCategoryForm.reset(),
                                  },
                              )
                    "
                    class="flex gap-3 mb-6"
                >
                    <select
                        v-model="subCategoryForm.category_id"
                        class="border p-2 rounded"
                    >
                        <option disabled value="">Select Category</option>
                        <option
                            v-for="cat in categories"
                            :key="cat.id"
                            :value="cat.id"
                        >
                            {{ cat.name }}
                        </option>
                    </select>
                    <span
                        class="text-red-600"
                        v-if="subCategoryForm.errors.category_id"
                        >{{ subCategoryForm.errors.category_id }}</span
                    >
                    <input
                        v-model="subCategoryForm.name"
                        type="text"
                        placeholder="Subcategory Name"
                        class="border p-2 rounded w-full"
                    />
                    <span
                        class="text-red-600"
                        v-if="subCategoryForm.errors.name"
                        >{{ subCategoryForm.errors.name }}</span
                    >
                    <button
                        type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded"
                    >
                        {{ subCategoryForm.id ? "Update" : "Save" }}
                    </button>
                </form>

                <table class="w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border">Name</th>
                            <th class="p-2 border">Category</th>
                            <th class="p-2 border text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sub in subcategories" :key="sub.id">
                            <td class="p-2 border">{{ sub.name }}</td>
                            <td class="p-2 border">{{ sub.category?.name }}</td>
                            <td class="p-2 border text-center space-x-2">
                                <button
                                    @click="editSubCategory(sub)"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>

                                <button
                                    @click="
                                        subCategoryForm.delete(
                                            route(
                                                'subcategories.destroy',
                                                sub.id,
                                            ),
                                        )
                                    "
                                    class="bg-red-600 text-white px-3 py-1 rounded"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Layout>
</template>
