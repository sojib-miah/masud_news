<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";

const props = defineProps({
    user: { type: Object, required: true },
    profile: { type: Object, default: null },
    bloodGroups: { type: Array, default: () => [] },
    educations: { type: Array, default: () => [] },
    educationals: { type: Array, default: () => [] },
    divisions: { type: Array, default: () => [] },
    reporterAreas: { type: Array, default: () => [] },
    reporterTypes: { type: Array, default: () => [] },
});

// Safe number coercion: "" | null | undefined -> null
function toNum(v) {
    if (v === "" || v === null || v === undefined) return null;
    const n = Number(v);
    return Number.isNaN(n) ? null : n;
}

// Safe split for comma-separated reporter_types
function splitTypes(v) {
    if (!v || typeof v !== "string") return [];
    return v
        .split(",")
        .map((s) => s.trim())
        .filter(Boolean)
        .map(Number);
}

// Option label helper (works with bn_name or name)
function label(row) {
    return row?.bn_name || row?.name || String(row?.id ?? "");
}

// ==================== FORM ====================
const form = useForm({
    name: props.user.name || "",
    email: props.user.email || "",
    phone: props.user.phone || "",

    dob: props.profile?.dob || "",
    blood_group: props.profile?.blood_group || "",
    father_name: props.profile?.father_name || "",
    mother_name: props.profile?.mother_name || "",
    address: props.profile?.address || "",

    education: props.profile?.education || "",
    education_year: props.profile?.education_year || "",

    division: toNum(props.profile?.division),
    district: toNum(props.profile?.district),
    upazila: toNum(props.profile?.upazila),
    union: toNum(props.profile?.union),
    city_corporation: toNum(props.profile?.city_corporation),
    ward: toNum(props.profile?.ward),

    reporter_area: props.profile?.reporter_area || "",
    nid_number: props.profile?.nid_number || "",

    photo: null,
    signature: null,
    nid_file: null,

    reporter_types: splitTypes(props.profile?.reporter_types),

    password: "",
    password_confirmation: "",
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

// Location mode: city corporation vs district path
const locationMode = computed(() => {
    if (form.city_corporation) return "city_corporation";
    if (form.district) return "district";
    return "";
});

// ==================== REQUEST GUARD ====================
const initializing = ref(true);
let districtAbort = null;
let cityCorpAbort = null;
let upazilaAbort = null;
let unionAbort = null;
let wardAbort = null;

function abortAll() {
    [districtAbort, cityCorpAbort, upazilaAbort, unionAbort, wardAbort].forEach(
        (c) => c && c.abort(),
    );
}
onBeforeUnmount(abortAll);

// ==================== FETCHERS ====================
async function fetchDistricts(id) {
    if (districtAbort) districtAbort.abort();
    districtAbort = new AbortController();
    loadingDistricts.value = true;
    try {
        const { data } = await axios.get(`/districts/${id}`, {
            signal: districtAbort.signal,
        });
        districts.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("districts", e);
    } finally {
        loadingDistricts.value = false;
    }
}

async function fetchCityCorporations(id) {
    if (cityCorpAbort) cityCorpAbort.abort();
    cityCorpAbort = new AbortController();
    loadingCityCorps.value = true;
    try {
        const { data } = await axios.get(`/city-corporations/${id}`, {
            signal: cityCorpAbort.signal,
        });
        cityCorporations.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("city-corporations", e);
    } finally {
        loadingCityCorps.value = false;
    }
}

async function fetchUpazilas(id) {
    if (upazilaAbort) upazilaAbort.abort();
    upazilaAbort = new AbortController();
    loadingUpazilas.value = true;
    try {
        const { data } = await axios.get(`/upazilas/${id}`, {
            signal: upazilaAbort.signal,
        });
        upazilas.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("upazilas", e);
    } finally {
        loadingUpazilas.value = false;
    }
}

async function fetchUnions(id) {
    if (unionAbort) unionAbort.abort();
    unionAbort = new AbortController();
    loadingUnions.value = true;
    try {
        const { data } = await axios.get(`/unions/${id}`, {
            signal: unionAbort.signal,
        });
        unions.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("unions", e);
    } finally {
        loadingUnions.value = false;
    }
}

async function fetchWards(id) {
    if (wardAbort) wardAbort.abort();
    wardAbort = new AbortController();
    loadingWards.value = true;
    try {
        const { data } = await axios.get(`/wards/${id}`, {
            signal: wardAbort.signal,
        });
        wards.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("wards", e);
    } finally {
        loadingWards.value = false;
    }
}

// ==================== INITIAL PREFILL ====================
onMounted(async () => {
    const tasks = [];
    if (form.division) {
        tasks.push(fetchDistricts(form.division));
        tasks.push(fetchCityCorporations(form.division));
    }
    if (form.district) tasks.push(fetchUpazilas(form.district));
    if (form.upazila) tasks.push(fetchUnions(form.upazila));
    if (form.city_corporation) tasks.push(fetchWards(form.city_corporation));

    await Promise.all(tasks);
    initializing.value = false;
});

// ==================== CASCADE WATCHERS ====================
watch(
    () => form.division,
    async (id) => {
        if (initializing.value) return;

        form.district = null;
        form.upazila = null;
        form.union = null;
        form.city_corporation = null;
        form.ward = null;

        districts.value = [];
        upazilas.value = [];
        unions.value = [];
        cityCorporations.value = [];
        wards.value = [];

        if (!id) return;
        await Promise.all([fetchDistricts(id), fetchCityCorporations(id)]);
    },
);

watch(
    () => form.district,
    async (id) => {
        if (initializing.value) return;

        form.upazila = null;
        form.union = null;
        upazilas.value = [];
        unions.value = [];

        if (id) {
            form.city_corporation = null;
            form.ward = null;
            wards.value = [];
        }
        if (!id) return;
        await fetchUpazilas(id);
    },
);

watch(
    () => form.upazila,
    async (id) => {
        if (initializing.value) return;

        form.union = null;
        unions.value = [];

        if (!id) return;
        await fetchUnions(id);
    },
);

watch(
    () => form.city_corporation,
    async (id) => {
        if (initializing.value) return;

        form.ward = null;
        wards.value = [];

        if (id) {
            form.district = null;
            form.upazila = null;
            form.union = null;
            upazilas.value = [];
            unions.value = [];
        }
        if (!id) return;
        await fetchWards(id);
    },
);

// ==================== OPTION BUILDERS ====================
const divisionOptions = computed(() =>
    props.divisions.map((d) => ({ label: label(d), value: d.id })),
);
const districtOptions = computed(() =>
    districts.value.map((d) => ({ label: label(d), value: d.id })),
);
const upazilaOptions = computed(() =>
    upazilas.value.map((u) => ({ label: label(u), value: u.id })),
);
const unionOptions = computed(() =>
    unions.value.map((u) => ({ label: label(u), value: u.id })),
);
const cityCorporationOptions = computed(() =>
    cityCorporations.value.map((c) => ({ label: label(c), value: c.id })),
);
const wardOptions = computed(() =>
    wards.value.map((w) => ({ label: label(w), value: w.id })),
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
    form.post(route("reporter.update", props.user.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset(
                "password",
                "password_confirmation",
                "photo",
                "signature",
                "nid_file",
            );
        },
    });
}
</script>

<template>
    <Layout>
        <Head title="Reporter Profile Update" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="py-10 px-3 flex justify-center items-start">
            <div
                class="w-full max-w-[820px] bg-white border border-green-600 rounded-2xl shadow-xl overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 text-center"
                >
                    <h2 class="text-white text-xl sm:text-2xl font-bold">
                        প্রোফাইল আপডেট
                    </h2>
                    <p class="text-green-100 text-xs sm:text-sm mt-1">
                        আপনার তথ্য হালনাগাদ করুন
                    </p>
                </div>

                <form
                    class="p-4 sm:p-6 lg:p-8 space-y-5"
                    @submit.prevent="submit"
                >
                    <!-- ==================== BASIC INFO ==================== -->
                    <section>
                        <h3
                            class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                        >
                            <i class="fa-solid fa-user mr-2"></i>ব্যক্তিগত তথ্য
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    পুরো নাম <span class="text-red-500">*</span>
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
                                    ইমেইল <span class="text-red-500">*</span>
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
                                        props.bloodGroups.map((bg) => ({
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
                                        props.educations.map((e) => ({
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
                                        props.educationals.map((e) => ({
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
                            <i class="fa-solid fa-location-dot mr-2"></i>ঠিকানা
                            (বিস্তারিত)
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    বিভাগ <span class="text-red-500">*</span>
                                </label>
                                <SelectInput
                                    v-model="form.division"
                                    :options="divisionOptions"
                                    placeholder="বিভাগ"
                                    :error="form.errors.division"
                                />
                            </div>

                            <div v-if="form.division" class="flex items-end">
                                <p class="text-xs text-slate-500 italic pb-2.5">
                                    <i
                                        class="fa-solid fa-info-circle text-green-600"
                                    ></i>
                                    জেলা <strong>অথবা</strong> সিটি কর্পোরেশন —
                                    যেকোনো একটি বেছে নিন
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

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >জেলা</label
                                    >
                                    <SelectInput
                                        v-model="form.district"
                                        :options="districtOptions"
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
                                        :options="upazilaOptions"
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
                                        :options="unionOptions"
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
                            v-if="form.division && locationMode !== 'district'"
                            class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200"
                        >
                            <p class="text-xs font-semibold text-blue-700 mb-3">
                                <i class="fa-solid fa-city mr-1"></i>
                                সিটি কর্পোরেশন ভিত্তিক ঠিকানা
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >সিটি কর্পোরেশন</label
                                    >
                                    <SelectInput
                                        v-model="form.city_corporation"
                                        :options="cityCorporationOptions"
                                        :placeholder="
                                            loadingCityCorps
                                                ? 'লোড হচ্ছে...'
                                                : 'সিটি কর্পোরেশন'
                                        "
                                        :error="form.errors.city_corporation"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 mb-1"
                                        >ওয়ার্ড</label
                                    >
                                    <SelectInput
                                        v-model="form.ward"
                                        :options="wardOptions"
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div class="sm:col-span-2">
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    রিপোর্টার এরিয়া
                                    <span class="text-red-500">*</span>
                                </label>
                                <SelectInput
                                    v-model="form.reporter_area"
                                    :options="
                                        props.reporterAreas.map((r) => ({
                                            label: r.name,
                                            value: r.name,
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
                            <i class="fa-solid fa-id-badge mr-2"></i>রিপোর্টার
                            টাইপ
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
                                v-for="type in props.reporterTypes"
                                :key="type.id"
                                type="button"
                                class="reporter-type-btn"
                                :class="{
                                    active: selectedReporterType === type.id,
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

                        <p class="text-xs text-slate-500 mb-3">
                            <i
                                class="fa-solid fa-info-circle text-green-600"
                            ></i>
                            পাসওয়ার্ড পরিবর্তন না করতে চাইলে খালি রাখুন
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    নতুন পাসওয়ার্ড
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

                    <!-- ==================== DOCUMENTS ==================== -->
                    <section>
                        <h3
                            class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                        >
                            <i class="fa-solid fa-file-arrow-up mr-2"></i
                            >প্রয়োজনীয় কাগজপত্র
                        </h3>

                        <p class="text-xs text-slate-500 mb-3">
                            <i
                                class="fa-solid fa-info-circle text-green-600"
                            ></i>
                            ফাইল পরিবর্তন করতে না চাইলে খালি রাখুন — বর্তমান
                            ফাইল সংরক্ষিত থাকবে
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    প্রোফাইল ছবি
                                </label>
                                <FileInput
                                    v-model="form.photo"
                                    accept="image/*"
                                    :multiple="false"
                                    :error="form.errors.photo"
                                />
                                <a
                                    v-if="props.profile?.photo"
                                    :href="`/storage/${props.profile.photo}`"
                                    target="_blank"
                                    class="inline-block"
                                >
                                    <img
                                        v-if="props.profile?.photo"
                                        :src="`/storage/${props.profile.photo}`"
                                        class="w-20 h-20 mt-2 border border-slate-300 rounded"
                                        alt="Photo"
                                    />
                                </a>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    স্বাক্ষর
                                </label>
                                <FileInput
                                    v-model="form.signature"
                                    accept="image/*"
                                    :multiple="false"
                                    :error="form.errors.signature"
                                />
                                <a
                                    v-if="props.profile?.signature"
                                    :href="`/storage/${props.profile.signature}`"
                                    target="_blank"
                                    class="inline-block"
                                >
                                    <img
                                        v-if="props.profile?.signature"
                                        :src="`/storage/${props.profile.signature}`"
                                        class="w-20 h-20 mt-2 border border-slate-300 rounded"
                                        alt="Signature"
                                    />
                                </a>
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    এনআইডি / পাসপোর্ট / জন্ম নিবন্ধন
                                </label>
                                <FileInput
                                    v-model="form.nid_file"
                                    accept="image/*,application/pdf"
                                    :multiple="false"
                                    :error="form.errors.nid_file"
                                />
                                <a
                                    v-if="props.profile?.nid_file"
                                    :href="`/storage/${props.profile.nid_file}`"
                                    target="_blank"
                                    class="inline-block"
                                >
                                    <img
                                        v-if="props.profile?.nid_file"
                                        :src="`/storage/${props.profile.nid_file}`"
                                        class="w-20 h-20 mt-2 border border-slate-300 rounded"
                                        alt="NID File"
                                    />
                                </a>
                                <p class="text-xs text-slate-500 mt-1">
                                    <i
                                        class="fa-solid fa-info-circle text-green-600"
                                    ></i>
                                    এনআইডি কার্ডের সামনের ও পিছনের ছবি একসাথে
                                    আপলোড করুন (PDF বা ছবি)
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
                                    ? "আপডেট হচ্ছে..."
                                    : "প্রোফাইল আপডেট করুন"
                            }}
                        </button>
                    </div>
                </form>
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
