<script setup>
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { route } from "ziggy-js";
import axios from "axios";

const props = defineProps({
    user: { type: Object, required: true },
    profile: { type: Object, default: null },
    divisions: { type: Array, default: () => [] },
});

const page = usePage();

// Pick the best human-readable label from an option row.
// Works whether backend sends `name` or `bn_name`.
function optionLabel(row) {
    if (!row) return "";
    return row.bn_name || row.name || String(row.id ?? "");
}

// ==================== FORM ====================
const form = useForm({
    name: props.user.name || "",
    email: props.user.email || "",
    phone: props.user.phone || "",

    division_id: props.profile?.division_id || "",
    district_id: props.profile?.district_id || "",
    upazilla_id: props.profile?.upazilla_id || "",
    union_id: props.profile?.union_id || "",
    city_corporation_id: props.profile?.city_corporation_id || "",
    ward_id: props.profile?.ward_id || "",

    village: props.profile?.village || "",
    occupation: props.profile?.occupation || "",

    old_password: "",
    password: "",
    password_confirmation: "",
    image: null,
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
    if (form.city_corporation_id) return "city_corporation";
    if (form.district_id) return "district";
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
        (ctrl) => ctrl && ctrl.abort(),
    );
}
onBeforeUnmount(abortAll);

// ==================== FETCH HELPERS ====================
async function fetchDistricts(divisionId) {
    if (districtAbort) districtAbort.abort();
    districtAbort = new AbortController();
    loadingDistricts.value = true;
    try {
        const { data } = await axios.get(`/districts/${divisionId}`, {
            signal: districtAbort.signal,
        });
        districts.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("districts", e);
    } finally {
        loadingDistricts.value = false;
    }
}

async function fetchCityCorporations(divisionId) {
    if (cityCorpAbort) cityCorpAbort.abort();
    cityCorpAbort = new AbortController();
    loadingCityCorps.value = true;
    try {
        const { data } = await axios.get(`/city-corporations/${divisionId}`, {
            signal: cityCorpAbort.signal,
        });
        cityCorporations.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("city-corporations", e);
    } finally {
        loadingCityCorps.value = false;
    }
}

async function fetchUpazilas(districtId) {
    if (upazilaAbort) upazilaAbort.abort();
    upazilaAbort = new AbortController();
    loadingUpazilas.value = true;
    try {
        const { data } = await axios.get(`/upazilas/${districtId}`, {
            signal: upazilaAbort.signal,
        });
        upazilas.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("upazilas", e);
    } finally {
        loadingUpazilas.value = false;
    }
}

async function fetchUnions(upazilaId) {
    if (unionAbort) unionAbort.abort();
    unionAbort = new AbortController();
    loadingUnions.value = true;
    try {
        const { data } = await axios.get(`/unions/${upazilaId}`, {
            signal: unionAbort.signal,
        });
        unions.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("unions", e);
    } finally {
        loadingUnions.value = false;
    }
}

async function fetchWards(cityCorpId) {
    if (wardAbort) wardAbort.abort();
    wardAbort = new AbortController();
    loadingWards.value = true;
    try {
        const { data } = await axios.get(`/wards/${cityCorpId}`, {
            signal: wardAbort.signal,
        });
        wards.value = data;
    } catch (e) {
        if (!axios.isCancel(e)) console.error("wards", e);
    } finally {
        loadingWards.value = false;
    }
}

// ==================== INITIAL LOAD ====================
onMounted(async () => {
    const tasks = [];

    if (form.division_id) {
        tasks.push(fetchDistricts(form.division_id));
        tasks.push(fetchCityCorporations(form.division_id));
    }
    if (form.district_id) {
        tasks.push(fetchUpazilas(form.district_id));
    }
    if (form.upazilla_id) {
        tasks.push(fetchUnions(form.upazilla_id));
    }
    if (form.city_corporation_id) {
        tasks.push(fetchWards(form.city_corporation_id));
    }

    await Promise.all(tasks);
    initializing.value = false;
});

// ==================== CASCADE WATCHERS ====================
watch(
    () => form.division_id,
    async (id) => {
        if (initializing.value) return;

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
        await Promise.all([fetchDistricts(id), fetchCityCorporations(id)]);
    },
);

watch(
    () => form.district_id,
    async (id) => {
        if (initializing.value) return;

        form.upazilla_id = "";
        form.union_id = "";
        upazilas.value = [];
        unions.value = [];

        if (id) {
            form.city_corporation_id = "";
            form.ward_id = "";
            wards.value = [];
        }

        if (!id) return;
        await fetchUpazilas(id);
    },
);

watch(
    () => form.upazilla_id,
    async (id) => {
        if (initializing.value) return;

        form.union_id = "";
        unions.value = [];

        if (!id) return;
        await fetchUnions(id);
    },
);

watch(
    () => form.city_corporation_id,
    async (id) => {
        if (initializing.value) return;

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
        await fetchWards(id);
    },
);

// ==================== IMAGE PREVIEW ====================
const imagePreview = ref(null);
const fileInputRef = ref(null);

const currentAvatar = computed(() => {
    if (imagePreview.value) return imagePreview.value;
    if (props.user.image) return `/storage/${props.user.image}`;
    return "/images/default.jpg";
});

function onImageChange(event) {
    const file = event.target.files?.[0] || null;
    form.image = file;

    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
    if (file) {
        imagePreview.value = URL.createObjectURL(file);
    }
}

// ==================== SHOW PASSWORD ====================
const showPasswordFields = ref(false);

// ==================== SUBMIT ====================
function submit() {
    form.post(route("pathock.profile.update"), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset(
                "old_password",
                "password",
                "password_confirmation",
                "image",
            );
            if (imagePreview.value) {
                URL.revokeObjectURL(imagePreview.value);
                imagePreview.value = null;
            }
            if (fileInputRef.value) fileInputRef.value.value = "";
            showPasswordFields.value = false;
        },
    });
}

// ==================== OPTION BUILDERS ====================
const divisionOptions = computed(() =>
    props.divisions.map((d) => ({ label: optionLabel(d), value: d.id })),
);
const districtOptions = computed(() =>
    districts.value.map((d) => ({ label: optionLabel(d), value: d.id })),
);
const upazilaOptions = computed(() =>
    upazilas.value.map((u) => ({ label: optionLabel(u), value: u.id })),
);
const unionOptions = computed(() =>
    unions.value.map((u) => ({ label: optionLabel(u), value: u.id })),
);
const cityCorporationOptions = computed(() =>
    cityCorporations.value.map((c) => ({ label: optionLabel(c), value: c.id })),
);
const wardOptions = computed(() =>
    wards.value.map((w) => ({ label: optionLabel(w), value: w.id })),
);
</script>

<template>
    <Head title="Pathock Profile Update" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <div class="profile-wrap">
            <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-6">
                <div class="profile-card">
                    <header class="profile-header">
                        <div class="header-icon">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <div>
                            <h1 class="header-title">প্রোফাইল আপডেট</h1>
                            <p class="header-subtitle">
                                আপনার তথ্য হালনাগাদ করুন
                            </p>
                        </div>
                    </header>

                    <form class="profile-form" @submit.prevent="submit">
                        <!-- AVATAR -->
                        <section class="avatar-section">
                            <div class="avatar-wrap">
                                <img
                                    class="avatar-img"
                                    :src="currentAvatar"
                                    alt="Profile"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                />
                                <label
                                    class="avatar-upload"
                                    for="avatar-upload"
                                    title="ছবি পরিবর্তন করুন"
                                    aria-label="ছবি পরিবর্তন করুন"
                                >
                                    <i class="fa-solid fa-camera"></i>
                                </label>
                                <input
                                    id="avatar-upload"
                                    ref="fileInputRef"
                                    type="file"
                                    accept="image/*"
                                    class="avatar-input"
                                    @change="onImageChange"
                                />
                            </div>
                            <p v-if="form.errors.image" class="avatar-error">
                                {{ form.errors.image }}
                            </p>
                        </section>

                        <!-- BASIC INFO -->
                        <section>
                            <h3 class="section-title">
                                <i class="fa-solid fa-id-card"></i>
                                ব্যক্তিগত তথ্য
                            </h3>
                            <div class="field-grid">
                                <div class="field full">
                                    <TextInput
                                        id="name"
                                        label="পূর্ণ নাম"
                                        v-model="form.name"
                                        :error="form.errors.name"
                                    />
                                </div>
                                <div class="field">
                                    <TextInput
                                        id="email"
                                        label="ইমেইল"
                                        type="email"
                                        v-model="form.email"
                                        :error="form.errors.email"
                                    />
                                </div>
                                <div class="field">
                                    <TextInput
                                        id="phone"
                                        label="ফোন নম্বর"
                                        v-model="form.phone"
                                        :error="form.errors.phone"
                                    />
                                </div>
                            </div>
                        </section>

                        <!-- LOCATION -->
                        <section>
                            <h3 class="section-title">
                                <i class="fa-solid fa-location-dot"></i>
                                ঠিকানা
                            </h3>

                            <div class="field-grid">
                                <div class="field full">
                                    <label class="field-label">বিভাগ</label>
                                    <SelectInput
                                        v-model="form.division_id"
                                        :options="divisionOptions"
                                        placeholder="বিভাগ নির্বাচন করুন"
                                        :error="form.errors.division_id"
                                    />
                                </div>
                            </div>

                            <p v-if="form.division_id" class="mode-hint">
                                <i class="fa-solid fa-info-circle"></i>
                                জেলা <strong>অথবা</strong> সিটি কর্পোরেশন —
                                যেকোনো একটি বেছে নিন
                            </p>

                            <!-- DISTRICT PATH -->
                            <div
                                v-if="
                                    form.division_id &&
                                    locationMode !== 'city_corporation'
                                "
                                class="path-box district"
                            >
                                <p class="path-label">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                    জেলা ভিত্তিক ঠিকানা
                                </p>
                                <div class="field-grid three">
                                    <div class="field">
                                        <label class="field-label">জেলা</label>
                                        <SelectInput
                                            v-model="form.district_id"
                                            :options="districtOptions"
                                            :placeholder="
                                                loadingDistricts
                                                    ? 'লোড হচ্ছে...'
                                                    : 'জেলা'
                                            "
                                            :error="form.errors.district_id"
                                        />
                                    </div>
                                    <div class="field">
                                        <label class="field-label"
                                            >উপজেলা</label
                                        >
                                        <SelectInput
                                            v-model="form.upazilla_id"
                                            :options="upazilaOptions"
                                            :placeholder="
                                                loadingUpazilas
                                                    ? 'লোড হচ্ছে...'
                                                    : 'উপজেলা'
                                            "
                                            :error="form.errors.upazilla_id"
                                        />
                                    </div>
                                    <div class="field">
                                        <label class="field-label"
                                            >ইউনিয়ন</label
                                        >
                                        <SelectInput
                                            v-model="form.union_id"
                                            :options="unionOptions"
                                            :placeholder="
                                                loadingUnions
                                                    ? 'লোড হচ্ছে...'
                                                    : 'ইউনিয়ন'
                                            "
                                            :error="form.errors.union_id"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- CITY CORPORATION PATH -->
                            <div
                                v-if="
                                    form.division_id &&
                                    locationMode !== 'district'
                                "
                                class="path-box city"
                            >
                                <p class="path-label">
                                    <i class="fa-solid fa-city"></i>
                                    সিটি কর্পোরেশন ভিত্তিক ঠিকানা
                                </p>
                                <div class="field-grid">
                                    <div class="field">
                                        <label class="field-label"
                                            >সিটি কর্পোরেশন</label
                                        >
                                        <SelectInput
                                            v-model="form.city_corporation_id"
                                            :options="cityCorporationOptions"
                                            :placeholder="
                                                loadingCityCorps
                                                    ? 'লোড হচ্ছে...'
                                                    : 'সিটি কর্পোরেশন'
                                            "
                                            :error="
                                                form.errors.city_corporation_id
                                            "
                                        />
                                    </div>
                                    <div class="field">
                                        <label class="field-label"
                                            >ওয়ার্ড</label
                                        >
                                        <SelectInput
                                            v-model="form.ward_id"
                                            :options="wardOptions"
                                            :placeholder="
                                                loadingWards
                                                    ? 'লোড হচ্ছে...'
                                                    : 'ওয়ার্ড'
                                            "
                                            :error="form.errors.ward_id"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="field-grid mt-4">
                                <div class="field">
                                    <TextInput
                                        id="village"
                                        label="গ্রাম / এলাকা"
                                        v-model="form.village"
                                        :error="form.errors.village"
                                    />
                                </div>
                                <div class="field">
                                    <TextInput
                                        id="occupation"
                                        label="পেশা"
                                        v-model="form.occupation"
                                        :error="form.errors.occupation"
                                    />
                                </div>
                            </div>
                        </section>

                        <!-- PASSWORD -->
                        <section>
                            <div class="section-header-row">
                                <h3 class="section-title">
                                    <i class="fa-solid fa-lock"></i>
                                    পাসওয়ার্ড
                                </h3>
                                <button
                                    type="button"
                                    class="toggle-pwd-btn"
                                    @click="
                                        showPasswordFields = !showPasswordFields
                                    "
                                >
                                    <i
                                        :class="
                                            showPasswordFields
                                                ? 'fa-solid fa-eye-slash'
                                                : 'fa-solid fa-eye'
                                        "
                                    ></i>
                                    {{
                                        showPasswordFields
                                            ? "লুকান"
                                            : "পাসওয়ার্ড পরিবর্তন করুন"
                                    }}
                                </button>
                            </div>

                            <p v-if="!showPasswordFields" class="pwd-hint">
                                <i class="fa-solid fa-info-circle"></i>
                                পাসওয়ার্ড পরিবর্তন করতে উপরের বাটনে ক্লিক করুন
                            </p>

                            <transition name="slide-fade">
                                <div
                                    v-if="showPasswordFields"
                                    class="field-grid three"
                                >
                                    <div class="field">
                                        <TextInput
                                            id="old_password"
                                            label="পুরানো পাসওয়ার্ড"
                                            type="password"
                                            v-model="form.old_password"
                                            :error="form.errors.old_password"
                                        />
                                    </div>
                                    <div class="field">
                                        <TextInput
                                            id="password"
                                            label="নতুন পাসওয়ার্ড"
                                            type="password"
                                            v-model="form.password"
                                            :error="form.errors.password"
                                        />
                                    </div>
                                    <div class="field">
                                        <TextInput
                                            id="password_confirmation"
                                            label="নিশ্চিত করুন"
                                            type="password"
                                            v-model="form.password_confirmation"
                                            :error="
                                                form.errors
                                                    .password_confirmation
                                            "
                                        />
                                    </div>
                                </div>
                            </transition>
                        </section>

                        <!-- SUBMIT -->
                        <div class="form-footer">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="submit-btn"
                            >
                                <i
                                    v-if="form.processing"
                                    class="fa-solid fa-spinner fa-spin"
                                ></i>
                                <i v-else class="fa-solid fa-floppy-disk"></i>
                                {{
                                    form.processing
                                        ? "সংরক্ষণ হচ্ছে..."
                                        : "আপডেট করুন"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* ==================== WRAPPER ==================== */
.profile-wrap {
    font-family: "Hind Siliguri", "Roboto", sans-serif;
}

.profile-card {
    max-width: 780px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 12px 32px rgba(0, 0, 0, 0.08);
}

/* ==================== HEADER ==================== */
.profile-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    background: linear-gradient(135deg, #00930c 0%, #00b515 100%);
    color: #fff;
}

.header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.18);
    font-size: 1.1rem;
    flex-shrink: 0;
}

.header-title {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    line-height: 1.3;
}

.header-subtitle {
    margin: 2px 0 0;
    font-size: 0.82rem;
    opacity: 0.85;
}

@media (min-width: 640px) {
    .profile-header {
        padding: 22px 26px;
    }
    .header-title {
        font-size: 1.3rem;
    }
}

/* ==================== FORM ==================== */
.profile-form {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 22px;
}

@media (min-width: 640px) {
    .profile-form {
        padding: 26px;
        gap: 26px;
    }
}

/* ==================== AVATAR ==================== */
.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-bottom: 18px;
    border-bottom: 1px solid #e2e8f0;
}

.avatar-wrap {
    position: relative;
    width: 100px;
    height: 100px;
}

.avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow:
        0 0 0 2px #00930c,
        0 4px 14px rgba(0, 147, 12, 0.2);
}

@media (min-width: 640px) {
    .avatar-wrap {
        width: 116px;
        height: 116px;
    }
}

.avatar-upload {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.4);
    border: 2px solid #fff;
    transition: transform 0.2s ease;
}

.avatar-upload:hover {
    transform: scale(1.08);
}

.avatar-input {
    display: none;
}

.avatar-error {
    color: #dc2626;
    font-size: 0.78rem;
    margin-top: 8px;
}

/* ==================== SECTION ==================== */
.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f1f5f9;
    color: #0f172a;
    font-size: 0.92rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.section-title i {
    color: #00930c;
    font-size: 0.95rem;
}

.section-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f1f5f9;
}

.section-header-row .section-title {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

/* ==================== FIELD GRID ==================== */
.field-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}

.field-grid.three {
    grid-template-columns: 1fr;
}

@media (min-width: 640px) {
    .field-grid {
        grid-template-columns: 1fr 1fr;
    }
    .field-grid.three {
        grid-template-columns: repeat(3, 1fr);
    }
}

.field {
    min-width: 0;
}
.field.full {
    grid-column: 1 / -1;
}

.field-label {
    display: block;
    margin-bottom: 4px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #334155;
}

/* ==================== MODE HINT ==================== */
.mode-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 12px 0;
    padding: 8px 12px;
    background: #f0fdf4;
    color: #065f46;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 0.8rem;
}

.mode-hint i {
    color: #00930c;
}

/* ==================== PATH BOX ==================== */
.path-box {
    margin-top: 14px;
    padding: 14px;
    border-radius: 10px;
    border: 1px solid;
}

.path-box.district {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.path-box.city {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.path-label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0 0 12px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.path-box.district .path-label {
    color: #006b09;
}
.path-box.city .path-label {
    color: #1d4ed8;
}

/* ==================== TOGGLE PASSWORD ==================== */
.toggle-pwd-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f0fdf4;
    color: #00930c;
    border: 1px solid #bbf7d0;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    font-family: inherit;
}

.toggle-pwd-btn:hover {
    background: #dcfce7;
}

.pwd-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    margin: 0;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.82rem;
    border-radius: 8px;
    border: 1px dashed #cbd5e1;
}

.pwd-hint i {
    color: #00930c;
    flex-shrink: 0;
}

/* ==================== SUBMIT ==================== */
.form-footer {
    padding-top: 18px;
    border-top: 1px solid #e2e8f0;
}

.submit-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px 20px;
    background: linear-gradient(135deg, #00930c 0%, #00b515 100%);
    color: #fff;
    font-size: 0.92rem;
    font-weight: 700;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(0, 147, 12, 0.28);
    transition:
        transform 0.2s,
        box-shadow 0.2s;
    font-family: inherit;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 10px 26px rgba(0, 147, 12, 0.35);
}

.submit-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

/* ==================== TRANSITION ==================== */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 360px) {
    .profile-header {
        padding: 14px 16px;
        gap: 10px;
    }
    .header-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    .header-title {
        font-size: 1.02rem;
    }
    .header-subtitle {
        font-size: 0.74rem;
    }

    .profile-form {
        padding: 16px;
        gap: 18px;
    }

    .avatar-wrap {
        width: 88px;
        height: 88px;
    }

    .section-title {
        font-size: 0.84rem;
    }
    .field-label {
        font-size: 0.78rem;
    }
    .field-grid {
        gap: 12px;
    }

    .submit-btn {
        padding: 12px 16px;
        font-size: 0.86rem;
    }
}
</style>
