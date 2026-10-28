<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import { route } from "ziggy-js";
</script>

<template>
    <Layout>
        <Head title="Registation Page" />

        <!-- Center Wrapper -->
        <div class="my-10 flex justify-center items-center px-3 font-robo">
            <!-- Card -->
            <div
                class="text-center border-2 border-green-600 p-5 sm:p-6 md:p-8 rounded-xl shadow-lg shadow-green-300 w-full sm:w-[400px] md:w-[600px] lg:w-[700px]"
            >
                <!-- Title -->
                <h2 class="text-green-600 text-lg sm:text-xl font-bold mb-4">
                    রেজিস্ট্রেশন করুন
                </h2>

                <!-- Options -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4 text-green-600"
                >
                    <Link
                        :href="route('pathock.register')"
                        class="p-3 border-2 border-green-600 rounded-md bg-green-50 w-full hover:bg-green-100 transition"
                    >
                        পাঠক
                    </Link>

                    <Link
                        :href="route('reporter.register')"
                        class="p-3 border-2 border-green-600 rounded-md bg-green-50 w-full hover:bg-green-100 transition"
                    >
                        রিপোর্টার/প্রতিবেদক
                    </Link>

                    <Link
                        :href="route('motamot.register')"
                        class="p-3 border-2 border-green-600 rounded-md bg-green-50 w-full hover:bg-green-100 transition"
                    >
                        মতামত/Business Idea
                    </Link>
                </div>

                <!-- Description -->
                <p class="text-sm text-green-600">
                    আপনি যে ধরনের রেজিস্ট্রেশন করতে চান তা নির্বাচন করুন।
                </p>
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
