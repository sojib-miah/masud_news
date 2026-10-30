<script setup>
import { Head, useForm, usePage } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import FileInput from "@/Shared/FileInput.vue";
import { route } from "ziggy-js";
import { ref, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
    bloodGroups: { type: Array, default: () => [] },
    educations: { type: Array, default: () => [] },
    educationals: { type: Array, default: () => [] },
    divisions: { type: Array, default: () => [] },
    reporterAreas: { type: Array, default: () => [] },
    reporterTypes: { type: Array, default: () => [] },
});

const page = usePage();
const registerOpen = computed(() => page.props.site?.register_status ?? true);

// ==================== FORM ====================
const form = useForm({
    name: "",
    email: "",
    phone: "",
    dob: "",
    blood_group: "",
    father_name: "",
    mother_name: "",
    address: "",
    education: "",
    education_year: "",
    division: "",
    district: "",
    upazila: "",
    union: "",
    city_corporation: "",
    ward: "",
    village: "",
    reporter_area: "",
    nid_number: "",
    photo: null,
    signature: null,
    nid_file: null,
    password: "",
    password_confirmation: "",
    reporter_types: [], // single-select UI pushes one ID into this array
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

const locationMode = computed(() => {
    if (form.city_corporation) return "city_corporation";
    if (form.district) return "district";
    return "";
});

// ==================== CASCADE WATCHERS ====================

// Division change → clear everything below → load districts + city corps
watch(
    () => form.division,
    async (id) => {
        form.district = "";
        form.upazila = "";
        form.union = "";
        form.city_corporation = "";
        form.ward = "";

        districts.value = [];
        upazilas.value = [];
        unions.value = [];
        cityCorporations.value = [];
        wards.value = [];

        if (!id) return;

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
            console.error(e);
        } finally {
            loadingDistricts.value = false;
            loadingCityCorps.value = false;
        }
    },
);

// District change → clear upazila/union/city corp → load upazilas
watch(
    () => form.district,
    async (id) => {
        form.upazila = "";
        form.union = "";
        upazilas.value = [];
        unions.value = [];

        if (id) {
            form.city_corporation = "";
            form.ward = "";
            wards.value = [];
        }

        if (!id) return;

        loadingUpazilas.value = true;
        try {
            const r = await axios.get(`/upazilas/${id}`);
            upazilas.value = r.data;
        } finally {
            loadingUpazilas.value = false;
        }
    },
);

// Upazila change → clear union → load unions
watch(
    () => form.upazila,
    async (id) => {
        form.union = "";
        unions.value = [];
        if (!id) return;

        loadingUnions.value = true;
        try {
            const r = await axios.get(`/unions/${id}`);
            unions.value = r.data;
        } finally {
            loadingUnions.value = false;
        }
    },
);

// City corporation change → clear district path → load wards
watch(
    () => form.city_corporation,
    async (id) => {
        form.ward = "";
        wards.value = [];

        if (id) {
            form.district = "";
            form.upazila = "";
            form.union = "";
            upazilas.value = [];
            unions.value = [];
        }

        if (!id) return;

        loadingWards.value = true;
        try {
            const r = await axios.get(`/wards/${id}`);
            wards.value = r.data;
        } finally {
            loadingWards.value = false;
        }
    },
);

// ==================== REPORTER TYPE (SINGLE-SELECT) ====================
const selectedReporterType = computed({
    get: () => form.reporter_types[0] ?? null,
    set: (val) => {
        form.reporter_types = val ? [val] : [];
    },
});

function toggleReporterType(id) {
    if (selectedReporterType.value === id) {
        form.reporter_types = [];
    } else {
        form.reporter_types = [id];
    }
}

// ==================== SUBMIT ====================
function submit() {
    form.post(route("reporter.store"), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Layout>
        <Head title="Reporter Registration" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div
            class="min-h-screen py-10 px-3 bg-slate-50 flex justify-center items-start"
        >
            <div
                class="w-full max-w-[820px] bg-white border border-green-600 rounded-2xl shadow-xl overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 text-center"
                >
                    <h2 class="text-white text-xl sm:text-2xl font-bold">
                        রিপোর্টার রেজিস্ট্রেশন
                    </h2>
                    <p class="text-green-100 text-xs sm:text-sm mt-1">
                        সঠিক তথ্য দিয়ে ফর্মটি পূরণ করুন
                    </p>
                </div>

                <div v-if="registerOpen">
                    <form
                        class="p-4 sm:p-6 lg:p-8 space-y-5"
                        @submit.prevent="submit"
                    >
                        <!-- ==================== BASIC INFO ==================== -->
                        <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-user mr-2"></i>ব্যক্তিগত
                                তথ্য
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        পুরো নাম
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <TextInput
                                        v-model="form.name"
                                        type="text"
                                        placeholder="বাংলায় পুরো নাম লিখুন"
                                        :error="form.errors.name"
                                    />
                                    <p class="text-xs text-red-500 mt-1">
                                        ! অনুগ্রহ করে বাংলা অক্ষরে লিখুন
                                    </p>
                                </div>

                                <div>
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

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        জন্ম তারিখ
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="form.dob"
                                        class="w-full border border-gray-400 shadow-sm rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 transition-all duration-200"
                                    />
                                    <div
                                        v-if="form.errors.dob"
                                        class="text-red-500 text-xs"
                                    >
                                        {{ form.errors.dob }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        রক্তের গ্রুপ
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <SelectInput
                                        v-model="form.blood_group"
                                        placeholder="রক্তের গ্রুপ"
                                        :options="
                                            bloodGroups.map((bg) => ({
                                                label: bg.name,
                                                value: bg.name,
                                            }))
                                        "
                                        :error="form.errors.blood_group"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        পিতার নাম
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <TextInput
                                        v-model="form.father_name"
                                        type="text"
                                        placeholder="পিতার নাম"
                                        :error="form.errors.father_name"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        মাতার নাম
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <TextInput
                                        v-model="form.mother_name"
                                        type="text"
                                        placeholder="মাতার নাম"
                                        :error="form.errors.mother_name"
                                    />
                                </div>

                                <!-- <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        ঠিকানা
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <TextInput
                                        v-model="form.address"
                                        type="text"
                                        placeholder="স্থায়ী ঠিকানা"
                                        :error="form.errors.address"
                                    />
                                </div> -->

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        শিক্ষাগত যোগ্যতা
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <SelectInput
                                        v-model="form.education"
                                        placeholder="শিক্ষাগত যোগ্যতা"
                                        :options="
                                            educations.map((e) => ({
                                                label: e.name,
                                                value: e.name,
                                            }))
                                        "
                                        :error="form.errors.education"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        শিক্ষাবর্ষ
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <SelectInput
                                        v-model="form.education_year"
                                        placeholder="শিক্ষাবর্ষ"
                                        :options="
                                            educationals.map((e) => ({
                                                label: e.year,
                                                value: e.year,
                                            }))
                                        "
                                        :error="form.errors.education_year"
                                    />
                                </div>
                            </div>
                        </section>

                        <!-- ==================== LOCATION ==================== -->
                        <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-location-dot mr-2"></i
                                >ঠিকানা (বিস্তারিত)
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        বিভাগ
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <SelectInput
                                        v-model="form.division"
                                        :options="
                                            divisions.map((d) => ({
                                                label: d.bn_name,
                                                value: d.id,
                                            }))
                                        "
                                        placeholder="বিভাগ"
                                        :error="form.errors.division"
                                    />
                                </div>

                                <div
                                    v-if="form.division"
                                    class="flex items-end"
                                >
                                    <p
                                        class="text-xs text-slate-500 italic pb-2.5"
                                    >
                                        <i
                                            class="fa-solid fa-info-circle text-green-600"
                                        ></i>
                                        জেলা <strong>অথবা</strong> সিটি
                                        কর্পোরেশন — যেকোনো একটি বেছে নিন
                                    </p>
                                </div>
                            </div>

                            <!-- DISTRICT PATH -->
                            <div
                                v-if="
                                    form.division &&
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

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-3 gap-3"
                                >
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-600 mb-1"
                                            >জেলা</label
                                        >
                                        <SelectInput
                                            v-model="form.district"
                                            :options="
                                                districts.map((d) => ({
                                                    label: d.bn_name,
                                                    value: d.id,
                                                }))
                                            "
                                            :placeholder="
                                                loadingDistricts
                                                    ? 'লোড হচ্ছে...'
                                                    : 'জেলা'
                                            "
                                            :error="form.errors.district"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-600 mb-1"
                                            >উপজেলা</label
                                        >
                                        <SelectInput
                                            v-model="form.upazila"
                                            :options="
                                                upazilas.map((u) => ({
                                                    label: u.bn_name,
                                                    value: u.id,
                                                }))
                                            "
                                            :placeholder="
                                                loadingUpazilas
                                                    ? 'লোড হচ্ছে...'
                                                    : 'উপজেলা'
                                            "
                                            :error="form.errors.upazila"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-600 mb-1"
                                            >ইউনিয়ন</label
                                        >
                                        <SelectInput
                                            v-model="form.union"
                                            :options="
                                                unions.map((u) => ({
                                                    label: u.bn_name,
                                                    value: u.id,
                                                }))
                                            "
                                            :placeholder="
                                                loadingUnions
                                                    ? 'লোড হচ্ছে...'
                                                    : 'ইউনিয়ন'
                                            "
                                            :error="form.errors.union"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- CITY CORPORATION PATH -->
                            <div
                                v-if="
                                    form.division && locationMode !== 'district'
                                "
                                class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200"
                            >
                                <p
                                    class="text-xs font-semibold text-blue-700 mb-3"
                                >
                                    <i class="fa-solid fa-city mr-1"></i>
                                    সিটি কর্পোরেশন ভিত্তিক ঠিকানা
                                </p>

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-3"
                                >
                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-600 mb-1"
                                            >সিটি কর্পোরেশন</label
                                        >
                                        <SelectInput
                                            v-model="form.city_corporation"
                                            :options="
                                                cityCorporations.map((c) => ({
                                                    label: c.name,
                                                    value: c.id,
                                                }))
                                            "
                                            :placeholder="
                                                loadingCityCorps
                                                    ? 'লোড হচ্ছে...'
                                                    : 'সিটি কর্পোরেশন'
                                            "
                                            :error="
                                                form.errors.city_corporation
                                            "
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-semibold text-slate-600 mb-1"
                                            >ওয়ার্ড</label
                                        >
                                        <SelectInput
                                            v-model="form.ward"
                                            :options="
                                                wards.map((w) => ({
                                                    label: w.name,
                                                    value: w.id,
                                                }))
                                            "
                                            :placeholder="
                                                loadingWards
                                                    ? 'লোড হচ্ছে...'
                                                    : 'ওয়ার্ড'
                                            "
                                            :error="form.errors.ward"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4"
                            >
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
                                    >
                                        রিপোর্টার এরিয়া
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <SelectInput
                                        v-model="form.reporter_area"
                                        :options="
                                            reporterAreas.map((r) => ({
                                                label: r.name,
                                                value: r.id,
                                            }))
                                        "
                                        placeholder="রিপোর্টার এরিয়া"
                                        :error="form.errors.reporter_area"
                                    />
                                </div>
                            </div>
                        </section>

                        <!-- ==================== REPORTER TYPE ==================== -->
                        <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-id-badge mr-2"></i
                                >রিপোর্টার টাইপ
                            </h3>

                            <p class="text-xs text-slate-500 mb-3">
                                <i
                                    class="fa-solid fa-info-circle text-green-600"
                                ></i>
                                শুধুমাত্র একটি টাইপ নির্বাচন করুন
                            </p>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"
                            >
                                <button
                                    v-for="type in reporterTypes"
                                    :key="type.id"
                                    type="button"
                                    class="reporter-type-btn"
                                    :class="{
                                        active:
                                            selectedReporterType === type.id,
                                    }"
                                    @click="toggleReporterType(type.id)"
                                >
                                    <i
                                        :class="
                                            selectedReporterType === type.id
                                                ? 'fa-solid fa-circle-check'
                                                : 'fa-regular fa-circle'
                                        "
                                    ></i>
                                    {{ type.name }}
                                </button>
                            </div>
                            <p
                                v-if="form.errors.reporter_types"
                                class="text-red-500 text-xs mt-2"
                            >
                                {{ form.errors.reporter_types }}
                            </p>
                        </section>

                        <!-- ==================== SECURITY ==================== -->
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
                                        :error="
                                            form.errors.password_confirmation
                                        "
                                    />
                                </div>
                            </div>
                        </section>

                        <!-- ==================== DOCUMENTS ==================== -->
                        <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-file-arrow-up mr-2"></i
                                >প্রয়োজনীয় কাগজপত্র
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        প্রোফাইল ছবি
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <FileInput
                                        v-model="form.photo"
                                        accept="image/*"
                                        :error="form.errors.photo"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        স্বাক্ষর
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <FileInput
                                        v-model="form.signature"
                                        accept="image/*"
                                        :error="form.errors.signature"
                                    />
                                </div>

                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        এনআইডি / পাসপোর্ট / জন্ম নিবন্ধন
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <FileInput
                                        v-model="form.nid_file"
                                        accept="image/*,application/pdf"
                                        :error="form.errors.nid_file"
                                    />
                                    <p class="text-xs text-slate-500 mt-1">
                                        <i
                                            class="fa-solid fa-info-circle text-green-600"
                                        ></i>
                                        এনআইডি কার্ডের সামনের ও পিছনের ছবি
                                        একসাথে আপলোড করুন (PDF বা ছবি)
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-1"
                                    >
                                        এনআইডি নম্বর
                                    </label>
                                    <TextInput
                                        v-model="form.nid_number"
                                        type="text"
                                        placeholder="এনআইডি নম্বর"
                                        :error="form.errors.nid_number"
                                    />
                                </div>
                            </div>
                        </section>

                        <!-- ==================== SUBMIT ==================== -->
                        <div class="pt-3 border-t border-slate-200">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 rounded-lg shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
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

                            <p class="text-center text-xs text-slate-500 mt-3">
                                ইতিমধ্যে অ্যাকাউন্ট আছে?
                                <a
                                    :href="route('user.login')"
                                    class="text-green-600 font-semibold hover:underline"
                                >
                                    লগইন করুন
                                </a>
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Registration Closed -->
                <div v-else class="p-8 text-center">
                    <i
                        class="fa-solid fa-lock text-4xl text-slate-300 mb-3"
                    ></i>
                    <p class="text-slate-600 font-semibold mb-2">
                        রেজিস্ট্রেশন বন্ধ আছে
                    </p>
                    <p class="text-sm text-slate-500">
                        রেজিস্ট্রেশন চালু করতে পেমেন্ট করুন
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.reporter-type-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition:
        background 0.2s,
        border-color 0.2s,
        color 0.2s,
        transform 0.15s;
    font-family: inherit;
    text-align: left;
}

.reporter-type-btn:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #00930c;
}

.reporter-type-btn.active {
    background: linear-gradient(135deg, #00930c, #00b515);
    border-color: #00930c;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.25);
    transform: translateY(-1px);
}

.reporter-type-btn i {
    font-size: 0.9rem;
    flex-shrink: 0;
}

@media (prefers-reduced-motion: reduce) {
    .reporter-type-btn {
        transition: none;
    }
    .reporter-type-btn.active {
        transform: none;
    }
}
</style>
