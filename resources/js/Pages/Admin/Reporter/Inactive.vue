<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import { route } from "ziggy-js";
import axios from "axios";
import { ref, watch, onMounted } from "vue";

// ─── State ───
const reporters = ref([]);
const search = ref("");
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
let debounceTimer = null;

// Modal
const modal = ref({
    show: false,
    loading: false,
    error: "",
    data: null,
});

// ─── Fetch list ───
async function fetchReporters(page = 1) {
    loading.value = true;
    try {
        const { data } = await axios.get(
            route("admin.reporters.inactive.list"),
            {
                params: { search: search.value, page },
            },
        );
        reporters.value = data.data;
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
        };
    } catch (e) {
        console.error("Failed to load reporters:", e);
    } finally {
        loading.value = false;
    }
}

// Debounced search
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchReporters(1), 350);
});

onMounted(() => fetchReporters(1));

// ─── Modal ───
async function openDetails(reporter) {
    modal.value = { show: true, loading: true, error: "", data: null };
    try {
        const { data } = await axios.get(
            route("admin.reporters.inactive.details", reporter.id),
        );
        modal.value.data = data;
    } catch (e) {
        modal.value.error = "বিস্তারিত লোড করা যায়নি। আবার চেষ্টা করুন।";
        console.error(e);
    } finally {
        modal.value.loading = false;
    }
}

function closeModal() {
    modal.value.show = false;
}

// ─── Helpers ───
function fmtDate(d) {
    if (!d) return "—";
    return new Date(d).toLocaleDateString("bn-BD", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}
function lastSeen(item) {
    if (!item.last_post_at) return "কখনো পোস্ট করেনি";
    const days = Math.floor(
        (Date.now() - new Date(item.last_post_at).getTime()) /
            (1000 * 60 * 60 * 24),
    );
    return `${days} দিন আগে`;
}
function imgUrl(path) {
    if (!path) return "/images/default.jpg";
    if (/^https?:\/\//i.test(path)) return path;
    return `/storage/${path}`;
}
</script>

<template>
    <Head title="Inactive Reporters" />
    <Layout>
        <div>
            <!-- Header -->
            <div class="mb-6">
                <h1
                    class="text-2xl sm:text-3xl font-bold text-slate-800 flex items-center gap-2"
                >
                    <i class="fa-solid fa-user-clock text-amber-500"></i>
                    নিষ্ক্রিয় রিপোর্টার
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    যেসব রিপোর্টার গত ৭ দিনে কোনো নিউজ পোস্ট করেননি।
                </p>
            </div>

            <!-- Toolbar -->
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
                        placeholder="নাম, ফোন বা ইমেইল দিয়ে সার্চ করুন..."
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
                                    Total News
                                </th>
                                <th
                                    class="text-left px-4 py-3 font-semibold text-slate-600"
                                >
                                    Last Post
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
                            <tr v-else-if="reporters.length === 0">
                                <td
                                    colspan="5"
                                    class="text-center py-10 text-slate-500"
                                >
                                    🎉 সব রিপোর্টার সক্রিয়! কোনো নিষ্ক্রিয়
                                    রিপোর্টার নেই।
                                </td>
                            </tr>
                            <tr
                                v-else
                                v-for="r in reporters"
                                :key="r.id"
                                class="border-b last:border-0 hover:bg-slate-50 transition"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-xs flex-shrink-0"
                                        >
                                            {{ (r.name || "?").charAt(0) }}
                                        </div>
                                        <div>
                                            <p
                                                class="font-medium text-slate-800"
                                            >
                                                {{ r.name }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ r.email || "—" }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ r.phone }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700"
                                    >
                                        {{ r.total_news }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1"
                                    >
                                        {{ lastSeen(r) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        @click="openDetails(r)"
                                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                        View
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
                <span>মোট {{ pagination.total }} জন</span>
                <div class="flex gap-2">
                    <button
                        :disabled="pagination.current_page <= 1"
                        @click="fetchReporters(pagination.current_page - 1)"
                        class="px-3 py-1.5 border border-slate-300 rounded-lg disabled:opacity-50 hover:bg-slate-100"
                    >
                        Prev
                    </button>
                    <span class="px-3 py-1.5"
                        >{{ pagination.current_page }} /
                        {{ pagination.last_page }}</span
                    >
                    <button
                        :disabled="
                            pagination.current_page >= pagination.last_page
                        "
                        @click="fetchReporters(pagination.current_page + 1)"
                        class="px-3 py-1.5 border border-slate-300 rounded-lg disabled:opacity-50 hover:bg-slate-100"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== DETAILS MODAL ==================== -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150"
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
                        class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col"
                    >
                        <div
                            class="h-1.5 w-full bg-gradient-to-r from-green-500 to-green-700 flex-shrink-0"
                        ></div>

                        <!-- Header -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-200"
                        >
                            <h3
                                class="text-lg font-bold text-slate-800 flex items-center gap-2"
                            >
                                <i
                                    class="fa-solid fa-id-card text-green-600"
                                ></i>
                                রিপোর্টার বিস্তারিত
                            </h3>
                            <button
                                @click="closeModal"
                                class="text-slate-400 hover:text-slate-600"
                            >
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="p-6 overflow-y-auto flex-1">
                            <div
                                v-if="modal.loading"
                                class="text-center py-16 text-slate-500"
                            >
                                <i
                                    class="fa-solid fa-spinner fa-spin text-2xl mb-2"
                                ></i>
                                <p class="text-sm">লোড হচ্ছে...</p>
                            </div>

                            <div
                                v-else-if="modal.error"
                                class="text-center py-16 text-red-500"
                            >
                                <i
                                    class="fa-solid fa-circle-exclamation text-2xl mb-2"
                                ></i>
                                <p class="text-sm">{{ modal.error }}</p>
                            </div>

                            <div v-else-if="modal.data" class="space-y-6">
                                <!-- User header -->
                                <div
                                    class="flex items-center gap-4 pb-4 border-b border-slate-100"
                                >
                                    <img
                                        :src="imgUrl(modal.data.user.image)"
                                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover border-2 border-green-500 flex-shrink-0"
                                        alt="photo"
                                        @error="
                                            (e) =>
                                                (e.target.src =
                                                    '/images/default.jpg')
                                        "
                                    />
                                    <div class="min-w-0">
                                        <h4
                                            class="text-xl font-bold text-slate-800 truncate"
                                        >
                                            {{ modal.data.user.name }}
                                        </h4>
                                        <p class="text-sm text-slate-500">
                                            {{ modal.data.user.email }}
                                        </p>
                                        <p class="text-sm text-slate-500">
                                            {{ modal.data.user.phone }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Stats -->
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-4 gap-3"
                                >
                                    <div
                                        class="bg-green-50 border border-green-200 rounded-lg p-3 text-center"
                                    >
                                        <p
                                            class="text-xs text-green-700 font-semibold"
                                        >
                                            Total News
                                        </p>
                                        <p
                                            class="text-lg font-bold text-green-800"
                                        >
                                            {{ modal.data.stats.total_news }}
                                        </p>
                                    </div>
                                    <div
                                        class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center"
                                    >
                                        <p
                                            class="text-xs text-blue-700 font-semibold"
                                        >
                                            Total Views
                                        </p>
                                        <p
                                            class="text-lg font-bold text-blue-800"
                                        >
                                            {{ modal.data.stats.total_views }}
                                        </p>
                                    </div>
                                    <div
                                        class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-center"
                                    >
                                        <p
                                            class="text-xs text-amber-700 font-semibold"
                                        >
                                            Last 7 Days
                                        </p>
                                        <p
                                            class="text-lg font-bold text-amber-800"
                                        >
                                            {{ modal.data.stats.posted_last_7 }}
                                        </p>
                                    </div>
                                    <div
                                        class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center"
                                    >
                                        <p
                                            class="text-xs text-purple-700 font-semibold"
                                        >
                                            Income
                                        </p>
                                        <p
                                            class="text-lg font-bold text-purple-800"
                                        >
                                            ৳
                                            {{
                                                modal.data.user.reporter_income
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Profile section -->
                                <div v-if="modal.data.profile">
                                    <h5
                                        class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                                    >
                                        <i class="fa-solid fa-user mr-1.5"></i>
                                        ব্যক্তিগত তথ্য
                                    </h5>
                                    <div
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm"
                                    >
                                        <div>
                                            <span class="text-slate-500"
                                                >জন্ম তারিখ:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile.dob ||
                                                    "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >রক্তের গ্রুপ:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        .blood_group || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >পিতার নাম:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        .father_name || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >মাতার নাম:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        .mother_name || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >শিক্ষাগত যোগ্যতা:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        .education || "—"
                                                }}
                                                ({{
                                                    modal.data.profile
                                                        .education_year || "—"
                                                }})</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >এনআইডি:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        .nid_number || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div class="sm:col-span-2">
                                            <span class="text-slate-500"
                                                >রিপোর্টার এরিয়া:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        .reporter_area || "—"
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div>
                                    <h5
                                        class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                                    >
                                        <i
                                            class="fa-solid fa-location-dot mr-1.5"
                                        ></i>
                                        লোকেশন
                                    </h5>
                                    <div
                                        class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-2 text-sm"
                                    >
                                        <div>
                                            <span class="text-slate-500"
                                                >বিভাগ:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        ?.division || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >জেলা:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        ?.district || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >উপজেলা:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        ?.upazila || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >ইউনিয়ন:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile?.union ||
                                                    "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >সিটি কর্পোরেশন:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        ?.city_corporation ||
                                                    "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >ওয়ার্ড:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile?.ward ||
                                                    "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >পৌরসভা:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        ?.pourashava || "—"
                                                }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >পৌরসভা ওয়ার্ড:
                                            </span>
                                            <span
                                                class="font-medium text-slate-800"
                                                >{{
                                                    modal.data.profile
                                                        ?.pourashava_ward || "—"
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Reporter Types -->
                                <div
                                    v-if="
                                        modal.data.types &&
                                        modal.data.types.length
                                    "
                                >
                                    <h5
                                        class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                                    >
                                        <i
                                            class="fa-solid fa-id-badge mr-1.5"
                                        ></i>
                                        রিপোর্টার টাইপ
                                    </h5>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="t in modal.data.types"
                                            :key="t.id"
                                            class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full"
                                        >
                                            {{ t.name }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Docs -->
                                <div>
                                    <h5
                                        class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                                    >
                                        <i
                                            class="fa-solid fa-file-arrow-up mr-1.5"
                                        ></i>
                                        ডকুমেন্টস
                                    </h5>
                                    <div class="flex flex-wrap gap-4">
                                        <div
                                            v-if="modal.data.profile?.signature"
                                            class="text-center"
                                        >
                                            <p
                                                class="text-xs text-slate-500 mb-1"
                                            >
                                                স্বাক্ষর
                                            </p>
                                            <img
                                                :src="
                                                    imgUrl(
                                                        modal.data.profile
                                                            .signature,
                                                    )
                                                "
                                                class="w-24 h-20 object-cover rounded border border-slate-300"
                                                alt="Signature"
                                            />
                                        </div>
                                        <div
                                            v-if="modal.data.profile?.nid_file"
                                            class="text-center"
                                        >
                                            <p
                                                class="text-xs text-slate-500 mb-1"
                                            >
                                                এনআইডি
                                            </p>
                                            <a
                                                :href="
                                                    imgUrl(
                                                        modal.data.profile
                                                            .nid_file,
                                                    )
                                                "
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-blue-600 underline text-xs"
                                            >
                                                <i class="fa-solid fa-file"></i>
                                                ফাইল দেখুন
                                            </a>
                                        </div>
                                        <div
                                            v-if="
                                                !modal.data.profile
                                                    ?.signature &&
                                                !modal.data.profile?.nid_file
                                            "
                                            class="text-sm text-slate-500"
                                        >
                                            কোনো ডকুমেন্ট আপলোড করা নেই।
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="px-6 py-4 border-t border-slate-100 flex justify-end"
                        >
                            <button
                                @click="closeModal"
                                class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg"
                            >
                                বন্ধ করুন
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </Layout>
</template>
