<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";

const form = useForm({
    name: "",
    password: "",
});
</script>

<template>
    <Layout>
        <Head title="Login Page" />

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
                    প্রবেশ করুন
                </h2>

                <!-- Form -->
                <form
                    class="p-4 sm:p-6 bg-center bg-no-repeat bg-contain"
                    @submit.prevent="
                        form.post(route('login.store'), {
                            preserveScroll: true,
                        })
                    "
                >
                    <!-- Email / Phone -->
                    <div class="mb-3">
                        <TextInput
                            label="ফোন নম্বর"
                            v-model="form.name"
                            type="text"
                            placeholder="ফোন নম্বর..."
                            :error="form.errors.name"
                        />
                        <!-- ইমেইল ঠিকানা বা  -->
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <TextInput
                            label="পাসওয়ার্ড"
                            v-model="form.password"
                            type="password"
                            placeholder="পাসওয়ার্ড..."
                            :error="form.errors.password"
                        />
                    </div>

                    <!-- Options -->
                    <div
                        class="mb-3 flex flex-col sm:flex-row sm:justify-between gap-2"
                    >
                        <label class="flex items-center gap-2">
                            <input type="checkbox" v-model="form.remember" />
                            সংরক্ষণ রাখুন
                        </label>

                        <Link
                            :href="route('password.request')"
                            class="text-green-400 text-sm"
                        >
                            পাসওয়ার্ড ভুলে গেছেন ?
                        </Link>
                    </div>

                    <!-- Button -->
                    <button
                        type="submit"
                        class="bg-green-600 p-3 w-full rounded text-white hover:bg-green-700 transition"
                        :disabled="form.processing"
                        :class="{ 'btn-spinner': form.processing }"
                    >
                        {{ form.processing ? "প্রবেশ করুন..." : "প্রবেশ করুন" }}
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
/* style="
    background-image: url(&quot;/storage/images/amarlogo.png&quot;);
    opacity: 0.1;
" */
</style>
