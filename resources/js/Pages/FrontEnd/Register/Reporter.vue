<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import FileInput from "@/Shared/FileInput.vue";
import { route } from "ziggy-js";
import { ref, watch } from "vue";
import axios from "axios";

const props = defineProps({
    bloodGroups: Array,
    educations: Array,
    educationals: Array,
    divisions: Array,
    reporterAreas: Array,
    reporterTypes: Array,
    siteSetting: Boolean,
});

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
    reporter_area: "",
    nid_number: "",
    signature: null,
    photo: null,
    nid_file: null,
    password: "",
    password_confirmation: "",
    reporter_types: [],
});

const districts = ref([]);
const upazilas = ref([]);
const unions = ref([]);

watch(
    () => form.division,
    async (divisionId) => {
        form.district = "";
        form.upazila = "";
        form.union = "";
        if (divisionId) {
            const res = await axios.get(`/districts/${divisionId}`);
            districts.value = res.data;
        } else {
            districts.value = [];
        }
    },
);

watch(
    () => form.district,
    async (districtId) => {
        form.upazila = "";
        form.union = "";
        if (districtId) {
            const res = await axios.get(`/upazilas/${districtId}`);
            upazilas.value = res.data;
        } else {
            upazilas.value = [];
        }
    },
);

watch(
    () => form.upazila,
    async (upazilaId) => {
        form.union = "";
        if (upazilaId) {
            const res = await axios.get(`/unions/${upazilaId}`);
            unions.value = res.data;
        } else {
            unions.value = [];
        }
    },
);

const selectedReporterType = ref(null);

// Handle change
function toggleReporterType(typeId) {
    // If clicked again, unselect
    if (selectedReporterType.value === typeId) {
        selectedReporterType.value = null;
    } else {
        selectedReporterType.value = typeId;
    }
}
</script>

<template>
    <Layout>
        <Head title="Reporter Registation" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="my-10 flex justify-center items-start px-3 py-10">
            <!-- Card -->
            <div
                class="font-robo border-2 border-green-600 rounded-xl shadow shadow-green-500 w-full sm:w-[400px] md:w-[600px] lg:w-[800px]"
            >
                <h2
                    class="bg-green-600 text-center rounded-t-lg text-white p-3"
                >
                    রেজিস্ট্রেশন
                </h2>

                <div v-if="$page.props.site?.register_status">
                    <form
                        class="p-4 sm:p-6"
                        @submit.prevent="
                            form.post(route('reporter.store'), {
                                forceFormData: true,
                            })
                        "
                    >
                        <!-- Name -->
                        <div class="mb-3 text-start">
                            <TextInput
                                v-model="form.name"
                                type="text"
                                placeholder="পুরো নাম"
                                class="focus:shadow-md focus:border focus:border-green-500 w-full"
                                :error="form.errors.name"
                            />
                            <small class="text-xs text-red-300">
                                ! অনুগ্রহ করে আপনার নাম শুধুমাত্র বাংলা অক্ষরে
                                লিখুন।
                            </small>
                        </div>

                        <!-- Email & Phone -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <TextInput
                                v-model="form.email"
                                type="email"
                                placeholder="ইমেল ঠিকানা"
                                class="focus:shadow-md focus:border focus:border-green-500 w-full"
                                :error="form.errors.email"
                            />
                            <TextInput
                                v-model="form.phone"
                                type="text"
                                placeholder="ফোন নম্বর"
                                class="focus:shadow-md focus:border focus:border-green-500 w-full"
                                :error="form.errors.phone"
                            />
                        </div>

                        <!-- DOB & Blood Group -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <input
                                v-model="form.dob"
                                type="text"
                                placeholder="জন্ম তারিখ"
                                onfocus="this.type = 'date'"
                                onblur="if (!this.value) this.type = 'text';"
                                class="border p-2 w-full rounded border-slate-300 focus:shadow-md focus:border focus:border-green-500"
                            />
                            <SelectInput
                                v-model="form.blood_group"
                                placeholder="রক্তের গ্রুপ"
                                :options="
                                    bloodGroups.map((bg) => ({
                                        label: bg.name,
                                        value: bg.name,
                                    }))
                                "
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.blood_group"
                            />
                        </div>

                        <!-- Parents -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <TextInput
                                v-model="form.father_name"
                                type="text"
                                placeholder="পিতার নাম"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.father_name"
                            />
                            <TextInput
                                v-model="form.mother_name"
                                type="text"
                                placeholder="মাতার নাম"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.mother_name"
                            />
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <TextInput
                                v-model="form.address"
                                type="text"
                                placeholder="ঠিকানা"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.address"
                            />
                        </div>

                        <!-- Education & Year -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <SelectInput
                                v-model="form.education"
                                :options="
                                    educations.map((e) => ({
                                        label: e.name,
                                        value: e.name,
                                    }))
                                "
                                placeholder="শিক্ষাগত যোগ্যতা"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.education"
                            />
                            <SelectInput
                                v-model="form.education_year"
                                :options="
                                    educationals.map((e) => ({
                                        label: e.year,
                                        value: e.year,
                                    }))
                                "
                                placeholder="শিক্ষাবর্ষ"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.education_year"
                            />
                        </div>

                        <!-- Division & District -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <SelectInput
                                v-model="form.division"
                                :options="
                                    divisions.map((d) => ({
                                        label: d.bn_name,
                                        value: d.id,
                                    }))
                                "
                                placeholder="বিভাগ"
                                class="w-full"
                                :error="form.errors.division"
                            />
                            <SelectInput
                                v-model="form.district"
                                :options="
                                    districts.map((d) => ({
                                        label: d.bn_name,
                                        value: d.id,
                                    }))
                                "
                                placeholder="জেলা"
                                class="w-full"
                                :error="form.errors.district"
                            />
                        </div>

                        <!-- Upazila & Union -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <SelectInput
                                v-model="form.upazila"
                                :options="
                                    upazilas.map((u) => ({
                                        label: u.bn_name,
                                        value: u.id,
                                    }))
                                "
                                placeholder="উপজেলা"
                                class="w-full"
                                :error="form.errors.upazila"
                            />
                            <SelectInput
                                v-model="form.union"
                                :options="
                                    unions.map((u) => ({
                                        label: u.bn_name,
                                        value: u.id,
                                    }))
                                "
                                placeholder="ইউনিয়ন"
                                class="w-full"
                                :error="form.errors.union"
                            />
                        </div>

                        <!-- Passwords -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <TextInput
                                v-model="form.password"
                                type="password"
                                placeholder="পাসওয়ার্ড"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.password"
                            />
                            <TextInput
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="পাসওয়ার্ড নিশ্চিত করুন"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.password_confirmation"
                            />
                        </div>

                        <!-- Reporter Area -->
                        <div class="mb-3">
                            <SelectInput
                                v-model="form.reporter_area"
                                :options="
                                    reporterAreas.map((r) => ({
                                        label: r.name,
                                        value: r.id,
                                    }))
                                "
                                placeholder="রিপোর্টার এরিয়া"
                                class="w-full focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.reporter_area"
                            />
                        </div>

                        <!-- Reporter Types -->
                        <div class="mb-4">
                            <h2 class="text-xl font-bold mb-2">
                                রিপোর্টার টাইপ
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div
                                    v-for="type in props.reporterTypes"
                                    :key="type.id"
                                >
                                    <label class="font-bold">
                                        <input
                                            type="checkbox"
                                            :value="type.id"
                                            :checked="
                                                selectedReporterType === type.id
                                            "
                                            :disabled="
                                                selectedReporterType !== null &&
                                                selectedReporterType !== type.id
                                            "
                                            @change="
                                                toggleReporterType(type.id)
                                            "
                                        />
                                        {{ type.name }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Uploads -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <FileInput
                                v-model="form.signature"
                                label="স্বাক্ষর আপলোড করুন"
                                accept="image/*"
                                :error="form.errors.signature"
                            />
                            <FileInput
                                v-model="form.photo"
                                label="ছবি আপলোড"
                                accept="image/*"
                                :error="form.errors.photo"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <FileInput
                                v-model="form.nid_file"
                                label="আপলোড এনআইডি/পাসপোর্ট/জন্ম নিবন্ধন"
                                accept="image/*"
                                :error="form.errors.nid_file"
                            />
                            <TextInput
                                v-model="form.nid_number"
                                label="এনআইডি নম্বর"
                                type="text"
                                placeholder="এনআইডি নম্বর"
                                class="w-full mt-1 focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.nid_number"
                            />
                        </div>

                        <p class="text-sm mb-3">
                            বিঃ দ্রঃ: আপনার এনআইডি কার্ডের সামনের ও পিছনের ছবি
                            'এক সাথে আপলোড' করুন।
                        </p>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="bg-green-600 p-3 w-full rounded text-white hover:bg-green-700 transition"
                            :class="{ 'btn-spinner': form.processing }"
                            :disabled="form.processing"
                        >
                            <span>
                                {{
                                    form.processing
                                        ? "প্রেরণ করুন..."
                                        : "প্রেরণ করুন"
                                }}
                            </span>
                            <span v-if="form.processing" class="spinner"></span>
                        </button>
                    </form>
                </div>

                <!-- Payment Message -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-10"
                >
                    <p>Free সময় শেষ। 1200 টাকা পেমেন্ট করুন</p>
                    <button @click="payNow" class="btn rounded mt-3">
                        Pay Now
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>
