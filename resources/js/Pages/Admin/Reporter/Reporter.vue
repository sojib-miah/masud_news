<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    reporters: Object,
    search: String,
});

const search = ref(props.search || "");

// onchange status
const form = useForm({});

const localreporters = ref([...props.reporters.data]);

watch(
    () => props.reporters.data,
    (newVal) => {
        localreporters.value = [...newVal];
    },
);

//  update status
const changeStatus = (reporter) => {
    form.transform(() => ({
        status: reporter.status,
    })).put(route("reporters.updateStatus", reporter.id), {
        preserveScroll: true,
        preserveState: true,
    });
};

// fetch
function fetchUsers() {
    router.get(
        "/admin/reporter",
        { search: search.value },
        { preserveState: true },
    );
}

function resetSearch() {
    router.get("/admin/reporter");
}

// highlight
function highlight(text) {
    if (!search.value) return text;

    const regex = new RegExp(`(${search.value})`, "gi");
    return text.replace(regex, '<span class="bg-yellow-300">$1</span>');
}

function deleteUser(id) {
    if (confirm("Are you sure you want to delete?")) {
        router.delete(`/admin/pathock/${id}`);
    }
}

const showModal = ref(false);
const selectedReporter = ref(null);

const openModal = (user) => {
    selectedReporter.value = user;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedReporter.value = null;
};
</script>

<template>
    <div>
        <Head title="Reporter Page" />

        <Layout>
            <Breadcrumb />
            <FlashMessage
                v-if="$page.props.flash?.status"
                :message="$page.props.flash.status"
                type="success"
            />
            <!-- Search -->
            <div class="flex justify-between items-center mb-4 w-full m-auto">
                <h2 class="text-xl font-bold">Reporter List</h2>
                <div>
                    <SearchFilter
                        v-model="search"
                        @search="fetchUsers"
                        @reset="resetSearch"
                    />
                </div>
                <p class="text-lg">Total Reporters: {{ reporters.total }}</p>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 border">#</th>
                            <th class="p-3 border">Name</th>
                            <th class="p-3 border">Email</th>
                            <th class="p-3 border">Role</th>
                            <th class="p-3 border">Reporter Types</th>
                            <th class="p-3 border">Status</th>
                            <th class="p-3 border">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(user, index) in reporters.data"
                            :key="user.id"
                        >
                            <td class="p-3 border">
                                {{ index + 1 }}
                            </td>
                            <td
                                class="p-3 border"
                                v-html="highlight(user.name)"
                            ></td>
                            <td
                                class="p-3 border"
                                v-html="highlight(user.email)"
                            ></td>
                            <td class="p-3 border">
                                {{ user.role }}
                            </td>
                            <td class="p-3 border">
                                <span
                                    v-for="(
                                        type, i
                                    ) in user.reporter_type_names"
                                    :key="i"
                                    class="bg-blue-100 text-blue-600 px-2 py-1 rounded mr-1 text-xs"
                                >
                                    {{ type }}
                                </span>
                            </td>
                            <td class="p-3 border">
                                <select
                                    v-model="user.status"
                                    @change="changeStatus(user)"
                                    class="border rounded px-2 py-1"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                </select>
                            </td>
                            <td class="p-3 border">
                                <button
                                    @click="openModal(user)"
                                    class="bg-blue-500 mr-2 text-white px-3 py-1 rounded"
                                >
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button
                                    @click="deleteUser(user.id)"
                                    class="bg-red-500 text-white px-3 py-1 rounded"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :links="reporters.links" class="mt-4" />
        </Layout>
    </div>

    <!-- Reporter Details Modal -->
    <div
        v-if="showModal"
        @click.self="closeModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
        <div
            class="bg-white w-full max-w-3xl rounded-lg shadow-lg p-6 relative max-h-[90vh] overflow-y-auto"
        >
            <!-- Close -->
            <button
                @click="closeModal"
                class="absolute top-2 right-3 text-2xl font-bold text-red-500"
            >
                ×
            </button>

            <h2 class="text-2xl font-bold mb-5 border-b pb-2">
                Reporter Details
            </h2>

            <div v-if="selectedReporter">
                <!-- Photo -->
                <div class="flex justify-center mb-5">
                    <img
                        :src="
                            selectedReporter.reporter_profile?.photo
                                ? `/storage/${selectedReporter.reporter_profile.photo}`
                                : '/storage/images/default.jpg'
                        "
                        class="w-32 h-32 rounded-full object-cover border"
                    />
                </div>

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p>
                            <strong>Name:</strong> {{ selectedReporter.name }}
                        </p>
                        <p>
                            <strong>Email:</strong> {{ selectedReporter.email }}
                        </p>
                        <p>
                            <strong>Role:</strong> {{ selectedReporter.role }}
                        </p>
                        <p>
                            <strong>Status:</strong>
                            {{ selectedReporter.status }}
                        </p>
                        <p>
                            <strong>Date of Birth:</strong>
                            {{
                                selectedReporter.reporter_profile?.dob || "N/A"
                            }}
                        </p>
                        <p>
                            <strong>Blood Group:</strong>
                            {{
                                selectedReporter.reporter_profile
                                    ?.blood_group || "N/A"
                            }}
                        </p>
                        <p>
                            <strong>Father Name:</strong>
                            {{
                                selectedReporter.reporter_profile
                                    ?.father_name || "N/A"
                            }}
                        </p>
                        <p>
                            <strong>Mother Name:</strong>
                            {{
                                selectedReporter.reporter_profile
                                    ?.mother_name || "N/A"
                            }}
                        </p>
                    </div>

                    <div>
                        <p>
                            <strong>Education:</strong>
                            {{
                                selectedReporter.reporter_profile?.education ||
                                "N/A"
                            }}
                        </p>

                        <p>
                            <strong>Education Year:</strong>
                            {{
                                selectedReporter.reporter_profile
                                    ?.education_year || "N/A"
                            }}
                        </p>

                        <p>
                            <strong>NID Number:</strong>
                            {{
                                selectedReporter.reporter_profile?.nid_number ||
                                "N/A"
                            }}
                        </p>

                        <p>
                            <strong>Division:</strong>
                            {{
                                selectedReporter.reporter_profile?.division_data
                                    .name || "N/A"
                            }}
                        </p>
                        <p>
                            <strong>District:</strong>
                            {{
                                selectedReporter.reporter_profile?.district_data
                                    .name || "N/A"
                            }}
                        </p>
                        <p>
                            <strong>Upazila:</strong>
                            {{
                                selectedReporter.reporter_profile?.upazila_data
                                    .name || "N/A"
                            }}
                        </p>

                        <p>
                            <strong>Union:</strong>
                            {{
                                selectedReporter.reporter_profile?.union_data
                                    .name || "N/A"
                            }}
                        </p>
                    </div>
                </div>

                <!-- Address -->
                <div class="mt-4">
                    <p>
                        <strong>Address:</strong>
                        {{
                            selectedReporter.reporter_profile?.address || "N/A"
                        }}
                    </p>

                    <p>
                        <strong>Reporter Area:</strong>
                        {{
                            selectedReporter.reporter_profile?.reporter_area ||
                            "N/A"
                        }}
                    </p>
                </div>

                <!-- Reporter Types -->
                <div class="mt-5">
                    <strong>Reporter Types:</strong>

                    <div class="mt-2 flex flex-wrap gap-2">
                        <span
                            v-for="(
                                type, i
                            ) in selectedReporter.reporter_type_names"
                            :key="i"
                            class="bg-blue-100 text-blue-600 px-3 py-1 rounded text-sm"
                        >
                            {{ type }}
                        </span>
                    </div>
                </div>

                <!-- Files -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
                    <div>
                        <p class="font-semibold mb-2">Signature</p>

                        <img
                            v-if="selectedReporter.reporter_profile?.signature"
                            :src="`/storage/${selectedReporter.reporter_profile.signature}`"
                            class="w-40 border rounded"
                        />
                    </div>

                    <div>
                        <p class="font-semibold mb-2">NID File</p>

                        <img
                            v-if="selectedReporter.reporter_profile?.nid_file"
                            :src="`/storage/${selectedReporter.reporter_profile.nid_file}`"
                            class="w-40 border rounded"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
