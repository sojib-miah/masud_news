<script setup>
import { ref } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";

const props = defineProps({
    shops: Object,
});

const form = ref({
    id: null,
    name: "",
    location: "",
});

const editMode = ref(false);

// Select shop for edit
const editShop = (shop) => {
    form.value = { ...shop };
    editMode.value = true;
};

// Reset form
const resetForm = () => {
    form.value = { id: null, name: "", location: "" };
    editMode.value = false;
};

// Submit form
const submit = () => {
    if (editMode.value) {
        router.put(`/admin/shops/${form.value.id}`, form.value, {
            onSuccess: resetForm,
        });
    } else {
        router.post("/admin/shops", form.value, {
            onSuccess: resetForm,
        });
    }
};

// Delete
const deleteShop = (id) => {
    if (confirm("Are you sure?")) {
        router.delete(`/admin/shops/${id}`);
    }
};
</script>

<template>
    <div>
        <Head title="Shop Page" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <Layout>
            <Breadcrumb />
            <div class="flex gap-6 p-6">
                <!-- LEFT: LIST -->
                <div class="w-1/2 bg-white shadow p-4 rounded">
                    <h2 class="text-lg font-bold mb-4">Shop List</h2>

                    <table class="w-full border">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="p-2 border">#</th>
                                <th class="p-2 border">Name</th>
                                <th class="p-2 border">Location</th>
                                <th class="p-2 border">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(shop, index) in shops.data"
                                :key="shop.id"
                                class="text-center"
                            >
                                <td class="border p-2">{{ index + 1 }}</td>
                                <td class="border p-2">{{ shop.name }}</td>
                                <td class="border p-2">{{ shop.location }}</td>
                                <td class="border p-2">
                                    <button
                                        @click="editShop(shop)"
                                        class="bg-blue-500 text-white px-2 py-1 mr-2"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        @click="deleteShop(shop.id)"
                                        class="bg-red-500 text-white px-2 py-1"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <Pagination :links="shops.links" />
                </div>

                <!-- RIGHT: FORM -->
                <div class="w-1/2 bg-white shadow p-4 rounded">
                    <h2 class="text-lg font-bold mb-4">
                        {{ editMode ? "Edit Shop" : "Add Shop" }}
                    </h2>

                    <form @submit.prevent="submit">
                        <div class="mb-3">
                            <label>Name</label>
                            <input
                                v-model="form.name"
                                class="border w-full p-2"
                                required
                            />
                        </div>

                        <div class="mb-3">
                            <label>Location</label>
                            <input
                                v-model="form.location"
                                class="border w-full p-2"
                                required
                            />
                        </div>

                        <button class="bg-green-500 text-white px-4 py-2">
                            {{ editMode ? "Update" : "Save" }}
                        </button>

                        <button
                            type="button"
                            @click="resetForm"
                            class="ml-2 bg-gray-400 text-white px-4 py-2"
                        >
                            Reset
                        </button>
                    </form>
                </div>
            </div>
        </Layout>
    </div>
</template>
