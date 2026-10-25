<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Pagination from "@/Shared/Pagination.vue";
import { route } from "ziggy-js";

const props = defineProps({
    stocks: Object,
});

const editId = ref(null);

const form = useForm({
    amount: "",
});

const submit = () => {
    if (editId.value) {
        form.put(route("stock-money.update", editId.value), {
            preserveScroll: true,
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route("stock-money.store"), {
            preserveScroll: true,
            onSuccess: () => resetForm(),
        });
    }
};

const editStock = (stock) => {
    editId.value = stock.id;

    form.amount = stock.amount;
};

const resetForm = () => {
    editId.value = null;

    form.reset();
};

const deleteStock = (id) => {
    if (confirm("Delete this item?")) {
        form.delete(route("stock-money.destroy", id));
    }
};
</script>

<template>
    <Layout>
        <Head title="Stock Money" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="grid grid-cols-12 gap-5 p-5">
            <!-- LEFT -->
            <div class="col-span-8 bg-white shadow rounded p-5">
                <h2 class="text-2xl font-bold mb-5">Stock Money List</h2>

                <table class="w-full border">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border p-2">#</th>
                            <th class="border p-2">Amount</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(stock, index) in stocks.data"
                            :key="stock.id"
                        >
                            <td class="border p-2">
                                {{ index + 1 }}
                            </td>

                            <td class="border p-2">৳ {{ stock.amount }}</td>
                            <td class="border p-2">
                                <span
                                    :class="{
                                        'bg-green-500':
                                            stock.status === 'approved',
                                        'bg-yellow-500':
                                            stock.status === 'pending',
                                        'bg-red-500':
                                            stock.status === 'rejected',
                                    }"
                                    class="text-white px-3 py-1 rounded-full text-sm"
                                >
                                    {{ stock.status }}
                                </span>
                            </td>

                            <td class="border p-2">
                                <div class="flex gap-2 justify-center">
                                    <button
                                        @click="editStock(stock)"
                                        class="bg-blue-500 text-white px-3 py-1 rounded"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    <button
                                        @click="deleteStock(stock.id)"
                                        class="bg-red-500 text-white px-3 py-1 rounded"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <pagination :links="stocks.links" class="mt-4" />
            </div>

            <!-- RIGHT -->
            <div class="col-span-4 bg-white shadow rounded p-5">
                <h2 class="text-2xl font-bold mb-5">
                    {{ editId ? "Edit" : "Add" }} Stock Money
                </h2>

                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block mb-2"> Amount </label>

                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            class="border rounded w-full p-2"
                            placeholder="Amount"
                        />

                        <div
                            v-if="form.errors.amount"
                            class="text-red-500 text-sm"
                        >
                            {{ form.errors.amount }}
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="bg-green-600 text-white px-5 py-2 rounded"
                        >
                            {{ editId ? "Update" : "Save" }}
                        </button>

                        <button
                            v-if="editId"
                            type="button"
                            @click="resetForm"
                            class="bg-gray-500 text-white px-5 py-2 rounded"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
