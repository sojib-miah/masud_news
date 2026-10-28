<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";

const form = useForm({
    name: "",
    phone: "",
    password: "",
    password_confirmation: "",
});
</script>

<template>
    <Layout>
        <Head title="Motamot Registration" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div class="flex justify-center items-start px-3 py-10">
            <!-- Card -->
            <div
                class="font-robo border-2 border-green-600 rounded-xl shadow-lg shadow-green-300 w-full sm:w-[400px] md:w-[500px] lg:w-[600px]"
            >
                <h2
                    class="bg-green-600 text-center rounded-t-lg text-white p-3"
                >
                    রেজিস্ট্রেশন
                </h2>

                <form
                    class="p-4 sm:p-6"
                    @submit.prevent="form.post(route('motamot.store'))"
                >
                    <!-- Name -->
                    <div class="mb-3">
                        <TextInput
                            v-model="form.name"
                            type="text"
                            placeholder="পুরো নাম"
                            class="w-full focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.name"
                        />
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <TextInput
                            v-model="form.phone"
                            type="text"
                            placeholder="ফোন নম্বর"
                            class="w-full focus:shadow-md focus:border focus:border-green-500"
                            :error="form.errors.phone"
                        />
                    </div>

                    <!-- Password & Confirm Password -->
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

                    <!-- Submit Button -->
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
