<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { route } from "ziggy-js";
import Layout from "@/Shared/Layout.vue";
import { Head } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";

// ────────── State ──────────
const users = ref([]);
const search = ref("");
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
let debounceTimer = null;

// ────────── Change-password modal ──────────
const modal = ref({
    show: false,
    userId: null,
    userName: "",
    password: "",
    processing: false,
    error: "",
    success: "",
});

// ────────── Fetch users ──────────
async function fetchUsers(page = 1) {
    loading.value = true;
    try {
        const { data } = await axios.get(
            route("admin.users.changePassword.list"),
            {
                params: { search: search.value, page },
            },
        );
        users.value = data.data;
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
        };
    } catch (e) {
        console.error("Failed to load users:", e);
    } finally {
        loading.value = false;
    }
}

// Debounced search
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchUsers(1), 350);
});

onMounted(() => fetchUsers(1));

// ────────── Modal open/close ──────────
function openModal(user) {
    modal.value = {
        show: true,
        userId: user.id,
        userName: user.name,
        password: "",
        processing: false,
        error: "",
        success: "",
    };
}

function closeModal() {
    if (modal.value.processing) return;
    modal.value.show = false;
}

// ────────── Submit new password ──────────
async function submitPassword() {
    modal.value.error = "";
    modal.value.success = "";

    if (!modal.value.password || modal.value.password.length < 6) {
        modal.value.error = "পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।";
        return;
    }

    modal.value.processing = true;

    try {
        const { data } = await axios.post(
            route("admin.users.changePassword", modal.value.userId),
            {
                password: modal.value.password,
                password_confirmation: modal.value.password, // duplicate to satisfy 'confirmed'
            },
        );

        modal.value.success = data.message || "পাসওয়ার্ড পরিবর্তন হয়েছে।";
        modal.value.password = "";

        setTimeout(() => {
            modal.value.show = false;
        }, 800);
    } catch (e) {
        if (e.response?.status === 422) {
            const errs = e.response.data.errors || {};
            modal.value.error =
                errs.password?.[0] || "ভ্যালিডেশন ব্যর্থ হয়েছে।";
        } else {
            modal.value.error =
                "পাসওয়ার্ড পরিবর্তন ব্যর্থ হয়েছে। আবার চেষ্টা করুন।";
        }
    } finally {
        modal.value.processing = false;
    }
}
</script>

<template>
    <Head title="Users Change Password Management" />
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <Layout>
        <div>
            <!-- Header -->
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6"
            >
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Users Change Password Management
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        সব ইউজার দেখুন, সার্চ করুন, এবং পাসওয়ার্ড পরিবর্তন
                        করুন।
                    </p>
                </div>
            </div>

            <!-- Search box -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 sm:p-4 mb-4"
            >
                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                    ></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="নাম, ফোন, ইমেইল বা রোল দিয়ে সার্চ করুন..."
                        class="w-full border border-slate-300 rounded-lg pl-10 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    />
                </div>
            </div>

            <!-- Table -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="text-left px-4 py-3 font-semibold text-slate-600"
                                >
                                    #
                                </th>
                                <th
                                    class="text-left px-4 py-3 font-semibold text-slate-600"
                                >
                                    Name
                                </th>
                                <th
                                    class="text-left px-4 py-3 font-semibold text-slate-600"
                                >
                                    Phone
                                </th>
                                <th
                                    class="text-left px-4 py-3 font-semibold text-slate-600"
                                >
                                    Role
                                </th>
                                <th
                                    class="text-right px-4 py-3 font-semibold text-slate-600"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loading">
                                <td
                                    colspan="5"
                                    class="text-center py-10 text-slate-500"
                                >
                                    <i
                                        class="fa-solid fa-spinner fa-spin mr-2"
                                    ></i>
                                    Loading...
                                </td>
                            </tr>
                            <tr v-else-if="users.length === 0">
                                <td
                                    colspan="5"
                                    class="text-center py-10 text-slate-500"
                                >
                                    কোনো ইউজার পাওয়া যায়নি।
                                </td>
                            </tr>
                            <tr
                                v-else
                                v-for="(user, i) in users"
                                :key="user.id"
                                class="border-b last:border-0 hover:bg-slate-50 transition"
                            >
                                <td class="px-4 py-3 text-slate-500">
                                    {{
                                        (pagination.current_page - 1) * 20 +
                                        i +
                                        1
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-800"
                                >
                                    {{ user.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ user.phone }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700 capitalize"
                                    >
                                        {{ user.role || "user" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        @click="openModal(user)"
                                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition"
                                    >
                                        <i class="fa-solid fa-key"></i>
                                        Change Password
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.last_page > 1"
                class="flex justify-between items-center mt-4 text-sm text-slate-600"
            >
                <span>মোট {{ pagination.total }} জন ইউজার</span>
                <div class="flex gap-2">
                    <button
                        :disabled="pagination.current_page <= 1"
                        @click="fetchUsers(pagination.current_page - 1)"
                        class="px-3 py-1.5 border border-slate-300 rounded-lg disabled:opacity-50 hover:bg-slate-100"
                    >
                        Prev
                    </button>
                    <span class="px-3 py-1.5">
                        {{ pagination.current_page }} /
                        {{ pagination.last_page }}
                    </span>
                    <button
                        :disabled="
                            pagination.current_page >= pagination.last_page
                        "
                        @click="fetchUsers(pagination.current_page + 1)"
                        class="px-3 py-1.5 border border-slate-300 rounded-lg disabled:opacity-50 hover:bg-slate-100"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== MODAL ==================== -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="modal.show"
                    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                        @click="closeModal"
                    ></div>

                    <div
                        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                    >
                        <div
                            class="h-1.5 w-full bg-gradient-to-r from-green-500 to-green-700"
                        ></div>

                        <div class="p-6">
                            <div class="flex items-start gap-3 mb-5">
                                <div
                                    class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0"
                                >
                                    <i class="fa-solid fa-key text-xl"></i>
                                </div>
                                <div>
                                    <h3
                                        class="text-lg font-bold text-slate-800"
                                    >
                                        পাসওয়ার্ড পরিবর্তন
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <strong>{{ modal.userName }}</strong> এর
                                        নতুন পাসওয়ার্ড দিন
                                    </p>
                                </div>
                            </div>

                            <label
                                class="block text-sm font-semibold text-slate-700 mb-1"
                            >
                                নতুন পাসওয়ার্ড
                            </label>
                            <input
                                v-model="modal.password"
                                type="password"
                                placeholder="কমপক্ষে ৬ অক্ষর"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                @keyup.enter="submitPassword"
                            />

                            <p
                                v-if="modal.error"
                                class="text-red-500 text-xs mt-2"
                            >
                                <i
                                    class="fa-solid fa-circle-exclamation mr-1"
                                ></i>
                                {{ modal.error }}
                            </p>
                            <p
                                v-if="modal.success"
                                class="text-green-600 text-xs mt-2"
                            >
                                <i class="fa-solid fa-circle-check mr-1"></i>
                                {{ modal.success }}
                            </p>

                            <div class="flex gap-3 mt-6">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    :disabled="modal.processing"
                                    class="flex-1 px-4 py-2 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                                >
                                    বাতিল
                                </button>
                                <button
                                    type="button"
                                    @click="submitPassword"
                                    :disabled="modal.processing"
                                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold disabled:opacity-50 flex items-center justify-center gap-2"
                                >
                                    <i
                                        v-if="modal.processing"
                                        class="fa-solid fa-spinner fa-spin"
                                    ></i>
                                    {{
                                        modal.processing
                                            ? "সেভ হচ্ছে..."
                                            : "সেভ করুন"
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </Layout>
</template>
