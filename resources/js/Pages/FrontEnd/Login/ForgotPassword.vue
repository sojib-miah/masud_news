<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import Layout from "@/Components/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import TextInput from "@/Shared/TextInput.vue";

const form = useForm({ email: "" });
</script>

<template>
    <Layout>
        <Head title="ForgotPassword Page" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <!-- Center Wrapper -->
        <div class="my-10 flex justify-center items-center px-3">
            <!-- Card -->
            <div
                class="font-robo border-2 border-green-600 rounded-xl shadow-lg shadow-green-300 w-full sm:w-[400px] md:w-[450px] lg:w-[500px]"
            >
                <!-- Header -->
                <h2
                    class="bg-green-600 text-center rounded-t-lg text-white p-3"
                >
                    পাসওয়ার্ড ভুলে গেছেন
                </h2>

                <!-- Form -->
                <form
                    @submit.prevent="form.post(route('password.email'))"
                    class="p-4 sm:p-6"
                >
                    <!-- Email -->
                    <div class="mb-4">
                        <TextInput
                            v-model="form.email"
                            type="email"
                            placeholder="ফোন নম্বর..."
                            label="ফোন নম্বর"
                        />
                    </div>

                    <!-- Login Link -->
                    <div class="flex justify-end mb-4">
                        <Link
                            :href="route('user.login')"
                            class="text-green-400 text-sm"
                        >
                            প্রবেশ করুন
                        </Link>
                    </div>

                    <!-- Button -->
                    <button
                        type="submit"
                        class="bg-green-600 p-3 w-full rounded text-white hover:bg-green-700 transition"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "প্রেরণ করুন..." : "প্রেরণ করুন" }}
                        <span v-if="form.processing" class="spinner"></span>
                    </button>
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
