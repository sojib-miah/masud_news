<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";
import { ref, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
    divisions: { type: Array, default: () => [] },
    referralCode: { type: String, default: null },
});

const params = new URLSearchParams(window.location.search);
const refFromUrl = params.get("ref");

const form = useForm({
    name: "",
    phone: "",
    email: "",
    division_id: "",
    district_id: "",
    upazilla_id: "",
    union_id: "",
    city_corporation_id: "",
    ward_id: "",
    village: "",
    occupation: "",
    password: "",
    password_confirmation: "",
    ref: refFromUrl || props.referralCode || "",
});

// ==================== AJAX STATE ====================
const districts = ref([]);
const upazilas = ref([]);
const unions = ref([]);
const cityCorporations = ref([]);
const wards = ref([]);

const loadingDistricts = ref(false);
const loadingUpazilas = ref(false);
const loadingUnions = ref(false);
const loadingCityCorps = ref(false);
const loadingWards = ref(false);

// Which "mode" the user is in
const locationMode = computed(() => {
    if (form.city_corporation_id) return "city_corporation";
    if (form.district_id) return "district";
    return "";
});

// ==================== CASCADE WATCHERS ====================

// Division changes → clear children → load districts + city corps in parallel
watch(
    () => form.division_id,
    async (id) => {
        // Clear all descendants
        form.district_id = "";
        form.upazilla_id = "";
        form.union_id = "";
        form.city_corporation_id = "";
        form.ward_id = "";

        districts.value = [];
        upazilas.value = [];
        unions.value = [];
        cityCorporations.value = [];
        wards.value = [];

        if (!id) return;

        // Load districts AND city corporations in parallel
        loadingDistricts.value = true;
        loadingCityCorps.value = true;

        try {
            const [d, c] = await Promise.all([
                axios.get(`/districts/${id}`),
                axios.get(`/city-corporations/${id}`),
            ]);
            districts.value = d.data;
            cityCorporations.value = c.data;
        } catch (e) {
            console.error("Failed to load districts/city corps", e);
        } finally {
            loadingDistricts.value = false;
            loadingCityCorps.value = false;
        }
    },
);

// District changes → clear upazila + union + city corp → load upazilas
watch(
    () => form.district_id,
    async (id) => {
        form.upazilla_id = "";
        form.union_id = "";

        upazilas.value = [];
        unions.value = [];

        // If a district is selected, clear city corporation path
        if (id) {
            form.city_corporation_id = "";
            form.ward_id = "";
            wards.value = [];
        }

        if (!id) return;

        loadingUpazilas.value = true;
        try {
            const r = await axios.get(`/upazilas/${id}`);
            upazilas.value = r.data;
        } catch (e) {
            console.error("Failed to load upazilas", e);
        } finally {
            loadingUpazilas.value = false;
        }
    },
);

// Upazila changes → clear union → load unions
watch(
    () => form.upazilla_id,
    async (id) => {
        form.union_id = "";
        unions.value = [];

        if (!id) return;

        loadingUnions.value = true;
        try {
            const r = await axios.get(`/unions/${id}`);
            unions.value = r.data;
        } catch (e) {
            console.error("Failed to load unions", e);
        } finally {
            loadingUnions.value = false;
        }
    },
);

// City corporation changes → clear district path → load wards
watch(
    () => form.city_corporation_id,
    async (id) => {
        form.ward_id = "";
        wards.value = [];

        if (id) {
            form.district_id = "";
            form.upazilla_id = "";
            form.union_id = "";
            upazilas.value = [];
            unions.value = [];
        }

        if (!id) return;

        loadingWards.value = true;
        try {
            const r = await axios.get(`/wards/${id}`);
            wards.value = r.data;
        } catch (e) {
            console.error("Failed to load wards", e);
        } finally {
            loadingWards.value = false;
        }
    },
);

// ==================== SUBMIT ====================
function submit() {
    form.post(route("pathock.store"), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Layout>
        <Head title="Pathock Registration" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div
            class="min-h-screen py-10 px-3 bg-slate-50 flex justify-center items-start"
        >
            <div
                class="w-full max-w-[720px] bg-white border border-green-600 rounded-2xl shadow-xl overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 text-center"
                >
                    <h2 class="text-white text-xl sm:text-2xl font-bold">
                        পাঠক রেজিস্ট্রেশন
                    </h2>
                    <p class="text-green-100 text-xs mt-1">
                        আপনার তথ্য সঠিকভাবে পূরণ করুন
                    </p>
                </div>

                <form
                    class="p-4 sm:p-6 lg:p-8 space-y-5"
                    @submit.prevent="submit"
                >
                    <!-- Basic info -->
                    <section>
                        <h3
                            class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                        >
                            <i class="fa-solid fa-user mr-2"></i>ব্যক্তিগত তথ্য
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    পুরো নাম <span class="text-red-500">*</span>
                                </label>
                                <TextInput
                                    v-model="form.name"
                                    type="text"
                                    placeholder="পুরো নাম"
                                    :error="form.errors.name"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    ফোন নম্বর
                                    <span class="text-red-500">*</span>
                                </label>
                                <TextInput
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="01XXXXXXXXX"
                                    :error="form.errors.phone"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    ইমেইল
                                    <span class="text-slate-400 text-xs"
                                        >(ঐচ্ছিক)</span
                                    >
                                </label>
                                <TextInput
                                    v-model="form.email"
                                    type="email"
                                    placeholder="example@email.com"
                                    :error="form.errors.email"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Location -->
                    <section>
                        <h3
                            class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                        >
                            <i class="fa-solid fa-location-dot mr-2"></i>ঠিকানা
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Division -->
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    বিভাগ <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.division_id"
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm bg-white shadow-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                                >
                                    <option value="">
                                        — বিভাগ নির্বাচন করুন —
                                    </option>
                                    <option
                                        v-for="d in divisions"
                                        :key="d.id"
                                        :value="d.id"
                                    >
                                        {{ d.bn_name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.division_id"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.division_id }}
                                </p>
                            </div>

                            <div v-if="form.division_id" class="flex items-end">
                                <p class="text-xs text-slate-500 italic pb-2.5">
                                    <i
                                        class="fa-solid fa-info-circle text-green-600"
                                    ></i>
                                    জেলা <strong>অথবা</strong> সিটি কর্পোরেশন —
                                    যেকোনো একটি বেছে নিন
                                </p>
                            </div>
                        </div>

                        <!-- ==================== DISTRICT PATH ==================== -->
                        <div
                            v-if="
                                form.division_id &&
                                locationMode !== 'city_corporation'
                            "
                            class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200"
                        >
                            <p
                                class="text-xs font-semibold text-green-700 mb-3"
                            >
                                <i
                                    class="fa-solid fa-map-location-dot mr-1"
                                ></i>
                                জেলা ভিত্তিক ঠিকানা
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- District -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >জেলা</label
                                    >
                                    <select
                                        v-model="form.district_id"
                                        :disabled="loadingDistricts"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 disabled:opacity-60"
                                    >
                                        <option value="">
                                            {{
                                                loadingDistricts
                                                    ? "লোড হচ্ছে..."
                                                    : "— জেলা —"
                                            }}
                                        </option>
                                        <option
                                            v-for="d in districts"
                                            :key="d.id"
                                            :value="d.id"
                                        >
                                            {{ d.bn_name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.district_id"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.district_id }}
                                    </p>
                                </div>

                                <!-- Upazila -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >উপজেলা</label
                                    >
                                    <select
                                        v-model="form.upazilla_id"
                                        :disabled="
                                            !form.district_id || loadingUpazilas
                                        "
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                    >
                                        <option value="">
                                            {{
                                                loadingUpazilas
                                                    ? "লোড হচ্ছে..."
                                                    : "— উপজেলা —"
                                            }}
                                        </option>
                                        <option
                                            v-for="u in upazilas"
                                            :key="u.id"
                                            :value="u.id"
                                        >
                                            {{ u.bn_name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.upazilla_id"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.upazilla_id }}
                                    </p>
                                </div>

                                <!-- Union -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >ইউনিয়ন</label
                                    >
                                    <select
                                        v-model="form.union_id"
                                        :disabled="
                                            !form.upazilla_id || loadingUnions
                                        "
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                    >
                                        <option value="">
                                            {{
                                                loadingUnions
                                                    ? "লোড হচ্ছে..."
                                                    : "— ইউনিয়ন —"
                                            }}
                                        </option>
                                        <option
                                            v-for="u in unions"
                                            :key="u.id"
                                            :value="u.id"
                                        >
                                            {{ u.bn_name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.union_id"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.union_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ==================== CITY CORPORATION PATH ==================== -->
                        <div
                            v-if="
                                form.division_id && locationMode !== 'district'
                            "
                            class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200"
                        >
                            <p class="text-xs font-semibold text-blue-700 mb-3">
                                <i class="fa-solid fa-city mr-1"></i>
                                সিটি কর্পোরেশন ভিত্তিক ঠিকানা
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- City Corp -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >সিটি কর্পোরেশন</label
                                    >
                                    <select
                                        v-model="form.city_corporation_id"
                                        :disabled="loadingCityCorps"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-60"
                                    >
                                        <option value="">
                                            {{
                                                loadingCityCorps
                                                    ? "লোড হচ্ছে..."
                                                    : "— সিটি কর্পোরেশন —"
                                            }}
                                        </option>
                                        <option
                                            v-for="c in cityCorporations"
                                            :key="c.id"
                                            :value="c.id"
                                        >
                                            {{ c.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.city_corporation_id"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.city_corporation_id }}
                                    </p>
                                </div>

                                <!-- Ward -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >ওয়ার্ড</label
                                    >
                                    <select
                                        v-model="form.ward_id"
                                        :disabled="
                                            !form.city_corporation_id ||
                                            loadingWards
                                        "
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 disabled:opacity-60 disabled:cursor-not-allowed"
                                    >
                                        <option value="">
                                            {{
                                                loadingWards
                                                    ? "লোড হচ্ছে..."
                                                    : "— ওয়ার্ড —"
                                            }}
                                        </option>
                                        <option
                                            v-for="w in wards"
                                            :key="w.id"
                                            :value="w.id"
                                        >
                                            {{ w.name }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.ward_id"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.ward_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Other location info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                    >গ্রাম / এলাকা</label
                                >
                                <TextInput
                                    v-model="form.village"
                                    type="text"
                                    placeholder="গ্রাম"
                                    :error="form.errors.village"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                    >পেশা</label
                                >
                                <TextInput
                                    v-model="form.occupation"
                                    type="text"
                                    placeholder="পেশা"
                                    :error="form.errors.occupation"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Password -->
                    <section>
                        <h3
                            class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                        >
                            <i class="fa-solid fa-lock mr-2"></i>নিরাপত্তা
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    পাসওয়ার্ড
                                    <span class="text-red-500">*</span>
                                </label>
                                <TextInput
                                    v-model="form.password"
                                    type="password"
                                    placeholder="কমপক্ষে ৬ অক্ষর"
                                    :error="form.errors.password"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    পাসওয়ার্ড নিশ্চিত করুন
                                    <span class="text-red-500">*</span>
                                </label>
                                <TextInput
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="পুনরায় লিখুন"
                                    :error="form.errors.password_confirmation"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- Submit -->
                    <div class="pt-3 border-t border-slate-200">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 rounded-lg shadow-lg transition disabled:opacity-60 flex items-center justify-center gap-2"
                        >
                            <i
                                v-if="form.processing"
                                class="fa-solid fa-spinner fa-spin"
                            ></i>
                            {{
                                form.processing
                                    ? "প্রেরণ করা হচ্ছে..."
                                    : "রেজিস্ট্রেশন করুন"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>
