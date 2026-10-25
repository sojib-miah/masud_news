<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import SelectInput from "@/Shared/SelectInput.vue";
import FileInput from "@/Shared/FileInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";
import { ref, watch, onMounted } from "vue";
import axios from "axios";

const props = defineProps({
    user: Object,
    profile: Object,
    bloodGroups: Array,
    educations: Array,
    educationals: Array,
    divisions: Array,
    reporterAreas: Array,
    reporterTypes: Array,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone,
    dob: props.profile.dob,
    blood_group: props.profile.blood_group,
    father_name: props.profile.father_name,
    mother_name: props.profile.mother_name,
    address: props.profile.address,
    education: props.profile.education,
    education_year: props.profile.education_year,

    division: props.profile.division ? Number(props.profile.division) : null,
    district: props.profile.district ? Number(props.profile.district) : null,
    upazila: props.profile.upazila ? Number(props.profile.upazila) : null,
    union: props.profile.union ? Number(props.profile.union) : null,

    reporter_area: props.profile.reporter_area,
    nid_number: props.profile.nid_number,
    photo: null,
    signature: null,
    nid_file: null,
    reporter_types: props.profile.reporter_types
        ? props.profile.reporter_types.split(",")
        : [],
});

const districts = ref([]);
const upazilas = ref([]);
const unions = ref([]);

let isInit = true;

watch(
    () => form.division,
    async (id) => {
        if (!isInit) {
            form.district = null;
            form.upazila = null;
            form.union = null;
        }
        if (id) {
            districts.value = (await axios.get(`/districts/${id}`)).data;
        }
    },
);

watch(
    () => form.district,
    async (id) => {
        if (!isInit) {
            form.upazila = null;
            form.union = null;
        }
        if (id) {
            upazilas.value = (await axios.get(`/upazilas/${id}`)).data;
        }
    },
);

watch(
    () => form.upazila,
    async (id) => {
        if (!isInit) {
            form.union = null;
        }
        if (id) {
            unions.value = (await axios.get(`/unions/${id}`)).data;
        }
    },
);

onMounted(async () => {
    if (form.division) {
        districts.value = (await axios.get(`/districts/${form.division}`)).data;
    }
    if (form.district) {
        upazilas.value = (await axios.get(`/upazilas/${form.district}`)).data;
    }
    if (form.upazila) {
        unions.value = (await axios.get(`/unions/${form.upazila}`)).data;
    }

    isInit = false;
});
</script>

<template>
    <Layout>
        <Head title="Reporter Update" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <div class="flex justify-center w-[60%] m-auto bg-white shadow rounded">
            <form
                class="p-6 w-full"
                @submit.prevent="
                    form.post(route('reporter.update', props.user.id), {
                        forceFormData: true,
                    })
                "
            >
                <h2 class="text-2xl font-bold text-center">Update Profile</h2>
                <div class="mb-3 text-start">
                    <TextInput
                        v-model="form.name"
                        placeholder="পুরো নাম"
                        :error="form.errors.name"
                        @update:modelValue="form.clearErrors('name')"
                    />

                    <small class="text-xs text-red-300"
                        >! অনুগ্রহ করে আপনার নাম শুধুমাত্র বাংলা অক্ষরে
                        লিখুন।</small
                    >
                </div>
                <div class="mb-3 flex gap-3">
                    <div class="w-[50%]">
                        <TextInput
                            v-model="form.email"
                            type="email"
                            placeholder="ইমেল ঠিকানা"
                            :error="form.errors.email"
                            @update:modelValue="form.clearErrors('email')"
                        />
                    </div>
                    <div class="w-[50%]">
                        <TextInput
                            v-model="form.phone"
                            placeholder="ফোন নম্বর"
                            :error="form.errors.phone"
                            @update:modelValue="form.clearErrors('phone')"
                        />
                    </div>
                </div>
                <div class="mb-3 flex gap-3">
                    <div class="w-[50%]">
                        <input
                            v-model="form.dob"
                            type="text"
                            placeholder="জন্ম তারিখ"
                            onfocus="this.type = 'date'"
                            onblur="if (!this.value) this.type = 'text';"
                            class="border p-2 w-full rounded border-slate-300 focus:shadow-md focus:border focus:border-green-500"
                        />
                        <br />
                        <span class="text-red-500" v-if="form.errors.dob">{{
                            form.errors.dob
                        }}</span>
                    </div>
                    <div class="w-[50%]">
                        <SelectInput
                            v-model="form.blood_group"
                            placeholder="রক্তের গ্রুপ"
                            :options="
                                bloodGroups.map((bg) => ({
                                    label: bg.name,
                                    value: bg.name,
                                }))
                            "
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.blood_group"
                        />
                    </div>
                </div>
                <div class="mb-3 flex gap-3">
                    <div class="w-[50%]">
                        <TextInput
                            v-model="form.father_name"
                            type="text"
                            placeholder="পিতার নাম"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.father_name"
                        />
                    </div>
                    <div class="w-[50%]">
                        <TextInput
                            v-model="form.mother_name"
                            type="text"
                            placeholder="মাতার নাম"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.mother_name"
                        />
                    </div>
                </div>
                <div class="mb-3">
                    <TextInput
                        v-model="form.address"
                        type="text"
                        placeholder="ঠিকানা"
                        class="focus:shadow-md focus:border focus:border-green-500"
                        :error="form.errors.address"
                    />
                </div>
                <div class="mb-3 flex gap-3">
                    <div class="w-[50%]">
                        <SelectInput
                            v-model="form.education"
                            :options="
                                educations.map((e) => ({
                                    label: e.name,
                                    value: e.name,
                                }))
                            "
                            placeholder="শিক্ষাগত যোগ্যতা"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.education"
                        />
                    </div>
                    <div class="w-[50%]">
                        <SelectInput
                            v-model="form.education_year"
                            placeholder="শিক্ষাবর্ষ"
                            :options="
                                educationals.map((e) => ({
                                    label: e.year,
                                    value: e.year,
                                }))
                            "
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.education_year"
                        />
                    </div>
                </div>

                <div class="mb-3 flex gap-3">
                    <div class="w-[50%]">
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
                    <div class="w-[50%]">
                        <SelectInput
                            v-model="form.district"
                            :options="
                                districts.map((d) => ({
                                    label: d.bn_name,
                                    value: d.id,
                                }))
                            "
                            placeholder="জেলা"
                            :error="form.errors.district"
                        />
                    </div>
                </div>

                <div class="mb-3 flex gap-3">
                    <div class="w-[50%]">
                        <SelectInput
                            v-model="form.upazila"
                            :options="
                                upazilas.map((u) => ({
                                    label: u.bn_name,
                                    value: u.id,
                                }))
                            "
                            placeholder="উপজেলা"
                            :error="form.errors.upazila"
                        />
                    </div>
                    <div class="w-[50%]">
                        <SelectInput
                            v-model="form.union"
                            :options="
                                unions.map((u) => ({
                                    label: u.bn_name,
                                    value: u.id,
                                }))
                            "
                            placeholder="ইউনিয়ন"
                            :error="form.errors.union"
                        />
                    </div>
                </div>
                <div class="mb-3 flex gap-3">
                    <div class="w-[50%]">
                        <TextInput
                            v-model="form.password"
                            type="password"
                            placeholder="পাসওয়ার্ড"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.password"
                        />
                    </div>
                    <div class="w-[50%]">
                        <TextInput
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="পাসওয়ার্ড নিশ্চিত করুন"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.password_confirmation"
                        />
                    </div>
                </div>
                <div class="mb-3">
                    <SelectInput
                        v-model="form.reporter_area"
                        :options="
                            reporterAreas.map((r) => ({
                                label: r.name,
                                value: r.name,
                            }))
                        "
                        placeholder="রিপোর্টার এরিয়া"
                        class="focus:shadow-md focus:border focus:border-green-500"
                        :error="form.errors.reporter_area"
                    />
                </div>
                <div class="mb-3 font-robo">
                    <h2 class="text-2xl font-bold">রিপোর্টার টাইপ</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="type in reporterTypes" :key="type.id">
                            <label class="form-label font-bold">
                                <input
                                    type="checkbox"
                                    :value="type.id"
                                    v-model="form.reporter_types"
                                />
                                {{ type.name }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-4 flex gap-3">
                    <div class="w-[50%]">
                        <FileInput
                            v-model="form.signature"
                            label="স্বাক্ষর আপলোড করুন"
                            accept="image/*"
                            :multiple="false"
                            :error="form.errors.signature"
                        />
                        <img
                            v-if="profile.signature"
                            :src="`/storage/${profile.signature}`"
                            class="w-20 h-20 mt-2 border border-slate-300"
                            alt="Signature preview"
                        />
                    </div>
                    <div class="w-[50%]">
                        <FileInput
                            v-model="form.photo"
                            label="ছবি আপলোড"
                            accept="image/*"
                            :multiple="false"
                            :error="form.errors.photo"
                        />
                        <img
                            v-if="profile.photo"
                            :src="`/storage/${profile.photo}`"
                            class="w-20 h-20 mt-2 border border-slate-300"
                            alt="Signature preview"
                        />
                    </div>
                </div>
                <div class="mb-3 flex gap-3 items-center">
                    <div class="w-[50%]">
                        <FileInput
                            v-model="form.nid_file"
                            label="আপলোড এনআইডি/পাসপোর্ট/জন্ম নিবন্ধন"
                            accept="image/*"
                            :multiple="false"
                            :error="form.errors.nid_file"
                        />
                        <img
                            v-if="profile.nid_file"
                            :src="`/storage/${profile.nid_file}`"
                            class="w-20 h-20 mt-2 border border-slate-300"
                            alt="Signature preview"
                        />
                    </div>
                    <div class="w-[50%]">
                        <TextInput
                            v-model="form.nid_number"
                            type="text"
                            placeholder="এনআইডি নম্বর"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.nid_number"
                        />
                    </div>
                </div>
                <p>
                    বিঃ দ্রঃ: আপনার এনআইডি কার্ডের সামনের ও পিছনের ছবি 'এক সাথে
                    আপলোড' করুন।
                </p>
                <div>
                    <button
                        type="submit"
                        class="bg-green-600 p-3 w-full rounded text-white hover:bg-green-700"
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
                </div>
            </form>
        </div>
    </Layout>
</template>
