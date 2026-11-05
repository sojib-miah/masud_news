<script setup>
import Layout from "@/Components/FrontEnd/Motamot/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    role: String,
    motamots: Object,
});
</script>

<template>
    <Head title="Motamot Dashboard" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <!-- ==================== PAGE HEADER ==================== -->
        <div
            class="mb-5 sm:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
        >
            <div>
                <h1
                    class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800"
                >
                    Motamot Dashboard
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Overview of your motamot activity
                </p>
            </div>

            <Link
                :href="route('motamot.index')"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition w-full sm:w-auto"
            >
                <i class="fa-solid fa-list"></i>
                <span>View All Motamots</span>
            </Link>
        </div>

        <!-- ==================== STAT CARDS ==================== -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6"
        >
            <!-- Total Motamots Card -->
            <div
                class="relative rounded-2xl shadow-lg overflow-hidden text-white group one"
            >
                <!-- Decorative circle -->
                <div
                    class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full pointer-events-none"
                ></div>
                <div
                    class="absolute -right-4 -bottom-10 w-24 h-24 bg-white/10 rounded-full pointer-events-none"
                ></div>

                <div class="relative p-5 sm:p-6 flex flex-col gap-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p
                                class="text-xs sm:text-sm font-medium uppercase tracking-wider opacity-90"
                            >
                                Total Motamots
                            </p>
                            <h2
                                class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mt-1 leading-none"
                            >
                                {{ props.motamots?.total ?? 0 }}
                            </h2>
                        </div>

                        <div
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0"
                        >
                            <i
                                class="fa-solid fa-newspaper text-xl sm:text-2xl"
                            ></i>
                        </div>
                    </div>

                    <Link
                        :href="route('motamot.index')"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white text-teal-700 text-sm font-bold hover:bg-teal-50 transition w-full sm:w-auto"
                    >
                        <span>View All</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </Link>
                </div>
            </div>
        </div>

        <!-- ==================== EMPTY STATE ==================== -->
        <div
            v-if="!props.motamots?.total"
            class="mt-6 sm:mt-8 bg-white border border-slate-200 rounded-2xl p-8 sm:p-12 text-center"
        >
            <div
                class="w-16 h-16 mx-auto rounded-full bg-teal-50 text-teal-500 flex items-center justify-center mb-4"
            >
                <i class="fa-solid fa-inbox text-2xl"></i>
            </div>
            <p class="text-slate-700 font-semibold">No motamots yet</p>
            <p class="text-sm text-slate-500 mt-1">
                Your motamot activity will appear here once available.
            </p>
        </div>
    </Layout>
</template>

<style scoped>
.one {
    background: linear-gradient(135deg, #14b8a6 0%, #0f9f95 100%);
}
h2 {
    margin-bottom: 0 !important;
}
</style>
