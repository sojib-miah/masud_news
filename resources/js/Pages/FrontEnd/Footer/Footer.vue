<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Layout from "@/Shared/Layout.vue";
import { route } from "ziggy-js";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    footers: Object,
});

const editing = ref(false);
const currentId = ref(null);

const form = useForm({
    som: "",
    pro: "",
    bab: "",
    nir: "",
    ban: "",
    phone1: "",
    phone2: "",
    email: "",
    web1: "",
    web2: "",
    copy: "",
});

function submit() {
    if (editing.value) {
        form.put(route("footers.update", currentId.value), {
            onSuccess: () => resetForm(),
        });
    } else {
        form.post(route("footers.store"), {
            onSuccess: () => resetForm(),
        });
    }
}

function edit(footer) {
    editing.value = true;
    currentId.value = footer.id;
    Object.assign(form, footer);
}

function remove(footer) {
    if (confirm("Are you sure you want to delete this footer?")) {
        router.delete(route("footers.destroy", footer.id));
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
        <Head title="Footer Management" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="flex gap-6">
            <!-- Table -->
            <div
                class="w-2/3 bg-white p-4 shadow rounded overflow-scroll h-fit"
            >
                <h2 class="text-lg font-semibold mb-4">Footer List</h2>
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-2 py-1">#</th>
                            <th class="px-2 py-1">সম্পাদক & প্রকাশক</th>
                            <!-- <th class="px-2 py-1">প্রকাশক</th> -->
                            <th class="px-2 py-1">ব্যবস্থাপনা সম্পাদক</th>
                            <th class="px-2 py-1">নির্বাহী সম্পাদক</th>
                            <th class="px-2 py-1">বাণিজ্যিক কার্যালয়</th>
                            <th class="px-2 py-1">ফোন</th>
                            <th class="px-2 py-1">ই-মেইল</th>
                            <th class="px-2 py-1">ওয়েবসাইট</th>
                            <th class="px-2 py-1">কপিরাইট</th>
                            <th class="px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(footer, i) in footers.data"
                            :key="footer.id"
                            class="border-t"
                        >
                            <td class="px-2 py-1">{{ i + 1 }}</td>
                            <td class="px-2 py-1 leading-6">
                                {{ footer.som }}
                            </td>
                            <!-- <td class="px-2 py-1 leading-6">{{ footer.pro }}</td> -->
                            <td class="px-2 py-1 leading-6">
                                {{ footer.bab }}
                            </td>
                            <td class="px-2 py-1 leading-6">
                                {{ footer.nir }}
                            </td>
                            <td class="px-2 py-1 leading-6">
                                {{ footer.ban }}
                            </td>
                            <td class="px-2 py-1 leading-6">
                                {{ footer.phone1 }}, {{ footer.phone2 }}
                            </td>
                            <td class="px-2 py-1 leading-6">
                                {{ footer.email }}
                            </td>
                            <td class="px-2 py-1 leading-6">
                                {{ footer.web1 }},{{ footer.web2 }}
                            </td>
                            <td class="px-2 py-1 leading-6">
                                {{ footer.copy }}
                            </td>
                            <td class="px-2 py-1 space-x-2">
                                <button
                                    @click="edit(footer)"
                                    class="text-blue-600"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button
                                    @click="remove(footer)"
                                    class="text-red-600"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Form -->
            <div class="w-1/3 bg-white p-4 shadow rounded">
                <h2 class="text-lg font-semibold mb-4">
                    {{ editing ? "Edit Footer" : "Create Footer" }}
                </h2>
                <form @submit.prevent="submit" class="space-y-3">
                    <div>
                        <label class="form-label">সম্পাদক & প্রকাশক</label>
                        <input
                            v-model="form.som"
                            placeholder="সম্পাদক & প্রকাশক..."
                            class="form-input"
                        />
                    </div>
                    <div style="display: none">
                        <label class="form-label">প্রকাশক</label>
                        <input
                            v-model="form.pro"
                            placeholder="প্রকাশক..."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">ব্যবস্থাপনা সম্পাদক</label>
                        <input
                            v-model="form.bab"
                            placeholder="ব্যবস্থাপনা সম্পাদক...."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">নির্বাহী সম্পাদক</label>
                        <input
                            v-model="form.nir"
                            placeholder="নির্বাহী সম্পাদক..."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">বাণিজ্যিক কার্যালয়</label>
                        <input
                            v-model="form.ban"
                            placeholder="বাণিজ্যিক কার্যালয়..."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input
                            v-model="form.phone1"
                            class="form-input"
                            placeholder="Phone one..."
                        />
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input
                            v-model="form.phone2"
                            placeholder="Phone two..."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input
                            v-model="form.email"
                            placeholder="Email..."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Website</label>
                        <input
                            v-model="form.web1"
                            placeholder="Web Site..."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Website</label>
                        <input
                            v-model="form.web2"
                            placeholder="Web Site...."
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">Copyright</label>
                        <input
                            v-model="form.copy"
                            placeholder="Copyright..."
                            class="form-input"
                        />
                    </div>

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
