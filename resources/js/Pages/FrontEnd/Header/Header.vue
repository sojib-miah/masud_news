<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Layout from "@/Shared/Layout.vue";
import { route } from "ziggy-js";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    headers: Object,
});

const editing = ref(false);
const currentId = ref(null);

const form = useForm({
    youtube: "",
    linkdin: "",
    instagram: "",
    facebook: "",
    twitter: "",
    ad: null,
});

function submit() {
    if (editing.value) {
        form.transform((data) => ({
            ...data,
            _method: "put",
        })).post(route("headers.update", { header: currentId.value }), {
            forceFormData: true,
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route("headers.store"), {
            forceFormData: true,
            onSuccess: () => resetForm(),
        });
    }
}

function edit(header) {
    editing.value = true;
    currentId.value = header.id;
    form.youtube = header.youtube;
    form.linkdin = header.linkdin;
    form.instagram = header.instagram;
    form.facebook = header.facebook;
    form.twitter = header.twitter;
    form.ad = null;
}

function remove(header) {
    if (confirm("Are you sure you want to delete this header?")) {
        router.delete(route("headers.destroy", header.id));
    }
}

function resetForm() {
    editing.value = false;
    currentId.value = null;
    form.reset();
}
</script>

<template>
    <Layout>
        <Breadcrumb />
        <Head title="Header Management" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="flex gap-6">
            <div
                class="w-2/3 bg-white p-4 shadow rounded overflow-scroll h-fit"
            >
                <h2 class="text-lg font-semibold mb-4">Header List</h2>
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-2 py-1">#</th>
                            <th class="px-2 py-1">Youtube</th>
                            <th class="px-2 py-1">Linkdin</th>
                            <th class="px-2 py-1">Instagram</th>
                            <th class="px-2 py-1">Facebook</th>
                            <th class="px-2 py-1">Twitter</th>
                            <th class="px-2 py-1">Ad</th>
                            <th class="px-2 py-1">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(header, i) in headers.data"
                            :key="header.id"
                            class="border-t"
                        >
                            <td class="px-2 py-1">{{ i + 1 }}</td>
                            <td class="px-2 py-1">
                                <a :href="header.youtube" target="_blank"
                                    >Youtube</a
                                >
                            </td>
                            <td class="px-2 py-1">
                                <a :href="header.linkdin" target="_blank"
                                    >Linkdin</a
                                >
                            </td>
                            <td class="px-2 py-1">
                                <a :href="header.instagram" target="_blank"
                                    >Instagram</a
                                >
                            </td>
                            <td class="px-2 py-1">
                                <a :href="header.facebook" target="_blank"
                                    >Facebook</a
                                >
                            </td>
                            <td class="px-2 py-1">
                                <a :href="header.twitter" target="_blank"
                                    >Twitter</a
                                >
                            </td>
                            <td class="px-2 py-1">
                                <div v-if="header.ad">
                                    <img
                                        :src="`/storage/${header.ad}`"
                                        alt="Ad"
                                        class="h-12 w-auto rounded"
                                    />
                                </div>
                            </td>

                            <td class="px-2 py-1 space-x-2">
                                <button
                                    @click="edit(header)"
                                    class="text-blue-600"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    @click="remove(header)"
                                    class="text-red-600"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="w-1/3 bg-white p-4 shadow rounded">
                <h2 class="text-lg font-semibold mb-4">
                    {{ editing ? "Edit Header" : "Create Header" }}
                </h2>
                <form @submit.prevent="submit" class="space-y-3">
                    <div>
                        <label class="form-label">Youtube</label>
                        <input
                            v-model="form.youtube"
                            placeholder="Youtube"
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Linkdin</label>
                        <input
                            v-model="form.linkdin"
                            placeholder="Linkdin"
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Instagram</label>
                        <input
                            v-model="form.instagram"
                            placeholder="Instagram"
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Facebook</label>
                        <input
                            v-model="form.facebook"
                            placeholder="Facebook"
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Twitter</label>
                        <input
                            v-model="form.twitter"
                            placeholder="Twitter"
                            class="form-input"
                        />
                    </div>

                    <div v-if="editing && currentId">
                        <p class="text-sm text-gray-500">Current Image:</p>
                        <img
                            :src="`/storage/${headers.data.find((h) => h.id === currentId)?.ad}`"
                            alt="Current Ad"
                            class="h-16 w-auto rounded border mb-2"
                        />
                    </div>

                    <FileInput
                        v-model="form.ad"
                        label="Upload New Advertize Image"
                        accept="image/*"
                        :multiple="false"
                        :error="form.errors.ad"
                    />

                    <button
                        type="submit"
                        class="btn-success flex justify-center items-center gap-4"
                        :class="{ 'btn-spinner': form.processing }"
                        :disabled="form.processing"
                    >
                        <span>
                            {{
                                form.processing
                                    ? "Processing..."
                                    : editing
                                      ? "Update"
                                      : "Create"
                            }}
                        </span>
                        <span v-if="form.processing" class="spinner"></span>
                    </button>
                </form>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
a {
    color: rgb(0, 205, 0);
    text-decoration: underline;
}
</style>
