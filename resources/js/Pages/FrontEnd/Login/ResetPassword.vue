<script setup>
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Layout from "@/Components/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import TextInput from "@/Shared/TextInput.vue";

defineProps({ token: String });

const form = useForm({
    email: "",
    password: "",
    password_confirmation: "",
    token: "",
});
</script>

<template>
    <Layout>
        <Head title="ResetPassword Page" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <!-- Center Wrapper -->
        <div class="my-10 flex justify-center items-center px-3">
            <!-- Card -->
            <div
                class="font-robo border-2 border-green-600 rounded-xl shadow shadow-green-500 w-full sm:w-[400px] md:w-[450px] lg:w-[500px]"
            >
                <!-- Header -->
                <h2
                    class="bg-green-600 text-center rounded-t-lg text-white p-3"
                >
                    পাসওয়ার্ড পরিবর্তন করুন
                </h2>

                <!-- Form -->
                <form
                    @submit.prevent="form.post(route('password.update'))"
                    class="p-4 sm:p-6"
                >
                    <!-- Email -->
                    <div class="mb-3">
                        <TextInput
                            v-model="form.email"
                            type="email"
                            placeholder="ইমেইল ঠিকানা বা ফোন"
                        />
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <TextInput
                            v-model="form.password"
                            type="password"
                            placeholder="নতুন পাসওয়ার্ড"
                        />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <TextInput
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="পাসওয়ার্ড নিশ্চিত করুন"
                        />
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="bg-green-600 p-3 w-full rounded text-white hover:bg-green-700 transition"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? "পাসওয়ার্ড পরিবর্তন করুন..."
                                : "পাসওয়ার্ড পরিবর্তন করুন"
                        }}
                        <span v-if="form.processing" class="spinner"></span>
                    </button>
                </form>
            </div>
        </div>
    </Layout>
</template>
