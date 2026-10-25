<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";
import SelectInput from "@/Shared/SelectInput.vue";
import { computed } from "vue";

const props = defineProps({
    divisions: Array,
    districts: Array,
    upazilas: Array,
    unions: Array,
});

const params = new URLSearchParams(window.location.search);

const ref = params.get("ref");

const form = useForm({
    name: "",
    phone: "",
    email: "",
    division_id: "",
    district_id: "",
    upazilla_id: "",
    union_id: "",
    village: "",
    occupation: "",
    password: "",
    password_confirmation: "",

    ref: ref,
});

// Filter Districts
const filteredDistricts = computed(() => {
    return props.districts.filter(
        (district) => district.division_id == form.division_id,
    );
});

// Filter Upazilas
const filteredUpazilas = computed(() => {
    return props.upazilas.filter(
        (upazila) => upazila.district_id == form.district_id,
    );
});

// Filter Unions
const filteredUnions = computed(() => {
    return props.unions.filter(
        (union) => union.upazilla_id == form.upazilla_id,
    );
});
</script>

<template>
    <Layout>
        <Head title="Pathock Registation" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <!-- Center Wrapper -->
        <div class="my-10 flex justify-center items-center px-3">
            <!-- Card -->
            <div
                class="font-robo border-2 border-green-600 rounded-xl shadow shadow-green-500 w-full sm:w-[400px] md:w-[500px] lg:w-[600px]"
            >
                <!-- Header -->
                <h2
                    class="bg-green-600 text-center rounded-t-lg text-white p-3"
                >
                    রেজিস্ট্রেশন
                </h2>

                <!-- Form -->
                <form
                    class="p-4 sm:p-6"
                    @submit.prevent="form.post(route('pathock.store'))"
                >
                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">পুরো নাম</label>
                        <TextInput
                            v-model="form.name"
                            type="text"
                            placeholder="পুরো নাম"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.name"
                        />
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">ফোন নম্বর</label>
                        <TextInput
                            v-model="form.phone"
                            type="text"
                            placeholder="ফোন নম্বর"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.phone"
                        />
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">ইমেইল</label>
                        <TextInput
                            v-model="form.email"
                            type="email"
                            placeholder="ইমেইল (ঐচ্ছিক)"
                            class="focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.email"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <!-- Division -->
                        <div>
                            <label for="division_id" class="form-label"
                                >Division</label
                            >
                            <select
                                v-model="form.division_id"
                                class="w-full border border-slate-400 shadow rounded p-2"
                            >
                                <option value="">Division Select করুন</option>

                                <option
                                    v-for="division in divisions"
                                    :key="division.id"
                                    :value="division.id"
                                >
                                    {{ division.name }}
                                </option>
                            </select>

                            <div
                                v-if="form.errors.division_id"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.division_id }}
                            </div>
                        </div>

                        <!-- District -->
                        <div>
                            <label class="form-label">District</label>
                            <select
                                v-model="form.district_id"
                                class="w-full border border-slate-400 shadow rounded p-2"
                            >
                                <option value="">District Select করুন</option>

                                <option
                                    v-for="district in filteredDistricts"
                                    :key="district.id"
                                    :value="district.id"
                                >
                                    {{ district.name }}
                                </option>
                            </select>

                            <div
                                v-if="form.errors.district_id"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.district_id }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="form-label">Upazila</label>
                            <select
                                v-model="form.upazilla_id"
                                class="w-full border border-slate-400 shadow rounded p-2"
                            >
                                <option value="">Upazila Select করুন</option>

                                <option
                                    v-for="upazila in filteredUpazilas"
                                    :key="upazila.id"
                                    :value="upazila.id"
                                >
                                    {{ upazila.name }}
                                </option>
                            </select>

                            <div
                                v-if="form.errors.upazilla_id"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.upazilla_id }}
                            </div>
                        </div>

                        <!-- Union -->
                        <div>
                            <label for="union_id" class="form-label"
                                >Union</label
                            >
                            <select
                                v-model="form.union_id"
                                class="w-full border border-slate-400 shadow rounded p-2"
                            >
                                <option value="">Union Select করুন</option>

                                <option
                                    v-for="union in filteredUnions"
                                    :key="union.id"
                                    :value="union.id"
                                >
                                    {{ union.name }}
                                </option>
                            </select>

                            <div
                                v-if="form.errors.union_id"
                                class="text-red-500 text-sm"
                            >
                                {{ form.errors.union_id }}
                            </div>
                        </div>
                    </div>
                    <!-- Village -->
                    <div class="mb-3">
                        <label class="form-label">গ্রাম</label>
                        <TextInput
                            v-model="form.village"
                            type="text"
                            placeholder="গ্রাম"
                            :error="form.errors.village"
                        />
                    </div>

                    <!-- Occupation -->
                    <div class="mb-3">
                        <label class="form-label">পেশা</label>
                        <TextInput
                            v-model="form.occupation"
                            type="text"
                            placeholder="পেশা"
                            :error="form.errors.occupation"
                        />
                    </div>
                    <!-- Password Section -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="form-label">পাসওয়ার্ড</label>
                            <TextInput
                                v-model="form.password"
                                type="password"
                                placeholder="পাসওয়ার্ড"
                                class="focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.password"
                            />
                        </div>

                        <div>
                            <label class="form-label"
                                >পাসওয়ার্ড নিশ্চিত করুন</label
                            >
                            <TextInput
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="পাসওয়ার্ড নিশ্চিত করুন"
                                class="focus:shadow-md focus:border focus:border-green-500"
                                :error="form.errors.password_confirmation"
                            />
                        </div>
                    </div>

                    <!-- Button -->
                    <div>
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
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
h2 {
    font-size: 24px;
    font-weight: 600;
}
</style>
